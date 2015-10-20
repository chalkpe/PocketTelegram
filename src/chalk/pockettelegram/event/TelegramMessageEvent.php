<?php

/**
 * @author ChalkPE <chalkpe@gmail.com>
 * @since 2015-10-21 00:01
 */

namespace chalk\pockettelegram\event;

use chalk\pockettelegram\PocketTelegram;
use chalk\pockettelegram\model\Message;
use pocketmine\event\plugin\PluginEvent;

class TelegramMessageEvent extends PluginEvent {
    /** @var Message */
    private $message;

    public function __construct(Message $message){
        parent::__construct(PocketTelegram::getInstance());

        $this->message = $message;
    }

    /**
     * @return Message
     */
    public function getMessage(){
        return $this->message;
    }

    /**
     * @param Message $message
     */
    public function setMessage($message){
        $this->message = $message;
    }
}