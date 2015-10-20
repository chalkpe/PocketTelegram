<?php

/**
 * @author ChalkPE <chalkpe@gmail.com>
 * @since 2015-10-20 17:20
 */

namespace chalk\pockettelegram\model;

class Message {
    /** @var int */
    private $messageId;

    /** @var int */
    private $date;

    /** @var Chat */
    private $chat;

    /** @var User|null */
    private $from = null;

    /** @var User|null */
    private $forwardFrom = null;

    /** @var int|null */
    private $forwardDate = 0;

    /** @var Message|null */
    private $replyToMessage = null;

    /**
     * @param int $messageId
     * @param int $date
     * @param Chat $chat
     * @param User|null $from
     * @param User|null $forwardFrom
     * @param int|null $forwardDate
     * @param Message|null $replyToMessage
     */
    public function __construct($messageId, $date, Chat $chat, User $from = null, User $forwardFrom = null, $forwardDate = 0, Message $replyToMessage = null){
        $this->messageId = $messageId;
        $this->date = $date;
        $this->chat = $chat;
        $this->from = $from;
        $this->forwardFrom = $forwardFrom;
        $this->forwardDate = $forwardDate;
        $this->replyToMessage = $replyToMessage;
    }

    /**
     * @param array $array
     * @return Message
     */
    public static function create(array $array){
        $message = new Message(intval($array['message_id']), intval($array['date']), Chat::create($array['chat']),
            isset($array['from'])             ? User::create($array['from'])                : null,
            isset($array['forward_from'])     ? User::create($array['forward_from'])        : null,
            isset($array['forward_date'])     ? intval($array['forward_date'])              : 0,
            isset($array['reply_to_message']) ? Message::create($array['reply_to_message']) : null);

        if(isset($array['text'])) return TextMessage::create($array, $message);

        return $message;
    }

    /**
     * @return int
     */
    public function getMessageId(){
        return $this->messageId;
    }

    /**
     * @return int
     */
    public function getDate(){
        return $this->date;
    }

    /**
     * @return Chat
     */
    public function getChat(){
        return $this->chat;
    }

    /**
     * @return User|null
     */
    public function getFrom(){
        return $this->from;
    }

    /**
     * @return User|null
     */
    public function getForwardFrom(){
        return $this->forwardFrom;
    }

    /**
     * @return int|null
     */
    public function getForwardDate(){
        return $this->forwardDate;
    }

    /**
     * @return Message|null
     */
    public function getReplyToMessage(){
        return $this->replyToMessage;
    }
}