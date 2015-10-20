<?php

/**
 * @author ChalkPE <chalkpe@gmail.com>
 * @since 2015-10-20 19:01
 */

namespace chalk\pockettelegram\model;

class Chat {
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
}