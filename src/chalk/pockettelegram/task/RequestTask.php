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
 * @since 2015-10-18 20:50
 */

namespace chalk\broadcaster;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class RequestTask extends AsyncTask {
    /** @var string */
    private $url;

    /** @var array */
    private $params;

    /** @var callable */
    private $callback;

    /**
     * @param string $url
     * @param array $params
     * @param callable $callback
     */
    public function __construct($url, array $params, callable $callback = null){
        $this->url = $url;
        $this->params = $params;
        $this->callback = $callback;
    }

    public function onRun(){
        $session = curl_init();
        curl_setopt($session, CURLOPT_URL, $this->url);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($session, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($session, CURLOPT_POST, 1);
        curl_setopt($session, CURLOPT_POSTFIELDS, $this->params);
        curl_setopt($session, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($session, CURLOPT_CONNECTTIMEOUT, 500);

        $this->setResult(curl_exec($session));
        curl_close($session);
    }

    public function onCompletion(Server $server){
        if($this->hasResult()){
            PocketTelegram::debug($this->getResult());
            if(!is_null($this->callback)) call_user_func($this->callback, $this->getResult());
        }
    }
}