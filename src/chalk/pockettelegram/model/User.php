<?php

/**
 * @author ChalkPE <chalkpe@gmail.com>
 * @since 2015-10-20 18:28
 */

namespace chalk\pockettelegram\model;

class User {
    /** @var int */
    private $id;

    /** @var string */
    private $firstName;

    /** @var string|null */
    private $lastName = "";

    /** @var string|null */
    private $username = "";

    /**
     * @param int $id
     * @param string $firstName
     * @param string|null $lastName
     * @param string|null $username
     */
    public function __construct($id, $firstName, $lastName = "", $username = ""){
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->username = $username;
    }

    /**
     * @param array $array
     * @return User
     */
    public static function create(array $array){
        return new User(intval($array['id']), $array['first_name'],
            isset($array['last_name']) ? $array['last_name'] : "",
            isset($array['username'])  ? $array['username']  : "");
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
     * @return string|null
     */
    public function getUsername(){
        return $this->username;
    }

    /**
     * @return string
     */
    public function getFullName(){
        return ($this->getLastName() === "") ? $this->getFirstName() : $this->getFirstName() . " " . $this->getLastName();
    }
}