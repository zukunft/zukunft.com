<?php

/*

    web/log/change_log_link.php - the frontend object to display one link change done by a user
    ---------------------------

    $cng_lnk is the suggested var name

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

    Copyright (c) 1995-2025 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\web\log;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::HTML . 'button.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::LOG . 'change_log_named.php';
include_once html_paths::SYSTEM . 'back_trace.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_ENUM . 'change_tables.php';
include_once paths::SHARED_ENUM . 'messages.php';

use Zukunft\ZukunftCom\main\php\web\html\button;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\system\back_trace;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\change_tables;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;

class change_log_link extends change_log_named
{

    /*
     * table
     */

    /**
     * @param back_trace|null $back the back trace url for the undo functionality
     * @param bool $condensed unused, kept to match the change_log_named signature
     * @param bool $user_changes unused, kept to match the change_log_named signature
     * @return string the html code to show one row of the link changes e.g. of a word
     */
    function tr(?back_trace $back = null, bool $condensed = false, bool $user_changes = false): string
    {
        global $ui_sys;
        $html = new html_base();

        $html_text = '';

        // time and user
        $html_text .= $html->td(date_format($this->change_time, $ui_sys->cfg->date_time_format()));
        if ($this->usr != null) {
            $html_text .= $html->td($this->usr->name());
        } else {
            $html_text .= $html->td();
        }

        // the link change description
        if ($this->old_value != '' and $this->new_value != '') {
            $html_text .= $html->td('change from ' . $this->old_value . ' to ' . $this->new_value);
        } elseif ($this->old_value != '') {
            $html_text .= $html->td('unlink from ' . $this->old_value);
        } elseif ($this->new_value != '') {
            $html_text .= $html->td('link to ' . $this->new_value);
        } else {
            $html_text .= $html->td();
        }

        // the undo button for a formula link change
        $undo_call = '';
        $undo_btn = '';
        if ($this->is_formula_link()) {
            $undo_call = $html->url_new(
                views::FORMULA_EDIT_ID, $this->row_id, '',
                ($back?->url_encode() ?? '') . '&undo_change=' . $this->id());
            $undo_btn = new button($undo_call)->undo(msg_id::UNDO_EDIT);
        }
        if ($undo_call != '') {
            $html_text .= $html->td($undo_btn);
        } else {
            $html_text .= $html->td();
        }

        return $html->tr($html_text);
    }


    /*
     * helpers
     */

    /**
     * @return string the name of the change table of this link change
     */
    private function table_name(): string
    {
        global $ui_sys;
        $table = $ui_sys->typ_lst_cache->cng_tbl->get($this->table_id);
        return $table->name;
    }

    /**
     * @return bool true if this link change is a formula link change (so an undo can be offered)
     */
    private function is_formula_link(): bool
    {
        $name = $this->table_name();
        return $name == change_tables::FORMULA_LINK or $name == change_tables::FORMULA_LINK_USR;
    }

    /**
     * @return string with the html table header to show the link changes
     */
    function th(): string
    {
        $html = new html_base();
        $head_text = $html->th_row(array('time', 'user', 'link'));
        $head_text .= $html->th('');  // extra column for the undo icon
        return $html->tr($head_text);
    }

}