<?php

/*

    api/log/change_log_list.php - a list function to create the HTML code to display a list of user changes
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

namespace html\log;

include_once API_SANDBOX_PATH . 'user_config.php';

use api\change_log_named_api;
use api\user_config;
use html\api;
use html\button;
use html\html_base;
use html\msg;
use html\system\back_trace;
use cfg\change_log_action;
use cfg\change_log_table;
use cfg\formula;

class change_log_named extends change_log_named_api
{


    /**
     * @return string with the html code to show one row of the changes of sandbox objects e.g. a words
     */
    function tr(back_trace $back, bool $condensed = false, bool $user_changes = false): string
    {
        $html = new html_base();

        $html_text = '';

        // pick the useful field name
        $txt_fld = '';
        if ($this->table_name() == change_log_table::VALUE) {
            $txt_fld .= $this->action_name() . ' value';
            // because changing the words creates a new value there is no need to display the words here
        /*
            if ($db_row['row_id'] > 0) {
              $val = New value;
              $val->id = $db_row['row_id'];
              $val->usr = $this;
              $val->load();
              $val->load_phrases();
              $txt_fld .= '<td>';
              if (isset($val->wrd_lst)) {
                $txt_fld .= implode(",",$val->wrd_lst->names_linked());
              }
              $txt_fld .= '</td>';
            } else {
              $txt_fld .= '<td>'.$db_row['type'].' value</td>';
            }
        */
        } elseif (!$user_changes) {
            $txt_fld .= $this->field_name();
            // probably not needed to display the action, because this can be seen by the change itself
            // $result .= $db_row['type'].' '.$db_row['type_field'];
        } else {
            $txt_fld .= $this->table_name() . ' ' . $this->field_name();
        }

        // create the description for the old and new field value for the user
        $txt_old = $this->old_value;
        $txt_new = $this->new_value;
        // encode of text
        if ($this->field_code_id() == formula::FLD_ALL_NEEDED) {
            if ($txt_old == "1") {
                $txt_old = "all values needed for calculation";
            } else {
                $txt_old = "calculate if one value is set";
            }
            if ($txt_new == "1") {
                $txt_new = "all values needed for calculation";
            } else {
                $txt_new = "calculate if one value is set";
            }
        }
        /* no encoding needed for this field at the moment
        if ($db_row["code_id"] == DBL_FLD_FORMULA_TYPE) {
          if ($txt_old <> "") { $txt_old = 'type '.$txt_old; }
          if ($txt_new <> "") { $txt_new = 'type '.$txt_new; }
        }
        */

        $usr_cfg = new user_config();
        $time_text = date_format($this->change_time, $usr_cfg->date_time_format());
        if (!$user_changes) {
            $time_text .= ' by ' . $this->usr->name;
        }
        $html_text .= $html->td($time_text);
        if ($condensed) {
            $html_text .= $html->td($txt_fld . ': ' . $txt_new);
        } else {

            // display the change
            $html_text .= $html->td($txt_fld);
            $html_text .= $html->td($txt_old);
            $html_text .= $html->td($txt_new);
            // switched off because "less seems to be more"
            //if ($txt_old == "") { $result .= '<td>'.$db_row["type"].'</td>'; } else { $result .= '<td>'.$txt_old.'</td>'; }
            //if ($txt_new == "") { $result .= '<td>'.$db_row["type"].'</td>'; } else { $result .= '<td>'.$txt_new.'</td>'; }
        }

        // encode the undo action
        $undo_text = '';
        $undo_call = '';
        $undo_btn = '';
        if ($this->table_name() == change_log_table::WORD) {
            if ($this->action_code_id() == change_log_action::ADD) {
                $undo_call = $html->url('value' . api::REMOVE, $this->id, $back->url_encode());
                $undo_btn = (new button($undo_call))->undo(msg::UNDO_ADD);
            }
        } elseif ($this->table_name() == change_log_table::VIEW) {
            if ($this->action_code_id() == change_log_action::ADD) {
                $undo_call = $html->url('value' . api::REMOVE, $this->id, $back->url_encode());
                $undo_btn = (new button($undo_call))->undo(msg::UNDO_EDIT);
            }
        } elseif ($this->table_name() == change_log_table::FORMULA) {
            if ($this->action_code_id() == change_log_action::UPDATE) {
                $undo_call = $html->url(
                    formula::class . api::UPDATE, $this->row_id,
                    $back->url_encode() . '&undo_change=' . $this->id());
                $undo_btn = (new button($undo_call))->undo(msg::UNDO_DEL);
            }
        }
        // display the undo button
        if ($condensed) {
            if ($undo_call <> '') {
                $html_text .= ' ' . $undo_btn;
            } else {
                $html_text .= '';
            }
        } else {
            if ($undo_call <> '') {
                $html_text .= $html->td($undo_btn);
            } else {
                $html_text .= $html->td();
            }
        }

        return $html->tr($html_text);
    }


    /*
     * helpers
     */

    /**
     * @return string the name of the change action e.g. add, change or delete
     */
    private function action_code_id(): string
    {
        global $change_log_actions;

        $action = $change_log_actions->get_by_id($this->action_id);
        return $action->code_id;
    }

    /**
     * @return string the name of the change action e.g. add, change or delete
     */
    private function action_name(): string
    {
        global $change_log_actions;

        $action = $change_log_actions->get_by_id($this->action_id);
        return $action->name;
    }

    /**
     * @return string the name of the change field code id for if tests
     */
    private function field_code_id(): string
    {
        global $change_log_fields;

        $field = $change_log_fields->get_by_id($this->field_id);
        return $field->code_id;
    }

    /**
     * @return string the name of the change field name to show it to the user
     */
    private function field_name(): string
    {
        global $change_log_fields;

        $field = $change_log_fields->get_by_id($this->field_id);
        return $field->comment;
    }

    /**
     * @return string the name of the change table name
     */
    private function table_name(): string
    {
        global $change_log_tables;

        $table = $change_log_tables->get_by_id($this->table_id);
        return $table->name;
    }

}
