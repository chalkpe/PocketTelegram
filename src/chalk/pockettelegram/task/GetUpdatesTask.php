<?php

/**
 * @author ChalkPE <chalkpe@gmail.com>
 * @since 2015-10-20 23:43
 */

namespace chalk\pockettelegram\task;

use chalk\broadcaster\PocketTelegram;
use chalk\pockettelegram\event\TelegramMessageEvent;
use chalk\pockettelegram\model\Update;
use pocketmine\scheduler\PluginTask;
use pocketmine\Server;

class GetUpdatesTask extends PluginTask {
    /** @var Update|null */
    private $lastUpdate = null;

    public function __construct(){
        parent::__construct(PocketTelegram::getInstance());
    }

    public function onRun($currentTick){
        PocketTelegram::request("getUpdates", is_null($this->lastUpdate) ? [] : [
            'offset' => $this->lastUpdate->getUpdateId() + 1
        ], function($raw){
            $response = json_decode($raw);
            if(!isset($response['ok']) or $response['ok'] !== true) return;

            foreach($response['result'] as $result){
                $update = Update::create($result);
                $this->lastUpdate = $update;

                if(is_null($update->getMessage())) continue;
                Server::getInstance()->getPluginManager()->callEvent(new TelegramMessageEvent($update->getMessage()));
            }
        });
    }
}