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

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::HTML . 'html_base.php';
include_once html_paths::LOG . 'change_log_list.php';
include_once html_paths::SANDBOX . 'db_object.php';
include_once html_paths::SYSTEM . 'sys_log_list.php';
include_once html_paths::WORD . 'triple.php';
include_once html_paths::WORD . 'word.php';
include_once paths::SHARED_CONST . 'def.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\log\change_log_list;
use Zukunft\ZukunftCom\main\php\web\sandbox\db_object;
use Zukunft\ZukunftCom\main\php\web\system\sys_log_list;
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
    function system_change_log(db_object $dbo, change_log_list $log_lst, bool $test_mode = false): string
    {
        // the pure change log table below uses the same prepared list, so both are sorted equally
        return $this->prepared_change_log($dbo, $log_lst)->dsp(null, false, false, $test_mode);
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
    function change_log_table_pure(db_object $dbo, change_log_list $log_lst, bool $test_mode = false): string
    {
        // use the same filtered, sorted and row-limited list as system_change_log, so the borderless
        // table is sorted with the same parameters as the previously used change log
        $log_lst = $this->prepared_change_log($dbo, $log_lst);
        // the max number of chars of the what column and the max number of rows both come from the
        // frontend config (config.yaml > ... > change log > what limit / row limit)
        global $ui_sys;
        $what_max_chars = 0;
        $max_rows = 0;
        if ($ui_sys?->cfg !== null) {
            $what_max_chars = (int)$ui_sys->cfg->get_by(
                [triples::WHAT_LIMIT, triples::CHANGE_LOG, words::FRONTEND, words::USER], 0);
            $max_rows = (int)$ui_sys->cfg->get_by(
                [triples::ROW_LIMIT, triples::CHANGE_LOG, words::FRONTEND, words::USER], 0);
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
     * @return change_log_list the filtered, sorted and row-limited change log ready to render
     */
    private function prepared_change_log(db_object $dbo, change_log_list $log_lst): change_log_list
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
        // newest change first; same-time changes are sorted ascending by the what text so the
        // display order never depends on the api/db row order (see docs/llm/frontend.md)
        $log_lst->sort_by_time_and_what();
        // the number of change rows to show comes from the frontend config (like the values list)
        global $ui_sys;
        $limit = def::FALLBACK_DB_PAGE_ROWS;
        if ($ui_sys?->cfg !== null) {
            $limit = (int)$ui_sys->cfg->get_by(
                [triples::WORD_CHANGES, triples::ROW_LIMIT, words::FRONTEND, words::USER],
                def::FALLBACK_DB_PAGE_ROWS);
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
    function user_system_errors(sys_log_list $err_lst, ?msg_id $ui_msg_code_id = null): string
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
                    def::FALLBACK_USER_ERRORS
                );
            } else {
                $limit = def::FALLBACK_USER_ERRORS;
            }
            $result .= $err_lst->head($limit)->get_html();
        }
        return $result;
    }

}
