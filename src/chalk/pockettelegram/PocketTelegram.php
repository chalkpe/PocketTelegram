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
    private static $defaultChannel = "";

    /** @var bool */
    private static $broadcastPlayerChats = false, $disableWebPagePreview = true, $enableMarkdownParsing = false, $debugMode = false;

    /** @var int */
    private static $updateInterval = 20;

    public function onEnable(){
        $this->saveDefaultConfig();
        PocketTelegram::$token = $this->getConfig()->get("token", "");
        PocketTelegram::$defaultChannel = $this->getConfig()->get("defaultChannel", "");

        if(PocketTelegram::$token === "" or PocketTelegram::$defaultChannel === ""){
            $this->getLogger()->alert("You need to set your configs to enable this plugin");
            $this->getLogger()->alert("-> " . $this->getDataFolder() . "config.yml");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }

        PocketTelegram::$broadcastPlayerChats = $this->getConfig()->get("broadcastPlayerChats", false);
        PocketTelegram::$disableWebPagePreview = $this->getConfig()->get("disableWebPagePreview", true);
        PocketTelegram::$enableMarkdownParsing = $this->getConfig()->get("enableMarkdownParsing", false);
        PocketTelegram::$debugMode = $this->getConfig()->get("debugMode", false);
        PocketTelegram::$updateInterval = $this->getConfig()->get("updateInterval", 20);

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        PocketTelegram::getUpdates();
    }

    /**
     * @param string $message
     */
    public static function debug($message){
        if(PocketTelegram::$debugMode){
            PocketTelegram::getInstance()->getLogger()->info($message);
        }
    }





    /** @var string */
    private static $token = "";

    /** @var User */
    private static $me = null;

    /**
     * @return string
     */
    public static function getBotToken(){
        return PocketTelegram::$token;
    }

    /**
     * @return string
     */
    public static function getBaseURL(){
        return "https://api.telegram.org/bot" . PocketTelegram::$token . "/";
    }

    /**
     * @param string $method
     * @param array $params
     * @param callable $callback
     */
    public static function request($method, $params, $callback = null){
        self::debug("Requesting " . $method . "? " . json_encode($params));

        Server::getInstance()->getScheduler()->scheduleAsyncTask(new RequestTask(PocketTelegram::getBaseURL() . $method, $params, $callback));
    }

    /**
     * @return User
     */
    public static function getMe(){
        if(PocketTelegram::$me === null){
            PocketTelegram::request("getMe", [], function($result){
                PocketTelegram::$me = User::create(json_decode($result, true));
            });
        }

        return PocketTelegram::$me;
    }

    public static function getUpdates(){
        Server::getInstance()->getScheduler()->scheduleDelayedTask(new GetUpdatesTask(), PocketTelegram::$updateInterval);
    }

    /**
     * @param string $message
     * @param string $channel
     */
    public static function sendMessage($message, $channel){
        if(is_null($message)) return;

        if($message instanceof TranslationContainer){
            $message = Server::getInstance()->getLanguage()->translateString($message->getText(), $message->getParameters());
        }

        $params = [
            'chat_id' => $channel,
            'text' => TextFormat::clean($message)
        ];

        if(PocketTelegram::$enableMarkdownParsing) $query['parse_mode'] = "Markdown";
        if(PocketTelegram::$disableWebPagePreview) $query['disable_web_page_preview'] = "true";

        PocketTelegram::request("sendMessage", $params);
    }





    public static function handleEvents(Event $event){
        if(!PocketTelegram::$broadcastPlayerChats) return;
        if($event instanceof Cancellable and $event->isCancelled()) return;

        $message = null;
        switch(true){
            case $event instanceof PlayerChatEvent:
                $message = Server::getInstance()->getLanguage()->translateString($event->getFormat(), [$event->getPlayer()->getName(), $event->getMessage()]);
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

            case $event instanceof TelegramMessageEvent:
                $message = $event->getMessage();
                if($message instanceof TextMessage){
                    PocketTelegram::debug($message->getText());
                }
                return;

            default:
                return;
        }

        PocketTelegram::sendMessage($message, PocketTelegram::$defaultChannel);
    }
}