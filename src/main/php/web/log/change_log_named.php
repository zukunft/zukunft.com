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
include_once html_paths::CONST . 'icons.php';
// the classes of the objects that a change can change, used by the undo icon of the all user
// overwrites column to build the confirm url of the edit view (see CLASS_BY_TABLE)
// component_exe.php is included before component.php, because component.php must never be the
// first file of the component package that is loaded: its own includes lead via view_list back
// to component_exe.php, which loads system_form, and system_form extends component, which is
// not defined yet at that moment; component_exe.php loads the two in the working order
include_once html_paths::COMPONENT . 'component_exe.php';
include_once html_paths::COMPONENT . 'component.php';
include_once html_paths::COMPONENT . 'component_link.php';
include_once html_paths::FORMULA . 'formula.php';
include_once html_paths::FORMULA . 'formula_link.php';
include_once html_paths::REF . 'source.php';
include_once html_paths::SANDBOX . 'db_object.php';
include_once html_paths::VALUE . 'value.php';
include_once html_paths::VIEW . 'term_view.php';
include_once html_paths::VIEW . 'view.php';
include_once html_paths::VIEW . 'view_relation.php';
include_once html_paths::WORD . 'triple.php';
include_once html_paths::WORD . 'word.php';
include_once html_paths::SHARED_CONST . 'views.php';
include_once html_paths::SHARED_CONST_FIELDS . 'fields.php';
include_once html_paths::SHARED_ENUM . 'change_actions.php';
include_once html_paths::SHARED_ENUM . 'change_tables.php';
include_once html_paths::SHARED_ENUM . 'change_fields.php';
include_once html_paths::SHARED_ENUM . 'messages.php';
include_once html_paths::SHARED . 'json_fields.php';

use Zukunft\ZukunftCom\main\php\web\component\component;
use Zukunft\ZukunftCom\main\php\web\component\component_link;
use Zukunft\ZukunftCom\main\php\web\const\icons;
use Zukunft\ZukunftCom\main\php\web\formula\formula;
use Zukunft\ZukunftCom\main\php\web\formula\formula_link;
use Zukunft\ZukunftCom\main\php\web\html\button;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\ref\source;
use Zukunft\ZukunftCom\main\php\web\sandbox\db_object;
use Zukunft\ZukunftCom\main\php\web\system\back_trace;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\value\value;
use Zukunft\ZukunftCom\main\php\web\view\term_view;
use Zukunft\ZukunftCom\main\php\web\view\view;
use Zukunft\ZukunftCom\main\php\web\view\view_relation;
use Zukunft\ZukunftCom\main\php\web\word\triple;
use Zukunft\ZukunftCom\main\php\web\word\word;
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

    //TODO Prio 2 check if this is not defined several times and move it to a mapper class
    // the frontend class of the changed object per standard table name, so that the undo icon of
    // the all user overwrites column can use the edit view and the url vars of the changed object;
    // a table that is missing here simply gets no undo icon, because without the class the link
    // would not know which edit view to open (see undo_link)
    const array CLASS_BY_TABLE = [
        change_tables::WORD => word::class,
        change_tables::TRIPLE => triple::class,
        change_tables::VALUE => value::class,
        change_tables::FORMULA => formula::class,
        change_tables::FORMULA_LINK => formula_link::class,
        change_tables::SOURCE => source::class,
        change_tables::VIEW => view::class,
        change_tables::VIEW_COMPONENT => component::class,
        change_tables::VIEW_LINK => component_link::class,
        change_tables::VIEW_TERM_LINK => term_view::class,
        change_tables::VIEW_RELATION => view_relation::class,
    ];

    // between the name of the changed object and the change itself in a change log that lists the
    // changes of more than one object e.g. 'Pi: added user description "..."'
    const string OBJECT_SEPARATOR = ': ';

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
    // the value resp. the reference id of the shared standard object, which the change log itself
    // does not contain and which the backend adds only for a change log that spans the objects of
    // one user (see cfg/log/change_log_list::load_changed_objects), so that the all user overwrites
    // column can show the user value beside the common value
    public ?string $std_value = null;
    public ?int $std_id = null;
    // the name of the changed object e.g. the word name of a word change, set by the backend from
    // the row id (see cfg/log/change_log_list::load_changed_objects) and null if the backend has not
    // resolved a name for this change; used to name the object in a change log that lists the
    // changes of more than one object e.g. the all user overwrites column of the user page
    public ?string $row_name = null;


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
        if (array_key_exists(json_fields::STD_VALUE, $json_array)) {
            $this->std_value = $json_array[json_fields::STD_VALUE];
        } else {
            $this->std_value = null;
        }
        if (array_key_exists(json_fields::STD_ID, $json_array)) {
            $this->std_id = $json_array[json_fields::STD_ID];
        } else {
            $this->std_id = null;
        }
        if (array_key_exists(json_fields::ROW_NAME, $json_array)) {
            $this->row_name = $json_array[json_fields::ROW_NAME];
        } else {
            $this->row_name = null;
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
                $undo_call = $html->url_back(views::VALUE_DEL_ID, $this->id(), '', $back->url_encode());
                $undo_btn = new button($undo_call)->undo(msg_id::UNDO_ADD);
            }
        } elseif ($this->table_name() == change_tables::VIEW) {
            if ($this->action_code_id() == change_actions::ADD) {
                $undo_call = $html->url_back(views::VALUE_DEL_ID, $this->id(), '', $back->url_encode());
                $undo_btn = new button($undo_call)->undo(msg_id::UNDO_EDIT);
            }
        } elseif ($this->table_name() == change_tables::FORMULA) {
            if ($this->action_code_id() == change_actions::UPDATE) {
                $undo_call = $html->url_back(
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
     *
     * public because a filter on the changed field must use the database field name and never the
     * translated name of field_name(), which would depend on the user language
     * (see change_log_list::filter_admin_fields)
     *
     * @return string the changed field name e.g. 'description', 'phrase_type_id' or 'word_name'
     */
    function field(): string
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
     * @param bool $with_object true to name the changed object in front of the text
     * @return string the length limited and escaped change description without the user name
     */
    function what(int $max_chars, bool $with_object = false): string
    {
        $html = new html_base();

        // start from the raw description, so the char limit counts the visible chars and never cuts
        // an html entity in half, and escape only the final shortened text; the '...' shows that
        // the full text is available in the mouseover popup (see tr_when_who_what)
        $what = $this->what_text($with_object);
        if ($max_chars > 0 and mb_strlen($what) > $max_chars) {
            $what = mb_substr($what, 0, $max_chars) . self::MORE_INDICATOR;
        }
        $result = $html->esc($what);
        // the link is added after the shortening, so that the char limit counts the visible chars
        // and never the html of the link
        if ($with_object) {
            $result = $this->object_link($result);
        }
        return $result;
    }

    /**
     * the raw (unescaped and untruncated) 'what' text of this change: the action and the old and new
     * value without the user (the user is the separate 'who' column); the shared source of what()
     * and the tie-break sort key of the change log table (change_log_list::sort_by_time_and_what)
     *
     * @param bool $with_object true to name the changed object in front of the text, which is
     *                          needed if the change log lists the changes of more than one object;
     *                          the sort key never names the object, so that the sort of a list
     *                          does not depend on how the list is displayed
     * @return string the change description without the user name
     */
    function what_text(bool $with_object = false): string
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
                        . ' "' . $new . '" ' . $mtr->txt(msg_id::SIDE_FROM) . ' "' . $old . '"'
                        . $this->std_value_txt($new);
                } else {
                    $result = $this->action_txt(msg_id::LOG_DEL) . ' ' . $fld . '"' . $old . '"';
                }
            } elseif ($this->is_user_overwrite_removal()) {
                // adding an empty value in the user sandbox removes the user's overwrite for that
                // field, so instead of 'added user view id ""' show 'remove user overwrite for view'
                $result = $this->user_overwrite_removal_txt();
            } else {
                $result = $this->action_txt(msg_id::LOG_ADD) . ' ' . $fld . '"' . $new . '"'
                    . $this->std_value_txt($new);
            }
        }
        if ($with_object) {
            $result = $this->object_prefix() . $result;
        }
        return $result;
    }

    /**
     * the name of the changed object put in front of the change text e.g. 'Pi: ', so that the user
     * knows which object has been changed if the change log lists more than one object
     *
     * the name comes first and not after the change, because the what column is shortened to the
     * configured number of chars (config.yaml 'what limit'), which would cut off a trailing object
     * name exactly for the long changes; this way the object stays visible and the value is the
     * part that moves into the mouseover popup
     *
     * @return string the name of the changed object and the separator,
     *                or '' if the backend has not sent a name for this change
     */
    /**
     * the value of the shared standard object shown after the user value of an overwrite, e.g.
     * ' instead of "the common description"', so that the user page shows the user value beside
     * the common value like the 'my' tab of an object page does in its two columns
     *
     * the standard value is empty for a change log of one object, because the backend adds it only
     * for the change log that spans the objects of one user (see change_log_list::load_changed_objects)
     *
     * @param string $new the shown user value, to skip a standard value that says the same
     * @return string the leading space and the standard value, or '' if there is nothing to compare
     */
    private function std_value_txt(string $new): string
    {
        global $mtr;
        $result = '';
        $std = $this->value_to_show($this->std_id, $this->std_value);
        if ($std != '' and $std != $new) {
            $result = ' ' . $mtr->txt(msg_id::LOG_INSTEAD_OF) . ' "' . $std . '"';
        }
        return $result;
    }

    private function object_prefix(): string
    {
        $result = '';
        if ($this->row_name != null and $this->row_name != '') {
            $result = $this->row_name . self::OBJECT_SEPARATOR;
        }
        return $result;
    }

    /**
     * replace the name of the changed object at the start of the what text with a link to the
     * default page of the object, so that the user can open the object that has been changed
     * from the all user overwrites column instead of having to search it by name
     *
     * @param string $esc_what the escaped and shortened what text starting with the object name
     * @return string the what text with the object name as a link, or unchanged if no link can
     *                be built for this change
     */
    private function object_link(string $esc_what): string
    {
        $html = new html_base();
        $result = $esc_what;
        $prefix = $html->esc($this->object_prefix());
        // link only if the whole name has survived the shortening of the what column, because a
        // link on a cut name would look like the complete name of the object
        if ($prefix != '' and str_starts_with($esc_what, $prefix)) {
            $url = $this->changed_object()?->default_page_url() ?? '';
            if ($url != '') {
                $result = $html->ref($url, $this->row_name)
                    . self::OBJECT_SEPARATOR . substr($esc_what, strlen($prefix));
            }
        }
        return $result;
    }

    /**
     * TODO Prio 2 check if this is not defined several times and move it to a mapper class
     * the name of the table with the standard objects e.g. 'words' for a change of 'user_words',
     * because the user sandbox change of an object is a change of the same object
     *
     * @return string the standard table name of this change
     */
    private function std_table_name(): string
    {
        $result = $this->table_name();
        if (str_starts_with($result, change_tables::USER_PREFIX)) {
            $result = substr($result, strlen(change_tables::USER_PREFIX));
        }
        // a value is stored in the table that matches its type and the size of its group id, but
        // all of them name a value, so 'values_prime' or 'user_values_text' name the value class
        if (in_array($result, change_tables::VALUE_TABLES, true)) {
            $result = change_tables::VALUE;
        }
        return $result;
    }

    /**
     * an empty frontend object of the changed class with only the id set, which is all that the
     * undo link needs: the edit view id of the class, its db field to url var map and the id
     *
     * @return db_object|null the changed object or null if the class of the table is not known
     */
    private function changed_object(): ?db_object
    {
        $result = null;
        $class = self::CLASS_BY_TABLE[$this->std_table_name()] ?? null;
        if ($class != null and $this->row_id != null) {
            $result = new $class();
            $result->set_id($this->row_id);
        }
        return $result;
    }

    /**
     * the undo icon of a row of the all user overwrites column: it opens the confirm page of the
     * edit view of the changed object with the field set back to the value it had before this
     * change, so that confirming undoes exactly this one logged change
     *
     * unlike the undo of the 'my' tab, which knows the standard value of the field, this link can
     * only use the old value of the change, because a change log entry records the change and not
     * the value of the shared standard object (see docs/llm/pending.md)
     *
     * @param array $url_array the parsed url of the current page, carried into the undo link
     * @return string the html code of the undo icon link or an empty string if no link can be built
     */
    private function undo_link(array $url_array): string
    {
        global $mtr;
        $html = new html_base();
        $result = '';
        // only a change that the user has written to the user sandbox can be undone; a change of
        // the shared standard object is not an overwrite of this user
        if ($this->is_user_sandbox_change()) {
            $dbo = $this->changed_object();
            if ($dbo != null) {
                // a field that links to another object (e.g. the view of a word) is logged with
                // the name of the linked object, whereas the url carries its id, so the id is
                // used whenever the change has one (like value_to_show does for the change text)
                $url = $dbo->field_change_confirm_url(
                    $this->field(),
                    (string)($this->old_id ?? $this->old_value ?? ''),
                    (string)($this->new_id ?? $this->new_value ?? ''),
                    $url_array);
                if ($url != '') {
                    $icon = '<' . html_base::I . ' ' . html_base::CLASS_HTML
                        . '="' . icons::UNDO . '"></' . html_base::I . '>';
                    $result = $html->ref($url, $icon, $mtr->txt(msg_id::MY_TBL_UNDO), '', true);
                }
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
     * @param bool $with_object true to name the changed object in the what column, which is needed
     *                          if the table lists the changes of more than one object
     * @param bool $with_undo true to add the undo icon column
     * @param array $url_array the parsed url of the current page, carried into the undo link
     * @return string the html code of the borderless table row
     */
    function tr_when_who_what(
        int   $what_max_chars,
        bool  $test_mode = false,
        bool  $with_object = false,
        bool  $with_undo = false,
        array $url_array = []
    ): string
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
        $full_what = $this->what_text($with_object);
        $popup = ($what_max_chars > 0 and mb_strlen($full_what) > $what_max_chars) ? $full_what : '';
        $what_cell = $html->td($this->what($what_max_chars, $with_object), '', 0, $popup);
        // the undo cell stays empty for a change that cannot be undone e.g. a change of a shared
        // standard object or of a table without an edit view, so that the columns stay aligned
        $undo_cell = $with_undo ? $html->td($this->undo_link($url_array)) : '';
        return $html->tr($html->td($when) . $html->td($who) . $what_cell . $undo_cell);
    }

}
