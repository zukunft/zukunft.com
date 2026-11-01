<?php

/*

    web/system/system_time_list.php - a list of measured request times e.g. the slow urls of the admin dashboard
    -------------------------------

    $sys_tim_lst is the suggested var name

    This file is part of the frontend of zukunft.com - calc with words

    zukunft.com is free software: you can redistribute it and/or modify it
    under the terms of the GNU General Public License as
    published by the Free Software Foundation, either version 3 of
    the License, or (at your option) any later version.
    zukunft.com is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\web\system;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::HTML . 'html_base.php';
include_once html_paths::HTML . 'rest_call.php';
include_once html_paths::SYSTEM . 'system_time.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::SHARED_CONST . 'def.php';
include_once html_paths::SHARED_CONST . 'triples.php';
include_once html_paths::SHARED_CONST . 'words.php';
include_once html_paths::SHARED_ENUM . 'messages.php';
include_once html_paths::SHARED . 'json_fields.php';

use Zukunft\ZukunftCom\main\php\shared\const\def;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\rest_call;
use Zukunft\ZukunftCom\main\php\web\user\user_message;

class system_time_list
{

    /*
     *  object vars
     */

    // the list of request times
    private array $lst = [];


    /*
     * set and get
     */

    /**
     * set the request times based on the api json array
     * @param array $json_array the list of the request times as api json arrays
     * @param user_message $msg to report the mapping problems
     * @return bool true if there are no errors
     */
    function set_from_json_array(array $json_array, user_message $msg): bool
    {
        foreach ($json_array as $value) {
            $new = new system_time();
            $new->api_mapper($value, $msg);
            $this->add($new);
        }
        return $msg->is_ok();
    }


    /*
     * load
     */

    /**
     * load the slow requests of the configured last days from the backend, which sends them only to an admin
     * @param user_message $msg to report the message of the backend e.g. that only an admin can see the slow requests
     * @return bool true if the backend has sent the slow requests
     */
    function load(user_message $msg): bool
    {
        $rest = new rest_call();
        $json_body = $rest->api_get($this::class, [], $msg);
        return $this->api_load($json_body, $msg);
    }

    /**
     * @param array $json_body the api answer with the slow requests or the message why they are not sent
     * @param user_message $msg to report the message of the backend e.g. that only an admin can see the slow requests
     * @return bool true if the backend has sent the slow requests
     */
    function api_load(array $json_body, user_message $msg): bool
    {
        $result = false;
        if (array_key_exists(json_fields::MSG, $json_body)) {
            $msg->add(msg_id::API_MESSAGE, [msg_id::VAR_JSON_TEXT => $json_body[json_fields::MSG]]);
        } else {
            $result = $this->set_from_json_array($json_body, $msg);
        }
        return $result;
    }


    /*
     * modify
     */

    /**
     * @param system_time $sys_tim the request time that should be added to the list
     * @return bool true if the request time has been added
     */
    function add(system_time $sys_tim): bool
    {
        $this->lst[] = $sys_tim;
        return true;
    }


    /*
     * info
     */

    /**
     * @return bool true if the list contains no request time
     */
    function is_empty(): bool
    {
        return count($this->lst) === 0;
    }

    /**
     * @return int the number of request times of this list
     */
    function count(): int
    {
        return count($this->lst);
    }


    /*
     * select
     */

    /**
     * @return system_time_list the request times with the slowest first and the same time sorted by the url
     */
    function slowest_first(): system_time_list
    {
        $result = new system_time_list();
        $result->lst = $this->lst;
        usort($result->lst, fn(system_time $a, system_time $b) => [$b->milliseconds, $a->url] <=> [$a->milliseconds, $b->url]);
        return $result;
    }

    /**
     * @param bool $critical true for the requests that took at least the limit, false for the faster ones
     * @param int $limit the milliseconds from which a request is critical
     * @return system_time_list the critical resp. the not critical request times in the order of this list
     */
    function critical(bool $critical, int $limit): system_time_list
    {
        $result = new system_time_list();
        foreach ($this->lst as $sys_tim) {
            if ($sys_tim->is_critical($limit) === $critical) {
                $result->add($sys_tim);
            }
        }
        return $result;
    }

    /**
     * @param int $limit the maximal number of request times to keep
     * @return system_time_list with only the first request times of this list
     */
    function head(int $limit): system_time_list
    {
        $result = new system_time_list();
        $result->lst = array_slice($this->lst, 0, $limit);
        return $result;
    }

    /**
     * @param user_message $msg to report a problem of the config lookup
     * @return int the milliseconds from which a request is critical
     *             (config.yaml pod > limits > usage > response time > millisecond > warning)
     */
    static function critical_limit(user_message $msg): int
    {
        global $ui_sys;
        $limit = def::FALLBACK_RESPONSE_TIME_WARNING;
        if ($ui_sys?->cfg !== null) {
            $limit = (int)$ui_sys->cfg->get_by(
                [words::WARNING, words::MILLISECOND, triples::RESPONSE_TIME, words::USAGE, words::LIMITS, words::POD],
                $msg, def::FALLBACK_RESPONSE_TIME_WARNING
            );
        }
        return $limit;
    }


    /*
     * display
     */

    /**
     * @return string the html table of the request times with a header row
     */
    function display(): string
    {
        $html = new html_base();
        $rows = new system_time()->header();
        foreach ($this->lst as $sys_tim) {
            $rows .= $sys_tim->display();
        }
        return $html->tbl($rows);
    }

}
