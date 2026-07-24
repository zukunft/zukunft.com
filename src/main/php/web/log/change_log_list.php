<?php

/*

    web/log/change_log_list.php - a list function to create the HTML code to display a list of user changes
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

namespace Zukunft\ZukunftCom\main\php\web\log;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::HTML . 'html_base.php';
include_once html_paths::CONST . 'icons.php';
include_once html_paths::HTML . 'rest_call.php';
include_once html_paths::SANDBOX . 'db_object.php';
include_once html_paths::SANDBOX . 'ListBase.php';
include_once html_paths::SYSTEM . 'back_trace.php';
include_once html_paths::USER . 'user.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::HTML . 'styles.php';
include_once paths::SHARED_CONST . 'rest_ctrl.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'url_var.php';
include_once paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\const\icons;
use Zukunft\ZukunftCom\main\php\web\html\rest_call;
use Zukunft\ZukunftCom\main\php\web\sandbox\db_object;
use Zukunft\ZukunftCom\main\php\web\sandbox\ListBase;
use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\main\php\web\system\back_trace;
use Zukunft\ZukunftCom\main\php\web\user\user;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\const\rest_ctrl;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\url_var;

class change_log_list extends ListBase
{

    /*
     * set and get
     */

    /**
     * set the vars of a word object based on the given json
     * @param array $json_array an api single object json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function api_mapper(array $json_array): user_message
    {
        return parent::api_mapper_list($json_array, new change_log_named());
    }


    /*
     * load
     */

    /**
     * load a list of changes from the api
     *
     * @param string $class the class name of the object to test
     * @param int|string $id the database id of the object to which the changes should be listed
     * @param string $fld the url api field name to select only some changes e.g. 'word_field'
     * @param user|null $usr to select only the changes of this user
     * @param int $size to set a page size that is different from the default page size
     * @param int $page offset the number of pages
     * @return user_message to report any problems to the user
     */
    function load_by_object_field(
        string     $class,
        int|string $id = 1,
        string     $fld = '',
        user|null  $usr = null,
        int        $size = 0,
        int        $page = 0
    ): user_message
    {
        $usr_msg = new user_message();
        $json = $this->load_api_by_object_field($class, $id, $fld, $usr, $size, $page);
        $actual = json_decode($json, true);

        $this->set_from_json($actual);

        return $usr_msg;
    }

    /**
     * get the json of a list of changes from the api
     *
     * @param string $class the class name of the object to test
     * @param int|string $id the database id of the object to which the changes should be listed
     * @param string $fld the url api field name to select only some changes e.g. 'word_field'
     * @param user|null $usr to select only the changes of this user
     * @param int $limit to set a page size that is different from the default page size
     * @param int $page offset the number of pages
     * @return string the api json as a string
     */
    function load_api_by_object_field(
        string     $class,
        int|string $id = 1,
        string     $fld = '',
        user|null  $usr = null,
        int        $limit = 0,
        int        $page = 0
    ): string
    {
        $lib = new library();
        $log_class = $lib->class_to_name(change_log_list::class);
        $url = THIS_URL . url_var::API_PATH . $lib->camelize_ex_1($log_class);
        $class = $lib->class_to_api_name($class);
        $data = [];
        $data[url_var::LOG_CLASS] = $class;
        $data[url_var::ID] = $id;
        $data[url_var::LOG_FIELD] = $fld;
        $ctrl = new rest_call();
        return $ctrl->api_call(rest_ctrl::GET, $url, $data);
    }

    /**
     * if the change log list is empty fill it with the last changes
     * to reduce the number of backend calls during user input
     * @return bool
     */
    function load_fallback(): bool
    {
        $result = false;
        if ($this->is_empty()) {
            // TODO Prio 3 replace with an frequently generated preloaded list
            $this->set_lst($this->last_changes()->lst());
            $result = true;
        }
        return $result;
    }

    /**
     * @return change_log_list with the most often used phrases as a frontend fallback list
     */
    private function last_changes(): change_log_list
    {
        // TODO Prio 1 review
        return new change_log_list();
    }


    /*
     * filter
     */

    function filter(db_object $dbo): change_log_list
    {
        $lib = new library();
        $result = new change_log_list();
        $tbl_id_lst = $lib->ui_class_to_table_id_list($dbo::class);
        foreach ($this->lst() as $chg) {
            if (in_array($chg->table_id, $tbl_id_lst) ) {
                if ($chg->row_id == $dbo->id()) {
                    // allow duplicates: the api change entries carry no own id (all id 0), so the
                    // default id-dedup of add() would collapse every change into a single row
                    $result->add_obj($chg, true);
                }
            }
        }
        return $result;
    }

    /*
     * list
     */

    /**
     * sort this change list in place so that the newest change is first; changes with the same
     * time are sorted alphabetically ascending by the what text (the change description shown in
     * the what column, without the user) so the display order is deterministic and independent of
     * the db/api row order
     *
     * TODO Prio 1  try to find the reason why the wrting order of the changes due to the full test run changes
     * @return void
     */
    function sort_by_time_and_what(): void
    {
        $lst = $this->lst();
        usort($lst, fn(change_log_named $a, change_log_named $b) => $b->change_time <=> $a->change_time
            ?: strcmp($a->what_text(), $b->what_text()));
        $this->set_lst($lst);
    }

    /**
     * the first $limit changes of this list, used to show only the configured number of change rows
     * (like sys_log_list::head limits the user error list)
     *
     * @param int $limit the maximal number of change entries to keep
     * @return change_log_list a new list with at most the first $limit changes
     */
    function head(int $limit): change_log_list
    {
        $result = new change_log_list();
        $i = 0;
        foreach ($this->lst() as $chg) {
            if ($i < $limit) {
                // allow duplicates: the api change entries carry no own id (all id 0), so the
                // default id-dedup of add() would collapse every change into a single row
                $result->add_obj($chg, true);
            }
            $i++;
        }
        return $result;
    }

    /**
     * show all changes of a named user sandbox object e.g. a word as table
     * @param back_trace|null $back the back trace url for the undo functionality
     * @return string the html code with all words of the list
     */
    function dsp(?back_trace $back = null, bool $condensed = false, bool $with_users = false, bool $test_mode = false): string
    {
        $html_text = '';
        foreach ($this->lst() as $chg) {
            $html_text .= $chg->dsp($test_mode);
        }
        return $html_text;
    }


    /*
     * table
     */

    /**
     * show all changes of a named user sandbox object e.g. a word as table
     * @param back_trace|null $back the back trace url for the undo functionality
     * @return string the html code with all words of the list
     */
    function tbl(?back_trace $back = null, bool $condensed = false, bool $with_users = false): string
    {
        $html = new html_base();
        $html_text = $this->th($condensed, $with_users);
        foreach ($this->lst() as $chg) {
            $html_text .= $chg->tr($back, $condensed, $with_users);
        }
        return $html->tbl($html_text, styles::STYLE_BORDERLESS);
    }

    /**
     * the borderless change log table with the three columns when, who and what;
     * the what column is limited to the given number of chars and the table to the given number of
     * rows (both from config.yaml, read by ui_log::change_log_table_pure), so a long change stays on
     * one line and only the most recent changes are shown
     *
     * @param int $what_max_chars the max number of chars per what entry, 0 for no limit
     * @param int $max_rows the max number of change rows shown, 0 for no limit
     * @param bool $test_mode true to keep the change time deterministic in the snapshots
     * @return string the html code of the borderless when / who / what table
     */
    function tbl_when_who_what(int $what_max_chars, int $max_rows = 0, bool $test_mode = false): string
    {
        global $mtr;
        $html = new html_base();
        $head = $html->th($mtr->txt(msg_id::CHANGE_LOG_TBL_WHEN))
            . $html->th($mtr->txt(msg_id::CHANGE_LOG_TBL_WHO))
            . $html->th($mtr->txt(msg_id::CHANGE_LOG_TBL_WHAT));
        $rows = $html->tr($head);
        // show only the most recent changes up to the configured row limit (the list is already
        // sorted newest first by ui_log::prepared_change_log resp. the test)
        $lst = $max_rows > 0 ? $this->head($max_rows) : $this;
        foreach ($lst->lst() as $chg) {
            $rows .= $chg->tr_when_who_what($what_max_chars, $test_mode);
        }
        // the forward button appears when more changes exist than the row limit shows; the back
        // button is prepared for the paging implementation (see docs/llm/pending.md) but stays hidden
        // until the page offset is passed in, because the table currently always starts at the newest
        // change, so the first page is always shown
        $more_rows = ($max_rows > 0 and $this->count() > $max_rows);
        $first_page = true;
        $rows .= $this->tr_page_nav($more_rows, $first_page);
        // borderless table with the standard zukunft.com grey text
        return $html->tbl($rows, styles::STYLE_BORDERLESS_GREY);
    }

    /**
     * the paging footer row of the change log table pure: a forward button when more changes exist
     * than shown (the row limit is reached) and a back button when not the first (newest) page is
     * shown; the buttons are only the icons for now and do not yet navigate (see docs/llm/pending.md)
     *
     * @param bool $more_rows true if the list has more changes than the shown row limit
     * @param bool $first_page true if the first (newest) page is shown, so no back button is needed
     * @return string the html of the footer row, or '' if neither button is needed
     */
    private function tr_page_nav(bool $more_rows, bool $first_page): string
    {
        $html = new html_base();
        $result = '';
        if ($more_rows or !$first_page) {
            $back = !$first_page ? $html->icon(icons::PAGE_BACK) : '';
            $forward = $more_rows ? $html->icon(icons::PAGE_FORWARD) : '';
            // back button on the left, forward button right-aligned at the end of the table
            $result = $html->tr(
                $html->td($back)
                . $html->td('')
                . $html->td($forward, styles::TEXT_RIGHT));
        }
        return $result;
    }

    /**
     * @return string with the html table header to show the changes of sandbox objects e.g. a words
     */
    private function th(bool $condensed = false, bool $with_users = false): string
    {
        $html = new html_base();
        $head_text = $html->th('time');
        if ($condensed) {
            $head_text .= $html->th('changed to');
        } else {
            if ($with_users) {
                $head_text .= $html->th('user');
            }
            $head_text .= $html->th_row(array('field','from','to'));
        }
        $head_text .= $html->th('');  // extra column for the undo icon
        return $html->tr($head_text);
    }

}
