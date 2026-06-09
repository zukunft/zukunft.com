<?php

/*

    model/component/component.php - a single display object like a headline or a table
    ---------------------------

    $cmp is the suggested var name

    The main sections of this object are
    - db const:          const for the database link
    - object vars:       the variables of this component object
    - construct and map: including the mapping of the db row to this component object
    - api:               create an api array for the frontend and set the vars based on a frontend api message
    - im- and export:    create an export object and set the vars from an import object
    - set and get:       to capsule the vars from unexpected changes
    - preloaded:         select e.g. types from cache
    - load:              database access object (DAO) functions
    - sql fields:        field names for sql and other load helper functions
    - related:           load related objects assigned to this component from the database
    - cast:              create an api object and set the vars from an api json
    - info:              functions to make code easier to read
    - log:               write the changes to the log
    - link:              link and release the component to and from a view
    - save:              manage to update the database
    - del:               manage to remove from the database
    - sql write:         sql statement creation to write to the database
    - sql write fields:  field list for writing to the database
    - debug:             internal support functions for debugging


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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace Zukunft\ZukunftCom\main\php\cfg\component;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_par_field_list.php';
include_once paths::DB . 'sql_type.php';
include_once paths::DB . 'sql_type_list.php';
include_once paths::DB . 'sql_par_type.php';
include_once paths::MODEL_CONST . 'def.php';
include_once paths::MODEL_COMPONENT . 'component_db.php';
include_once paths::MODEL_COMPONENT . 'view_style.php';
include_once paths::EXPORT . 'export_type_list.php';
include_once paths::MODEL_FORMULA . 'formula.php';
include_once paths::MODEL_FORMULA . 'formula_db.php';
include_once paths::MODEL_HELPER . 'data_object.php';
include_once paths::MODEL_HELPER . 'db_object_seq_id.php';
include_once paths::MODEL_LOG . 'change.php';
include_once paths::MODEL_LOG . 'change_action.php';
include_once paths::MODEL_LOG . 'change_link.php';
include_once paths::MODEL_PHRASE . 'phrase.php';
include_once paths::MODEL_SANDBOX . 'sandbox.php';
include_once paths::MODEL_SANDBOX . 'sandbox_code_id.php';
include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_db.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::MODEL_VIEW . 'view.php';
include_once paths::MODEL_WORD . 'word.php';
include_once paths::SHARED_CONST . 'components.php';
include_once paths::SHARED_ENUM . 'change_actions.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_HELPER . 'CombineObject.php';
include_once paths::SHARED_HELPER . 'IdObject.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED_TYPES . 'position_types.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_field_list;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type_list;
use Zukunft\ZukunftCom\main\php\cfg\export\export_type_list;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_db;
use Zukunft\ZukunftCom\main\php\cfg\helper\data_object;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_object_seq_id;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;
use Zukunft\ZukunftCom\main\php\cfg\log\change;
use Zukunft\ZukunftCom\main\php\cfg\log\change_link;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_code_id;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_db;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\shared\enum\change_actions;
use Zukunft\ZukunftCom\main\php\shared\helper\CombineObject;
use Zukunft\ZukunftCom\main\php\shared\helper\IdObject;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\const\components;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\types\position_types;

class component extends sandbox_code_id
{

    /*
     * db const
     */

    // comments used for the database creation
    const string TBL_COMMENT = 'for the single components of a view';

    // forward the const to enable usage of $this::CONST_NAME
    const string FLD_ID = component_db::FLD_ID;
    const string FLD_NAME = component_db::FLD_NAME;
    const array FLD_LST_MUST_BE_IN_STD = component_db::FLD_LST_MUST_BE_IN_STD;
    const array FLD_LST_MUST_BUT_USER_CAN_CHANGE = component_db::FLD_LST_MUST_BUT_USER_CAN_CHANGE;
    const array FLD_LST_USER_CAN_CHANGE = component_db::FLD_LST_USER_CAN_CHANGE;
    const array FLD_LST_NON_CHANGEABLE = component_db::FLD_LST_NON_CHANGEABLE;
    const array FLD_NAMES = component_db::FLD_NAMES;
    const array FLD_NAMES_USR = component_db::FLD_NAMES_USR;
    const array FLD_NAMES_NUM_USR = component_db::FLD_NAMES_NUM_USR;
    const array ALL_SANDBOX_FLD_NAMES = component_db::ALL_SANDBOX_FLD_NAMES;


    /*
     * object vars
     */

    // database fields additional to the user sandbox fields for the view component

    // the parent code_id var is used to select a specific system component by the program code
    // the code id cannot be changed by the user
    // so this field is not part of the table user_components

    // to select a user interface language specific message
    // e.g. "add word" or "Wort zufügen"
    // the code id cannot be changed by the user
    // so this field is not part of the table user_components
    public ?msg_id $ui_msg_code_id = null;
    public ?msg_id $ui_msg_code_id_vars = null;
    public ?msg_id $ui_msg_code_id_exception = null;
    // TODO Prio 2 maybe use string instead and allow the usage of a formula for dynamic exception values
    // TODO Prio 3 maybe use a list of exception values and messages
    // TODO Prio 3 maybe link the system values and vars to user values so that the standard setup can also be used for system values
    public ?float $ui_msg_value_exception = null;

    // the position in the linked view
    // TODO dismiss and use link order number instead
    public ?int $order_nbr = null;

    // the word link type used to build the word tree started with the $start_word_id
    public ?int $link_type_id = null;

    // for a table to defined second columns layer or the second axis in case of a chart
    // e.g. for a "company cash flow statement" the "col word" could be "year"
    //      "col2 word" could be "Quarter" to show the Quarters between the year upon request
    public ?int $word_id_col2 = null;

    // database fields repeated from the component link for a easy to use in memory view object
    // TODO create a component_phrase_link table with a type fields where the type can be at least row, row_right, col and sub_col
    // TODO easy use the position type object similar to the style

    // linked fields

    // the object that should be shown to the user
    public ?object $obj = null;

    // if the view component uses a related word tree this is the start node
    // e.g. for "company" the start node could be "cash flow statement"
    // to show the cash flow for any company
    public ?phrase $row_phrase = null;

    // for a table to defined which columns should be used (if not defined by the calling word)
    public ?phrase $col_phrase = null;

    // the word object for $word_id_col2
    public ?phrase $col_sub_phrase = null;

    // the formula object for the main dynamic adjustment of the component
    private ?formula $frm = null;

    // the default display style for this component which can be overwritten by the link
    private ?type_object $style = null;


    /*
     * construct and map
     */

    /**
     * define the settings for this view component object
     * @param user $usr the user who requested to see this view
     */
    function __construct(user $usr)
    {
        $this->reset();
        parent::__construct($usr);

        $this->rename_can_switch = def::UI_CAN_CHANGE_VIEW_COMPONENT_NAME;
    }

    /**
     * clear the view component object values
     * @param bool $keep_user set to true to keep the original user
     * @return void
     */
    function reset(bool $keep_user = false): void
    {
        parent::reset($keep_user);

        $this->order_nbr = null;
        $this->type_id = null;
        $this->style = null;
        $this->link_type_id = null;
        $this->frm = null;
        $this->word_id_col2 = null;
        $this->row_phrase = null;
        $this->col_phrase = null;
        $this->col_sub_phrase = null;
        $this->ui_msg_code_id = null;
        $this->ui_msg_code_id_vars = null;
        $this->ui_msg_code_id_exception = null;
        $this->ui_msg_value_exception = null;
    }

    /**
     * map the database fields to the object fields
     *
     * @param array|null $db_row with the data directly from the database
     * @param bool $load_std true if only the standard user sandbox object is loaded
     * @param bool $allow_usr_protect false for using the standard protection settings for the default object used for all users
     * @param string $id_fld the name of the id field as defined in this child and given to the parent
     * @param string $name_fld the name of the name field as defined in this child class
     * @param string $type_fld the name of the type field as defined in this child class
     * @return bool true if the view component is loaded and valid
     */
    function row_mapper_sandbox(
        ?array $db_row,
        bool   $load_std = false,
        bool   $allow_usr_protect = true,
        string $id_fld = component_db::FLD_ID,
        string $name_fld = component_db::FLD_NAME,
        string $type_fld = component_db::FLD_TYPE
    ): bool
    {
        global $mtr;

        $result = parent::row_mapper_sandbox($db_row, $load_std, $allow_usr_protect, $id_fld, $name_fld, $type_fld);
        if ($result) {
            if (array_key_exists(component_db::FLD_UI_MSG_ID, $db_row)) {
                $msg_id_txt = $db_row[component_db::FLD_UI_MSG_ID];
                if ($msg_id_txt == null) {
                    $this->ui_msg_code_id = null;
                } else {
                    $this->ui_msg_code_id = $mtr->get($db_row[component_db::FLD_UI_MSG_ID]);
                }
            }
            if (array_key_exists(component_db::FLD_UI_MSG_ID_VARS, $db_row)) {
                $msg_id_txt = $db_row[component_db::FLD_UI_MSG_ID_VARS];
                if ($msg_id_txt == null) {
                    $this->ui_msg_code_id_vars = null;
                } else {
                    $this->ui_msg_code_id_vars = $mtr->get($db_row[component_db::FLD_UI_MSG_ID_VARS]);
                }
            }
            if (array_key_exists(component_db::FLD_UI_MSG_ID_EXCEPTION, $db_row)) {
                $msg_id_txt = $db_row[component_db::FLD_UI_MSG_ID_EXCEPTION];
                if ($msg_id_txt == null) {
                    $this->ui_msg_code_id_exception = null;
                } else {
                    $this->ui_msg_code_id_exception = $mtr->get($db_row[component_db::FLD_UI_MSG_ID_EXCEPTION]);
                }
            }
            if (array_key_exists(component_db::FLD_UI_MSG_VAL_EXCEPTION, $db_row)) {
                $msg_id_txt = $db_row[component_db::FLD_UI_MSG_VAL_EXCEPTION];
                if ($msg_id_txt == null) {
                    $this->ui_msg_value_exception = null;
                } else {
                    $this->ui_msg_value_exception = $db_row[component_db::FLD_UI_MSG_VAL_EXCEPTION];
                }
            }
            if (array_key_exists(component_db::FLD_STYLE, $db_row)) {
                $this->set_style_by_id($db_row[component_db::FLD_STYLE]);
            }
            if (array_key_exists(component_db::FLD_ROW_PHRASE, $db_row)) {
                $this->reload_row_phrase($db_row[component_db::FLD_ROW_PHRASE]);
            }
            if (array_key_exists(component_db::FLD_LINK_TYPE, $db_row)) {
                $this->link_type_id = $db_row[component_db::FLD_LINK_TYPE];
            }
            if (array_key_exists(formula_db::FLD_ID, $db_row)) {
                $this->set_formula_by_id($db_row[formula_db::FLD_ID]);
            }
            if (array_key_exists(component_db::FLD_COL_PHRASE, $db_row)) {
                $this->reload_col_phrase($db_row[component_db::FLD_COL_PHRASE]);
            }
            if (array_key_exists(component_db::FLD_COL2_PHRASE, $db_row)) {
                $this->word_id_col2 = $db_row[component_db::FLD_COL2_PHRASE];
            }
        }
        return $result;
    }

    /**
     * map a component api json to this model component object
     * @param array $api_json the api array with the values that should be mapped
     * @param user_message $usr_msg the message for the user why the action has failed and a suggested solution
     * @return bool true if the mapping has been completed successfully
     */
    function api_mapper(array $api_json, user_message $usr_msg): bool
    {
        parent::api_mapper($api_json, $usr_msg);

        // it is expected that the code id is set via import by an admin not via api
        if (array_key_exists(json_fields::UI_MSG_CODE_ID, $api_json)) {
            global $mtr;
            $this->ui_msg_code_id = $mtr->get($api_json[json_fields::UI_MSG_CODE_ID]);
        }
        if (array_key_exists(json_fields::UI_MSG_CODE_ID_VARS, $api_json)) {
            global $mtr;
            $this->ui_msg_code_id_vars = $mtr->get($api_json[json_fields::UI_MSG_CODE_ID_VARS]);
        }
        if (array_key_exists(json_fields::UI_MSG_CODE_ID_EXCEPTION, $api_json)) {
            global $mtr;
            $this->ui_msg_code_id_exception = $mtr->get($api_json[json_fields::UI_MSG_CODE_ID_EXCEPTION]);
        }
        if (array_key_exists(json_fields::UI_MSG_CODE_VAL_EXCEPTION, $api_json)) {
            global $mtr;
            $this->ui_msg_value_exception = $mtr->get($api_json[json_fields::UI_MSG_CODE_VAL_EXCEPTION]);
        }
        if (array_key_exists(json_fields::STYLE, $api_json)) {
            $this->set_style_by_id($api_json[json_fields::STYLE]);
        }
        if (array_key_exists(json_fields::FORMULA_ID, $api_json)) {
            $frm = $this->formula_from_api_json($api_json[json_fields::FORMULA_ID]);
            $this->set_formula($frm);
        }
        // TODO map e.g. the $row_phrase

        return $usr_msg->is_ok();
    }

    /**
     * import a view component from a JSON object
     * @param array $in_ex_json an array with the data of the json object
     * @param user_message $msg to enrich with warnings, problems and solutions including the user who has initiated the import mainly used to add tge code id to the database
     * @param data_object|null $dto cache of the objects imported until now for the primary references
     * @return bool true if everything was fine
     */
    function import_mapper(
        array        $in_ex_json,
        user_message $msg,
        ?data_object $dto = null
    ): bool
    {
        parent::import_mapper($in_ex_json, $msg, $dto);

        if (array_key_exists(json_fields::UI_MSG_CODE_ID, $in_ex_json)) {
            global $mtr;
            $this->ui_msg_code_id = $mtr->get($in_ex_json[json_fields::UI_MSG_CODE_ID]);
        }
        if (array_key_exists(json_fields::UI_MSG_CODE_ID_VARS, $in_ex_json)) {
            global $mtr;
            $this->ui_msg_code_id_vars = $mtr->get($in_ex_json[json_fields::UI_MSG_CODE_ID_VARS]);
        }
        if (array_key_exists(json_fields::UI_MSG_CODE_ID_EXCEPTION, $in_ex_json)) {
            global $mtr;
            $this->ui_msg_code_id_exception = $mtr->get($in_ex_json[json_fields::UI_MSG_CODE_ID_EXCEPTION]);
        }
        if (array_key_exists(json_fields::UI_MSG_CODE_VAL_EXCEPTION, $in_ex_json)) {
            $this->ui_msg_value_exception = $in_ex_json[json_fields::UI_MSG_CODE_VAL_EXCEPTION];
        }
        if (key_exists(json_fields::POSITION, $in_ex_json)) {
            $this->order_nbr = $in_ex_json[json_fields::POSITION];
        }
        if (key_exists(json_fields::STYLE, $in_ex_json)) {
            $style_name = $in_ex_json[json_fields::STYLE];
            if ($style_name != '') {
                $this->set_style($style_name);
            }
        }
        if (key_exists(json_fields::TYPE_NAME, $in_ex_json)) {
            $type_name = $in_ex_json[json_fields::TYPE_NAME];
            if ($type_name != '') {
                $this->set_type_id($this->type_id_by_code_id($type_name), $msg->usr);
            }
        }

        return $msg->is_ok();
    }


    /*
     * api
     */

    /**
     * create an array for the api json creation
     * differs from the export array by using the internal id instead of the names
     * @param api_type_list $typ_lst configuration for the api message e.g. if phrases should be included
     * @param user|null $usr the user for whom the api message should be created which can differ from the session user
     * @return array the filled array used to create the api json message to the frontend
     */
    function api_json_array(api_type_list $typ_lst, user|null $usr = null): array
    {
        $vars = [];
        if (!$this->is_excluded() or $typ_lst->test_mode() or $typ_lst->with_excluded()) {
            $vars = parent::api_json_array($typ_lst, $usr);
            if ($this->ui_msg_code_id != null) {
                $vars[json_fields::UI_MSG_CODE_ID] = $this->ui_msg_code_id;
            }
            if ($this->ui_msg_code_id_vars != null) {
                $vars[json_fields::UI_MSG_CODE_ID_VARS] = $this->ui_msg_code_id_vars;
            }
            if ($this->ui_msg_code_id_exception != null) {
                $vars[json_fields::UI_MSG_CODE_ID_EXCEPTION] = $this->ui_msg_code_id_exception;
            }
            if ($this->ui_msg_value_exception != null) {
                $vars[json_fields::UI_MSG_CODE_VAL_EXCEPTION] = $this->ui_msg_value_exception;
            }
            if ($this->get_style_id() > 0) {
                $vars[json_fields::STYLE] = $this->get_style_id();
            }
            if ($this->frm != null) {
                $vars[json_fields::FORMULA_ID] = $this->get_formula_id();
            }
        } elseif ($this->is_excluded() and $typ_lst->with_excluded_id()) {
            $vars[json_fields::ID] = $this->id();
            $vars[json_fields::EXCLUDED] = true;
        }

        return $vars;
    }

    /**
     * get a formula either with the id set or with all fields set based on an api json
     * TODO Prio 1 add user_message as parameter
     * @param int|array $value either the id itself or an array with the id
     * @return formula with at least the id set
     */
    private function formula_from_api_json(int|array $value): formula
    {
        $usr_msg = new user_message();
        $frm = new formula($this->get_user());
        if (is_array($value)) {
            $frm->api_mapper($value, $usr_msg);
        } elseif (is_int($value)) {
            if ($value != 0) {
                // TODO use formula cache
                $frm->id = $value;
            }
        } else {
            log_err('unexpected format of api message');
        }
        return $frm;
    }


    /*
     * im- and export
     */

    /**
     * create an array with the export json fields
     * @param export_type_list|array $exp_typ define the export format
     * @param bool $do_load true if any missing data should be loaded while creating the array
     * @return array with the json fields
     */
    function export_json(export_type_list|array $exp_typ = [], bool $do_load = true): array
    {
        $vars = parent::export_json($exp_typ, $do_load);

        if ($this->order_nbr >= 0) {
            $vars[json_fields::POSITION] = $this->order_nbr;
        }
        if ($this->ui_msg_code_id != null) {
            $vars[json_fields::UI_MSG_CODE_ID] = $this->ui_msg_code_id->value;
        }
        if ($this->ui_msg_code_id_vars != null) {
            $vars[json_fields::UI_MSG_CODE_ID_VARS] = $this->ui_msg_code_id_vars->value;
        }
        if ($this->ui_msg_code_id_exception != null) {
            $vars[json_fields::UI_MSG_CODE_ID_EXCEPTION] = $this->ui_msg_code_id_exception->value;
        }
        if ($this->ui_msg_value_exception != null) {
            $vars[json_fields::UI_MSG_CODE_VAL_EXCEPTION] = $this->ui_msg_value_exception;
        }
        if ($this->style != null) {
            $vars[json_fields::STYLE] = $this->style->get_code_id();
        }

        // add the phrases used
        if ($do_load) {
            $this->reload_phrases();
        }
        if ($this->row_phrase != null) {
            if ($this->row_phrase->name() != '') {
                $vars[json_fields::ROW] = $this->row_phrase->name();
            }
        }
        if ($this->col_phrase != null) {
            if ($this->col_phrase->name() != '') {
                $vars[json_fields::COLUMN] = $this->col_phrase->name();
            }
        }
        if ($this->col_sub_phrase != null) {
            if ($this->col_sub_phrase->name() != '') {
                $vars[json_fields::COLUMN2] = $this->col_sub_phrase->name();
            }
        }

        return $vars;
    }


    /*
     * set and get
     */

    /**
     * set the predefined view component type by the given code id or name
     *
     * @param string $code_id_or_name the code id or name that should be added to this view component
     * @param user $usr_req the user who wants to change the type
     * @return user_message a warning if the view type code id is not found
     */
    function set_type(string $code_id_or_name, user $usr_req = new user()): user_message
    {
        global $sys;
        if ($sys->typ_lst->cmp_typ->has_code_id($code_id_or_name)) {
            return parent::set_type_by_code_id(
                $code_id_or_name, $sys->typ_lst->cmp_typ, msg_id::COMPONENT_TYPE_NOT_FOUND, $usr_req);
        } else {
            return parent::set_type_by_name(
                $code_id_or_name, $sys->typ_lst->cmp_typ, msg_id::COMPONENT_TYPE_NOT_FOUND, $usr_req);
        }
    }

    /**
     * set the default style for this component by the code id
     *
     * @param string|null $code_id the code id of the display style use for im and export
     * @return void
     */
    function set_style(?string $code_id): void
    {
        global $sys;
        if ($code_id == null) {
            $this->style = null;
        } else {
            $this->style = $sys->typ_lst->msk_sty->get_by_code_id($code_id);
        }
    }

    /**
     * set the default style for this component by the database id
     *
     * @param int|null $style_id the database id of the display style
     * @return void
     */
    function set_style_by_id(?int $style_id): void
    {
        global $sys;
        if ($style_id == null) {
            $this->style = null;
        } else {
            $this->style = $sys->typ_lst->msk_sty->get($style_id);
        }
    }

    /**
     * @return view_style|type_object|null the view style for this component or null if the parent style should be used
     */
    function get_style(): view_style|type_object|null
    {
        return $this->style;
    }

    /**
     * @return int|null the database id of the view style or null
     */
    function get_style_id(): ?int
    {
        return $this->style?->id();
    }

    /**
     * define or remove the phrase that is used to select the table rows
     * @param phrase|null $phr e.g. if "year" each table row is one year
     * @return void
     */
    function set_row_phrase(?phrase $phr): void
    {
        $this->row_phrase = $phr;
    }

    function get_row_phrase_id(): int
    {
        if ($this->row_phrase != null) {
            return $this->row_phrase->id();
        } else {
            return 0;
        }
    }

    function get_row_phrase_name(): string
    {
        if ($this->row_phrase != null) {
            return $this->row_phrase->name();
        } else {
            return 0;
        }
    }

    /**
     * define or remove the phrase that is used to select the table columns
     * @param phrase|null $phr e.g. if "canton" the canton names are used for the table columns
     * @return void
     */
    function set_col_phrase(?phrase $phr): void
    {
        $this->col_phrase = $phr;
    }

    function get_col_phrase_id(): int
    {
        if ($this->col_phrase != null) {
            return $this->col_phrase->id();
        } else {
            return 0;
        }
    }

    function get_col_phrase_name(): string
    {
        if ($this->col_phrase != null) {
            return $this->col_phrase->name();
        } else {
            return 0;
        }
    }

    /**
     * define or remove the phrase that is used as the second selection for table columns
     * @param phrase|null $phr e.g. if "city" and "canton" is the col_phrase the cities of each canton are used
     * @return user_message if the sub phrase has no relation to the column phrase a suggestion of the possible sub phrases
     */
    function set_col_sub_phrase(?phrase $phr): user_message
    {
        $this->col_sub_phrase = $phr;
        return new user_message();
    }

    function get_col_sub_phrase_id(): int
    {
        if ($this->col_sub_phrase != null) {
            return $this->col_sub_phrase->id();
        } else {
            return 0;
        }
    }

    function get_col_sub_phrase_name(): string
    {
        if ($this->col_sub_phrase != null) {
            return $this->col_sub_phrase->name();
        } else {
            return 0;
        }
    }

    /**
     * set the ui message code id of this object to write the change to the db
     * but only if the requesting user hat the permission to do so
     *
     * @param msg_id|null $ui_msg_id the updated message id
     * @param user $usr the user who has requested the change
     * @return user_message warning message for the user if the permissions are missing
     */
    function set_ui_msg_code_id(?msg_id $ui_msg_id, user $usr): user_message
    {
        $msg = new user_message();
        if ($usr->can_set_ui_msg_id()) {
            $this->ui_msg_code_id = $ui_msg_id;
        } else {
            $lib = new library();
            $msg->add(msg_id::NOT_ALLOWED_TO, [
                msg_id::VAR_USER_NAME => $usr->name(),
                msg_id::VAR_USER_PROFILE => $usr->profile_code_id(),
                msg_id::VAR_NAME => component_db::FLD_UI_MSG_ID,
                msg_id::VAR_CLASS_NAME => $lib->class_to_name($this::class)
            ]);
        }
        return $msg;
    }

    /**
     * @return msg_id|null the message id or null
     */
    function get_ui_msg_code_id(): ?msg_id
    {
        return $this->ui_msg_code_id;
    }

    /**
     * set the ui message code id to be used after the number to write the change to the db
     * but only if the requesting user hat the permission to do so
     *
     * @param msg_id|null $ui_msg_id the updated message id
     * @param user $usr the user who has requested the change
     * @return user_message warning message for the user if the permissions are missing
     */
    function set_ui_msg_code_id_vars(?msg_id $ui_msg_id, user $usr): user_message
    {
        $msg = new user_message();
        if ($usr->can_set_ui_msg_id()) {
            $this->ui_msg_code_id_vars = $ui_msg_id;
        } else {
            $lib = new library();
            $msg->add(msg_id::NOT_ALLOWED_TO, [
                msg_id::VAR_USER_NAME => $usr->name(),
                msg_id::VAR_USER_PROFILE => $usr->profile_code_id(),
                msg_id::VAR_NAME => component_db::FLD_UI_MSG_ID_VARS,
                msg_id::VAR_CLASS_NAME => $lib->class_to_name($this::class)
            ]);
        }
        return $msg;
    }

    /**
     * @return msg_id|null the message id or null
     */
    function get_ui_msg_code_id_vars(): ?msg_id
    {
        return $this->ui_msg_code_id_vars;
    }

    /**
     * set the ui message code id to be used as an exception to write the change to the db
     * but only if the requesting user hat the permission to do so
     *
     * @param msg_id|null $ui_msg_id the updated message id
     * @param user $usr the user who has requested the change
     * @return user_message warning message for the user if the permissions are missing
     */
    function set_ui_msg_code_id_exception(?msg_id $ui_msg_id, user $usr): user_message
    {
        $msg = new user_message();
        if ($usr->can_set_ui_msg_id()) {
            $this->ui_msg_code_id_exception = $ui_msg_id;
        } else {
            $lib = new library();
            $msg->add(msg_id::NOT_ALLOWED_TO, [
                msg_id::VAR_USER_NAME => $usr->name(),
                msg_id::VAR_USER_PROFILE => $usr->profile_code_id(),
                msg_id::VAR_NAME => component_db::FLD_UI_MSG_ID_EXCEPTION,
                msg_id::VAR_CLASS_NAME => $lib->class_to_name($this::class)
            ]);
        }
        return $msg;
    }

    /**
     * @return msg_id|null the message id or null
     */
    function get_ui_msg_code_id_exception(): ?msg_id
    {
        return $this->ui_msg_code_id_exception;
    }

    /**
     * set the value to select the exception message to write the change to the db
     * but only if the requesting user hat the permission to do so
     *
     * @param float|null $ui_msg_value_exception the updated message id
     * @param user $usr the user who has requested the change
     * @return user_message warning message for the user if the permissions are missing
     */
    function set_ui_msg_value_exception(?float $ui_msg_value_exception, user $usr): user_message
    {
        $msg = new user_message();
        if ($usr->can_set_ui_msg_id()) {
            $this->ui_msg_value_exception = $ui_msg_value_exception;
        } else {
            $lib = new library();
            $msg->add(msg_id::NOT_ALLOWED_TO, [
                msg_id::VAR_USER_NAME => $usr->name(),
                msg_id::VAR_USER_PROFILE => $usr->profile_code_id(),
                msg_id::VAR_NAME => component_db::FLD_UI_MSG_VAL_EXCEPTION,
                msg_id::VAR_CLASS_NAME => $lib->class_to_name($this::class)
            ]);
        }
        return $msg;
    }

    /**
     * @return float|null the message id or null
     */
    function get_ui_msg_value_exception(): ?float
    {
        return $this->ui_msg_value_exception;
    }

    /**
     * set the formula of the component by the id
     * TODO use cache to reduce the db loads
     * TODO use this as a sample for all row_mappers
     * @param int|null $id the id for the formula
     * @return user_message message for the user if the id is strange
     */
    function set_formula_by_id(?int $id): user_message
    {
        $msg = new user_message();
        $frm = null;
        if ($id != null) {
            if ($id > 0) {
                $frm = new formula($this->get_user());
                $frm->id = $id;
            } else {
                $lib = new library();
                $msg->add(msg_id::LOAD_FORMULA_ID, [
                    msg_id::VAR_FORMULA => $id,
                    msg_id::VAR_CLASS_NAME => $lib->class_to_name($this::class),
                    msg_id::VAR_SANDBOX_NAME => $this->name(),
                ]);
            }
        }
        $this->frm = $frm;
        return $msg;
    }

    /**
     * set the formula used for the component
     * @param formula $frm
     * @return user_message if setting the formula does not make sense with a suggested solution
     */
    function set_formula(formula $frm): user_message
    {
        $this->frm = $frm;
        return new user_message();
    }

    function get_formula(): ?formula
    {
        return $this->frm;
    }

    function get_formula_id(): int
    {
        if ($this->frm != null) {
            return $this->frm->id();
        } else {
            return 0;
        }
    }

    /**
     * set the type of linked components
     *
     * @param string $type_code_id the code id that should be added to this view component
     * @return void
     */
    function set_link_type(string $type_code_id): void
    {
        global $sys;
        $this->link_type_id = $sys->typ_lst->cmp_lnk_typ->id($type_code_id);
    }

    /**
     * TODO use a set_join function for all not simple sql joins
     * @param sql_creator $sc the sql creator without component joins
     * @return sql_creator the sql creator with the components join set
     */
    function set_join(sql_creator $sc): sql_creator
    {
        $sc->set_join_fields(component::FLD_NAMES, component::class);
        $sc->set_join_usr_fields(component::FLD_NAMES_USR, component::class);
        $sc->set_join_usr_num_fields(component::FLD_NAMES_NUM_USR, component::class);
        return $sc;
    }


    /*
     * preloaded
     */

    /**
     * @return string|null the code_id of the component type
     */
    function type_code_id(): string|null
    {
        global $sys;
        return $sys->typ_lst->cmp_typ->code_id($this->type_id);
    }

    /**
     * @return string the name of the component type
     */
    function type_name(): string
    {
        global $sys;
        return $sys->typ_lst->cmp_typ->name($this->type_id);
    }

    /**
     * get the name of the component type or null if no type is set
     * @return string|null the name of the component type
     */
    function type_name_or_null(): ?string
    {
        global $sys;
        return $sys->typ_lst->cmp_typ->name_or_null($this->type_id);
    }

    /**
     * get the view component type database id based on the code id
     * @param string $code_id
     * @return int
     */
    private function type_id_by_code_id(string $code_id): int
    {
        global $sys;
        return $sys->typ_lst->cmp_typ->id($code_id);
    }


    /*
     * load
     */

    /**
     * just set the class name for the user sandbox function
     * load a view component object by name
     * @param string $name the name view component
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_name(string $name): int
    {
        $id = parent::load_by_name($name);
        if ($this->id() > 0) {
            $this->reload_phrases();
        }
        return $id;
    }

    /**
     * just set the class name for the user sandbox function
     * load a view component object by database id
     * @param int $id the id of the view component
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_id(int $id): int
    {
        $id = parent::load_by_id($id);
        if ($this->id() > 0) {
            $this->reload_phrases();
        }
        return $id;
    }


    /*
     * load sql
     */

    /**
     * create the common part of an SQL statement to retrieve the parameters of a view component from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name extension to make the query name unique
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name, string $class = self::class): sql_par
    {
        return parent::load_sql_usr_num($sc, $this, $query_name);
    }

    /**
     * create an SQL statement to retrieve the user changes of the current view component
     *
     * @param sql_creator $sc with the target db_type set
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation e.g. standard for values and results
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_user_changes(
        sql_creator   $sc,
        sql_type_list $sc_par_lst = new sql_type_list()
    ): sql_par
    {
        $sc->set_class($this::class, new sql_type_list([sql_type::USER]));
        $sc->set_fields(array_merge(
            component_db::FLD_NAMES_USR,
            component_db::FLD_NAMES_NUM_USR
        ));
        return parent::load_sql_user_changes($sc, $sc_par_lst);
    }


    /*
     * sql fields
     */

    function name_field(): string
    {
        return component_db::FLD_NAME;
    }

    /**
     * @return array with all fields names of this object
     */
    protected function all_fields(): array
    {
        return array_merge(
            component_db::FLD_NAMES,
            component_db::FLD_NAMES_USR,
            component_db::FLD_NAMES_NUM_USR,
            array(user_db::FLD_ID));
    }

    function all_sandbox_fields(): array
    {
        return self::ALL_SANDBOX_FLD_NAMES;
    }


    /*
     * related
     */

    /**
     * load the related word and formula objects
     * @return bool false if a technical error on loading has occurred; an empty list if fine and returns true
     */
    function reload_phrases(): bool
    {
        $result = true;
        $this->reload_row_phrase();
        $this->reload_col_phrase();
        $this->reload_wrd_col2();
        $this->reload_formula();
        log_debug('done for ' . $this->dsp_id());
        return $result;
    }

    /**
     * load the phrase that should be used for the rows of a table
     * or the left Y-axis of a chart
     *
     * @param int|null $id the id of suggested the row phrase
     * @return int the id of the loaded phrase or 0 if no phrase has been loaded
     */
    function reload_row_phrase(?int $id = null): int
    {
        $result = 0;
        $row_phr = $this->reload_phrase($id);
        if ($row_phr != null) {
            $this->row_phrase = $row_phr;
            $result = $id;
        }
        return $result;
    }

    /**
     * load the phrase that should be used for the columns of a table
     *  load the word object that defines the column names
     *  e.g. "year" to display the yearly values
     *       or the left X-axis of a chart
     *
     * @param int|null $id the id of suggested the col phrase
     * @return int the id of the loaded phrase or 0 if no phrase has been loaded
     */
    function reload_col_phrase(?int $id = null): int
    {
        $result = 0;
        $col_phr = $this->reload_phrase($id);
        if ($col_phr != null) {
            $this->col_phrase = $col_phr;
            $result = $id;
        }
        return $result;
    }

    /**
     * load a phrase if the id is valid
     *
     * @param int|null $id the id of suggested the phrase
     * @return phrase|null the loaded phrase
     */
    private function reload_phrase(?int $id = null): ?phrase
    {
        $result = null;
        if ($id != null) {
            if ($id != 0) {
                $phr = new phrase($this->get_user());
                if ($phr->load_by_id($id) != 0) {
                    $result = $phr;
                }
            }
        }
        return $result;
    }

    //
    function reload_wrd_col2(): string
    {
        $result = '';
        if ($this->word_id_col2 > 0) {
            $wrd_col2 = new word($this->get_user());
            $wrd_col2->load_by_id($this->word_id_col2);
            $this->col_sub_phrase = $wrd_col2->phrase();
            $result = $wrd_col2->name();
        }
        return $result;
    }

    // load the related formula and returns the name of the formula
    function reload_formula(): string
    {
        $result = '';
        if ($this->get_formula_id() > 0) {
            $frm = new formula($this->get_user());
            $frm->load_by_id($this->get_formula_id());
            $this->frm = $frm;
            $result = $frm->name();
        }
        return $result;
    }


    /*
     * info
     */

    /**
     * Create an object where only the vars are set
     * where the var of this object differs from the var of the given object.
     *
     * @param component|CombineObject|db_object_seq_id $std_obj the norm object as saved in the database
     * @param component|CombineObject|db_object_seq_id $result empty clone of the target user object
     * @return component|CombineObject|db_object_seq_id the object where only the vars are set that are changed compared to the given $obj
     */
    function delta(
        component|CombineObject|db_object_seq_id $std_obj,
        component|CombineObject|db_object_seq_id $result
    ): component|CombineObject|db_object_seq_id
    {
        parent::delta($std_obj, $result);
        if ($std_obj->ui_msg_code_id !== $this->ui_msg_code_id) {
            $result->ui_msg_code_id = $this->ui_msg_code_id;
        }
        if ($std_obj->ui_msg_code_id_vars !== $this->ui_msg_code_id_vars) {
            $result->ui_msg_code_id_vars = $this->ui_msg_code_id_vars;
        }
        if ($std_obj->ui_msg_code_id_exception !== $this->ui_msg_code_id_exception) {
            $result->ui_msg_code_id_exception = $this->ui_msg_code_id_exception;
        }
        if ($std_obj->ui_msg_value_exception !== $this->ui_msg_value_exception) {
            $result->ui_msg_value_exception = $this->ui_msg_value_exception;
        }
        if ($std_obj->row_phrase !== $this->row_phrase) {
            $result->row_phrase = $this->row_phrase;
        }
        if ($std_obj->col_phrase !== $this->col_phrase) {
            $result->col_phrase = $this->col_phrase;
        }
        if ($std_obj->col_sub_phrase !== $this->col_sub_phrase) {
            $result->col_sub_phrase = $this->col_sub_phrase;
        }
        if ($std_obj->get_formula_id() !== $this->get_formula_id()) {
            $result->set_formula($this->get_formula());
        }
        if ($std_obj->get_style_id() != $this->get_style_id()) {
            $result->set_style_by_id($this->get_style_id());
        }
        if ($std_obj->link_type_id !== $this->link_type_id) {
            $result->link_type_id = $this->link_type_id;
        }
        return $result;
    }


    /*
     * modify
     */

    /**
     * fill this component based on the given component
     * if the id is set in the given word loaded from the database but this import word does not yet have the db id, set the id
     * if the given description is not set (null) the description is not remove
     * if the given description is an empty string the description is removed
     *
     * @param component|CombineObject|db_object_seq_id $obj word with the values that should have been updated e.g. based on the import
     * @param user $usr_req the user who has requested the fill
     * @return user_message a warning in case of a conflict e.g. due to a missing change time
     */
    function fill(component|CombineObject|db_object_seq_id $obj, user $usr_req): user_message
    {
        $usr_msg = parent::fill($obj, $usr_req);
        if ($this->ui_msg_code_id === null and $obj->ui_msg_code_id != null) {
            $usr_msg->merge($this->set_ui_msg_code_id($obj->ui_msg_code_id, $usr_req));
        }
        if ($this->ui_msg_code_id_vars === null and $obj->ui_msg_code_id_vars != null) {
            $usr_msg->merge($this->set_ui_msg_code_id_vars($obj->ui_msg_code_id_vars, $usr_req));
        }
        if ($this->ui_msg_code_id_exception === null and $obj->ui_msg_code_id_exception != null) {
            $usr_msg->merge($this->set_ui_msg_code_id_exception($obj->ui_msg_code_id_exception, $usr_req));
        }
        if ($this->ui_msg_value_exception === null and $obj->ui_msg_value_exception !== null) {
            $usr_msg->merge($this->set_ui_msg_value_exception($obj->ui_msg_value_exception, $usr_req));
        }
        if ($this->row_phrase === null and $obj->row_phrase != null) {
            $this->row_phrase = $obj->row_phrase;
        }
        if ($this->col_phrase === null and $obj->col_phrase != null) {
            $this->col_phrase = $obj->col_phrase;
        }
        if ($this->col_sub_phrase === null and $obj->col_sub_phrase != null) {
            $this->col_sub_phrase = $obj->col_sub_phrase;
        }
        if ($this->get_formula_id() === null and $obj->get_formula_id() != null) {
            $this->set_formula($obj->get_formula());
        }
        if ($this->get_style_id() === null and $obj->get_style_id() != null) {
            $this->set_style_by_id($obj->get_style_id());
        }
        // TODO Prio 2 review and maybe deprecate
        if ($this->link_type_id === null and $obj->link_type_id != null) {
            $this->link_type_id = $obj->link_type_id;
        }
        return $usr_msg;
    }


    /*
     * info
     */

    /**
     * create human-readable messages of the differences between the objects
     * is expected to be similar to the has_diff function
     * @param component|sandbox|CombineObject|db_object_seq_id $obj which might be different to this sandbox object
     * @return user_message the human-readable messages of the differences between the sandbox objects
     */
    function diff_msg(component|sandbox|CombineObject|db_object_seq_id $obj): user_message
    {
        $msg = parent::diff_msg($obj);
        // TODO add the missing fields and review the unit test
        if ($this->get_formula_id() != $obj->get_formula_id()) {
            $lib = new library();
            $msg->add(msg_id::DIFF_FORMULA, [
                msg_id::VAR_FORMULA => $obj->get_formula_id(),
                msg_id::VAR_FORMULA_CHK => $this->get_formula_id(),
                msg_id::VAR_CLASS_NAME => $lib->class_to_name($this::class),
                msg_id::VAR_SANDBOX_NAME => $this->name(),
            ]);
        }
        return $msg;
    }

    /**
     * check if the named object in the database needs to be updated
     * is expected to be similar to the diff_msg function
     *
     * @param component|CombineObject|IdObject $db_obj the word as saved in the database
     * @return bool true if this word has info that should be saved in the database
     */
    function needs_db_update(component|CombineObject|IdObject $db_obj): bool
    {
        $result = parent::needs_db_update($db_obj);
        if ($this->get_formula_id() != null) {
            if ($this->get_formula_id() != $db_obj->get_formula_id()) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * returns the next free order number for a new view component
     */
    function next_nbr(int $view_id): int
    {
        log_debug('component->next_nbr for view "' . $view_id . '"');

        global $db_con;

        $result = 1;
        if ($view_id == '' or $view_id == Null or $view_id == 0) {
            log_err('Cannot get the next position, because the view_id is not set', 'component->next_nbr');
        } else {
            $vcl = new component_link($this->get_user());
            $result = $vcl->load_max_pos_by_view($view_id);

            // if nothing is found, assume one as the next free number
            if ($result <= 0) {
                $result = 1;
            } else {
                $result++;
            }
        }

        log_debug($result);
        return $result;
    }


    /*
     * log
     */

    // set the log entry parameters for a value update
    function log_link($dsp): bool
    {
        log_debug('component->log_link ' . $this->dsp_id() . ' to "' . $dsp->name . '"  for user ' . $this->get_user()->id);
        $log = new change_link($this->get_user());
        $log->set_action(change_actions::ADD);
        $log->set_class(component_link::class);
        $log->new_from = clone $this;
        $log->new_to = clone $dsp;
        $log->row_id = $this->id();
        $result = $log->add_link_ref();

        log_debug('logged ' . $log->id());
        return $result;
    }

    // set the log entry parameters to unlink a display component ($cmp) from a view ($dsp)
    function log_unlink($dsp): bool
    {
        log_debug($this->dsp_id() . ' from "' . $dsp->name . '" for user ' . $this->get_user()->id);
        $log = new change_link($this->get_user());
        $log->set_action(change_actions::DELETE);
        $log->set_class(component_link::class);
        $log->old_from = clone $this;
        $log->old_to = clone $dsp;
        $log->row_id = $this->id();
        $result = $log->add_link_ref();

        log_debug('logged ' . $log->id());
        return $result;
    }


    /*
     * link
     */

    /**
     * link this component to a view
     * @param view $msk the view object to which this component should be added
     * @param int $order_nbr the position where the component should be added and all existing component should be move one position further
     * @param user_message $usr_msg the message for the user why adding of the component has failed and the potential solutions
     * @return bool true if the component has been added
     */
    function link(view $msk, int $order_nbr, user_message $usr_msg): bool
    {
        $cmp_lnk = new component_link($this->get_user());
        $cmp_lnk->reset(true);
        $cmp_lnk->set_view($msk);
        $cmp_lnk->set_component($this);
        $cmp_lnk->order_nbr = $order_nbr;
        $cmp_lnk->set_predicate(component_link_type::DEFAULT);
        $cmp_lnk->set_pos_type(position_types::DEFAULT);
        return $cmp_lnk->save($usr_msg);
    }

    /**
     * remove a view component from a view
     * TODO check if the view component is not linked anywhere else
     *        and if yes, delete the view component after confirmation
     * @param view $msk the view from where this component should be removed
     * @param user_message $usr_msg explain to the user why the component cannot be removed from the view
     * @return bool true if the component has been removed from the view
     */
    function unlink(view $msk, user_message $usr_msg): bool
    {
        $dsp_lnk = new component_link($this->get_user());
        $dsp_lnk->load_by_link($msk, $this);
        $dsp_lnk->reload_objects($usr_msg);
        return $dsp_lnk->del($usr_msg);
    }


    /*
     * save helper
     */

    /**
     * @return array with the reserved component names
     */
    protected function reserved_names(): array
    {
        return components::RESERVED_COMPONENTS;
    }

    /**
     * @return array with the fixed component names for db read testing
     */
    protected function fixed_names(): array
    {
        return components::FIXED_NAMES;
    }


    /*
     * del
     */

    /**
     * delete the view component links of linked to this view component
     *
     * @param user_message $usr_msg the message for the user why deleting the component links has failed and a suggested solution
     * @return bool true if the component links has been deleted
     */
    function del_links(user_message $usr_msg): bool
    {
        // collect all component links where this component is used
        $lnk_lst = new component_link_list($this->get_user());
        $lnk_lst->load_by_component($this);

        // if there are links, delete if not used by anybody else than the user who has requested the deletion
        // or exclude the links for the user if the link is used by someone else
        if (!$lnk_lst->is_empty()) {
            $lnk_lst->del($usr_msg);
        }

        return $usr_msg->is_ok();
    }


    /*
     * sql write fields
     */

    /**
     * get a list of all database fields that might be changed
     * excluding the internal fields e.g. the database id
     * field list must be corresponding to the db_fields_changed fields
     *
     * @param sql_type_list $sc_par_lst only used for link objects
     * @return array list of all database field names that have been updated
     */
    function db_fields_all(sql_type_list $sc_par_lst = new sql_type_list()): array
    {
        return array_merge(
            parent::db_fields_all(),
            [
                component_db::FLD_TYPE,
                component_db::FLD_STYLE,
                component_db::FLD_UI_MSG_ID,
                component_db::FLD_UI_MSG_ID_VARS,
                component_db::FLD_UI_MSG_ID_EXCEPTION,
                component_db::FLD_UI_MSG_VAL_EXCEPTION,
                component_db::FLD_ROW_PHRASE,
                component_db::FLD_COL_PHRASE,
                component_db::FLD_COL2_PHRASE,
                formula_db::FLD_ID,
                //component_db::FLD_LINK_COMP,
                //component_db::FLD_LINK_COMP_TYPE,
                component_db::FLD_LINK_TYPE,
            ],
            parent::db_fields_all_sandbox()
        );
    }

    /**
     * get a list of database field names, values and types that have been updated
     *
     * @param component|db_object_seq_id $obj the compare value to detect the changed fields
     * @param user_message $msg the user message object that collects any issues during the sql creation
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list list 3 entry arrays with the database field name, the value and the sql type that have been updated
     */
    function db_fields_changed(
        component|db_object_seq_id $obj,
        user_message               $msg,
        sql_type_list              $sc_par_lst = new sql_type_list()
    ): sql_par_field_list
    {
        global $sys;

        $sc = new sql_creator();
        $do_log = $sc_par_lst->incl_log();
        $table_id = $sc->table_id($this::class);

        $lst = parent::db_fields_changed($obj, $msg, $sc_par_lst);
        if ($obj->type_id() !== $this->type_id()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . component_db::FLD_TYPE,
                    $sys->typ_lst->cng_fld->id($table_id . component_db::FLD_TYPE),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            if ($this->type_id() < 0) {
                $msg->add(msg_id::COMPONENT_TYPE_MISSING, [
                    msg_id::VAR_TYPE => $this->type_name(),
                    msg_id::VAR_NAME => $this->dsp_id()
                ]);
            }
            $lst->add_type_field(
                component_db::FLD_TYPE,
                type_object::FLD_NAME,
                $this->type_id(),
                $obj->type_id(),
                $sys->typ_lst->cmp_typ
            );
        }
        if ($obj->get_style_id() !== $this->get_style_id()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . component_db::FLD_STYLE,
                    $sys->typ_lst->cng_fld->id($table_id . component_db::FLD_STYLE),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            // TODO easy move to id function of type list
            if ($this->get_style_id() < 0) {
                $msg->add(msg_id::COMPONENT_STYLE_MISSING, [
                    msg_id::VAR_TYPE => $this->get_style_id(),
                    msg_id::VAR_NAME => $this->dsp_id()
                ]);
            }
            $lst->add_type_field(
                component_db::FLD_STYLE,
                view_style::FLD_NAME,
                $this->get_style_id(),
                $obj->get_style_id(),
                $sys->typ_lst->msk_sty
            );
        }
        if ($obj->ui_msg_code_id !== $this->ui_msg_code_id) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . component_db::FLD_UI_MSG_ID,
                    $sys->typ_lst->cng_fld->id($table_id . component_db::FLD_UI_MSG_ID),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                component_db::FLD_UI_MSG_ID,
                $this->ui_msg_code_id?->value,
                component_db::FLD_UI_MSG_ID_SQL_TYP,
                $obj->ui_msg_code_id?->value
            );
        }
        if ($obj->ui_msg_code_id_vars !== $this->ui_msg_code_id_vars) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . component_db::FLD_UI_MSG_ID_VARS,
                    $sys->typ_lst->cng_fld->id($table_id . component_db::FLD_UI_MSG_ID_VARS),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                component_db::FLD_UI_MSG_ID_VARS,
                $this->ui_msg_code_id_vars?->value,
                component_db::FLD_UI_MSG_ID_SQL_TYP,
                $obj->ui_msg_code_id_vars?->value
            );
        }
        if ($obj->ui_msg_code_id_exception !== $this->ui_msg_code_id_exception) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . component_db::FLD_UI_MSG_ID_EXCEPTION,
                    $sys->typ_lst->cng_fld->id($table_id . component_db::FLD_UI_MSG_ID_EXCEPTION),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                component_db::FLD_UI_MSG_ID_EXCEPTION,
                $this->ui_msg_code_id_exception?->value,
                component_db::FLD_UI_MSG_ID_SQL_TYP,
                $obj->ui_msg_code_id_exception?->value
            );
        }
        if ($obj->ui_msg_value_exception !== $this->ui_msg_value_exception) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . component_db::FLD_UI_MSG_VAL_EXCEPTION,
                    $sys->typ_lst->cng_fld->id($table_id . component_db::FLD_UI_MSG_VAL_EXCEPTION),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                component_db::FLD_UI_MSG_VAL_EXCEPTION,
                $this->ui_msg_value_exception,
                sql_field_type::NUMERIC_FLOAT,
                $obj->ui_msg_value_exception
            );
        }
        if ($obj->get_row_phrase_id() !== $this->get_row_phrase_id()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . component_db::FLD_ROW_PHRASE,
                    $sys->typ_lst->cng_fld->id($table_id . component_db::FLD_ROW_PHRASE),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $old_val = $obj->get_row_phrase_id();
            if ($obj->row_phrase == null) {
                $old_val = null;
            }
            $lst->add_field(
                component_db::FLD_ROW_PHRASE,
                $this->get_row_phrase_id(),
                phrase::FLD_ID_SQL_TYP,
                $old_val
            );
        }
        if ($obj->get_col_phrase_id() !== $this->get_col_phrase_id()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . component_db::FLD_COL_PHRASE,
                    $sys->typ_lst->cng_fld->id($table_id . component_db::FLD_COL_PHRASE),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $old_val = $obj->get_col_phrase_id();
            if ($obj->col_phrase == null) {
                $old_val = null;
            }
            $lst->add_field(
                component_db::FLD_COL_PHRASE,
                $this->get_col_phrase_id(),
                phrase::FLD_ID_SQL_TYP,
                $old_val
            );
        }
        if ($obj->get_col_sub_phrase_id() !== $this->get_col_sub_phrase_id()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . component_db::FLD_COL2_PHRASE,
                    $sys->typ_lst->cng_fld->id($table_id . component_db::FLD_COL2_PHRASE),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $old_val = $obj->get_col_sub_phrase_id();
            if ($obj->col_sub_phrase == null) {
                $old_val = null;
            }
            $lst->add_field(
                component_db::FLD_COL2_PHRASE,
                $this->get_col_sub_phrase_id(),
                phrase::FLD_ID_SQL_TYP,
                $old_val
            );
        }
        if ($obj->get_formula_id() !== $this->get_formula_id()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . formula_db::FLD_ID,
                    $sys->typ_lst->cng_fld->id($table_id . formula_db::FLD_ID),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $old_val = $obj->get_formula_id();
            if ($obj->get_formula_id() == null) {
                $old_val = null;
            }
            $lst->add_field(
                formula_db::FLD_ID,
                $this->get_formula_id(),
                formula_db::FLD_ID_SQL_TYP,
                $old_val
            );
        }
        // TODO add FLD_LINK_COMP and FLD_LINK_COMP_TYPE
        if ($obj->link_type_id !== $this->link_type_id) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . component_db::FLD_LINK_TYPE,
                    $sys->typ_lst->cng_fld->id($table_id . component_db::FLD_LINK_TYPE),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                component_db::FLD_LINK_TYPE,
                $this->link_type_id,
                component_db::FLD_LINK_TYPE_SQL_TYP,
                $obj->link_type_id
            );
        }
        return $lst->merge($this->db_changed_sandbox_list($obj, $sc_par_lst));
    }


    /*
     * debug
     */

    // not used at the moment
    /*  private function link_type_name() {
        if ($this->type_id > 0) {
          $sql = "SELECT type_name
                    FROM component_types
                   WHERE component_type_id = ".$this->type_id.";";
          $db_con = new mysql;
          $db_con->usr_id = $this->get_user()->id;
          $db_type = $db_con->get1($sql);
          $this->type_name = $db_type[sql_db::FLD_TYPE_NAME];
        }
        return $this->type_name;
      } */

    /*
      to link and unlink a component
    */

    /**
     * @return array with all view ids that are directly assigned to this view component
     */
    function assigned_msk_ids(): array
    {
        $result = array();

        if ($this->id() > 0 and $this->get_user() != null) {
            $lst = new component_link_list($this->get_user());
            $lst->load_by_component($this);
            $result = $lst->view_ids();
        } else {
            log_err("The user id must be set to list the component links.", "component->assign_ui_ids");
        }

        return $result;
    }

}

