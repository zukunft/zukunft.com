<?php

/*

    web/user/user_log_sum_list.php - the users with the most server time for the admin dashboard
    ------------------------------

    $usr_log_sum_lst is the suggested var name


    This file is part of zukunft.com - calc with words

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

namespace Zukunft\ZukunftCom\main\php\web\user;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::HTML . 'html_base.php';
include_once html_paths::HTML . 'rest_call.php';
include_once html_paths::USER . 'user_log_sum.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::SHARED_CONST . 'def.php';
include_once html_paths::SHARED_CONST . 'words.php';
include_once html_paths::SHARED_ENUM . 'messages.php';
include_once html_paths::SHARED . 'json_fields.php';

use Zukunft\ZukunftCom\main\php\shared\const\def;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\rest_call;

class user_log_sum_list
{

    // the config.yaml path of the thresholds from which a user is critical
    // (config.yaml pod > limits > user > log > dashboard > threshold)
    const array THRESHOLD_PATH = [words::THRESHOLD, words::DASHBOARD, words::LOG, words::USER, words::LIMITS, words::POD];


    /*
     *  object vars
     */

    // the summed sessions per user with the most server time first as sent by the backend
    private array $lst = [];


    /*
     * set and get
     */

    /**
     * set the summed sessions based on the api json array
     * @param array $json_array the list of the summed sessions per user as api json arrays
     * @param user_message $msg to report the mapping problems
     * @return bool true if there are no errors
     */
    function set_from_json_array(array $json_array, user_message $msg): bool
    {
        foreach ($json_array as $value) {
            $new = new user_log_sum();
            if ($new->api_mapper($value)) {
                $this->add($new);
            }
        }
        return $msg->is_ok();
    }


    /*
     * load
     */

    /**
     * load the users with the most server time from the backend, which sends them only to an admin
     * @param user_message $msg to report the message of the backend e.g. that only an admin can see the list
     * @return bool true if the backend has sent the list
     */
    function load(user_message $msg): bool
    {
        $rest = new rest_call();
        $json_body = $rest->api_get($this::class, [], $msg);
        return $this->api_load($json_body, $msg);
    }

    /**
     * @param array $json_body the api answer with the summed sessions or the message why they are not sent
     * @param user_message $msg to report the message of the backend e.g. that only an admin can see the list
     * @return bool true if the backend has sent the list
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
     * @param user_log_sum $usr_log_sum the summed sessions of a user that should be added to the list
     * @return bool true if the summed sessions have been added
     */
    function add(user_log_sum $usr_log_sum): bool
    {
        $this->lst[] = $usr_log_sum;
        return true;
    }


    /*
     * info
     */

    /**
     * @return bool true if the list contains no user
     */
    function is_empty(): bool
    {
        return count($this->lst) === 0;
    }

    /**
     * @return int the number of users of this list
     */
    function count(): int
    {
        return count($this->lst);
    }


    /*
     * select
     */

    /**
     * @return user_log_sum_list the users with the most server time first and the same time sorted by the name
     */
    function most_first(): user_log_sum_list
    {
        $result = new user_log_sum_list();
        $result->lst = $this->lst;
        usort($result->lst, fn(user_log_sum $a, user_log_sum $b)
            => [$b->server_time(), $a->user_name] <=> [$a->server_time(), $b->user_name]);
        return $result;
    }

    /**
     * @param bool $critical true for the users that have reached a threshold, false for the others
     * @param array $limits the thresholds as returned by critical_limits
     * @return user_log_sum_list the critical resp. the not critical users in the order of this list
     */
    function critical(bool $critical, array $limits): user_log_sum_list
    {
        $result = new user_log_sum_list();
        foreach ($this->lst as $usr_log_sum) {
            if ($usr_log_sum->is_critical($limits) === $critical) {
                $result->add($usr_log_sum);
            }
        }
        return $result;
    }

    /**
     * @param user_message $msg to report a problem of the config lookup
     * @return array the thresholds from which a user is critical by the json field of the sum
     */
    static function critical_limits(user_message $msg): array
    {
        global $ui_sys;
        $limits = [
            json_fields::REQUESTS => def::FALLBACK_USER_LOG_MAX_REQUESTS,
            json_fields::UPDATES => def::FALLBACK_USER_LOG_MAX_UPDATES,
            json_fields::REQUEST_MS => def::FALLBACK_USER_LOG_MAX_REQUEST_MS,
            json_fields::UPDATE_MS => def::FALLBACK_USER_LOG_MAX_UPDATE_MS,
        ];
        if ($ui_sys?->cfg !== null) {
            $names = [
                json_fields::REQUESTS => [words::REQUESTS, ...self::THRESHOLD_PATH],
                json_fields::UPDATES => [words::UPDATES, ...self::THRESHOLD_PATH],
                json_fields::REQUEST_MS => [words::MILLISECOND, words::REQUEST, ...self::THRESHOLD_PATH],
                json_fields::UPDATE_MS => [words::MILLISECOND, words::UPDATE, ...self::THRESHOLD_PATH],
            ];
            foreach ($names as $fld => $path) {
                $limits[$fld] = (int)$ui_sys->cfg->get_by($path, $msg, $limits[$fld]);
            }
        }
        return $limits;
    }


    /*
     * display
     */

    /**
     * @return string the html table of the summed sessions with a header row
     */
    function display(): string
    {
        $html = new html_base();
        $rows = new user_log_sum()->header();
        foreach ($this->lst as $usr_log_sum) {
            $rows .= $usr_log_sum->display();
        }
        return $html->tbl($rows);
    }

}
