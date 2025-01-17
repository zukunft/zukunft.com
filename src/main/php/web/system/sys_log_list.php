<?php

/*

    /web/log/sys_log_list.php - the display extension of the system error log api object
    -------------------------


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

namespace html\system;

include_once WEB_SANDBOX_PATH . 'list_dsp.php';
include_once WEB_SYSTEM_PATH . 'sys_log.php';

use controller\controller;
use html\html_base;
use html\sandbox\list_dsp;
use html\system\sys_log as sys_log_dsp;
use html\user\user_message;
use shared\api;

class sys_log_list extends list_dsp
{

    /*
     * set and get
     */

    /**
     * set the vars of these list display objects bases on the api json array
     * TODO can be moved to list_dsp as soon as all list api message include the header
     * @param array $json_array an api list json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function set_from_json_array(array $json_array): user_message
    {
        $ctrl = new controller();
        $json_array = $ctrl->check_api_msg($json_array, api::JSON_BODY_SYS_LOG);
        return parent::set_list_from_json($json_array, new sys_log());
    }


    /*
     * modify
     */

    /**
     * add a system log entry to the list
     * @param sys_log_dsp $sys_log the log frontend entry that should be added to the list
     * @returns bool true if the system log entry has been added
     */
    function add(sys_log_dsp $sys_log): bool
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
     * @return string with a list of the sys_log names with html links
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
