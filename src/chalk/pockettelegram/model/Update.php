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
 * @since 2015-10-20 23:52
 */

namespace chalk\pockettelegram\model;

class Update {
    /** @var int */
    private $updateId;

    /** @var Message|null */
    private $message = null;

    /**
     * @param int $updateId
     * @param Message|null $message
     */
    public function __construct($updateId, $message = null){
        $this->updateId = $updateId;
        $this->message = $message;
    }

    public static function create(array $array){
        return new Update(intval($array['update_id']),
            isset($array['message']) ? Message::create($array['message']) : null);
    }

    /**
     * @return int
     */
    public function getUpdateId(){
        return $this->updateId;
    }

    /**
     * @return Message|null
     */
    public function getMessage(){
        return $this->message;
    }
}