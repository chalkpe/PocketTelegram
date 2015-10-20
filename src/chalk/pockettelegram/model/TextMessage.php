<?php

/**
 * @author ChalkPE <chalkpe@gmail.com>
 * @since 2015-10-20 19:17
 */

namespace chalk\pockettelegram\model;

class TextMessage extends Message {
    /** @var string */
    private $text = "";

    /**
     * @param Message $message
     * @param string $text
     */
    public function __construct(Message $message, $text){
        parent::__construct($message->getMessageId(), $message->getDate(), $message->getChat(), $message->getFrom(), $message->getForwardFrom(), $message->getForwardDate(), $message->getReplyToMessage());

        $this->text = $text;
    }

    /**
     * @param array $array
     * @param Message $message
     * @return TextMessage
     */
    public static function create(array $array, Message $message){
        return new TextMessage($message, $array['text']);
    }

    /**
     * @return string
     */
    public function getText(){
        return $this->text;
    }
}