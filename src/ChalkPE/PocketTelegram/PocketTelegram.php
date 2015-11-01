<?php

/*
 * Copyright (C) 2015  ChalkPE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.

 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @author ChalkPE <chalkpe@gmail.com>
 * @since 2015-10-18 19:02
 */

namespace ChalkPE\PocketTelegram;

use ChalkPE\PocketTelegram\model\Chat;
use ChalkPE\PocketTelegram\model\message\Message;
use ChalkPE\PocketTelegram\model\message\TextMessage;
use ChalkPE\PocketTelegram\model\User;
use ChalkPE\PocketTelegram\task\GetUpdatesTask;
use ChalkPE\PocketTelegram\task\RequestTask;
use pocketmine\event\TranslationContainer;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class PocketTelegram extends PluginBase {
    /** @var PocketTelegram */
    private static $instance = null;

    public function onLoad(){
        PocketTelegram::$instance = $this;
    }

    public function onDisable(){
        PocketTelegram::$instance = null;
    }

    /**
     * @return PocketTelegram
     */
    public static function getInstance(){
        return PocketTelegram::$instance;
    }



    /** @var string */
    private static $token = "", $defaultChannel = "";

    /** @var int */
    private static $updateInterval = 20;

    /** @var User */
    public static $me = null;

    /** @var bool */
    public static $broadcastToTelegram = true, $broadcastTelegramMessages = true, $enableTelegramCommands = true;

    /** @var bool */
    private static $disableWebPagePreview = true, $enableMarkdownParsing = false;

    /** @var bool */
    private static $debugMode = false;

    public function onEnable(){
        $this->saveDefaultConfig();
        self::$token          = $this->getConfig()->get("token",          "");
        self::$defaultChannel = $this->getConfig()->get("defaultChannel", "");
        self::$updateInterval = $this->getConfig()->get("updateInterval", 20);

        if(self::$token === ""){
            $this->getLogger()->alert("You need to set your Telegram bot token to enable this plugin");
            $this->getLogger()->alert("-> " . $this->getDataFolder() . "config.yml");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }

        self::$broadcastToTelegram       = $this->getConfig()->get("broadcastToTelegram",       true);
        self::$broadcastTelegramMessages = $this->getConfig()->get("broadcastTelegramMessages", true);
        self::$enableTelegramCommands    = $this->getConfig()->get("enableTelegramCommands",    true);
        self::$disableWebPagePreview     = $this->getConfig()->get("disableWebPagePreview",     true);
        self::$enableMarkdownParsing     = $this->getConfig()->get("enableMarkdownParsing",     false);
        self::$debugMode                 = $this->getConfig()->get("debugMode",                 false);

        $handler = new EventHandler();
        $this->getServer()->getPluginManager()->registerEvents($handler, $this);
        $this->getServer()->getPluginManager()->subscribeToPermission(Server::BROADCAST_CHANNEL_USERS, $handler);

        PocketTelegram::getMe();
        PocketTelegram::getUpdates();

        if($this->getConfig()->get("broadcastServerStart", false) === true) PocketTelegram::sendMessage(PocketTelegram::translateString("pocketmine.server.start", [TextFormat::AQUA . $this->getServer()->getVersion()]) . PHP_EOL . PocketTelegram::translateString("pocketmine.server.defaultGameMode", [Server::getGamemodeString($this->getServer()->getGamemode())]) . PHP_EOL . PHP_EOL . "#" . $this->getServer()->getMotd(), self::$defaultChannel);
    }



    /**
     * @param string $message
     */
    public static function debug($message){
        if(PocketTelegram::getInstance() === null) return;
        if(PocketTelegram::$debugMode) PocketTelegram::getInstance()->getLogger()->debug($message);
    }

    /**
     * @param string|TranslationContainer $str
     * @param string[] $params
     * @return string
     */
    public static function translateString($str, array $params = null){
        if($str instanceof TranslationContainer){
            $params = $str->getParameters();
            $str = $str->getText();
        }

        return Server::getInstance()->getLanguage()->translateString($str, $params);
    }





    /**
     * @return string
     */
    public static function getBotToken(){
        return self::$token;
    }

    /**
     * @return string
     */
    public static function getBaseURL(){
        return "https://api.telegram.org/bot" . self::$token . "/";
    }

    /**
     * @return string
     */
    public static function getDefaultChannel(){
        return self::$defaultChannel;
    }

    /**
     * @param string $method
     * @param array $params
     * @param callable $callback
     */
    public static function request($method, $params, $callback = null){
        PocketTelegram::debug("Requesting " . $method . " - " . json_encode($params));
        Server::getInstance()->getScheduler()->scheduleAsyncTask(new RequestTask(PocketTelegram::getBaseURL() . $method, $params, $callback));
    }

    /**
     * @return User|null
     */
    public static function getMe(){
        if(PocketTelegram::$me === null){
            PocketTelegram::request("getMe", [], function($json){
                $result = json_decode($json, true);
                if(!isset($result['ok']) or $result['ok'] !== true) return;

                PocketTelegram::$me = User::create($result['result']);
            });
        }

        return PocketTelegram::$me;
    }

    /**
     * @param int $delay
     */
    public static function getUpdates($delay = -1){
        if($delay < 0) $delay = self::$updateInterval;
        if($delay < 0) return;

        Server::getInstance()->getScheduler()->scheduleDelayedTask(new GetUpdatesTask(), $delay);
    }

    /**
     * @param TextMessage|TranslationContainer|string $message
     * @param Chat|string $chatId
     * @param Message|int $replyToMessage
     */
    public static function sendMessage($message, $chatId, $replyToMessage = null){
        if(is_null($message) or $message === "" or is_null($chatId) or $chatId === "") return;

        if($message instanceof TextMessage){
            $message = $message->getText();
        }else if($message instanceof TranslationContainer){
            $message = PocketTelegram::translateString($message);
        }
        $message = TextFormat::clean($message);

        if($chatId instanceof Chat){
            $chatId = $chatId->getId();
        }

        if($replyToMessage instanceof Message){
            $replyToMessage = $replyToMessage->getMessageId();
        }

        while(true){
            $nextMessage = null;
            if(($len = mb_strlen($message, 'UTF-8')) >= 4096){
                $nextMessage = mb_substr($message, 4096, $len, 'UTF-8');
                $message     = mb_substr($message,    0, 4096, 'UTF-8');
            }

            $params = [
                'chat_id' => $chatId,
                'text'    => $message
            ];

            if(self::$enableMarkdownParsing) $params['parse_mode']               = "Markdown";
            if(self::$disableWebPagePreview) $params['disable_web_page_preview'] = "true";
            if(!is_null($replyToMessage))    $params['reply_to_message_id']      = $replyToMessage;

            PocketTelegram::request("sendMessage", $params);

            if(is_null($nextMessage) or $nextMessage === "") break;
            $message = $nextMessage;
        }
    }
}