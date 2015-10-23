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
 * @since 2015-10-23 22:32
 */

namespace ChalkPE\PocketTelegram;

use ChalkPE\PocketTelegram\event\TelegramMessageEvent;
use ChalkPE\PocketTelegram\model\message\Message;
use ChalkPE\PocketTelegram\model\message\PhotoMessage;
use ChalkPE\PocketTelegram\model\message\TextMessage;
use pocketmine\event\Cancellable;
use pocketmine\event\Event;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use pocketmine\Server;

class EventHandler implements Listener {
    /** @var int[] */
    public static $lastCommand = [];

    public function onTelegramMessage(TelegramMessageEvent $event){
        if(!PocketTelegram::$broadcastTelegramMessages) return;

        $message = $event->getMessage();
        switch(true){
            case $message instanceof TextMessage:
                if(PocketTelegram::$enableTelegramCommands and $message->isCommand()){
                    self::handleCommands($message);
                    return;
                }
                break;

            case $message instanceof PhotoMessage: break;
            default: return;
        }
        $this->broadcastMessage($message);
    }

    private function broadcastMessage(Message $message){
        if($message->getChat()->getId() !== PocketTelegram::getDefaultChannel()) return;
        if(is_null($from = $message->getFrom()) or is_null($username = $from->getUsername())) return;

             if($message instanceof TextMessage)  $message = $message->getText();
        else if($message instanceof PhotoMessage) $message = "(Photo)";

        Server::getInstance()->broadcastMessage(PocketTelegram::translateString("chat.type.text", [$username, $message]));
    }

    /**
     * @param TextMessage $message
     */
    private static function handleCommands(TextMessage $message){
        $chatId = $message->getChat()->getId();
        if(!isset(self::$lastCommand[$chatId])) self::$lastCommand[$chatId] = 0;
        if((time() - self::$lastCommand[$chatId]) < 2) return;

        $commands = $message->getCommands();
        if(count($command = explode('@', $commands[0])) > 1){
            if(!is_null($me = PocketTelegram::getMe()) and strToLower($command[1]) !== strToLower($me->getUsername())) return;
            $commands[0] = $command[0];
        }

        switch(strToLower($commands[0])){
            case "chat_id":
                PocketTelegram::sendMessage($chatId, $message->getChat(), $message);
                break;

            case "online":
                $players = array_map(function(Player $player){ return $player->getDisplayName(); }, array_filter(Server::getInstance()->getOnlinePlayers(), function(Player $player){ return $player->isOnline(); }));
                PocketTelegram::sendMessage(PocketTelegram::translateString("commands.players.list", [count($players), Server::getInstance()->getMaxPlayers()]) . PHP_EOL . implode(", " , $players), $message->getChat(), $message);
                break;
        }
        self::$lastCommand[$chatId] = time();
    }

    public function onPlayerChat(PlayerChatEvent $event){
        self::handleEvents($event);
    }

    public function onPlayerJoin(PlayerJoinEvent $event){
        self::handleEvents($event);
    }

    public function onPlayerQuit(PlayerQuitEvent $event){
        self::handleEvents($event);
    }

    public function onPlayerDeath(PlayerDeathEvent $event){
        self::handleEvents($event);
    }

    private static function handleEvents(Event $event){
        if(!PocketTelegram::$broadcastToTelegram) return;
        if($event instanceof Cancellable and $event->isCancelled()) return;

        $message = null;
        switch(true){
            case $event instanceof PlayerChatEvent:
                $message = PocketTelegram::translateString($event->getFormat(), [$event->getPlayer()->getName(), $event->getMessage()]);
                break;

            case $event instanceof PlayerJoinEvent:
                $message = $event->getJoinMessage();
                break;

            case $event instanceof PlayerQuitEvent:
                $message = $event->getQuitMessage();
                break;

            case $event instanceof PlayerDeathEvent:
                $message = $event->getDeathMessage();
                break;

            default:
                return;
        }

        if(PocketTelegram::getDefaultChannel() !== "") PocketTelegram::sendMessage($message, PocketTelegram::getDefaultChannel());
    }
}