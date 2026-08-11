<?php

/*

    web/system/sys_log_list.php - the display extension of the system error log api object
    ---------------------------


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

namespace Zukunft\ZukunftCom\main\php\web\system;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::API_OBJECT . 'api_message.php';
include_once html_paths::API_OBJECT . 'controller.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::HTML . 'rest_call.php';
include_once html_paths::SANDBOX . 'ListBase.php';
include_once html_paths::SYSTEM . 'sys_log.php';
include_once html_paths::USER . 'user.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::SHARED . 'api.php';
include_once html_paths::SHARED . 'url_var.php';
include_once html_paths::SHARED_CONST . 'views.php';
include_once html_paths::SHARED_TYPES . 'api_type_list.php';

use Zukunft\ZukunftCom\main\php\api\api_message;
use Zukunft\ZukunftCom\main\php\api\controller;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\rest_call;
use Zukunft\ZukunftCom\main\php\web\user\user;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\url_var;

class sys_log_list
{

    /*
     *  object vars
     */

    // the list of log entries
    private array $lst = [];


    /*
     * construct and map
     */

    /**
     * @param string|null $api_json the api message to fill this system log list
     * @param user_message|null $msg to report the api mapping problems; null loses them
     */
    function __construct(?string $api_json = null, ?user_message $msg = null)
    {
        if ($api_json != null) {
            // only the api mapping needs a message; a caller that passes none loses the mapping
            // problems, so every caller that has one should hand it over
            $map_msg = $msg ?? new user_message(); // a local, because a parameter is never reassigned
            $this->set_from_json($api_json, $map_msg);
        }
    }


    /*
     * set and get
     */

    /**
     * set the vars of these list display objects bases on the api message
     * @param string $json_api_msg an api json message as a string
     * @param user_message $msg ok or a warning e.g. if the server version does not match
     * @return bool true if there are no errors
     */
    function set_from_json(string $json_api_msg, user_message $msg): bool
    {
        return $this->set_from_json_array(json_decode($json_api_msg, true), $msg);
    }

    /**
     * set the vars of these list display objects bases on the api json array
     * TODO can be moved to list_dsp as soon as all list api message include the header
     * @param array $json_array an api list json message
     * @param user_message $msg ok or a warning e.g. if the server version does not match
     * @return bool true if there are no errors
     */
    function set_from_json_array(array $json_array, user_message $msg): bool
    {
        $ctrl = new controller();
        $json_array = $ctrl->check_api_msg($json_array, api::JSON_BODY_SYS_LOG);
        foreach ($json_array as $value) {
            $new = new sys_log();
            $new->api_mapper($value, $msg);
            $this->add($new);
        }
        return $msg->is_ok();
    }


    /*
     * load
     */

    /**
     * request the system log entries related to the session user from the backend
     * @param string $dsp_type which log entries should be loaded e.g. 'all' or only the entries relevant for the user
     * @param user_message $msg the reasons why loading has failed
     * @param int $size the maximal number of log entries to load
     * @param int $page the offset in pages of the given size to load additional entries
     * @return bool true if all fine
     */
    function load_by_user(string $dsp_type, user_message $msg, int $size, int $page = 0): bool
    {
        $data = [];
        $data[url_var::LOG_STATUS] = $dsp_type;
        $data[url_var::LOG_SIZE] = $size;
        $data[url_var::LOG_PAGE] = $page;
        $rest = new rest_call();
        $json_body = $rest->api_get($this::class, $data);
        return $this->set_from_json_array($json_body, $msg);
    }


    /*
     * api
     */

    /**
     * create the api json message string of this list that can be sent to the backend
     * @param api_type_list|array $typ_lst configuration for the api message e.g. if phrases should be included
     * @param user|null $usr the user for whom the api message should be created which can differ from the session user
     * @return string with the api json string that should be sent to the backend
     */
    function api_json(api_type_list|array $typ_lst = [], user_message $msg = new user_message(), user|null $usr = null): string
    {
        $api_msg = new api_message();
        $pod_name = $api_msg->api_site_name();
        if (is_array($typ_lst)) {
            $typ_lst = new api_type_list($typ_lst);
        }
        $vars = $this->api_array($typ_lst, $msg);
        return $api_msg->api_json($pod_name, $this::class, $vars, $typ_lst, $usr);
    }

    /**
     * @return array the json message array to send the updated data to the backend
     * an array is used (instead of a string) to enable combinations of api_array($msg) calls
     */
    function api_array(api_type_list|array $typ_lst, user_message $msg): array
    {
        $result = array();
        foreach ($this->lst as $obj) {
            if ($obj != null) {
                $result[] = $obj->api_array($typ_lst, $msg);
            }
        }
        return $result;
    }


    /*
     * modify
     */

    /**
     * add a system log entry to the list
     * @param sys_log $sys_log the log frontend entry that should be added to the list
     * @returns bool true if the system log entry has been added
     */
    function add(sys_log $sys_log): bool
    {
        $this->lst[] = $sys_log;
        return true;
    }

    /**
     * @return bool true when the list contains no entries; mirrors the ListBase API so callers
     *              that pre-load the list can decide between rendering the table and an empty-state notice
     */
    function is_empty(): bool
    {
        return count($this->lst) === 0;
    }

    /**
     * @param int $limit the maximal number of log entries to keep
     * @return sys_log_list with only the first and most relevant entries of this list
     */
    function head(int $limit): sys_log_list
    {
        $err_lst = new sys_log_list();
        $err_lst->lst = array_slice($this->lst, 0, $limit);
        return $err_lst;
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
            $result .= $sys_log->display();
        }
        return $html->tbl($result);
    }

    /**
     * @param string $back the back trace url for the undo functionality
     * @return string with a list of the sys_log names with html links
     * ex. names_linked
     */
    function display_admin(user $usr, string $back = '', string $style = ''): string
    {
        $html = new html_base();
        $result = '';
        foreach ($this->lst as $sys_log) {
            if ($result == '') {
                $result .= $sys_log->header_admin();
            }
            $result .= $sys_log->display_admin($usr, $back, $style);
        }
        return $html->tbl($result);
    }

    /**
     * display the error that are related to the user, so that he can track when they are closed
     * or display the error that are related to the user, so that he can track when they are closed
     * called also from user_display.php/dsp_errors
     * @param user_message $msg to collect error message during the html creation
     * @param user|null $usr e.g. an admin user to allow updating the system errors
     * @param string $back
     * @return string the html code of the system log
     */
    function get_html(user_message $msg, ?user $usr = null, string $back = ''): string
    {
        $html = new html_base();
        $result = ''; // reset the html code var
        $rows = '';   // the html code of the rows

        if (count($this->lst) > 0) {
            // prepare to show a system log entry
            $row_nbr = 0;
            foreach ($this->lst as $log_ui) {
                $row_nbr++;
                if ($row_nbr == 1) {
                    $rows .= $this->headline_html();
                }
                $rows .= $log_ui->get_html($msg, $usr, $back);
            }
            $result = $html->tbl($rows);
        }

        return $html->main($result);
    }

    /**
     * @return string the HTML code for the table headline
     * should be corresponding to system_error_log_dsp::get_html
     */
    private function headline_html(): string
    {
        $result = '<tr>';
        $result .= '<th> creation time     </th>';
        $result .= '<th> user              </th>';
        $result .= '<th> issue description </th>';
        $result .= '<th> trace             </th>';
        $result .= '<th> program part      </th>';
        $result .= '<th> owner             </th>';
        $result .= '<th> status            </th>';
        $result .= '</tr>';
        return $result;
    }

    function get_html_page(user_message $msg, ?user $usr = null, string $back = ''): string
    {
        return $this->get_html_header('System log', $msg)
            . $this->get_html_navbar()
            . $this->get_html($msg, $usr, $back)
            . $this->get_html_footer();
    }

    /*
     * to review
     */

    function get_html_header(string $title, user_message $msg): string
    {
        if ($title == null) {
            $title = 'api message';
        } elseif ($title == '') {
            $title = 'api message';
        }
        $html = new html_base();
        return $html->header($title, $msg);
    }

    function get_html_navbar(): string
    {
        $html = new html_base();
        return $html->navbar(views::SYSTEM_LOG_ID);
    }

    function get_html_footer(): string
    {
        $html = new html_base();
        return $html->footer();
    }

}
