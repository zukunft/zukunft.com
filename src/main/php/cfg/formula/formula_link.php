<?php

/*

    model/formula/formula_link.php - link a formula to a word
    ------------------------------

    The main sections of this object are
    - db const:          const for the database link
    - object vars:       the variables of this word object
    - construct and map: including the mapping of the db row to this word object
    - set and get:       to capsule the vars from unexpected changes
    - preloaded:         select e.g. types from cache
    - load:              database access object (DAO) functions
    - save:              manage to update the database
    - sql write fields:  field list for writing to the database
    - message:           add message function that might be overwritten by a child object for a more precise message
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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace Zukunft\ZukunftCom\main\php\cfg\formula;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_par_field_list.php';
include_once paths::DB . 'sql_par_type.php';
include_once paths::DB . 'sql_type.php';
include_once paths::DB . 'sql_type_list.php';
include_once paths::EXPORT . 'export_type_list.php';
include_once paths::MODEL_HELPER . 'combine_named.php';
include_once paths::MODEL_HELPER . 'data_object.php';
include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::MODEL_LOG . 'change.php';
include_once paths::MODEL_LOG . 'change_action.php';
include_once paths::MODEL_LOG . 'change_table_list.php';
include_once paths::MODEL_PHRASE . 'phrase.php';
include_once paths::MODEL_SANDBOX . 'sandbox.php';
include_once paths::MODEL_SANDBOX . 'sandbox_link.php';
include_once paths::MODEL_SANDBOX . 'sandbox_named.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_db.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_ENUM . 'change_actions.php';
include_once paths::SHARED_ENUM . 'change_tables.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_default;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_field_list;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type_list;
use Zukunft\ZukunftCom\main\php\cfg\export\export_type_list;
use Zukunft\ZukunftCom\main\php\cfg\helper\combine_named;
use Zukunft\ZukunftCom\main\php\cfg\helper\data_object;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;
use Zukunft\ZukunftCom\main\php\cfg\log\change;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_link;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_named;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_db;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\enum\change_actions;
use Zukunft\ZukunftCom\main\php\shared\enum\change_tables;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;

class formula_link extends sandbox_link
{

    /*
     * db const
     */

    // object specific database and JSON object field names
    const string TBL_COMMENT = 'for the link of a formula to phrases e.g. if the term pattern of a value matches this term pattern';
    // the database and JSON object field names used only for formula links
    const string FLD_ID = 'formula_link_id';
    const string FLD_TYPE = 'formula_link_type_id';
    const string FLD_ORDER = 'order_nbr';
    const sql_par_type FLD_ORDER_SQL_TYP = sql_par_type::INT;

    // all database field names excluding the id
    const array FLD_NAMES = array(
        formula_db::FLD_ID,
        phrase::FLD_ID,
        user_db::FLD_ID,
        formula_link_type::FLD_ID,
        self::FLD_ORDER,
        sql_db::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );
    // list of the link database field names
    const array FLD_NAMES_LINK = array(
        formula_db::FLD_ID,
        phrase::FLD_ID
    );
    // all numeric database field names that the user can change
    const array FLD_NAMES_NUM_USR = array(
        formula_link_type::FLD_ID,
        self::FLD_ORDER,
        sql_db::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );
    // all database field names excluding the id used to identify if there are some user specific changes
    const array ALL_SANDBOX_FLD_NAMES = array(
        formula_link_type::FLD_ID,
        self::FLD_ORDER,
        sql_db::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );
    // list of fields that CAN be changed by the user
    const array FLD_LST_USER_CAN_CHANGE = array(
        [formula_link_type::FLD_ID, type_object::FLD_ID_SQL_TYP, sql_field_default::NULL, sql::INDEX, formula_link_type::class, '', formula_link_type::FLD_ID],
        [self::FLD_ORDER, sql_field_type::INT, sql_field_default::NULL, '', '', ''],
    );
    // list of fields that CANNOT be changed by the user
    const array FLD_LST_NON_CHANGEABLE = array(
        [formula_db::FLD_ID, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, formula::class, ''],
        [phrase::FLD_ID, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, '', ''],
    );


    /*
     * object vars
     */

    // database fields additional to the user sandbox_link fields
    public ?int $order_nbr = null;    // to set the priority of the formula links


    /*
     * construct and map
     */

    /**
     * formula_link constructor that set the parameters for the _sandbox object
     */
    function __construct(user $usr)
    {
        parent::__construct($usr);
        $lib = new library();
        $this->from_name = $lib->class_to_name(formula::class);
        $this->to_name = $lib->class_to_name(phrase::class);

        $this->reset();
    }

    function reset(bool $keep_user = false): void
    {
        parent::reset($keep_user);

        $this->reset_objects($this->get_user());

        $this->order_nbr = null;
        global $sys;
        $this->set_predicate_id($sys->typ_lst->frm_lnk_typ->id(formula_link_type::DEFAULT));
    }

    /**
     * reset the in memory fields used e.g. if some ids are updated
     */
    private function reset_objects(user $usr): void
    {
        $this->set_formula(new formula($usr));
        $this->set_phrase(new phrase($usr));
    }

    /**
     * map the database fields to the object fields
     *
     * @param array|null $db_row with the data directly from the database
     * @param bool $load_std true if only the standard user sandbox object is loaded
     * @param bool $allow_usr_protect false for using the standard protection settings for the default object used for all users
     * @param string $id_fld the name of the id field as defined in this child and given to the parent
     * @return bool true if the formula link is loaded and valid
     */
    function row_mapper_sandbox(
        ?array $db_row,
        bool   $load_std = false,
        bool   $allow_usr_protect = true,
        string $id_fld = self::FLD_ID
    ): bool
    {
        $result = parent::row_mapper_sandbox($db_row, $load_std, $allow_usr_protect, self::FLD_ID);
        if ($result) {
            // TODO load by if from cache?
            $this->formula()->id = $db_row[formula_db::FLD_ID];
            $this->phrase()->set_id($db_row[phrase::FLD_ID]);
            $this->predicate_id = $db_row[formula_link_type::FLD_ID];
            $this->order_nbr = $db_row[formula_link::FLD_ORDER];
        }
        return $result;
    }

    /**
     * map a formula link api json to this model formula link object
     * @param array $api_json the api array with the values that should be mapped
     * @param user_message $usr_msg the message for the user why the action has failed and a suggested solution
     * @param data_object|null $dto the data object that contains the already imported formulas
     * @return bool true if the mapping has been completed successful
     */
    function api_mapper(
        array        $api_json,
        user_message $usr_msg,
        ?data_object $dto = null
    ): bool
    {
        parent::api_mapper($api_json, $usr_msg);

        if (array_key_exists(json_fields::FORMULA_ID, $api_json)) {
            $this->set_formula_from_id($api_json[json_fields::FORMULA_ID], $usr_msg, $dto);
        }
        if (array_key_exists(json_fields::FORMULA, $api_json)) {
            $this->set_formula_from_api_json($api_json[json_fields::FORMULA], $usr_msg);
        }
        if (array_key_exists(json_fields::PHRASE_ID, $api_json)) {
            $this->set_phrase_from_id($api_json[json_fields::PHRASE_ID], $usr_msg, $dto);
        }
        if (array_key_exists(json_fields::PHRASE, $api_json)) {
            $this->set_phrase_from_api_json($api_json[json_fields::PHRASE], $usr_msg);
        }
        if (array_key_exists(json_fields::PRIORITY, $api_json)) {
            $this->order_nbr = $api_json[json_fields::PRIORITY];
        }

        return $usr_msg->is_ok();
    }

    /**
     * set the vars of this formula link object based on the given json without writing to the database
     * the code_id is not expected to be included in the im- and export because the internal views are not expected to be included in the ex- and import
     *
     * @param array $in_ex_json an array with the data of the json object
     * @param user_message $usr_msg to enrich with warnings, problems and solutions
     * @param data_object|null $dto the data object that contains the already imported formulas
     * @return bool true if everything was fine
     */
    function import_mapper(
        array        $in_ex_json,
        user_message $usr_msg,
        ?data_object $dto = null
    ): bool
    {
        // reset the all parameters for these formula link object but keep the user
        $this->reset(true);

        parent::import_mapper($in_ex_json, $usr_msg, $dto);

        // import the formula
        if (array_key_exists(json_fields::FORMULA, $in_ex_json)) {
            $frm_json = $in_ex_json[json_fields::FORMULA];
            if (is_array($frm_json)) {
                if (count($frm_json) == 1 and array_key_exists(json_fields::NAME, $frm_json)) {
                    $frm_json = $frm_json[json_fields::NAME];
                }
            }
            if (is_string($frm_json)) {
                $frm = $dto?->get_formula_by_name($frm_json);
                if ($frm == null) {
                    $usr_msg->add_id_with_vars(msg_id::FORMULA_MISSING_IMPORT, [
                        msg_id::VAR_FORMULA => $frm_json,
                        msg_id::VAR_JSON_TEXT => json_encode($in_ex_json)
                    ]);
                    $frm = new formula($usr_msg->usr);
                    $frm->set_name($frm_json);
                }
                $this->set_formula($frm);
            } elseif (is_array($frm_json)) {
                $frm = new formula($usr_msg->usr);
                $frm->import_mapper($frm_json, $usr_msg, $dto);
                if ($usr_msg->is_ok()) {
                    $this->set_formula($frm);
                }
            }
        } else {
            $usr_msg->add_info_with_vars(msg_id::FORMULA_CREATED, [
                msg_id::VAR_FORMULA_NAME => $in_ex_json[json_fields::NAME]
            ]);
            $frm = new formula($usr_msg->usr);
            $frm->import_mapper($in_ex_json, $usr_msg, $dto);
            $this->set_formula($frm);
        }

        // import the phrase
        if (array_key_exists(json_fields::PHRASE, $in_ex_json)) {
            $phr_json = $in_ex_json[json_fields::PHRASE];
            if (is_array($phr_json)) {
                if (count($phr_json) == 1 and array_key_exists(json_fields::NAME, $phr_json)) {
                    $phr_json = $phr_json[json_fields::NAME];
                }
            }
            if (is_string($phr_json)) {
                $phr = $dto?->get_phrase_by_name($phr_json);
                if ($phr == null) {
                    $usr_msg->add_id_with_vars(msg_id::PHRASE_MISSING_IMPORT, [
                        msg_id::VAR_PHRASE => $phr_json,
                        msg_id::VAR_JSON_TEXT => json_encode($in_ex_json)
                    ]);
                    $phr = new phrase($usr_msg->usr);
                    $phr->set_name($phr_json);
                }
                $this->set_phrase($phr);
            } elseif (is_array($phr_json)) {
                $phr = new phrase($usr_msg->usr);
                $phr->import_mapper($phr_json, $usr_msg, $dto);
                if ($usr_msg->is_ok()) {
                    $this->set_phrase($phr);
                }
            }
        } else {
            $usr_msg->add_info_with_vars(msg_id::PHRASE_CREATED, [
                msg_id::VAR_PHRASE_NAME => $in_ex_json[json_fields::NAME]
            ]);
            $phr = new phrase($usr_msg->usr);
            //$phr->import_mapper($in_ex_json, $usr_msg, $dto);
            $this->set_phrase($phr);
        }

        if (array_key_exists(json_fields::PREDICATE, $in_ex_json)) {
            global $sys;
            $this->predicate_id = $sys->typ_lst->frm_lnk_typ->id($in_ex_json[json_fields::PREDICATE]);;
        }

        if (array_key_exists(json_fields::PRIORITY, $in_ex_json)) {
            $this->order_nbr = $in_ex_json[json_fields::PRIORITY];
        }

        return $usr_msg->is_ok();
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
        $vars = parent::api_json_array($typ_lst, $usr);

        if ($this->formula_id() != 0) {
            if ($typ_lst->include_phrases()) {
                $vars[json_fields::FORMULA] = $this->formula()->api_json_array($typ_lst, $usr);
            } else {
                $vars[json_fields::FORMULA_ID] = $this->formula_id();
            }
        }
        if ($this->phrase_id() != 0) {
            if ($typ_lst->include_phrases()) {
                $vars[json_fields::PHRASE] = $this->phrase()->api_json_array($typ_lst, $usr);
            } else {
                $vars[json_fields::PHRASE_ID] = $this->phrase_id();
            }
        }

        return $vars;
    }


    /*
     * set and get
     */

    // TODO add function "formula()" that returns the "From_OBject (fob)"
    // TODO check that all link objects have a self speaking interface function for the "From_OBject (fob)" and "To_OBject (tob)"

    /**
     * set the main vars with one function
     * @param int $id the database id of the link
     * @param formula $frm the formula that should be linked
     * @param phrase $phr the phrase to which the formula should be linked
     * @return void
     */
    function set(int $id, formula $frm, phrase $phr): void
    {
        $this->id = $id;
        $this->set_formula($frm);
        $this->set_phrase($phr);
    }

    /**
     * set the formula of this link based on the formula array
     * @param int|array $api_msg_part either the id itself or an array with the id and the formula details
     * @param user_message $usr_msg the message for the user why the action has failed and a suggested solution
     * @return void true if setting the formula has been successful
     */
    private function set_formula_from_api_json(
        int|array    $api_msg_part,
        user_message $usr_msg
    ): void
    {
        $frm = new formula($this->get_user());
        if (is_array($api_msg_part)) {
            $frm->api_mapper($api_msg_part, $usr_msg);
        } else {
            $usr_msg->add_id_with_vars(msg_id::FORMULA_JSON_MISSING, [
                msg_id::VAR_JSON_TEXT => json_encode($api_msg_part)
            ]);
        }
        if ($usr_msg->is_ok()) {
            $this->set_formula($frm);
        }
    }

    /**
     * set the formula of this link based on the id
     * and fill the formula based on the cache if possible
     * @param int|array $api_msg_part the id itself or an array which leads to an user error message
     * @param user_message $usr_msg the message for the user why the action has failed and a suggested solution
     * @param data_object|null $dto the data object that contains the already imported formulas
     * @return void true if setting the formula has been successful
     */
    private function set_formula_from_id(
        int|array    $api_msg_part,
        user_message $usr_msg,
        ?data_object $dto = null
    ): void
    {
        $frm = new formula($this->get_user());
        if (is_int($api_msg_part)) {
            if ($dto != null) {
                $frm = $dto->get_formula_by_id($api_msg_part, $frm);
            } else {
                $frm->id = $api_msg_part;
            }
        } else {
            $usr_msg->add_id_with_vars(msg_id::FORMULA_ID_MISSING, [
                msg_id::VAR_JSON_TEXT => json_encode($api_msg_part)
            ]);
        }
        if ($usr_msg->is_ok()) {
            $this->set_formula($frm);
        }
    }

    /**
     * rename and cast the parent from object function
     * @param formula $frm the formula that should be linked
     * @return void
     */
    function set_formula(formula $frm): void
    {
        $this->set_fob($frm);
    }

    /**
     * set the phrase of this link based on the phrase array
     * @param int|array $api_msg_part either the id itself or an array with the id and the phrase details
     * @param user_message $usr_msg the message for the user why the action has failed and a suggested solution
     * @return void true if setting the phrase has been successful
     */
    private function set_phrase_from_api_json(
        int|array    $api_msg_part,
        user_message $usr_msg
    ): void
    {
        $phr = new phrase($this->get_user());
        if (is_array($api_msg_part)) {
            $phr->api_mapper($api_msg_part, $usr_msg);
        } else {
            $usr_msg->add_id_with_vars(msg_id::FORMULA_JSON_MISSING, [
                msg_id::VAR_JSON_TEXT => json_encode($api_msg_part)
            ]);
        }
        if ($usr_msg->is_ok()) {
            $this->set_phrase($phr);
        }
    }

    /**
     * set the phrase of this link based on the id
     * and fill the phrase based on the cache if possible
     * @param int|array $api_msg_part the id itself or an array which leads to an user error message
     * @param user_message $usr_msg the message for the user why the action has failed and a suggested solution
     * @param data_object|null $dto the data object that contains the already imported phrases
     * @return void true if setting the phrase has been successful
     */
    private function set_phrase_from_id(
        int|array    $api_msg_part,
        user_message $usr_msg,
        ?data_object $dto = null
    ): void
    {
        $phr = new phrase($this->get_user());
        if (is_int($api_msg_part)) {
            if ($dto != null) {
                $phr = $dto->get_phrase_by_id($api_msg_part, $phr);
            } else {
                $phr->set_id($api_msg_part);
            }
        } else {
            $usr_msg->add_id_with_vars(msg_id::FORMULA_ID_MISSING, [
                msg_id::VAR_JSON_TEXT => json_encode($api_msg_part)
            ]);
        }
        if ($usr_msg->is_ok()) {
            $this->set_phrase($phr);
        }
    }

    /**
     * rename and cast the parent from object function
     * @param phrase $phr the phrase to which the formula should be linked
     * @return void
     */
    function set_phrase(phrase $phr): void
    {
        $this->set_tob($phr);
    }

    function formula(): combine_named|sandbox_named|formula
    {
        return $this->fob();
    }

    function phrase(): combine_named|sandbox_named|phrase
    {
        return $this->tob();
    }

    /**
     * @return int the formula id and null if the formula is not set
     */
    function formula_id(): int
    {
        $result = 0;
        if ($this->fob() != null) {
            if ($this->fob()->id() > 0) {
                $result = $this->fob()->id();
            }
        }
        return $result;
    }

    /**
     * @return int the phrase id and null if the phrase is not set
     */
    function phrase_id(): int
    {
        $result = 0;
        if ($this->tob() != null) {
            if ($this->tob()->id() != 0) {
                $result = $this->tob()->id();
            }
        }
        return $result;
    }

    /**
     * expose the order number as pos
     * @return int|null
     */
    function pos(): ?int
    {
        return $this->order_nbr;
    }

    /**
     * overwrite the link type function
     * @return string|null the code id of the verb
     */
    function get_predicate_code_id(): ?string
    {
        global $sys;
        $id = $this->predicate_id();
        $typ = $sys->typ_lst->frm_lnk_typ->get($this->predicate_id());
        if ($typ != null) {
            return $typ->get_code_id();
        } else {
            $msg = 'formula link type with id ' . $id . ' is missing';
            log_err($msg);
            return $msg;
        }
    }


    /*
     * preloaded
     */

    /**
     * get the name of the formula link type
     * @return string the name of the formula link type
     */
    function predicate_name(): string
    {
        global $sys;
        return $sys->typ_lst->frm_lnk_typ->name($this->predicate_id);
    }


    /*
     * load
     */

    /**
     * create an SQL statement to retrieve the user specific formula link from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation e.g. standard for values and results
     * @return sql_par the SQL statement base on the parameters set in $this
     */
    function load_sql_user_changes(
        sql_creator   $sc,
        sql_type_list $sc_par_lst = new sql_type_list()
    ): sql_par
    {
        $sc->set_class($this::class, new sql_type_list([sql_type::USER]));
        return parent::load_sql_user_changes($sc, $sc_par_lst);
    }

    /**
     * create the common part of an SQL statement to retrieve the parameters of a formula link from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name, string $class = self::class): sql_par
    {
        $qp = new sql_par($class);
        $qp->name .= $query_name;

        $sc->set_class($class);
        $sc->set_name($qp->name);
        $sc->set_usr($this->get_user()->id);
        $sc->set_fields(self::FLD_NAMES_LINK);
        $sc->set_usr_num_fields(self::FLD_NAMES_NUM_USR);

        return $qp;
    }

    /**
     * @return string the query name extension to make the query name unique and parameter specific
     */
    private function load_sql_name_extension(): string
    {
        $result = '';
        if ($this->id() != 0) {
            $result .= sql_db::FLD_ID;
        } elseif ($this->is_unique()) {
            $result .= 'link_ids';
        } else {
            log_err("Either the database ID (" . $this->id() . ") or the link ids must be set to load a word.", "formula_link->load");
        }
        return $result;
    }

    /**
     * create an SQL statement to retrieve the parameters of the standard formula link from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_standard(sql_creator $sc): sql_par
    {
        $sc->set_class($this::class);
        $qp = new sql_par($this::class, new sql_type_list([sql_type::NORM]));
        $qp->name .= $this->load_sql_name_extension();
        $sc->set_name($qp->name);
        $sc->set_usr($this->get_user()->id);
        $sc->set_fields(self::FLD_NAMES);
        if ($this->id() != 0) {
            $sc->add_where($this->id_field(), $this->id());
        } elseif ($this->formula_id() != 0 and $this->phrase_id() != 0) {
            $sc->add_where(formula_db::FLD_ID, $this->formula_id());
            $sc->add_where(phrase::FLD_ID, $this->phrase_id());
        } else {
            log_err('Cannot load default formula link because no unique field is set');
        }
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * load the standard formula link to check if the user has done some personal changes
     * e.g. switched off a formula assignment
     * @param sql_par|null $qp placeholder to align the function parameters with the parent
     * @return bool true if the loading of the standard formula link been successful
     */
    function load_standard(?sql_par $qp = null): bool
    {

        global $db_con;
        $result = false;

        if ($this->is_unique()) {
            $qp = $this->load_sql_standard($db_con->sql_creator());

            if ($qp->name <> '') {
                $db_frm = $db_con->get1($qp);
                $this->row_mapper_sandbox($db_frm, true, false);
                $result = $this->load_owner();
            }
        }
        return $result;
    }

    /**
     * load a named user sandbox object by name
     * @param formula $frm the formula that is supposed to be linked
     * @param phrase $phr the phrase that is linked to the formula
     * @param string $class the name of the child class from where the call has been triggered
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_link(formula $frm, phrase $phr, string $class = self::class): int
    {
        global $sys;
        $id = parent::load_by_link_id($frm->id(), $sys->typ_lst->frm_lnk_typ->default_id(), $phr->id(), $class);
        // no need to reload the linked objects, just assign it
        if ($id != 0) {
            $this->set_formula($frm);
            $this->set_phrase($phr);
        }
        return $id;
    }

    /**
     * to load the formula and the phase object
     * if the link object is loaded by an external query like in user_display to show the sandbox
     * @return bool true if the loading of the linked objects has been successful
     */
    function reload_objects(): bool
    {
        $result = true;
        if ($this->formula_id() > 0) {
            $frm = new formula($this->get_user());
            $frm->load_by_id($this->formula_id());
            if ($frm->id() > 0) {
                $this->set_formula($frm);
            } else {
                $result = false;
            }
        }
        if ($result) {
            if ($this->phrase_id() <> 0) {
                $phr = new phrase($this->get_user());
                $phr->load_by_id($this->phrase_id());
                if ($phr->id() != 0) {
                    $this->set_phrase($phr);
                } else {
                    $result = false;
                }
            }
        }
        return $result;
    }

    function from_field(): string
    {
        return formula_db::FLD_ID;
    }

    function to_field(): string
    {
        return phrase::FLD_ID;
    }

    function type_field(): string
    {
        return formula_link_type::FLD_ID;
    }

    /**
     * @return string the field name of the name db field as a function for complex overwrites
     */
    function name_field(): string
    {
        return '';
    }


    /*
     * im- and export
     */

    /**
     * create an array with the export json fields of this component
     * which does not include the internal database id
     * @param export_type_list|array $exp_typ define the export format
     * @param bool $do_load true if any missing data should be loaded while creating the array
     * @return array with the json fields
     */
    function export_json(export_type_list|array $exp_typ = [], bool $do_load = true): array
    {
        $vars = parent::export_json($exp_typ, $do_load);
        if ($this->formula()?->name() != null) {
            $vars[json_fields::FORMULA] = $this->formula()->export_json($exp_typ, $do_load);
        }
        if ($this->phrase()?->name() != null) {
            $vars[json_fields::PHRASE] = $this->phrase()->export_json($exp_typ, $do_load);
        }

        // do not include the default link type in the export
        global $sys;
        if ($this->predicate_id == $sys->typ_lst->frm_lnk_typ->id(formula_link_type::DEFAULT)) {
            unset($vars[json_fields::PREDICATE]);
        }
        if ($this->order_nbr != null) {
            $vars[json_fields::PRIORITY] = $this->order_nbr;
        }

        return $vars;
    }


    /*
     * save
     */

    /**
     * @return bool true if no one has used this formula
     */
    function not_used(): bool
    {
        log_debug('formula_link->not_used (' . $this->id() . ')');

        // to review: maybe replace by a database foreign key check
        return $this->not_changed();
    }

    /**
     * @return bool true if no other user has modified the formula link
     */
    function not_changed(): bool
    {
        log_debug($this->id() . ' by someone else than the owner (' . $this->owner_id() . ')');

        global $db_con;
        $lib = new library();
        $result = true;
        $qp = $this->not_changed_sql($db_con->sql_creator());
        $db_con->usr_id = $this->get_user()->id;
        $db_row = $db_con->get1($qp);
        if ($db_row != null) {
            if ($db_row[user_db::FLD_ID] > 0) {
                $result = false;
            }
        }
        log_debug('for ' . $this->dsp_id() . ' is ' . $lib->dsp_bool($result));
        return $result;
    }

    /**
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     *                 to check if no one else has changed the formula link
     */
    function not_changed_sql(sql_creator $sc): sql_par
    {
        $sc->set_class(formula_link::class);
        return $sc->load_sql_not_changed($this->id(), $this->owner_id());
    }

    /**
     * set the main log entry parameters for updating one display word link field
     * e.g. that the user can see "moved formula list to position 3 in word view"
     * @return change the change log object with the presets for formula links
     */
    function log_upd_field(): change
    {
        $log = new change($this->get_user());
        $log->set_action(change_actions::UPDATE);
        if ($this->can_change()) {
            $log->set_class(formula_link::class);
        } else {
            $log->set_table(change_tables::FORMULA_LINK_USR);
        }

        return $log;
    }

    /**
     * save all updated formula_link fields excluding the name, because already done when adding a formula_link
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param formula_link|sandbox $db_obj the database record before the saving
     * @param formula_link|sandbox $norm_obj the database record defined as standard because it is used by most users
     * @return user_message the message that should be shown to the user in case something went wrong
     */
    function save_all_fields(sql_db $db_con, formula_link|sandbox $db_obj, formula_link|sandbox $norm_obj): user_message
    {
        // link type not used at the moment
        $usr_msg = $this->save_field_type($db_con, $db_obj, $norm_obj);
        $usr_msg->add($this->save_field_excluded($db_con, $db_obj, $norm_obj));
        log_debug('all fields for "' . $this->formula()->name() . '" to "' . $this->phrase()->name() . '" has been saved');
        return $usr_msg;
    }

    /**
     * create a new link object including the order number
     * @returns int the id of the creates object
     */
    function add_insert(): int
    {
        global $db_con;
        $db_con->set_class(self::class);
        return $db_con->insert_old(
            array($this->from_name . sql_db::FLD_EXT_ID, $this->to_name . sql_db::FLD_EXT_ID, user_db::FLD_ID, 'order_nbr'),
            array($this->formula_id(), $this->phrase_id(), $this->get_user()->id, $this->order_nbr));
    }

    /**
     * update a formula_link in the database or create a user formula_link
     * @param user_message $usr_msg the message object that is enriched in case something went wrong to show the user the problem and the suggested solutions
     * @param bool $use_func if true a predefined function is used that also creates the log entries
     * @return bool true if everything has been fine
     */
    function save(user_message $usr_msg, ?bool $use_func = null): bool
    {

        global $db_con;

        // check if the required parameters are set
        if ($this->formula_id() != 0 and $this->phrase_id() != 0) {
            log_debug('"' . $this->formula()->name() . '" to "' . $this->phrase()->name() . '" (id ' . $this->id() . ') for user ' . $this->get_user()->name);
        } elseif ($this->id() > 0) {
            log_debug('id ' . $this->id() . ' for user ' . $this->get_user()->name);
        } else {
            log_err("Either the formula and the word or the id must be set to link a formula to a word.", "formula_link->save");
        }

        // decide which db write method should be used
        if ($use_func === null) {
            $use_func = $this->sql_default_script_usage();
        }

        // load the objects if needed
        $this->reload_objects();

        // build the database object because the is anyway needed
        $db_con->set_usr($this->get_user()->id);
        $db_con->set_class(formula_link::class);

        // check if a new value is supposed to be added
        if ($this->id() <= 0) {
            log_debug('check if a new formula_link for "' . $this->formula()->name() . '" and "' . $this->phrase()->name() . '" needs to be created');
            // check if a formula_link with the same formula and word is already in the database
            $db_chk = new formula_link($this->get_user());
            $db_chk->set_formula($this->formula());
            $db_chk->set_phrase($this->phrase());
            $db_chk->load_standard();
            if ($db_chk->id() > 0) {
                $this->id = $db_chk->id();
            }
        }

        if ($this->id() <= 0) {
            if ($this->db_ready($usr_msg)) {
                log_debug('new formula link from "' . $this->formula()->name() . '" to "' . $this->phrase()->name() . '"');
                $this->add($usr_msg, $use_func);
            }
        } else {
            log_debug('update "' . $this->id() . '"');
            // read the database values to be able to check if something has been changed; done first,
            // because it needs to be done for user and general formulas
            $db_rec = new formula_link($this->get_user());
            $db_rec->load_by_id($this->id());
            $db_rec->reload_objects();
            $db_con->set_class(formula_link::class);
            // relevant is if there is a user config in the database
            // so use this information to prevent
            // the need to forward the db_rec to all functions
            if ($db_rec->has_usr_cfg() and !$this->has_usr_cfg()) {
                $this->usr_cfg_id = $db_rec->usr_cfg_id;
            }
            log_debug("database formula loaded (" . $db_rec->id() . ")");
            $std_rec = new formula_link($this->get_user()); // must also be set to allow to take the ownership
            $std_rec->id = $this->id();
            $std_rec->load_standard();
            log_debug("standard formula settings loaded (" . $std_rec->id() . ")");

            // for a correct user formula link detection (function can_change) set the owner even if the formula link has not been loaded before the save
            if ($this->owner_id() <= 0) {
                $this->set_owner_id($std_rec->owner_id());
            }

            // it should not be possible to change the formula or the word, but nevertheless check
            // instead of changing the formula or the word, a new link should be created and the old deleted
            if ($db_rec->formula() != null) {
                if ($db_rec->formula()->id() <> $this->formula()->id()
                    or $db_rec->phrase()->id() <> $this->phrase()->id()) {
                    log_debug("update link settings for id " . $this->id() . ": change formula " . $db_rec->formula_id() . " to " . $this->formula_id() . " and " . $db_rec->phrase_id() . " to " . $this->phrase_id());
                    $usr_msg->add_message_text(log_info('The formula link "' . $db_rec->formula()->name() . '" with "' . $db_rec->phrase()->name() . '" (id ' . $db_rec->formula_id() . ',' . $db_rec->phrase_id() . ') " cannot be changed to "' . $this->formula()->name() . '" with "' . $this->phrase()->name() . '" (id ' . $this->formula()->id() . ',' . $this->phrase()->id() . '). Instead the program should have created a new link.', "formula_link->save"));
                }
            }

            // check if the id parameters are supposed to be changed
            $this->reload_objects();
            if ($usr_msg->is_ok()) {
                $this->save_id_if_updated($db_con, $db_rec, $std_rec, $usr_msg, $use_func);
            }

            // if a problem has appeared up to here, don't try to save the values
            // the problem is shown to the user by the calling interactive script
            if ($usr_msg->is_ok()) {
                if ($use_func) {
                    $this->save_fields_func($db_con, $db_rec, $std_rec, $usr_msg);
                } else {
                    $usr_msg->add($this->save_all_fields($db_con, $db_rec, $std_rec));
                }
            }
        }

        if (!$usr_msg->is_ok()) {
            // TODO Prio 1 activate error
            //log_err($usr_msg->all_message_text());
            log_warning($usr_msg->all_message_text());
        }

        return $usr_msg->is_ok();
    }


    protected function all_sandbox_fields(): array
    {
        return self::ALL_SANDBOX_FLD_NAMES;
    }


    /*
     * sql write fields
     */

    /**
     * get a list of all database fields that might be changed
     * excluding the internal fields e.g. the database id
     * field list must be corresponding to the db_fields_changed fields
     *
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return array list of all database field names that have been updated
     */
    function db_fields_all(sql_type_list $sc_par_lst = new sql_type_list()): array
    {
        return array_merge(
            parent::db_all_fields_link($sc_par_lst),
            [
                self::FLD_ORDER,
            ],
            parent::db_fields_all_sandbox()
        );
    }

    /**
     * get a list of database field names, values and types that have been updated
     *
     * @param sandbox|formula_link $sbx the compare value to detect the changed fields
     * @param user_message $usr_msg the user message object that collects any issues during the sql creation
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list list 3 entry arrays with the database field name, the value and the sql type that have been updated
     */
    function db_fields_changed(
        sandbox|formula_link $sbx,
        user_message         $usr_msg,
        sql_type_list        $sc_par_lst = new sql_type_list()
    ): sql_par_field_list
    {
        global $sys;

        $sc = new sql_creator();
        $do_log = $sc_par_lst->incl_log();
        $usr_tbl = $sc_par_lst->is_usr_tbl();
        $table_id = $sc->table_id($this::class);

        $lst = parent::db_fields_changed($sbx, $usr_msg, $sc_par_lst);
        // for the standard table the type field should always be included because it is part of the prime index
        if ($sbx->predicate_id() !== $this->predicate_id() or (!$usr_tbl and $sc_par_lst->is_insert())) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . formula_link_type::FLD_ID,
                    $sys->typ_lst->cng_fld->id($table_id . formula_link_type::FLD_ID),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            global $sys;
            if ($this->predicate_id() < 0) {
                $usr_msg->add_id_with_vars(msg_id::FORMULA_LINK_TYPE_MISSING, [
                    msg_id::VAR_TYPE => $this->predicate_id(),
                    msg_id::VAR_NAME => $this->dsp_id()
                ]);
            }
            $lst->add_type_field(
                formula_link_type::FLD_ID,
                type_object::FLD_NAME,
                $this->predicate_id(),
                $sbx->predicate_id(),
                $sys->typ_lst->frm_lnk_typ
            );
        }
        if ($sbx->pos() !== $this->pos()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . self::FLD_ORDER,
                    $sys->typ_lst->cng_fld->id($table_id . self::FLD_ORDER),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                self::FLD_ORDER,
                $this->pos(),
                self::FLD_ORDER_SQL_TYP,
                $sbx->pos()
            );
        }
        return $lst->merge($this->db_changed_sandbox_list($sbx, $sc_par_lst));
    }


    /*
     * message
     */

    function message_from_invalid(user_message $usr_msg): void
    {
        $usr_msg->add_id_with_vars(msg_id::MANDATORY_FORMULA_IN_LINK_INVALID, [
            msg_id::VAR_FORMULA_NAME => $this->formula()->dsp_id(),
            msg_id::VAR_NAME => $this->phrase()->dsp_id(),
        ]);
    }

    function message_to_invalid(user_message $usr_msg): void
    {
        $usr_msg->add_id_with_vars(msg_id::MANDATORY_PHRASE_IN_LINK_INVALID, [
            msg_id::VAR_PHRASE_NAME => $this->phrase()->dsp_id(),
            msg_id::VAR_NAME => $this->formula()->dsp_id(),
        ]);
    }


    /*
     * debug
     */

    /**
     * @return string the html code to display the link name
     */
    function name(): string
    {
        $result = '';

        if ($this->formula() != null) {
            $result = $this->formula()->name();
        }
        if ($this->phrase_id() != 0) {
            $result = ' to ' . $this->phrase()->name();
        }

        return $result;
    }

    /**
     * @return string return the html code to display the link name
     */
    function name_linked(string $back = ''): string
    {
        $result = '';

        $this->reload_objects();
        if ($this->formula_id() != 0 and $this->phrase_id() != 0) {
            $result = $this->formula()->name_linked($back) . ' to ' . $this->phrase()->display_linked();
        } else {
            $result .= log_err("The formula or the linked word cannot be loaded.", "formula_link->name");
        }

        return $result;
    }

}