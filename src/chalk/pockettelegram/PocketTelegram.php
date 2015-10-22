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

namespace chalk\pockettelegram;

use chalk\pockettelegram\event\TelegramMessageEvent;
use chalk\pockettelegram\model\Chat;
use chalk\pockettelegram\model\Message;
use chalk\pockettelegram\model\TextMessage;
use chalk\pockettelegram\model\User;
use chalk\pockettelegram\task\GetUpdatesTask;
use chalk\pockettelegram\task\RequestTask;
use pocketmine\event\Cancellable;
use pocketmine\event\Event;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\TranslationContainer;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class PocketTelegram extends PluginBase implements Listener {
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
    private static $broadcastToTelegram = true, $broadcastTelegramMessages = true, $enableTelegramCommands = true;

    /** @var bool */
    private static $disableWebPagePreview = true, $enableMarkdownParsing = false;

    /** @var bool */
    private static $debugMode = false;

    /** @var int[] */
    public static $lastCommand = [];

    public function onEnable(){
        $this->saveDefaultConfig();
        self::$token          = $this->getConfig()->get("token",          "");
        self::$defaultChannel = $this->getConfig()->get("defaultChannel", "");
        self::$updateInterval = $this->getConfig()->get("updateInterval", 20);

        if(self::$token === "" or self::$defaultChannel === ""){
            $this->getLogger()->alert("You need to set your configs to enable this plugin");
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

        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        PocketTelegram::getMe();
        PocketTelegram::getUpdates();
    }





    /**
     * @param string $message
     */
    public static function debug($message){
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
        if(is_null($message) or $message === "") return;

        if($message instanceof TextMessage){
            $message = $message->getText();
        }else if($message instanceof TranslationContainer){
            $message = PocketTelegram::translateString($message);
        }

        if($chatId instanceof Chat){
            $chatId = $chatId->getId();
        }

        if($replyToMessage instanceof Message){
            $replyToMessage = $replyToMessage->getMessageId();
        }

        $params = [
            'chat_id' => $chatId,
            'text'    => TextFormat::clean($message)
        ];

        if(self::$enableMarkdownParsing) $params['parse_mode']               = "Markdown";
        if(self::$disableWebPagePreview) $params['disable_web_page_preview'] = "true";
        if(!is_null($replyToMessage))    $params['reply_to_message_id']      = $replyToMessage;

        PocketTelegram::request("sendMessage", $params);
    }





    public function onTelegramMessage(TelegramMessageEvent $event){
        if(!self::$broadcastTelegramMessages) return;

        $message = $event->getMessage();
        if($message instanceof TextMessage){
            $text = $message->getText();
            if(self::$enableTelegramCommands and $text[0] === '/'){
                self::handleCommands($message);
                return;
            }

            if(!is_null($message->getFrom())){
                $username = $message->getFrom()->getUsername();
                if($username === "") return;

                if($message->getChat()->getId() === self::$defaultChannel){
                    Server::getInstance()->broadcastMessage(PocketTelegram::translateString("chat.type.text", [$username, $text]));
                }
            }
        }else{
            PocketTelegram::debug("Unknown type of message sent");
        }
    }

    /**
     * @param TextMessage $message
     */
    private static function handleCommands(TextMessage $message){
        $key = $message->getChat()->getId();
        if(!isset(self::$lastCommand[$key])) self::$lastCommand[$key] = 0;

        $diff = time() - self::$lastCommand[$key];
        self::$lastCommand[$key] = time();
        if($diff < 2){
            return;
        }

        $command = explode(' ', substr($message->getText(), 1));
        if(strpos($command[0], '@') !== false){
            $mainCommand = explode('@', $command[0]);

            if(!is_null($me = PocketTelegram::getMe()) and strToLower($mainCommand[1]) !== strToLower($me->getUsername())) return;
            $command[0] = $mainCommand[0];
        }

        switch(strToLower($command[0])){
            case "chat_id":
                PocketTelegram::sendMessage($message->getChat()->getId(), $message->getChat(), $message);
                break;

            case "online":
                $players = [];
                foreach(Server::getInstance()->getOnlinePlayers() as $player){
                    if($player->isOnline()) $players[] = $player->getDisplayName();
                }

                $str = PocketTelegram::translateString("commands.players.list", [count($players), Server::getInstance()->getMaxPlayers()]);
                PocketTelegram::sendMessage($str . PHP_EOL . implode(", " , $players), $message->getChat(), $message);
                break;
        }
    }

    public function onPlayerChat(PlayerChatEvent $event){
        self::handleEvents($event);
    }

    public function onPlayerJoin(PlayerJoinEvent $event){
        self::handleEvents($event);
    }

    public function onPlayerQuit(PlayerQuitEvent $event){
        self::handleEvents($event);
    }

    public function onPlayerDeath(PlayerDeathEvent $event){
        self::handleEvents($event);
    }

    private static function handleEvents(Event $event){
        if(!self::$broadcastToTelegram) return;
        if($event instanceof Cancellable and $event->isCancelled()) return;

        $message = null;
        switch(true){
            case $event instanceof PlayerChatEvent:
                $message = PocketTelegram::translateString($event->getFormat(), [$event->getPlayer()->getName(), $event->getMessage()]);
                break;

            case $event instanceof PlayerJoinEvent:
                $message = $event->getJoinMessage();
                break;

            case $event instanceof PlayerQuitEvent:
                $message = $event->getQuitMessage();
                break;

            case $event instanceof PlayerDeathEvent:
                $message = $event->getDeathMessage();
                break;

            default:
                return;
        }

        PocketTelegram::sendMessage($message, self::$defaultChannel);
    }
}