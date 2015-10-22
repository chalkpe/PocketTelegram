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
 * @since 2015-10-20 19:01
 */

namespace ChalkPE\PocketTelegram\model;

class Chat implements Identifiable, Nameable {
    const TYPE_PRIVATE = "private";
    const TYPE_GROUP = "group";
    const TYPE_CHANNEL = "channel";

    /** @var int */
    private $id;

    /** @var string */
    private $type;

    /** @var string|null */
    private $title = null;

    /** @var string|null */
    private $username = null;

    /** @var string|null */
    private $firstName = null;

    /** @var string|null */
    private $lastName = null;

    /**
     * @param int $id
     * @param string $type
     * @param string|null $title
     * @param string|null $username
     * @param string|null $firstName
     * @param string|null $lastName
     */
    public function __construct($id, $type, $title = null, $username = null, $firstName = null, $lastName = null){
        $this->id = $id;
        $this->type = $type;
        $this->title = $title;
        $this->username = $username;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    /**
     * @param array $array
     * @return Chat
     */
    public static function create(array $array){
        return new Chat(intval($array['id']), $array['type'],
            isset($array['title'])      ? $array['title']      : null,
            isset($array['username'])   ? $array['username']   : null,
            isset($array['first_name']) ? $array['first_name'] : null,
            isset($array['last_name'])  ? $array['last_name']  : null);
    }

    /**
     * @return int
     */
    public function getId(){
        return $this->id;
    }

    /**
     * @return string
     */
    public function getType(){
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isPrivateChat(){
        return $this->getType() === Chat::TYPE_PRIVATE or $this->getId() > 0;
    }

    /**
     * @return bool
     */
    public function isGroupChat(){
        return $this->getType() === Chat::TYPE_GROUP or $this->getId() < 0;
    }

    /**
     * @return bool
     */
    public function isChannel(){
        return $this->getType() === Chat::TYPE_CHANNEL or (is_string($this->getId()) and $this->getId()[0] === '@');
    }

    /**
     * @return string|null
     */
    public function getTitle(){
        return $this->title;
    }

    /**
     * @return string|null
     */
    public function getUsername(){
        return $this->username;
    }

    /**
     * @return string|null
     */
    public function getFirstName(){
        return $this->firstName;
    }

    /**
     * @return string|null
     */
    public function getLastName(){
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getFullName(){
        return ($this->getLastName() === null) ? $this->getFirstName() : $this->getFirstName() . " " . $this->getLastName();
    }
}