<?php

/*

    /web/log/sys_log.php - create the html code to display on system log entry
    --------------------

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

include_once WEB_LOG_PATH . 'log.php';
include_once SHARED_PATH . 'json_fields.php';

use shared\api;
use DateTimeInterface;
use html\html_base;
use html\log\log as log_dsp;
use html\user\user_message;
use shared\json_fields;

class sys_log extends log_dsp
{

    /*
     * object vars
     */

    private string $trace;
    public ?string $prg_part;
    // the user or user group who is supposed to fix the issue
    // TODO use a simple user object instead of the id
    public string $owner_id;
    public ?string $description;


    /*
     * set and get
     */

    /**
     * set the vars of this system log html object bases on the api json array
     * @param array $json_array an api json message including the api message header
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function set_from_json_array(array $json_array): user_message
    {
        $usr_msg = parent::set_from_json_array($json_array);
        if (array_key_exists(json_fields::TRACE, $json_array)) {
            $this->set_trace($json_array[json_fields::TRACE]);
        } else {
            $this->set_trace('');
        }
        if (array_key_exists(json_fields::DESCRIPTION, $json_array)) {
            $this->description = $json_array[json_fields::DESCRIPTION];
        } else {
            $this->description = '';
        }
        if (array_key_exists(json_fields::PRG_PART, $json_array)) {
            $this->set_prg_part($json_array[json_fields::PRG_PART]);
        } else {
            $this->set_prg_part('');
        }
        if (array_key_exists(json_fields::OWNER, $json_array)) {
            if (is_numeric($json_array[json_fields::OWNER])) {
                $this->set_owner_id($json_array[json_fields::OWNER]);
            } else {
                $this->set_owner_id(0);
            }
        } else {
            $this->set_owner_id(0);
        }
        return $usr_msg;
    }

    function set_trace(string $trace): void
    {
        $this->trace = $trace;
    }

    function trace(): string
    {
        return $this->trace;
    }

    function set_prg_part(string $prg_part): void
    {
        $this->prg_part = $prg_part;
    }

    function prg_part(): string
    {
        return $this->prg_part;
    }

    function set_owner_id(int $owner_id): void
    {
        $this->owner_id = $owner_id;
    }

    function owner_id(): int
    {
        return $this->owner_id;
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
        $result = '';
        // TODO replace with the user date format setting,
        //      which can also be the local system setting
        //      or the pod setting
        $result .= $html->td($this->time()->format(DateTimeInterface::ATOM));
        // TODO show the username instead of the id
        $result .= $html->td($this->user_id());
        $result .= $html->td($this->text());
        $result .= $html->td($this->owner_id());
        $result .= $html->td($this->status());
        return $result;
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
    function display_admin(?string $back = '', string $style = ''): string
    {
        $html = new html_base();
        $result = '';
        // TODO replace with the user date format setting,
        //      which can also be the local system setting
        //      or the pod setting
        $result .= $html->td($this->time()->format(DateTimeInterface::ATOM));
        // TODO show the user name instead of the id
        $result .= $html->td($this->user_id());
        $result .= $html->td($this->text());
        $result .= $html->td($this->trace());
        $result .= $html->td($this->prg_part());
        $result .= $html->td($this->owner_id());
        $result .= $html->td($this->status());
        return $result;
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
        $vars[json_fields::TRACE] = $this->trace();
        $vars[json_fields::PRG_PART] = $this->prg_part();
        $vars[json_fields::OWNER] = $this->owner_id();
        return $vars;
    }


    /*
     * internal helper
     */

    private function status_text(): string
    {
        global $sys_log_stati;
        return $sys_log_stati->name($this->status());
    }

}
