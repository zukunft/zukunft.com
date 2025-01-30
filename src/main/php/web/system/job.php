<?php

/*

    web/system/job.php - the extension of the batch task API objects to create job base html code
    ------------------

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
include_once API_OBJECT_PATH . 'controller.php';
include_once HTML_PATH . 'html_base.php';
include_once WEB_USER_PATH . 'user_message.php';
include_once SHARED_PATH . 'api.php';
include_once SHARED_PATH . 'json_fields.php';

use DateTime;
use DateTimeInterface;
use Exception;
use shared\api;
use html\sandbox\db_object as db_object_dsp;
use html\html_base;
use html\user\user_message;
use shared\json_fields;

class job extends db_object_dsp
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
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function set_from_json_array(array $json_array): user_message
    {
        $usr_msg = parent::set_from_json_array($json_array);
        // TODO use empty date instead?
        $request_timestamp = new DateTime();
        if (array_key_exists(json_fields::TIME_REQUEST, $json_array)) {
            try {
                $request_timestamp = new DateTime($json_array[json_fields::TIME_REQUEST]);
            } catch (Exception $e) {
                $usr_msg->add_err('Error converting system log timestamp ' . $json_array[json_fields::TIME_REQUEST]
                    . ' because ' . $e->getMessage());
            }
        } else {
            log_err('Mandatory time missing in API JSON ' . json_encode($json_array));
        }
        $this->set_request_time($request_timestamp);
        $start_time = null;
        if (array_key_exists(json_fields::TIME_START, $json_array)) {
            try {
                $request_timestamp = new DateTime($json_array[json_fields::TIME_START]);
            } catch (Exception $e) {
                $usr_msg->add_err('Error converting system log timestamp ' . $json_array[json_fields::TIME_START]
                    . ' because ' . $e->getMessage());
            }
        }
        $this->set_start_time($start_time);
        $end_time = null;
        if (array_key_exists(json_fields::TIME_END, $json_array)) {
            try {
                $request_timestamp = new DateTime($json_array[json_fields::TIME_END]);
            } catch (Exception $e) {
                $usr_msg->add_err('Error converting system log timestamp ' . $json_array[json_fields::TIME_END]
                    . ' because ' . $e->getMessage());
            }
        }
        $this->set_end_time($end_time);
        if (array_key_exists(json_fields::USER_ID, $json_array)) {
            $this->set_user_id($json_array[json_fields::USER_ID]);
        } else {
            $this->set_user_id(0);
        }
        if (array_key_exists(json_fields::TYPE, $json_array)) {
            $this->set_type($json_array[json_fields::TYPE]);
        } else {
            $this->set_type(0);
        }
        if (array_key_exists(json_fields::STATUS, $json_array)) {
            $this->set_status($json_array[json_fields::STATUS]);
        } else {
            $this->set_status('');
        }
        if (array_key_exists(json_fields::PRIORITY, $json_array)) {
            $this->set_priority($json_array[json_fields::PRIORITY]);
        } else {
            $this->set_priority(0);
        }
        return $usr_msg;
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
        $vars[json_fields::TIME_REQUEST] = $this->request_time()->format(DateTimeInterface::ATOM);
        $vars[json_fields::TIME_START] = $this->start_time()->format(DateTimeInterface::ATOM);
        $vars[json_fields::TIME_END] = $this->end_time()->format(DateTimeInterface::ATOM);
        $vars[json_fields::USER_ID] = $this->user_id();
        $vars[json_fields::TYPE] = $this->type();
        $vars[json_fields::STATUS] = $this->status();
        $vars[json_fields::PRIORITY] = $this->priority();
        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }

    /*
     * to review
     */

    /**
     * display a job with a link to the main page for the job
     * @param string|null $back the back trace url for the undo functionality
     * @param string $style the CSS style that should be used
     * @returns string the html code
     */
    function display_linked(?string $back = '', string $style = ''): string
    {
        $html = new html_base();
        $url = $html->url(\html\rest_ctrl::VIEW, $this->id(), $back, api::URL_VAR_WORDS);
        return $html->ref($url, $this->name(), $this->description(), $style);
    }

    /**
     * @param string $back the back trace url for the undo functionality
     * @param string $style the CSS style that should be used
     * @returns string the job as a table cell
     */
    function td(string $back = '', string $style = '', int $intent = 0): string
    {
        $cell_text = $this->display_linked($back, $style);
        return (new html_base)->td($cell_text, $intent);
    }

    /**
     * @param string $back the back trace url for the undo functionality
     * @param string $style the CSS style that should be used
     * @returns string the batch_job as a table cell
     */
    function th(string $back = '', string $style = ''): string
    {
        return (new html_base)->th($this->display_linked($back, $style));
    }

    /**
     * @return string the html code for a table row with the batch_job
     */
    function tr(): string
    {
        return (new html_base())->tr($this->td());
    }


}
