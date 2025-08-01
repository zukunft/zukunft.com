<?php

/*

    web/log/log.php - base log object to create the html code to display a change of system log entry
    ---------------

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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace html\log;

use cfg\const\paths;
use html\const\paths as html_paths;
include_once html_paths::SANDBOX . 'db_object.php';
include_once paths::API_OBJECT . 'controller.php';
include_once html_paths::USER . 'user_message.php';
include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'json_fields.php';


use html\sandbox\db_object as db_object_dsp;
use html\user\user_message;
use shared\json_fields;
use DateTime;
use DateTimeInterface;
use Exception;

class log extends db_object_dsp
{

    /*
     * object vars
     */

    protected DateTime $time;
    protected int $user_id;
    private ?string $user_name = null;
    protected string $text;
    protected int $status;


    /*
     * set and get
     */

    /**
     * set the vars of this log html object bases on the api json array
     * @param array $json_array an api json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function api_mapper(array $json_array): user_message
    {
        $usr_msg = parent::api_mapper($json_array);
        // TODO use empty date instead?
        $sys_log_timestamp = new DateTime();
        if (array_key_exists(json_fields::TIME, $json_array)) {
            try {
                $sys_log_timestamp = new DateTime($json_array[json_fields::TIME]);
            } catch (Exception $e) {
                // TODO avoid loops if date writing in log_err fails ?
                log_err('Error converting system log timestamp ' . $json_array[json_fields::TIME]
                    . ' because ' . $e->getMessage());
            }
        } else {
            log_warning('Mandatory time missing in API JSON ' . json_encode($json_array));
        }
        $this->set_time($sys_log_timestamp);
        if (array_key_exists(json_fields::USER_ID, $json_array)) {
            $this->set_user_id($json_array[json_fields::USER_ID]);
        } else {
            $this->set_user_id(0);
        }
        if (array_key_exists(json_fields::USER_NAME, $json_array)) {
            $this->set_user_name($json_array[json_fields::USER_NAME]);
        } else {
            $this->set_user_name(null);
        }
        if (array_key_exists(json_fields::TEXT, $json_array)) {
            $this->set_text($json_array[json_fields::TEXT]);
        } else {
            $this->set_text('');
        }
        if (array_key_exists(json_fields::STATUS, $json_array)) {
            $this->set_status($json_array[json_fields::STATUS]);
        } else {
            $this->set_status(0);
        }
        return $usr_msg;
    }

    function set_time(DateTime $iso_time_str): void
    {
        $this->time = $iso_time_str;
    }

    function time(): DateTime
    {
        return $this->time;
    }

    function set_user_id(int $user_id): void
    {
        $this->user_id = $user_id;
    }

    function user_id(): int
    {
        return $this->user_id;
    }

    function set_user_name(string|null $user_name): void
    {
        $this->user_name = $user_name;
    }

    function user_name(): string|null
    {
        return $this->user_name;
    }

    function set_text(string $text): void
    {
        $this->text = $text;
    }

    function text(): string
    {
        return $this->text;
    }

    function set_status(int $status): void
    {
        $this->status = $status;
    }

    function status(): int
    {
        return $this->status;
    }


    /*
     * interface
     */

    /**
     * @return array the json message array to send the updated data to the backend
     * an array is used (instead of a string) to enable combinations of api_array() calls
     */
    function api_array(): array
    {
        $vars = parent::api_array();
        $vars[json_fields::TIME] = $this->time()->format(DateTimeInterface::ATOM);
        if ($this->user_id() > 0) {
            $vars[json_fields::USER_ID] = $this->user_id();
        }
        if ($this->user_name() != null) {
            $vars[json_fields::USER_NAME] = $this->user_name();
        }
        $vars[json_fields::TEXT] = $this->text();
        $vars[json_fields::STATUS] = $this->status();
        return $vars;
    }

}
