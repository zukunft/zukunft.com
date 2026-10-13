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
include_once html_paths::SHARED . 'json_fields.php';
include_once html_paths::SHARED . 'url_var.php';
include_once html_paths::SHARED_CONST . 'def.php';
include_once html_paths::SHARED_CONST . 'triples.php';
include_once html_paths::SHARED_CONST . 'views.php';
include_once html_paths::SHARED_CONST . 'words.php';
include_once html_paths::SHARED_ENUM . 'messages.php';
include_once html_paths::SHARED_ENUM . 'sys_log_functions.php';
include_once html_paths::SHARED_ENUM . 'sys_log_levels.php';
include_once html_paths::SHARED_TYPES . 'api_type_list.php';

use Zukunft\ZukunftCom\main\php\api\api_message;
use Zukunft\ZukunftCom\main\php\api\controller;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\rest_call;
use Zukunft\ZukunftCom\main\php\web\user\user;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\const\def;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\enum\sys_log_functions;
use Zukunft\ZukunftCom\main\php\shared\enum\sys_log_levels;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\url_var;

class sys_log_list
{

    /*
     * const
     */

    // the backend selection of all not closed entries (see cfg/system/sys_log_list::DSP_ALL)
    const string DSP_ALL = 'all';
    // the number of the newest not closed entries read for the admin dashboard, which filters them by status
    // (see docs/llm/pending.md 'admin dashboard': the backend has no filter by status and solver yet)
    const int ADMIN_READ_SIZE = 1000;
    // the keys of one row of the failed logins grouped by the typed name (see login_fail_groups)
    const string GROUP_NAME = 'name';
    const string GROUP_COUNT = 'count';
    const string GROUP_LAST = 'last';


    /*
     *  object vars
     */

    // the list of log entries
    private array $lst = [];


    /*
     * construct and map
     */

    // an empty list needs no message, so this class has no constructor;
    // a caller with an api json message fills the list with set_from_json($api_json, $msg),
    // which reports the mapping problems


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
        // the backend sends the log entries like the other lists in the body of the api message header
        $json_array = $ctrl->check_api_msg($json_array);
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
        $json_body = $rest->api_get($this::class, $data, $msg);
        log_debug('sysLogList answer with ' . count($json_body) . ' entries', url_var::DEBUG_LEVEL_API_CALL);
        return $this->api_load($json_body, $msg);
    }

    /**
     * @param array $json_body the api answer with the log entries or the message why the entries are not sent
     * @param user_message $msg to report the message of the backend e.g. that only an admin can see the system log,
     *                          which is shown to the user in the notification bar of the page
     * @return bool true if the backend has sent the log entries
     */
    function api_load(array $json_body, user_message $msg): bool
    {
        if (array_key_exists(json_fields::MSG, $json_body)) {
            $msg->add(msg_id::API_MESSAGE, [msg_id::VAR_JSON_TEXT => $json_body[json_fields::MSG]]);
            $result = false;
        } else {
            $result = $this->set_from_json_array($json_body, $msg);
        }
        return $result;
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
     * @return int the number of log entries of this list
     */
    function count(): int
    {
        return count($this->lst);
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

    /**
     * @param array $status_codes the code ids of the statuum to keep e.g. sys_log_statuum::OPEN
     * @param bool $unassigned true to keep only the entries without a solver
     * @return sys_log_list the entries with one of the given statuum
     */
    function filtered(array $status_codes, bool $unassigned): sys_log_list
    {
        global $ui_sys;
        // the status list is preloaded in the frontend cache like for sys_log::status_text
        $sta_lst = $ui_sys->typ_lst_cache->sys_log_sta;
        $ids = array_map(fn(string $code_id) => $sta_lst->id($code_id), $status_codes);
        $err_lst = new sys_log_list();
        foreach ($this->lst as $err) {
            $has_status = in_array($err->status_id, $ids);
            $is_free = (!$unassigned or ($err->solver_id ?? 0) == 0);
            if ($has_status and $is_free) {
                $err_lst->add($err);
            }
        }
        return $err_lst;
    }

    /**
     * @param bool $newest_first true for the newest entry of the same level first, false for the longest not updated
     * @return sys_log_list the entries with the most critical level first
     */
    function critical_first(bool $newest_first): sys_log_list
    {
        $err_lst = new sys_log_list();
        $err_lst->lst = $this->lst;
        usort($err_lst->lst, fn(sys_log $a, sys_log $b) => $this->compare_critical($a, $b, $newest_first));
        return $err_lst;
    }

    /**
     * @param sys_log $a the first entry
     * @param sys_log $b the second entry
     * @param bool $newest_first true to sort the entries of the same level by the newest log time first
     * @return int below zero if $a is shown first
     */
    private function compare_critical(sys_log $a, sys_log $b, bool $newest_first): int
    {
        $result = $this->level_rank($a) <=> $this->level_rank($b);
        if ($result == 0 and $newest_first) {
            $result = $b->log_time <=> $a->log_time;
        } elseif ($result == 0) {
            $result = ($a->update_time ?? $a->log_time) <=> ($b->update_time ?? $b->log_time);
        }
        return $result;
    }

    /**
     * @param bool $critical true for the entries with a critical level (sys_log_levels::CRITICAL), false for the others
     * @return sys_log_list the critical resp. the not critical entries in the order of this list
     */
    function critical(bool $critical): sys_log_list
    {
        $err_lst = new sys_log_list();
        foreach ($this->lst as $err) {
            if (in_array($this->level_code_id($err), sys_log_levels::CRITICAL, true) === $critical) {
                $err_lst->add($err);
            }
        }
        return $err_lst;
    }

    /**
     * @param bool $only true to keep only the failed logins, false to keep all other entries
     * @return sys_log_list the failed logins resp. the other entries in the order of this list
     */
    function login_fails(bool $only): sys_log_list
    {
        $err_lst = new sys_log_list();
        foreach ($this->lst as $err) {
            if (($err->text() === sys_log_functions::LOGIN_FAILED_TEXT) === $only) {
                $err_lst->add($err);
            }
        }
        return $err_lst;
    }

    /**
     * group the failed logins by the typed name, which the log entry has as its description
     *
     * @return array one row per typed name with the keys self::GROUP_NAME, self::GROUP_COUNT and self::GROUP_LAST,
     *               the most often tried name first, then the newest last try and then the name
     */
    function login_fail_groups(): array
    {
        $groups = [];
        foreach ($this->login_fails(true)->lst as $err) {
            $name = $err->description ?? '';
            if (!array_key_exists($name, $groups)) {
                $groups[$name] = [self::GROUP_NAME => $name, self::GROUP_COUNT => 0, self::GROUP_LAST => $err->log_time];
            }
            $groups[$name][self::GROUP_COUNT]++;
            if ($err->log_time > $groups[$name][self::GROUP_LAST]) {
                $groups[$name][self::GROUP_LAST] = $err->log_time;
            }
        }
        $result = array_values($groups);
        usort($result, fn(array $a, array $b) => [$b[self::GROUP_COUNT], $b[self::GROUP_LAST], $a[self::GROUP_NAME]]
            <=> [$a[self::GROUP_COUNT], $a[self::GROUP_LAST], $b[self::GROUP_NAME]]);
        return $result;
    }

    /**
     * @param sys_log $err the log entry
     * @return int the position of its level in sys_log_levels::CRITICAL_FIRST, an unknown level last
     */
    private function level_rank(sys_log $err): int
    {
        $rank = array_search($this->level_code_id($err), sys_log_levels::CRITICAL_FIRST, true);
        return ($rank === false) ? count(sys_log_levels::CRITICAL_FIRST) : $rank;
    }

    /**
     * @param sys_log $err the log entry
     * @return string the code id of its level e.g. sys_log_levels::ERROR or an empty string if the level is not set
     */
    private function level_code_id(sys_log $err): string
    {
        global $ui_sys;
        // the level list is preloaded in the frontend cache like the status list
        $code_id = '';
        if ($err->level_id !== null) {
            $code_id = $ui_sys->typ_lst_cache->sys_log_lvl->get_code_id($err->level_id);
        }
        return $code_id;
    }

    /**
     * @param user_message $msg to report a problem of the config lookup
     * @return int the number of failed logins of the same name from which they are critical
     *             (config.yaml pod > limits > max login > per day > by ip-address)
     */
    static function login_fail_limit(user_message $msg): int
    {
        global $ui_sys;
        $limit = def::FALLBACK_MAX_LOGIN;
        if ($ui_sys?->cfg !== null) {
            $limit = (int)$ui_sys->cfg->get_by(
                [triples::BY_IP_ADDRESS, triples::PER_DAY, triples::MAX_LOGIN, words::LIMITS, words::POD],
                $msg, def::FALLBACK_MAX_LOGIN
            );
        }
        return $limit;
    }

    /**
     * @param user_message $msg to report a problem of the config lookup
     * @return int the number of system errors shown at once (config.yaml user > frontend > lists > limit > system errors)
     */
    static function display_limit(user_message $msg): int
    {
        global $ui_sys;
        $limit = def::FALLBACK_USER_ERRORS;
        if ($ui_sys?->cfg !== null) {
            $limit = (int)$ui_sys->cfg->get_by(
                [triples::SYSTEM_ERRORS, words::LIMIT, words::LISTS, words::FRONTEND, words::USER],
                $msg, def::FALLBACK_USER_ERRORS
            );
        }
        return $limit;
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
     * @param array $url_arr the url vars of the calling page for the back link of the close links
     * @return string with a list of the sys_log names with html links
     * ex. names_linked
     */
    function display_admin(user $usr, array $url_arr = [], string $style = ''): string
    {
        $html = new html_base();
        $result = '';
        foreach ($this->lst as $sys_log) {
            if ($result == '') {
                $result .= $sys_log->header_admin();
            }
            $result .= $sys_log->display_admin($usr, $url_arr, $style);
        }
        return $html->tbl($result);
    }

    /**
     * display the error that are related to the user, so that he can track when they are closed
     * or display the error that are related to the user, so that he can track when they are closed
     * @param user_message $msg to collect error message during the html creation
     * @param user|null $usr e.g. an admin user to allow updating the system errors
     * @param array $url_arr the url vars of the calling page for the back link of the close links
     * @return string the html code of the system log
     */
    function get_html(user_message $msg, ?user $usr = null, array $url_arr = []): string
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
                $rows .= $log_ui->get_html($msg, $usr, $url_arr);
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

    function get_html_page(user_message $msg, ?user $usr = null, array $url_arr = []): string
    {
        return $this->get_html_header('System log', $msg)
            . $this->get_html_navbar()
            . $this->get_html($msg, $usr, $url_arr)
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
