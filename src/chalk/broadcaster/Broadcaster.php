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

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;

class Broadcaster extends PluginBase implements Listener {
    /** @var Broadcaster */
    private static $instance = null;

    /** @var string */
    public static $token = "", $channel = "";

    /** @var bool */
    public static $broadcastPlayerChats = false, $disableWebPagePreview = true, $enableMarkdownParsing = false, $debugMode = false;

    public function onLoad(){
        self::$instance = $this;
    }

    public function onDisable(){
        self::$instance = null;
    }

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
     * @return Broadcaster
     */
    public static function getInstance(){
        return self::$instance;
    }

    /**
     * @param string $message
     * @param string $channel
     */
    public static function broadcast($message, $channel = ""){
        Server::getInstance()->getScheduler()->scheduleAsyncTask(new BroadcastTask($message, $channel));
    }

    public function onPlayerChat(PlayerChatEvent $event){
        if(Broadcaster::$broadcastPlayerChats and !$event->isCancelled()){
            Broadcaster::broadcast($this->getServer()->getLanguage()->translateString($event->getFormat(), [$event->getPlayer()->getName(), $event->getMessage()]));
        }
    }

    public function onPlayerJoin(PlayerJoinEvent $event){
        if(Broadcaster::$broadcastPlayerChats){
            Broadcaster::broadcast($event->getJoinMessage());
        }
    }

    public function onPlayerQuit(PlayerQuitEvent $event){
        if(Broadcaster::$broadcastPlayerChats){
            Broadcaster::broadcast($event->getQuitMessage());
        }
    }

    public function onPlayerDeath(PlayerDeathEvent $event){
        if(Broadcaster::$broadcastPlayerChats){
            Broadcaster::broadcast($event->getDeathMessage());
        }
    }
}