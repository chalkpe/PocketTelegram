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
 * @since 2015-10-18 20:50
 */

namespace chalk\broadcaster;

use pocketmine\event\player\PlayerChatEvent;
use pocketmine\scheduler\AsyncTask;

class BroadcastTask extends AsyncTask {
    /** @var Broadcaster */
    private $broadcaster;

    /** @var PlayerChatEvent */
    private $event;

    /**
     * BroadcastTask constructor.
     * @param Broadcaster $broadcaster
     * @param PlayerChatEvent $event
     */
    public function __construct(Broadcaster $broadcaster, PlayerChatEvent $event){
        $this->broadcaster = $broadcaster;
        $this->event = $event;
    }

    public function onRun(){
        try{
            $data = [
                "chat_id" => $this->broadcaster->chatId,
                "text" => $this->broadcaster->getServer()->getLanguage()->translateString($this->event->getFormat(), [$this->event->getPlayer()->getName(), $this->event->getMessage()]),
                "disable_web_page_preview" => "true"
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot" . $this->broadcaster->token . "/sendMessage");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 500);

            $this->broadcaster->getLogger()->debug(curl_exec($ch));
            curl_close($ch);
        }catch(\Exception $e){
            $this->broadcaster->getLogger()->error($e);
        }
    }
}