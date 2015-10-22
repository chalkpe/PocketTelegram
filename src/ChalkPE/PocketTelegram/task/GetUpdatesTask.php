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
 * @since 2015-10-20 23:43
 */

namespace ChalkPE\PocketTelegram\task;

use ChalkPE\PocketTelegram\PocketTelegram;
use ChalkPE\PocketTelegram\event\TelegramMessageEvent;
use ChalkPE\PocketTelegram\model\Update;
use pocketmine\scheduler\PluginTask;
use pocketmine\Server;

class GetUpdatesTask extends PluginTask {
    /** @var Update|null */
    public static $lastUpdate = null;

    /** @var int */
    public static $errorCounter = 1;

    public function __construct(){
        parent::__construct(PocketTelegram::getInstance());
    }

    public function onRun($currentTick){
        PocketTelegram::request("getUpdates", is_null(self::$lastUpdate) ? [] : ['offset' => self::$lastUpdate->getUpdateId() + 1], function($json){
            $response = json_decode($json, true);
            if(!isset($response['ok']) or $response['ok'] !== true){
                PocketTelegram::getUpdates(GetUpdatesTask::$errorCounter *= 2);
                return;
            }

            foreach($response['result'] as $result){
                GetUpdatesTask::$lastUpdate = $update = Update::create($result);
                if(!is_null($update->getMessage())) Server::getInstance()->getPluginManager()->callEvent(new TelegramMessageEvent($update->getMessage()));
            }

            GetUpdatesTask::$errorCounter = 1;
            PocketTelegram::getUpdates();
        });
    }
}