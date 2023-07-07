<?php

/*

    /web/system/batch_job.php - the extension of the batch_job API objects to create batch_job base html code
    -------------------------

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

namespace html\system;

include_once WEB_SANDBOX_PATH . 'db_object.php';
include_once API_PATH . 'api.php';
include_once API_PATH . 'controller.php';

use controller\controller;
use DateTime;
use DateTimeInterface;
use Exception;
use api\api;
use html\sandbox\db_object as db_object_dsp;
use html\html_base;

class batch_job extends db_object_dsp
{

    /*
     * object vars
     */

    private DateTime $request_time;
    private ?DateTime $start_time;
    private ?DateTime $end_time;
    private int $user_id;
    public string $type;
    private string $status;
    private int $priority;


    /*
     * set and get
     */

    /**
     * set the vars of this batch job html object bases on the api json array
     * @param array $json_array an api json message
     * @return void
     */
    function set_from_json_array(array $json_array): void
    {
        parent::set_from_json_array($json_array);
        // TODO use empty date instead?
        $request_timestamp = new DateTime();
        if (array_key_exists(controller::API_FLD_TIME_REQUEST, $json_array)) {
            try {
                $request_timestamp = new DateTime($json_array[controller::API_FLD_TIME_REQUEST]);
            } catch (Exception $e) {
                // TODO avoid loops if date writing in log_err fails ?
                log_err('Error converting system log timestamp ' . $json_array[controller::API_FLD_TIME_REQUEST]
                    . ' because ' . $e->getMessage());
            }
        } else {
            log_err('Mandatory time missing in API JSON ' . json_encode($json_array));
        }
        $this->set_request_time($request_timestamp);
        $start_time = null;
        if (array_key_exists(controller::API_FLD_TIME_START, $json_array)) {
            try {
                $request_timestamp = new DateTime($json_array[controller::API_FLD_TIME_START]);
            } catch (Exception $e) {
                // TODO avoid loops if date writing in log_err fails ?
                log_err('Error converting system log timestamp ' . $json_array[controller::API_FLD_TIME_START]
                    . ' because ' . $e->getMessage());
            }
        }
        $this->set_start_time($start_time);
        $end_time = null;
        if (array_key_exists(controller::API_FLD_TIME_END, $json_array)) {
            try {
                $request_timestamp = new DateTime($json_array[controller::API_FLD_TIME_END]);
            } catch (Exception $e) {
                // TODO avoid loops if date writing in log_err fails ?
                log_err('Error converting system log timestamp ' . $json_array[controller::API_FLD_TIME_END]
                    . ' because ' . $e->getMessage());
            }
        }
        $this->set_end_time($end_time);
        if (array_key_exists(api::FLD_USER_ID, $json_array)) {
            $this->set_user_id($json_array[api::FLD_USER_ID]);
        } else {
            $this->set_user_id(0);
        }
        if (array_key_exists(api::FLD_TYPE, $json_array)) {
            $this->set_type($json_array[api::FLD_TYPE]);
        } else {
            $this->set_type(0);
        }
        if (array_key_exists(controller::API_FLD_STATUS, $json_array)) {
            $this->set_status($json_array[controller::API_FLD_STATUS]);
        } else {
            $this->set_status('');
        }
        if (array_key_exists(controller::API_FLD_PRIORITY, $json_array)) {
            $this->set_priority($json_array[controller::API_FLD_PRIORITY]);
        } else {
            $this->set_priority(0);
        }
    }

    function set_request_time(DateTime $iso_time_str): void
    {
        $this->request_time = $iso_time_str;
    }

    function request_time(): DateTime
    {
        return $this->request_time;
    }

    function set_start_time(?DateTime $iso_time_str): void
    {
        $this->start_time = $iso_time_str;
    }

    function start_time(): ?DateTime
    {
        return $this->start_time;
    }

    function set_end_time(?DateTime $iso_time_str): void
    {
        $this->end_time = $iso_time_str;
    }

    function end_time(): ?DateTime
    {
        return $this->end_time;
    }

    function set_user_id(int $user_id): void
    {
        $this->user_id = $user_id;
    }

    function user_id(): int
    {
        return $this->user_id;
    }

    function set_type(int $type): void
    {
        $this->type = $type;
    }

    function type(): int
    {
        return $this->type;
    }

    function set_status(string $status): void
    {
        $this->status = $status;
    }

    function status(): string
    {
        return $this->status;
    }

    function set_priority(int $priority): void
    {
        $this->priority = $priority;
    }

    function priority(): int
    {
        return $this->priority;
    }


    /*
     * base elements
     */

    /**
     * @returns string the html code to show one batch job for non admin users
     */
    function display(): string
    {
        $html = new html_base();
        $result = '';
        // TODO replace with the user date format setting,
        //      which can also be the local system setting
        //      or the pod setting
        $result .= $html->td($this->request_time()->format(DateTimeInterface::ATOM));
        if ($this->start_time() != null) {
            $result .= $html->td($this->start_time()->format(DateTimeInterface::ATOM));
        } else {
            $result .= $html->td('');
        }
        if ($this->end_time() != null) {
            $result .= $html->td($this->end_time()->format(DateTimeInterface::ATOM));
        } else {
            $result .= $html->td('');
        }
        // TODO show the username instead of the id
        $result .= $html->td($this->user_id());
        $result .= $html->td($this->type());
        $result .= $html->td($this->status());
        $result .= $html->td($this->priority());
        return $result;
    }

    /**
     * @returns string the html code to show the table header for system log entries and non admin users
     */
    function header(): string
    {
        $html = new html_base();
        // TODO replace with language specific headers
        $result = $html->th('request time');
        $result .= $html->th('start time');
        $result .= $html->th('end time');
        $result .= $html->th('user');
        $result .= $html->th('type');
        $result .= $html->th('status');
        $result .= $html->th('priority');
        return $html->tr($result);
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
        $vars[controller::API_FLD_TIME_REQUEST] = $this->request_time()->format(DateTimeInterface::ATOM);
        $vars[controller::API_FLD_TIME_START] = $this->start_time()->format(DateTimeInterface::ATOM);
        $vars[controller::API_FLD_TIME_END] = $this->end_time()->format(DateTimeInterface::ATOM);
        $vars[api::FLD_USER_ID] = $this->user_id();
        $vars[api::FLD_TYPE] = $this->type();
        $vars[controller::API_FLD_STATUS] = $this->status();
        $vars[controller::API_FLD_PRIORITY] = $this->priority();
        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }

}
