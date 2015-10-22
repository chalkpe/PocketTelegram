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
 * @since 2015-10-20 19:17
 */

namespace ChalkPE\PocketTelegram\model;

class TextMessage extends Message {
    /** @var string */
    private $text = "";

    /**
     * @param int $messageId
     * @param int $date
     * @param Chat $chat
     * @param string $text
     * @param User|null $from
     * @param User|null $forwardFrom
     * @param int $forwardDate
     * @param Message|null $replyToMessage
     */
    public function __construct($messageId, $date, Chat $chat, $text, User $from = null, User $forwardFrom = null, $forwardDate = 0, Message $replyToMessage = null){
        parent::__construct($messageId, $date, $chat, $from, $forwardFrom, $forwardDate, $replyToMessage);

        $this->text = $text;
    }

    /**
     * @param array $array
     * @return TextMessage
     */
    public static function create(array $array){
        return new TextMessage(intval($array['message_id']), intval($array['date']), Chat::create($array['chat']), $array['text'],
            isset($array['from'])             ? User::create($array['from'])                : null,
            isset($array['forward_from'])     ? User::create($array['forward_from'])        : null,
            isset($array['forward_date'])     ? intval($array['forward_date'])              : 0,
            isset($array['reply_to_message']) ? Message::create($array['reply_to_message']) : null);
    }

    /**
     * @return string
     */
    public function getText(){
        return $this->text;
    }
}