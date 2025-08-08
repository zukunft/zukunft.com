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

namespace html\log;

use cfg\const\paths;
use html\const\paths as html_paths;

include_once html_paths::HTML . 'button.php';
include_once html_paths::HTML . 'html_base.php';
include_once paths::SHARED_CONST . 'rest_ctrl.php';
//include_once html_paths::FORMULA . 'formula.php';
include_once html_paths::LOG . 'change_log.php';
//include_once html_paths::HELPER . 'config.php';
include_once html_paths::SYSTEM . 'back_trace.php';
include_once html_paths::USER . 'user_message.php';
include_once paths::SHARED_CONST . 'rest_ctrl.php';
include_once paths::SHARED_ENUM . 'change_actions.php';
include_once paths::SHARED_ENUM . 'change_tables.php';
include_once paths::SHARED_ENUM . 'change_fields.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED . 'json_fields.php';

use html\formula\formula;
use html\helper\config;
use html\button;
use html\html_base;
use html\system\back_trace;
use html\user\user_message;
use shared\const\rest_ctrl;
use shared\enum\change_actions;
use shared\enum\change_fields;
use shared\enum\change_tables;
use shared\enum\messages as msg_id;
use shared\json_fields;

class change_log_named extends change_log
{

    /*
     * object vars
     */

    public ?string $old_value = null;      // the field value before the user change
    public ?int $old_id = null;            // the reference id before the user change e.g. for fields using a sub table such as status
    public ?string $new_value = null;      // the field value after the user change
    public ?int $new_id = null;            // the reference id after the user change e.g. for fields using a sub table such as status
    public ?string $std_value = null;  // the standard field value for all users that does not have changed it
    public ?int $std_id = null;        // the standard reference id for all users that does not have changed it


    /*
     * api
     */

    /**
     * set the vars of this object bases on the api json array
     * public because it is reused e.g. by the phrase group display object
     * @param array $json_array an api json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function api_mapper(array $json_array): user_message
    {
        $usr_msg = parent::api_mapper($json_array);
        if (array_key_exists(json_fields::OLD_VALUE, $json_array)) {
            $this->old_value = $json_array[json_fields::OLD_VALUE];
        } else {
            $this->old_value = null;
        }
        if (array_key_exists(json_fields::OLD_ID, $json_array)) {
            $this->old_id = $json_array[json_fields::OLD_ID];
        } else {
            $this->old_id = null;
        }
        if (array_key_exists(json_fields::NEW_VALUE, $json_array)) {
            $this->new_value = $json_array[json_fields::NEW_VALUE];
        } else {
            $this->new_value = null;
        }
        if (array_key_exists(json_fields::NEW_ID, $json_array)) {
            $this->new_id = $json_array[json_fields::NEW_ID];
        } else {
            $this->new_id = null;
        }
        return $usr_msg;
    }


    /*
     * table
     */

    /**
     * @return string with the html code to show one row of the changes of sandbox objects e.g. a words
     */
    function tr(back_trace $back, bool $condensed = false, bool $user_changes = false): string
    {
        $html = new html_base();

        $html_text = '';

        // pick the useful field name
        $txt_fld = '';
        if ($this->table_name() == change_tables::VALUE) {
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
            $txt_fld .= $this->field_description();
            // probably not needed to display the action, because this can be seen by the change itself
            // $result .= $db_row['type'].' '.$db_row['type_field'];
        } else {
            $txt_fld .= $this->table_name() . ' ' . $this->field_description();
        }

        // create the description for the old and new field value for the user
        $txt_old = $this->old_value;
        $txt_new = $this->new_value;
        // encode of text
        if ($this->field_code_id() == change_fields::FLD_ALL_NEEDED) {
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

        $usr_cfg = new config();
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
        // $undo_text = '';
        $undo_call = '';
        $undo_btn = '';
        if ($this->table_name() == change_tables::WORD) {
            if ($this->action_code_id() == change_actions::ADD) {
                $undo_call = $html->url('value' . rest_ctrl::REMOVE, $this->id(), $back->url_encode());
                $undo_btn = (new button($undo_call))->undo(msg_id::UNDO_ADD);
            }
        } elseif ($this->table_name() == change_tables::VIEW) {
            if ($this->action_code_id() == change_actions::ADD) {
                $undo_call = $html->url('value' . rest_ctrl::REMOVE, $this->id(), $back->url_encode());
                $undo_btn = (new button($undo_call))->undo(msg_id::UNDO_EDIT);
            }
        } elseif ($this->table_name() == change_tables::FORMULA) {
            if ($this->action_code_id() == change_actions::UPDATE) {
                $undo_call = $html->url(
                    formula::class . rest_ctrl::UPDATE, $this->row_id,
                    $back->url_encode() . '&undo_change=' . $this->id());
                $undo_btn = (new button($undo_call))->undo(msg_id::UNDO_DEL);
            }
        }
        // display the undo button
        if ($undo_call <> '') {
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
     * @return string the name of the change action e.g. add, change or delete
     */
    private function action_code_id(): string
    {
        global $cng_act_cac;

        $action = $cng_act_cac->get($this->action_id);
        return $action->code_id;
    }

    /**
     * @return string the name of the change action e.g. add, change or delete
     */
    private function action_name(): string
    {
        global $cng_act_cac;

        $action = $cng_act_cac->get_by_id($this->action_id);
        return $action->name;
    }

    /**
     * @return string the name of the change field code id for if tests
     */
    private function field_code_id(): string
    {
        global $cng_fld_cac;

        $field = $cng_fld_cac->get($this->field_id);
        return $field->code_id;
    }

    /**
     * @return string the name of the change field name to show it to the user
     */
    private function field_description(): string
    {
        global $cng_fld_cac;

        $field = $cng_fld_cac->get($this->field_id);
        return $field->description;
    }

    /**
     * @return string the name of the change table name
     */
    private function table_name(): string
    {
        global $cng_tbl_cac;

        $table = $cng_tbl_cac->get($this->table_id);
        return $table->name;
    }

    /**
     * @return string the current change as a human-readable text
     *                optional without time for automatic testing
     */
    public function dsp(bool $ex_time = false): string
    {
        global $mtr;
        $result = '';
        $usr_cfg = new config();

        if (!$ex_time) {
            $result .= date_format($this->change_time, $usr_cfg->date_time_format()) . ' ';
        }
        if ($this->usr != null) {
            if ($this->usr->name() <> '') {
                $result .= $this->usr->name() . ' ';
            }
        }
        if ($this->old_value <> '') {
            if ($this->new_value <> '') {
                $result .= $mtr->txt(msg_id::LOG_UPDATE) . ' "' . $this->old_value . '" ' . $mtr->txt(msg_id::LOG_TO) . ' "' . $this->new_value . '"';
            } else {
                $result .= $mtr->txt(msg_id::LOG_DEL) . ' "' . $this->old_value . '"';;
            }
        } else {
            $result .= $mtr->txt(msg_id::LOG_ADD) . ' "' . $this->new_value . '"';;
        }
        return $result;
    }

}
