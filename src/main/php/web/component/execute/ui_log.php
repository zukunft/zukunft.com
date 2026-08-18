<?php

/*

    web/component/execute/ui_log.php - html user interface components for change log
    ---------------------------------

    $log is the suggested var name

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

namespace Zukunft\ZukunftCom\main\php\web\component\execute;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::HTML . 'html_base.php';
include_once html_paths::LOG . 'change_log_list.php';
include_once html_paths::SANDBOX . 'db_object.php';
include_once html_paths::SYSTEM . 'sys_log_list.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::USER . 'user.php';
include_once html_paths::WORD . 'triple.php';
include_once html_paths::WORD . 'word.php';
include_once html_paths::SHARED_CONST . 'def.php';
include_once html_paths::SHARED_CONST . 'triples.php';
include_once html_paths::SHARED_CONST . 'words.php';
include_once html_paths::SHARED_ENUM . 'messages.php';
include_once html_paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\log\change_log_list;
use Zukunft\ZukunftCom\main\php\web\sandbox\db_object;
use Zukunft\ZukunftCom\main\php\web\system\sys_log_list;
use Zukunft\ZukunftCom\main\php\web\user\user;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\word\triple;
use Zukunft\ZukunftCom\main\php\web\word\word;
use Zukunft\ZukunftCom\main\php\shared\const\def;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;

class ui_log
{

    /*
     * display
     */

    /**
     * @return string with the html code that shows the recent changes of this object
     */
    function system_change_log(db_object $dbo, change_log_list $log_lst, user_message $msg, bool $test_mode = false): string
    {
        // the pure change log table below uses the same prepared list, so both are sorted equally
        return $this->prepared_change_log($dbo, $log_lst, $msg, $test_mode)->dsp(null, false, false, $test_mode);
    }

    /**
     * the pure (borderless) change log table of the given object with the three columns
     * when, who and what; the number of shown rows and the max chars of the what column both
     * come from the frontend config (config.yaml, like system_change_log above)
     *
     * @param db_object $dbo the word or triple whose change log is shown
     * @param change_log_list $log_lst the change log as loaded from the backend, used as fallback
     * @param bool $test_mode true to keep the change time deterministic in the snapshots
     * @return string the html code of the borderless when / who / what change log table
     */
    function change_log_table_pure(
        db_object       $dbo,
        change_log_list $log_lst,
        user_message    $msg,
        bool            $test_mode = false
    ): string
    {
        // use the same filtered, sorted and row-limited list as system_change_log, so the borderless
        // table is sorted with the same parameters as the previously used change log
        $log_lst = $this->prepared_change_log($dbo, $log_lst, $msg, $test_mode);
        return $this->table_pure($log_lst, $msg, $test_mode);
    }

    /**
     * the borderless when / who / what table of the session user's own overwrites of the given
     * object - the changes the user has written to the user_ overlay tables (e.g. user_words for
     * a word) - used by the 'my' tab of the view tab box; an empty string if the user is not
     * logged in or has no overwrites of this object, so the tab is only shown when it has content
     *
     * @param db_object $dbo the word or triple whose user overwrites are shown
     * @param change_log_list $log_lst the change log as loaded from the backend, used as fallback
     * @param bool $test_mode true to keep the change time deterministic in the snapshots
     * @return string the html code of the overwrite table or an empty string if there is nothing to show
     */
    function user_overwrites_table_pure(
        db_object       $dbo,
        change_log_list $log_lst,
        user_message    $msg,
        bool            $test_mode = false
    ): string
    {
        // the max number of chars of the what column and the max number of rows both come from the
        // frontend config (config.yaml > ... > change log > what limit / row limit)
        global $ui_sys;
        $result = '';
        $usr = $ui_sys->usr ?? null;
        if ($usr != null and ($usr->id() ?? 0) > 0) {
            $my_lst = $this->prepared_change_log($dbo, $log_lst, $msg, $test_mode, $usr);
            if (!$my_lst->is_empty()) {
                $result = $this->table_pure($my_lst, $msg, $test_mode);
            }
        }
        return $result;
    }

    /**
     * the fixed column of the user page with all changes the shown user has written to the user
     * sandbox (overlay) tables - today the word overwrites, and every further object type as soon
     * as its changes are part of the change log - reusing the filter, sort and table of the 'my'
     * tab of the word page, but filtered by the shown user over all objects instead of by the
     * session user for one object; unlike a tab the column is always shown, so an empty list
     * renders the no-changes message instead of an empty string
     *
     * @param db_object $dbo the user shown on the page whose sandbox changes are listed
     * @param change_log_list $log_lst the change log as loaded from the backend, used as fallback
     * @param user_message $msg to collect the mapping errors
     * @param bool $test_mode true to keep the change time deterministic in the snapshots
     * @param msg_id|null $ui_msg_code_id the message id of the headline shown in the user language
     * @return string the html code with the overwrite table or the no-changes message
     */
    function all_user_overwrites(
        db_object       $dbo,
        change_log_list $log_lst,
        user_message    $msg,
        bool            $test_mode = false,
        ?msg_id         $ui_msg_code_id = null
    ): string
    {
        global $mtr;
        global $ui_sys;

        $html = new html_base();
        $result = '';
        if ($ui_msg_code_id != null) {
            $result .= $html->text_h3($mtr->txt($ui_msg_code_id));
        }
        $my_lst = new change_log_list();
        if ($dbo instanceof user) {
            // use the given change log or, if that is empty, the global request cache
            // (like prepared_change_log, but without the object filter, because all
            // sandbox changes of the shown user are listed, not the changes of one object)
            if ($log_lst->is_empty() and $ui_sys != null) {
                $log_lst = $ui_sys->chg_log;
            }
            // hide the changes of the admin-only fields from users without admin rights
            $my_lst = $log_lst->filter_admin_fields($ui_sys->usr ?? null);
            // keep only the sandbox (user_ table) changes of the shown user
            $my_lst = $my_lst->filter_user_overwrites($dbo);
            $my_lst->sort_by_time_and_what($test_mode);
        }
        if ($my_lst->is_empty()) {
            $result .= $mtr->txt(msg_id::ALL_USER_OVERWRITES_NONE);
        } else {
            $result .= $this->table_pure($my_lst, $msg, $test_mode);
        }
        return $result;
    }

    /**
     * render the prepared change log as the borderless when / who / what table with the char and
     * row limits from the frontend config (config.yaml > ... > change log > what limit / row limit);
     * the shared final step of change_log_table_pure, user_overwrites_table_pure and all_user_overwrites
     *
     * @param change_log_list $log_lst the filtered, sorted and row-limited change log to render
     * @param bool $test_mode true to keep the change time deterministic in the snapshots
     * @return string the html code of the borderless when / who / what change log table
     */
    private function table_pure(change_log_list $log_lst, user_message $msg, bool $test_mode): string
    {
        global $ui_sys;
        $what_max_chars = 0;
        $max_rows = 0;
        if ($ui_sys?->cfg !== null) {
            $what_max_chars = (int)$ui_sys->cfg->get_by(
                [triples::WHAT_LIMIT, triples::CHANGE_LOG, words::FRONTEND, words::USER],
                $msg, 0);
            $max_rows = (int)$ui_sys->cfg->get_by(
                [triples::ROW_LIMIT, triples::CHANGE_LOG, words::FRONTEND, words::USER],
                $msg, 0);
        }
        return $log_lst->tbl_when_who_what($what_max_chars, $max_rows, $test_mode);
    }

    /**
     * select the change log source for the given object (the object's own recent changes, else the
     * given list, else the request cache), keep only its changes filtered to the object and sorted
     * newest first (change_log_list::sort_by_time_and_what), and limit them to the configured number
     * of rows; the shared preparation of system_change_log and change_log_table_pure so both use the
     * exact same filter, sort and limit parameters
     *
     * @param db_object $dbo the word or triple whose change log is shown
     * @param change_log_list $log_lst the change log as loaded from the backend, used as fallback
     * @param bool $test_mode true to sort the change time at whole-second resolution so the snapshot stays stable
     * @param user|null $overwrites_of if set keep only the user sandbox changes of this user (the 'my' tab)
     * @return change_log_list the filtered, sorted and row-limited change log ready to render
     */
    private function prepared_change_log(
        db_object       $dbo,
        change_log_list $log_lst,
        user_message    $msg,
        bool            $test_mode = false,
        ?user           $overwrites_of = null
    ): change_log_list
    {
        // a word or triple loaded for its page carries its recent changes directly (like the
        // related values, formulas and references); otherwise use the given change log or, if
        // that is empty, the global request cache
        if (($dbo::class == word::class or $dbo::class == triple::class) and $dbo->chg_log != null and !$dbo->chg_log->is_empty()) {
            $log_lst = $dbo->chg_log;
        } elseif ($log_lst->is_empty()) {
            global $ui_sys;
            if ($ui_sys == null) {
                log_warning('ui cache is empty');
                $log_lst = new change_log_list();
            } else {
                $log_lst = $ui_sys->chg_log;
            }
        }
        // filter the change log based on the given object
        $log_lst = $log_lst->filter($dbo);
        // hide the changes of the admin-only fields (the cached impact and usage numbers)
        // from users without admin or system rights
        global $ui_sys;
        $log_lst = $log_lst->filter_admin_fields($ui_sys->usr ?? null);
        // for the 'my' tab keep only the user sandbox (user_ table) changes of the session user;
        // filtered before the row limit so older overwrites are never pushed out by other changes
        if ($overwrites_of != null) {
            $log_lst = $log_lst->filter_user_overwrites($overwrites_of);
        }
        // newest change first; same-time changes are sorted ascending by the what text so the
        // display order never depends on the api/db row order (see docs/llm/frontend.md); in test
        // mode the time is bucketed to the whole second so sub-second write jitter cannot reorder rows
        $log_lst->sort_by_time_and_what($test_mode);
        // the number of change rows to show comes from the frontend config (like the values list)
        global $ui_sys;
        $limit = def::FALLBACK_DB_PAGE_ROWS;
        if ($ui_sys?->cfg !== null) {
            $limit = (int)$ui_sys->cfg->get_by(
                [triples::WORD_CHANGES, triples::ROW_LIMIT, words::FRONTEND, words::USER],
                $msg, def::FALLBACK_DB_PAGE_ROWS);
        }
        return $log_lst->head($limit);
    }

    /**
     * show the most relevant open system errors related to the user
     * so that the user can track when they are solved
     *
     * @param sys_log_list $err_lst the open system errors related to the user as loaded from the backend
     * @param msg_id|null $ui_msg_code_id the message id of the headline shown in the user language
     * @return string the html code with the error list or the no-error message
     */
    function user_system_errors(
        sys_log_list $err_lst,
        user_message $msg,
        ?msg_id      $ui_msg_code_id = null
    ): string
    {
        global $mtr;
        global $ui_sys;

        $html = new html_base();
        $result = '';
        if ($ui_msg_code_id != null) {
            $result .= $html->text_h3($mtr->txt($ui_msg_code_id));
        }
        if ($err_lst->is_empty()) {
            $result .= $mtr->txt(msg_id::USER_SYSTEM_ERRORS_NONE);
        } else {
            if ($ui_sys?->cfg !== null) {
                $limit = (int)$ui_sys->cfg->get_by(
                    [triples::SYSTEM_ERRORS, words::LIMIT, words::LISTS, words::FRONTEND, words::USER],
                    $msg, def::FALLBACK_USER_ERRORS
                );
            } else {
                $limit = def::FALLBACK_USER_ERRORS;
            }
            $result .= $err_lst->head($limit)->get_html($msg);
        }
        return $result;
    }

}
