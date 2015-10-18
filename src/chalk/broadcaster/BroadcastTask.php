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

use pocketmine\scheduler\AsyncTask;

class BroadcastTask extends AsyncTask {
    /** @var string */
    private $message, $channel;

    /**
     * @param string $message
     * @param string $channel
     */
    public function __construct($message, $channel = ""){
        $this->message = $message;
        $this->channel = $channel;
    }

    public function onRun(){
        $data = [
            "chat_id" => ($this->channel === "") ? Broadcaster::$channel : $this->channel,
            "text" => $this->message,
            "disable_web_page_preview" => Broadcaster::$disableWebPagePreview
        ];

        if(Broadcaster::$enableMarkdownParsing){
            $data["parse_mode"] = "Markdown";
        }

        $session = curl_init();
        curl_setopt($session, CURLOPT_URL, "https://api.telegram.org/bot" . Broadcaster::$token . "/sendMessage");
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($session, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($session, CURLOPT_POST, 1);
        curl_setopt($session, CURLOPT_POSTFIELDS, $data);
        curl_setopt($session, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($session, CURLOPT_CONNECTTIMEOUT, 500);

        $this->onResult($result = curl_exec($session));
        curl_close($session);
    }

    public function onResult($result){
        Broadcaster::getInstance()->getLogger()->debug($result);
    }
}