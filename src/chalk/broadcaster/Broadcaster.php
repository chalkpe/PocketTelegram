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
    public static $token = "", $channel = "";

    public function onLoad(){
        self::$instance = $this;
    }

    public function onEnable(){
        $this->saveDefaultConfig();
        self::$token = $this->getConfig()->get("token");
        self::$channel = $this->getConfig()->get("channel");

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    /**
     * @return Broadcaster
     */
    public static function getInstance(){
        return self::$instance;
    }

    /**
     * @param string $str
     */
    public static function broadcast($str){
        Server::getInstance()->getScheduler()->scheduleAsyncTask(new BroadcastTask($str));
    }

    public function onPlayerChat(PlayerChatEvent $event){
        if($this->getConfig()->get("broadcastPlayerChats", false)){
            Broadcaster::broadcast($this->getServer()->getLanguage()->translateString($event->getFormat(), [$event->getPlayer()->getName(), $event->getMessage()]));
        }
    }
}