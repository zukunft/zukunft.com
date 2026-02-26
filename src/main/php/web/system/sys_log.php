<?php

/*

    web/system/sys_log.php - create the html code to display on system log entry
    ----------------------

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

namespace Zukunft\ZukunftCom\main\php\web\system;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::LOG . 'log.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::USER . 'user.php';
include_once html_paths::USER . 'user_message.php';
include_once paths::SHARED_CONST . 'rest_ctrl.php';
include_once paths::SHARED_ENUM . 'sys_log_statuum.php';
include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';
include_once paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\log\log;
use Zukunft\ZukunftCom\main\php\web\user\user;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\rest_ctrl;
use Zukunft\ZukunftCom\main\php\shared\enum\sys_log_statuum;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use DateTimeInterface;
use DateTime;

class sys_log extends log
{

    /*
     * object vars
     */

    // TODO Prio 0 add the missing backend object fields
    public ?DateTime $log_time = null;
    public int $user_id = 0;
    public ?int $function_id = null;
    private ?string $trace = null;
    public ?int $level_id = null;
    public ?DateTime $update_time = null;
    public ?string $log_text = null;
    public ?string $description = null;
    // TODO use a simple user object instead of the id
    // the user or user group who is supposed to fix the issue
    public ?int $solver_id = null;
    public ?int $status_id = null;


    /*
     * set and get
     */

    /**
     * set the vars of this system log html object base on the api json array
     * @param array $json_array an api json message including the api message header
     * @param user_message $msg OK or a warning e.g. if the server version does not match
     * @return bool true if the mapping has been completed successfully
     */
    function api_mapper(array $json_array, user_message $msg): bool
    {
        $lib = new library();
        parent::api_mapper($json_array, $msg);
        if (array_key_exists(json_fields::TIME, $json_array)) {
            $this->log_time = $lib->get_datetime($json_array[json_fields::TIME]);
        }
        if (array_key_exists(json_fields::USER_ID, $json_array)) {
            if (is_numeric($json_array[json_fields::USER_ID])) {
                $this->user_id = $json_array[json_fields::USER_ID];
            } else {
                // TODO Prio 1 create the user object based of the json message
                $this->user_id = 0;
            }
        } else {
            $this->user_id = 0;
        }
        if (array_key_exists(json_fields::FUNCTION_ID, $json_array)) {
            $this->set_function_id($json_array[json_fields::FUNCTION_ID]);
        }
        if (array_key_exists(json_fields::TRACE, $json_array)) {
            $this->set_trace($json_array[json_fields::TRACE]);
        }
        if (array_key_exists(json_fields::TYPE, $json_array)) {
            $this->level_id = $json_array[json_fields::TYPE];
        }
        if (array_key_exists(json_fields::TIME_UPDATE, $json_array)) {
            $this->update_time = $lib->get_datetime($json_array[json_fields::TIME_UPDATE]);
        }
        if (array_key_exists(json_fields::TEXT, $json_array)) {
            $this->text = $json_array[json_fields::TEXT];
        }
        if (array_key_exists(json_fields::DESCRIPTION, $json_array)) {
            $this->description = $json_array[json_fields::DESCRIPTION];
        }
        if (array_key_exists(json_fields::SOLVER, $json_array)) {
            if (is_numeric($json_array[json_fields::SOLVER])) {
                $this->set_solver_id($json_array[json_fields::SOLVER]);
            } else {
                $this->set_solver_id(0);
            }
        } else {
            $this->set_solver_id(0);
        }
        if (array_key_exists(json_fields::STATUS, $json_array)) {
            $this->status_id = $json_array[json_fields::STATUS];
        }

        return $msg->is_ok();
    }

    function set_trace(string $trace): void
    {
        $this->trace = $trace;
    }

    function trace(): string
    {
        return $this->trace;
    }

    function set_function_id(string $function_id): void
    {
        $this->function_id = $function_id;
    }

    function prg_part(): string
    {
        return $this->function_id;
    }

    function set_solver_id(int|null $solver_id): void
    {
        $this->solver_id = $solver_id;
    }

    function owner_id(): int|null
    {
        return $this->solver_id;
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
        $vars[json_fields::TIME] = $this->time?->format(DateTimeInterface::ATOM);
        if ($this->user_id() > 0) {
            $vars[json_fields::USER_ID] = $this->user_id();
        }
        if ($this->function_id != null) {
            $vars[json_fields::FUNCTION_ID] = $this->function_id;
        }
        if ($this->trace != null) {
            $vars[json_fields::TRACE] = $this->trace;
        }
        if ($this->level_id != null) {
            $vars[json_fields::TYPE] = $this->level_id;
        }

        if ($this->update_time != null) {
            $vars[json_fields::TIME_UPDATE] = $this->update_time?->format(DateTimeInterface::ATOM);
        }
        if ($this->text != null) {
            $vars[json_fields::TEXT] = $this->text();
        }
        if ($this->description != null) {
            $vars[json_fields::DESCRIPTION] = $this->description;
        }
        if ($this->solver_id > 0) {
            $vars[json_fields::SOLVER] = $this->solver_id;
        }
        $vars[json_fields::STATUS] = $this->status();
        return $vars;
    }


    /*
     * display
     */

    /**
     * one user table entry
     * @returns string the html code to show one system log entry for non admin users
     */
    function display(): string
    {
        $html = new html_base();
        $row = '';
        // TODO replace with the user date format setting,
        //      which can also be the local system setting
        //      or the pod setting
        $row .= $html->td($this->time()->format('Y-m-d H:i:s'));
        // TODO show the username instead of the id
        $row .= $html->td($this->user_id());
        $row .= $html->td($this->text());
        $row .= $html->td($this->owner_id());
        $row .= $html->td($this->status());
        return $html->tr($row);
    }

    /**
     * one system log error as an overview page
     * @return string
     */
    function page_view(): string
    {
        $result = "";
        $html = new html_base();

        $result .= $html->dsp_text_h2("Status of error #"
            . $this->id() . ': ' . $this->status_text());
        $result .= '"' . $this->text() . '" <br>';
        if ($this->description <> 'NULL') {
            $result .= '"' . $this->description . '" <br>';
        }
        $result .= '<br>';
        $result .= 'Program trace:<br>';
        $result .= $this->trace() . ' ';
        //echo "<style color=green>OK</style>" .$test_text;
        //echo "<style color=red>Error</style>".$test_text;

        return $result;
    }

    /**
     * display a sys_log with a link to the main page for the sys_log
     * @param string|null $back the back trace url for the undo functionality
     * @param string $style the CSS style that should be used
     * @returns string the html code to show one system log entry for admin users
     */
    function display_admin(user $usr, ?string $back = '', string $style = ''): string
    {
        global $sys;

        $html = new html_base();
        $row = '';
        // TODO replace with the user date format setting,
        //      which can also be the local system setting
        //      or the pod setting
        $row .= $html->td($this->time()->format(DateTimeInterface::ATOM));
        // TODO show the user name instead of the id
        $row .= $html->td($this->user_name());
        $row .= $html->td($this->text());
        $row .= $html->td($this->trace());
        $row .= $html->td($this->prg_part());
        $row .= $html->td($this->owner_id());
        $row .= $html->td($this->status());
        if ($usr->is_admin() or $usr->is_system()) {
            $par_status = rest_ctrl::PAR_LOG_STATUS . '=' . $sys->typ_lst->sys_log_sta->id(sys_log_statuum::CLOSED);
            $url = $html->url(rest_ctrl::ERROR_UPDATE, $this->id, $back, '', $par_status);
            $row .= $html->td($html->ref($url, 'close'));
        }
        return $html->tr($row);
    }

    /**
     * @returns string the html code to show the table header for system log entries and non admin users
     */
    function header(): string
    {
        $html = new html_base();
        // TODO replace with language specific headers
        $result = $html->th('creation time');
        $result .= $html->th('user');
        $result .= $html->th('issue description');
        $result .= $html->th('owner');
        $result .= $html->th('status');
        return $html->tr($result);
    }

    /**
     * @returns string the html code to show the table header for system log entries and admin users
     */
    function header_admin(): string
    {
        $html = new html_base();
        // TODO replace with language specific headers
        $result = $html->th('creation time');
        $result .= $html->th('user');
        $result .= $html->th('issue description');
        $result .= $html->th('trace');
        $result .= $html->th('program part');
        $result .= $html->th('owner');
        $result .= $html->th('status');
        return $html->tr($result);
    }

    function get_html(?user $usr = null, string $back = ''): string
    {
        global $sys;

        $html = new html_base();
        $row_text = $html->td($this->time->format(DateTimeInterface::ATOM));
        if ($this->user_id() > 0) {
            $row_text .= $html->td($this->user()->name());
        } else {
            $row_text .= $html->td();
        }
        $row_text .= $html->td($this->text);
        $row_text .= $html->td($this->description);
        $row_text .= $html->td($this->trace);
        $row_text .= $html->td($this->function_id);
        if ($this->owner_id() > 0) {
            $row_text .= $html->td($this->owner()->name());
        } else {
            $row_text .= $html->td();
        }
        $row_text .= $html->td($this->status_name());
        if ($usr != null) {
            if ($usr->is_admin() or $usr->is_system()) {
                $par_status = rest_ctrl::PAR_LOG_STATUS . '=' . $sys->typ_lst->sys_log_sta->id(sys_log_statuum::CLOSED);
                $url = $html->url(rest_ctrl::ERROR_UPDATE, $this->id, $back, '', $par_status);
                $row_text .= $html->td($html->ref($url, 'close'));
            }
        }

        return $html->tr($row_text);
    }

    function user(): user
    {
        $usr = new user();
        $usr->load_by_id($this->user_id);
        return $usr;
    }

    function owner(): user
    {
        $usr = new user();
        $usr->load_by_id($this->owner_id());
        return $usr;
    }

    // TODO review
    function status_name(): string
    {
        return '"' . $this->status . '"';
    }


    /*
     * internal helper
     */

    private function status_text(): string
    {
        global $sys;
        return $sys->typ_lst->sys_log_sta->name($this->status());
    }

}
