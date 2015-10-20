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

namespace chalk\broadcaster;

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
        self::$instance = $this;
    }

    public function onDisable(){
        self::$instance = null;
    }

    /**
     * @return PocketTelegram
     */
    public static function getInstance(){
        return self::$instance;
    }



    /** @var string */
    private static $token = "", $channel = "";

    /** @var bool */
    private static $broadcastPlayerChats = false, $disableWebPagePreview = true, $enableMarkdownParsing = false, $debugMode = false;

    public function onEnable(){
        $this->saveDefaultConfig();
        self::$token = $this->getConfig()->get("token", "");
        self::$channel = $this->getConfig()->get("channel", "");

        if(self::$token === "" || self::$channel === ""){
            $this->getLogger()->alert("You need to set your configs to enable this plugin");
            $this->getLogger()->alert("-> " . $this->getDataFolder() . "config.yml");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }

        self::$broadcastPlayerChats = $this->getConfig()->get("broadcastPlayerChats", false);
        self::$disableWebPagePreview = $this->getConfig()->get("disableWebPagePreview", true);
        self::$enableMarkdownParsing = $this->getConfig()->get("enableMarkdownParsing", false);
        self::$debugMode = $this->getConfig()->get("debugMode", false);

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    /**
     * @param string $message
     */
    public static function debug($message){
        if(PocketTelegram::$debugMode){
            PocketTelegram::getInstance()->getLogger()->debug($message);
        }
    }





    public static function getBotToken(){
        return self::$token;
    }

    public static function getBaseURL(){
        return "https://api.telegram.org/bot" . self::$token . "/";
    }

    /**
     * @param string $message
     * @param string $channel
     */
    public static function sendMessage($message, $channel){
        if($message instanceof TranslationContainer){
            $message = Server::getInstance()->getLanguage()->translateString($message->getText(), $message->getParameters());
        }

        $query = [
            'chat_id' => $channel,
            'text' => TextFormat::clean($message)
        ];

        if(PocketTelegram::$enableMarkdownParsing) $query['parse_mode'] = "Markdown";
        if(PocketTelegram::$disableWebPagePreview) $query['disable_web_page_preview'] = "true";

        Server::getInstance()->getScheduler()->scheduleAsyncTask(new RequestTask(self::getBaseURL() . "sendMessage", $query));
    }






    public function onPlayerChat(PlayerChatEvent $event){
        PocketTelegram::handlePlayerEvents($event);
    }

    public function onPlayerJoin(PlayerJoinEvent $event){
        PocketTelegram::handlePlayerEvents($event);
    }

    public function onPlayerQuit(PlayerQuitEvent $event){
        PocketTelegram::handlePlayerEvents($event);
    }

    public function onPlayerDeath(PlayerDeathEvent $event){
        PocketTelegram::handlePlayerEvents($event);
    }

    public static function handlePlayerEvents(Event $event){
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

            default:
                return;
        }

        PocketTelegram::sendMessage($message, PocketTelegram::$channel);
    }
}