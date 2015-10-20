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
 * @since 2015-10-21 00:01
 */

namespace chalk\pockettelegram\event;

use chalk\pockettelegram\model\Message;
use chalk\pockettelegram\PocketTelegram;
use pocketmine\event\plugin\PluginEvent;

class TelegramMessageEvent extends PluginEvent {
    public static $handlerList = null;

    /** @var Message */
    private $message;

    public function __construct(Message $message){
        parent::__construct(PocketTelegram::getInstance());

        $this->message = $message;
    }

    /**
     * @return Message
     */
    public function getMessage(){
        return $this->message;
    }

    /**
     * @param Message $message
     */
    public function setMessage($message){
        $this->message = $message;
    }
}