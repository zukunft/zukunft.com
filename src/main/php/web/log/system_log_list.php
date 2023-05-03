<?php

/*

    /web/log/system_log_list.php - the display extension of the system error log api object
    ----------------------------


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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace html\log;

include_once WEB_SANDBOX_PATH . 'list.php';
include_once WEB_LOG_PATH . 'system_log.php';

use controller\controller;
use html\html_base;
use html\list_dsp;
use html\log\system_log as system_log_dsp;

class system_log_list extends list_dsp
{

    /*
     * set and get
     */

    /**
     * set the vars of these list display objects bases on the api json array
     * TODO can be moved to list_dsp as soon as all list api message include the header
     * @param array $json_array an api list json message
     * @return void
     */
    function set_from_json_array(array $json_array): void
    {
        $ctrl = new controller();
        $json_array = $ctrl->check_api_msg($json_array, controller::API_BODY_SYS_LOG);
        foreach ($json_array as $value) {
            $this->add_obj($this->set_obj_from_json_array($value));
        }
    }

    /**
     * set the vars of a system log object based on the given json
     * @param array $json_array an api single object json message
     * @return object a system log set based on the given json
     */
    function set_obj_from_json_array(array $json_array): object
    {
        $sys_log = new system_log_dsp();
        $sys_log->set_from_json_array($json_array);
        return $sys_log;
    }


    /*
     * modify
     */

    /**
     * add a system_log to the list
     * @returns bool true if the system_log has been added
     */
    function add(system_log_dsp $sys_log): bool
    {
        return parent::add_obj($sys_log);
    }


    /*
     * display
     */

    /**
     * @return string with a table of the system log entries for users
     */
    function display(): string
    {
        $html = new html_base();
        $result = '';
        foreach ($this->lst as $sys_log) {
            if ($result == '') {
                $result .= $sys_log->header();
            }
            $result .= $html->tr($sys_log->display());
        }
        return $html->tbl($result);
    }

    /**
     * @param string $back the back trace url for the undo functionality
     * @return string with a list of the system_log names with html links
     * ex. names_linked
     */
    function display_admin(string $back = '', string $style = ''): string
    {
        $html = new html_base();
        $result = '';
        foreach ($this->lst as $sys_log) {
            if ($result == '') {
                $result .= $sys_log->header_admin();
            }
            $result .= $html->tr($sys_log->display_admin($back, $style));
        }
        return $html->tbl($result);
    }

}
