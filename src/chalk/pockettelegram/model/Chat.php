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

namespace chalk\pockettelegram\model;

class Chat implements Identifiable, Nameable {
    const TYPE_PRIVATE = "private";
    const TYPE_GROUP = "group";
    const TYPE_CHANNEL = "channel";

    /** @var int */
    private $id;

    /** @var string */
    private $type;

    /** @var string|null */
    private $title = "";

    /** @var string|null */
    private $username = "";

    /** @var string|null */
    private $firstName = "";

    /** @var string|null */
    private $lastName = "";

    /**
     * @param int $id
     * @param string $type
     * @param string|null $title
     * @param string|null $username
     * @param string|null $firstName
     * @param string|null $lastName
     */
    public function __construct($id, $type, $title = "", $username = "", $firstName = "", $lastName = ""){
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
            isset($array['title'])      ? $array['title']      : "",
            isset($array['username'])   ? $array['username']   : "",
            isset($array['first_name']) ? $array['first_name'] : "",
            isset($array['last_name'])  ? $array['last_name']  : "");
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
        return ($this->getLastName() === "") ? $this->getFirstName() : $this->getFirstName() . " " . $this->getLastName();
    }
}