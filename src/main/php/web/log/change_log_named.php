<?php

/*

    web/log/change_log_named.php - a list function to create the HTML code to display a list of user changes
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

namespace Zukunft\ZukunftCom\main\php\web\log;

use DateTime;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::HTML . 'button.php';
include_once html_paths::HTML . 'html_base.php';
//include_once html_paths::FORMULA . 'formula.php';
include_once html_paths::LOG . 'change_log.php';
include_once html_paths::SYSTEM . 'back_trace.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::SHARED_CONST . 'views.php';
include_once html_paths::SHARED_CONST_FIELDS . 'fields.php';
include_once html_paths::SHARED_ENUM . 'change_actions.php';
include_once html_paths::SHARED_ENUM . 'change_tables.php';
include_once html_paths::SHARED_ENUM . 'change_fields.php';
include_once html_paths::SHARED_ENUM . 'messages.php';
include_once html_paths::SHARED . 'json_fields.php';

use Zukunft\ZukunftCom\main\php\web\html\button;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\system\back_trace;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\fields\fields;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\change_actions;
use Zukunft\ZukunftCom\main\php\shared\enum\change_fields;
use Zukunft\ZukunftCom\main\php\shared\enum\change_tables;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;

class change_log_named extends change_log
{

    /*
     * test
     */

    // a fixed change time shown in test mode so the change log snapshots stay
    // deterministic regardless of when the test data was created;
    // matches test_const::DUMMY_DATETIME used to set the test change log entries
    const string TEST_TIME = '2022-12-26T18:23:45+01:00';

    // appended to the what column of the change log table pure when the text is longer than the
    // configured char limit, to indicate that the full text is shown in the mouseover popup
    const string MORE_INDICATOR = '...';

    // the translated suffix of a reference (id) field name that is dropped when only the field itself
    // is named (not its value), e.g. 'view id' -> 'view' in 'remove user overwrite for view'
    // TODO Prio 2 detect by an const array of the db id field names not a text pattern
    const string FIELD_ID_SUFFIX = ' id';


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
     * @param user_message $msg ok or a warning e.g. if the server version does not match
     */
    function api_mapper(array $json_array, user_message $msg): bool
    {
        parent::api_mapper($json_array, $msg);
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
        return $msg->is_ok();
    }


    /*
     * table
     */

    /**
     * @return string with the html code to show one row of the changes of sandbox objects e.g. a words
     */
    function tr(back_trace $back, bool $condensed = false, bool $user_changes = false): string
    {
        global $ui_sys;
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
        // adding an empty value in the user sandbox removes the user's overwrite for that field, so
        // show 'remove user overwrite for view' in the field column (the old and new value are empty)
        if ($this->is_user_overwrite_removal()) {
            $txt_fld = $this->user_overwrite_removal_txt();
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

        $time_text = date_format($this->change_time, $ui_sys->cfg->date_time_format());
        if (!$user_changes) {
            $time_text .= ' by ' . $html->esc($this->usr->name);
        }
        // the old and new value are user settable (e.g. a word name or a
        // description), so escape them before they reach the history table
        $txt_old = $html->esc($txt_old);
        $txt_new = $html->esc($txt_new);
        $html_text .= $html->td($time_text);
        if ($condensed) {
            // the overwrite removal has no value to show, so show only 'remove user overwrite for
            // view' without the trailing ': ' that a normal condensed field / value change uses
            if ($this->is_user_overwrite_removal()) {
                $html_text .= $html->td($txt_fld);
            } else {
                $html_text .= $html->td($txt_fld . ': ' . $txt_new);
            }
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
                $undo_call = $html->url_new(views::VALUE_DEL_ID, $this->id(), '', $back->url_encode());
                $undo_btn = new button($undo_call)->undo(msg_id::UNDO_ADD);
            }
        } elseif ($this->table_name() == change_tables::VIEW) {
            if ($this->action_code_id() == change_actions::ADD) {
                $undo_call = $html->url_new(views::VALUE_DEL_ID, $this->id(), '', $back->url_encode());
                $undo_btn = new button($undo_call)->undo(msg_id::UNDO_EDIT);
            }
        } elseif ($this->table_name() == change_tables::FORMULA) {
            if ($this->action_code_id() == change_actions::UPDATE) {
                $undo_call = $html->url_new(
                    views::FORMULA_EDIT_ID, $this->row_id, '',
                    $back->url_encode() . '&undo_change=' . $this->id());
                $undo_btn = new button($undo_call)->undo(msg_id::UNDO_DEL);
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
        global $ui_sys;
        $action = $ui_sys->typ_lst_cache->cng_act->get($this->action_id);
        return $action->code_id;
    }

    /**
     * @return string the name of the change action e.g. add, change or delete
     */
    private function action_name(): string
    {
        global $ui_sys;
        $action = $ui_sys->typ_lst_cache->cng_act->get($this->action_id);
        return $action->name;
    }

    /**
     * @return string the name of the change field name to show it to the user
     */
    private function field_description(): string
    {
        global $ui_sys;
        $field = $ui_sys->typ_lst_cache->cng_fld->get($this->field_id);
        return $field->description;
    }

    /**
     * the changed field name without the table id prefix, e.g. 'description' for the code id
     * '5description' (the code id is the table id followed by the field name, see change_log::set_field)
     * @return string the changed field name e.g. 'description', 'phrase_type_id' or 'word_name'
     */
    private function field(): string
    {
        $prefix = (string)$this->table_id;
        $code_id = $this->field_code_id();
        $result = $code_id;
        if (str_starts_with($code_id, $prefix)) {
            $result = substr($code_id, strlen($prefix));
        }
        return $result;
    }

    /**
     * the translated name of the changed field followed by a space, used to prefix the changed value
     * in the change log table pure (e.g. 'added description "..."'); empty for the object's own prime
     * field (name, value or external key), because the log row already names that object
     * @return string the lower-cased translated field name and a space e.g. 'description ', or '' for a prime field
     */
    private function field_name_prefix(): string
    {
        global $mtr;
        $field = $this->field();
        $result = '';
        if ($field != '' and !in_array($field, change_fields::PRIME_FIELDS, true)) {
            $result = lcfirst($mtr->text_db_field($field)) . ' ';
        }
        return $result;
    }

    /**
     * the translated name of the changed field on its own (no value), with the reference (id) suffix
     * dropped so a reference field reads naturally, e.g. 'view_id' -> 'view'; used to name the field
     * when only the field is shown e.g. 'remove user overwrite for view'
     * @return string the lower-cased translated field name without a trailing ' id' e.g. 'view'
     */
    function field_name(): string
    {
        global $mtr;
        $name = lcfirst($mtr->text_db_field($this->field()));
        $result = $name;
        if (str_ends_with($name, self::FIELD_ID_SUFFIX)) {
            $result = substr($name, 0, -strlen(self::FIELD_ID_SUFFIX));
        }
        return $result;
    }

    /**
     * the value to show for the old or new value of a change: for a type field the type name resolved
     * from the type id, so the user sees the type name instead of the type number; the type id is taken
     * from the reference id if set (a change logged via sql_par_field_list::add_type_field) and else
     * from a numeric value (e.g. the protection id logged via sandbox_multi::add_field); any other field
     * keeps its raw value
     *
     * @param int|null $ref_id the change reference id (old_id / new_id), set for a type change via add_type_field
     * @param string|null $value the raw old / new value, which for some type fields is the numeric type id
     * @return string the type name for a type field, else the raw value
     */
    private function value_to_show(?int $ref_id, ?string $value): string
    {
        global $ui_sys;
        $result = $value ?? '';
        $typ_lst = $ui_sys->typ_lst_cache->field_to_type_list($this->field());
        if ($typ_lst != null) {
            $type_id = $ref_id;
            if ($type_id === null and is_numeric($value)) {
                $type_id = (int)$value;
            }
            if ($type_id !== null) {
                $result = $typ_lst->name($type_id);
            }
        }
        // a view reference that carries only the view id (e.g. logged by a save that only knows
        // the id, see word::set_view_id) shows the view name resolved from the cache
        if ($result == '' and $ref_id != null and $this->field() == fields::FLD_VIEW) {
            $result = $this->view_name_from_cache($ref_id);
        }
        return $result;
    }

    /**
     * @param int $msk_id the database id of the view
     * @return string the view name from the system or user view cache or '' if unknown
     */
    private function view_name_from_cache(int $msk_id): string
    {
        global $ui_sys;
        $result = '';
        $msk = $ui_sys?->typ_lst_cache?->msk_sys?->get($msk_id);
        if ($msk == null) {
            $msk = $ui_sys?->msk_lst?->get($msk_id);
        }
        if ($msk != null) {
            $result = $msk->name() ?? '';
        }
        return $result;
    }

    /**
     * @return string the name of the change table name
     */
    private function table_name(): string
    {
        global $ui_sys;
        $table = $ui_sys->typ_lst_cache->cng_tbl->get($this->table_id);
        return $table->name;
    }

    /**
     * @param bool $test_mode true to show a fixed change time so that automatic
     *                        test snapshots stay deterministic
     * @return string the current change as a human-readable text
     */
    public function dsp(bool $test_mode = false): string
    {
        global $ui_sys;

        // in test mode use a fixed change time so the change log snapshots do not
        // change with the moment the test data happened to be created
        $time = $test_mode ? new DateTime(self::TEST_TIME) : $this->change_time;
        return date_format($time, $ui_sys->cfg->date_time_format()) . ' ' . $this->entry();
    }

    /**
     * the human-readable text of this change without the change time, also used to sort changes
     * of the same time deterministically (see change_log_list::sort_by_time_and_what)
     * @return string the change entry text e.g. 'zukunft.com system test added "Zurich"'
     */
    function entry(): string
    {
        global $mtr;
        $html = new html_base();
        $result = '';

        // the user name and the old / new value are user-settable (e.g. a word / triple / formula
        // name on add or rename), and this one-line entry is rendered raw into the html body by the
        // "changes" tab and the system change-log component, so escape them here to stop stored xss
        // (matching change_log_named::tr()); escaping in entry() also keeps the sort deterministic
        $usr_name = $this->usr != null ? $html->esc($this->usr->name()) : '';
        $old_value = $html->esc($this->old_value);
        $new_value = $html->esc($this->new_value);

        if ($usr_name <> '') {
            $result .= $usr_name . ' ';
        }
        // a change in the user sandbox adds a translatable 'user' after the action
        // (see action_txt), so a user sandbox add shows e.g. 'zukunft.com system added user "Zurich"'
        if ($this->old_value <> '') {
            if ($this->new_value <> '') {
                // show the new value first because it is the more relevant one: 'changed to "new" from "old"'
                $result .= $this->action_txt(msg_id::LOG_UPDATE) . ' ' . $mtr->txt(msg_id::LOG_TO) . ' "' . $new_value . '" ' . $mtr->txt(msg_id::SIDE_FROM) . ' "' . $old_value . '"';
            } else {
                $result .= $this->action_txt(msg_id::LOG_DEL) . ' "' . $old_value . '"';
            }
        } elseif ($this->is_user_overwrite_removal()) {
            // adding an empty value in the user sandbox removes the user's overwrite for that field,
            // so instead of '... added user ""' show '... remove user overwrite for view'
            $result .= $this->user_overwrite_removal_txt();
        } else {
            $result .= $this->action_txt(msg_id::LOG_ADD) . ' "' . $new_value . '"';
        }
        return $result;
    }

    /**
     * the change as the 'what' column of the change log table pure: the action and the old and
     * new value without the user (the user is the separate 'who' column), limited to the given number
     * of chars (from config.yaml, see ui_log::change_log_table_pure) and html escaped to stop
     * stored xss because the old and new value are user settable (like entry() and tr())
     *
     * @param int $max_chars the max number of chars shown, 0 or less for no limit
     * @return string the length limited and escaped change description without the user name
     */
    function what(int $max_chars): string
    {
        $html = new html_base();

        // start from the raw description, so the char limit counts the visible chars and never cuts
        // an html entity in half, and escape only the final shortened text; the '...' shows that
        // the full text is available in the mouseover popup (see tr_when_who_what)
        $what = $this->what_text();
        if ($max_chars > 0 and mb_strlen($what) > $max_chars) {
            $what = mb_substr($what, 0, $max_chars) . self::MORE_INDICATOR;
        }
        return $html->esc($what);
    }

    /**
     * the raw (unescaped and untruncated) 'what' text of this change: the action and the old and new
     * value without the user (the user is the separate 'who' column); the shared source of what()
     * and the tie-break sort key of the change log table (change_log_list::sort_by_time_and_what)
     *
     * @return string the change description without the user name
     */
    function what_text(): string
    {
        global $mtr;
        // for a type field show the type name instead of the type id (see value_to_show)
        $new = $this->value_to_show($this->new_id, $this->new_value);
        if ($this->field() == change_fields::FLD_USER_ID) {
            // a change of the owner (user_id) shows 'set owner to' instead of 'added user id'; the new
            // owner is often the change author, whose name is already resolved (and shown in the who
            // column), so reuse that name instead of the raw user id, otherwise keep the quoted id
            if ($this->usr != null and $new === (string)$this->usr->id()) {
                $result = $this->action_txt(msg_id::LOG_SET_OWNER) . ' ' . $this->usr->name();
            } else {
                $result = $this->action_txt(msg_id::LOG_SET_OWNER) . ' "' . $new . '"';
            }
        } else {
            // the translated field name (e.g. 'description ') unless the object's own prime field changed
            $fld = $this->field_name_prefix();
            $old = $this->value_to_show($this->old_id, $this->old_value);
            if ($this->old_value <> '') {
                if ($this->new_value <> '') {
                    // show the new value first because it is the more relevant one:
                    // 'changed description to "new" from "old"'
                    $result = $this->action_txt(msg_id::LOG_UPDATE) . ' ' . $fld . $mtr->txt(msg_id::LOG_TO)
                        . ' "' . $new . '" ' . $mtr->txt(msg_id::SIDE_FROM) . ' "' . $old . '"';
                } else {
                    $result = $this->action_txt(msg_id::LOG_DEL) . ' ' . $fld . '"' . $old . '"';
                }
            } elseif ($this->is_user_overwrite_removal()) {
                // adding an empty value in the user sandbox removes the user's overwrite for that
                // field, so instead of 'added user view id ""' show 'remove user overwrite for view'
                $result = $this->user_overwrite_removal_txt();
            } else {
                $result = $this->action_txt(msg_id::LOG_ADD) . ' ' . $fld . '"' . $new . '"';
            }
        }
        return $result;
    }

    /**
     * the translated change action, prefixed with a translatable 'user' for a change in the user
     * sandbox (a *_usr overlay table) e.g. 'added user' instead of 'added'; shared by the change log
     * table (what_text) and the changes tab / system change-log text (entry)
     *
     * @param msg_id $action the change action message id e.g. msg_id::LOG_ADD
     * @return string the translated action, with the 'user' prefix for a user sandbox change
     */
    private function action_txt(msg_id $action): string
    {
        global $mtr;
        $result = $mtr->txt($action);
        if ($this->is_user_sandbox_change()) {
            $result = $result . ' ' . $mtr->txt(msg_id::LOG_USER);
        }
        return $result;
    }

    /**
     * public because the test helpers check with it in which table a change has been logged
     * @return bool true if this change is logged to a user sandbox (overlay) table, i.e. it is a
     *              user-specific change and not a change of the shared standard object
     */
    function is_user_sandbox_change(): bool
    {
        return in_array($this->table_name(), change_tables::USER_TABLES, true);
    }

    /**
     * @return bool true if this change adds an empty value in the user sandbox, i.e. it removes the
     *              user's overwrite for that field (shown as 'remove user overwrite for ...')
     */
    private function is_user_overwrite_removal(): bool
    {
        $result = false;
        if ($this->is_user_sandbox_change() and $this->old_value == '' and $this->new_value == '') {
            $result = true;
        }
        return $result;
    }

    /**
     * the text shown when a user sandbox change removes the user's overwrite for a field, e.g.
     * 'remove user overwrite for view'; shared by the change log table (what_text) and the changes
     * tab / system change-log text (entry)
     * @return string the translated 'remove user overwrite for' message and the field name
     */
    private function user_overwrite_removal_txt(): string
    {
        global $mtr;
        return $mtr->txt(msg_id::LOG_REMOVE_USER_OVERWRITE) . ' ' . $this->field_name();
    }

    /**
     * one row of the change log table pure with the three columns when, who and what
     *
     * @param int $what_max_chars the max number of chars shown in the what column (config.yaml), 0 for no limit
     * @param bool $test_mode true to use a fixed change time so the change log snapshots stay deterministic
     * @return string the html code of the borderless table row
     */
    function tr_when_who_what(int $what_max_chars, bool $test_mode = false): string
    {
        global $ui_sys;
        $html = new html_base();

        // in test mode use a fixed change time so the snapshot does not change with the moment
        // the test data happened to be created (like dsp())
        $time = $test_mode ? new DateTime(self::TEST_TIME) : $this->change_time;
        $when = date_format($time, $ui_sys->cfg->date_time_format());
        // link the user name to the user default page so a click shows that user (name_link escapes
        // the user-settable name via html_base::ref, like the plain esc() used before)
        $who = $this->usr != null ? $this->usr->name_link() : '';
        // show the full change text as a mouseover popup only when the what column is shortened, so the
        // user can still read the complete change that the '...' indicates (td escapes the title)
        $full_what = $this->what_text();
        $popup = ($what_max_chars > 0 and mb_strlen($full_what) > $what_max_chars) ? $full_what : '';
        $what_cell = $html->td($this->what($what_max_chars), '', 0, $popup);
        return $html->tr($html->td($when) . $html->td($who) . $what_cell);
    }

}
