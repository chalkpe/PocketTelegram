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
use pocketmine\plugin\PluginBase;
use pocketmine\Server;

class Broadcaster extends PluginBase implements Listener {
    /** @var Broadcaster */
    private static $instance = null;

    /** @var string */
    public $token = "", $channel = "";

    /** @var bool */
    public $broadcastPlayerChats = false, $disableWebPagePreview = true, $enableMarkdownParsing = false, $debugMode = false;

    public function onLoad(){
        self::$instance = $this;
    }

    public function onDisable(){
        self::$instance = null;
    }

    public function onEnable(){
        $this->saveDefaultConfig();
        $this->token = $this->getConfig()->get("token", "");
        $this->channel = $this->getConfig()->get("channel", "");

        if($this->token === "" || $this->channel === ""){
            $this->getLogger()->alert("You need to set your configs to enable this plugin");
            $this->getLogger()->alert("-> " . $this->getDataFolder() . "config.yml");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }

        $this->broadcastPlayerChats = $this->getConfig()->get("broadcastPlayerChats", false);
        $this->disableWebPagePreview = $this->getConfig()->get("disableWebPagePreview", true);
        $this->enableMarkdownParsing = $this->getConfig()->get("enableMarkdownParsing", false);
        $this->debugMode = $this->getConfig()->get("debugMode", false);

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
        Server::getInstance()->getScheduler()->scheduleAsyncTask(new BroadcastTask(Broadcaster::getInstance(), $message, $channel));
    }

    public function onPlayerChat(PlayerChatEvent $event){
        if($this->broadcastPlayerChats){
            Broadcaster::broadcast($this->getServer()->getLanguage()->translateString($event->getFormat(), [$event->getPlayer()->getName(), $event->getMessage()]));
        }
    }
}