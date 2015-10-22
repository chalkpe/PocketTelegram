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
    private $text;

    /**
     * @param Message $message
     * @param string $text
     */
    public function __construct(Message $message, $text){
        parent::__construct($message, null, null);
        $this->text = $text;
    }

    /**
     * @param array $array
     * @return TextMessage
     */
    public static function create(array $array){
        return new TextMessage(Message::create($array, false), $array['text']);
    }

    /**
     * @return string
     */
    public function getText(){
        return $this->text;
    }
}