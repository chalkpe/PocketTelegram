<?php

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