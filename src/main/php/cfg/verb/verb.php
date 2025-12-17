<?php

/*

    model/verb/verb.php - predicate object to link two words
    -------------------

    TODO maybe move the reverse to a linked predicate


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

namespace Zukunft\ZukunftCom\main\php\cfg\verb;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_CONST . 'def.php';
include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_par_field_list.php';
include_once paths::DB . 'sql_par_type.php';
include_once paths::DB . 'sql_type.php';
include_once paths::DB . 'sql_type_list.php';
include_once paths::EXPORT . 'export_type_list.php';
include_once paths::MODEL_HELPER . 'data_object.php';
include_once paths::MODEL_HELPER . 'db_object.php';
include_once paths::MODEL_HELPER . 'db_object_seq_id.php';
include_once paths::MODEL_LOG . 'change.php';
include_once paths::MODEL_LOG . 'change_action.php';
//include_once paths::MODEL_LOG . 'change_table_list.php';
include_once paths::MODEL_LOG . 'changes_norm.php';
include_once paths::MODEL_LOG . 'changes_big.php';
//include_once paths::MODEL_PHRASE . 'term.php';
include_once paths::MODEL_SANDBOX . 'sandbox.php';
include_once paths::MODEL_SANDBOX . 'sandbox_named.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_db.php';
include_once paths::MODEL_USER . 'user_message.php';
//include_once paths::MODEL_WORD . 'word.php';
include_once paths::SHARED_ENUM . 'change_actions.php';
include_once paths::SHARED_ENUM . 'change_tables.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED_TYPES . 'verbs.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_field_list;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type_list;
use Zukunft\ZukunftCom\main\php\cfg\export\export_type_list;
use Zukunft\ZukunftCom\main\php\cfg\helper\data_object;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_object_seq_id;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;
use Zukunft\ZukunftCom\main\php\cfg\log\change;
use Zukunft\ZukunftCom\main\php\cfg\log\change_action;
use Zukunft\ZukunftCom\main\php\cfg\phrase\term;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_db;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\shared\enum\change_actions;
use Zukunft\ZukunftCom\main\php\shared\enum\change_tables;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;

class verb extends type_object
{

    // TODO MAYBE base verb on named sandbox object like suggested in the frontend or change the frontend object
    // TODO add an easy way to get the name from the code id


    /*
     * database link
     */

    // object specific database and JSON object field names and comments
    const string TBL_COMMENT = 'for verbs / triple predicates to use predefined behavior';

    // forward the const to enable usage of $this::CONST_NAME
    const string FLD_ID = verb_db::FLD_ID;
    const array FLD_NAMES = verb_db::FLD_NAMES;
    const array FLD_LST_NAME = verb_db::FLD_LST_NAME;
    const array FLD_LST_ALL = verb_db::FLD_LST_ALL;


    /*
     * object vars
     */

    // the user is used only to allow adding the code id on import
    // but there should not be any user specific verbs
    // otherwise if id is 0 (not NULL) the standard word link type,
    // otherwise the user specific verb
    private ?user $usr = null;
    // name used if more than one word is shown
    // e.g. instead of "ABB" "is a" "company"
    // use "ABB", Nestlé" "are" "companies"
    // TODO move to language forms
    private ?string $plural = null;
    // name used if displayed the other way round
    // e.g. for "Country" "has a" "Human Development Index"
    // the reverse would be "Human Development Index" "is used for" "Country"
    private ?string $reverse = null;
    // the reverse name for many words
    private ?string $rev_plural = null;
    // short name of the verb for the use in formulas
    // because there both sides are combined
    private ?string $frm_name = null;
    // how often this current used has used the verb
    // (until now just the usage of all users)
    private ?int $usage = null;
    // the importance of the word based on the value defined for each word by the words "impact" and "criteria"
    private ?float $impact = null;


    /*
     * construct and map
     */

    /**
     * set the main mandatory vars of this verb object
     * @param int $id the id can be null for new verbs
     * @param string $name the name of the verb must be unique
     * @param string|null $code_id the code id can be null for new verbs that does not yet have any fixed assigned code
     */
    function __construct(int $id = 0, string $name = '', ?string $code_id = null)
    {
        parent::__construct($code_id);
        if ($id > 0) {
            $this->id = $id;
        }
        if ($name != '') {
            $this->set_name($name);
        }
        if ($code_id != '') {
            $this->code_id = $code_id;
        }
    }

    /**
     * set the vars of this verb object to the default values
     * @param bool $keep_user set to true to keep the original user
     * @return void
     */
    function reset(bool $keep_user = false): void
    {
        parent::reset($keep_user);
        $this->set_user(null);
        $this->plural = null;
        $this->reverse = null;
        $this->rev_plural = null;
        $this->frm_name = null;
        $this->usage = null;
        $this->impact = null;
    }

    function clone_reset(): verb
    {
        $obj_cpy = clone $this;
        $obj_cpy->reset();
        return $obj_cpy;
    }

    /**
     * map a verb api json to this model verb object
     * @param array $api_json the api array with the word values that should be mapped
     * @param user_message $usr_msg the message for the user why the action has failed and a suggested solution
     * @return bool true if the mapping has been completed successful
     */
    function api_mapper(array $api_json, user_message $usr_msg): bool
    {
        parent::api_mapper($api_json, $usr_msg);

        // TODO add user to request new verbs via api

        // TODO move plural to language forms
        if (array_key_exists(json_fields::PLURAL, $api_json)) {
            if ($api_json[json_fields::PLURAL] <> '') {
                $this->set_plural($api_json[json_fields::PLURAL]);
            }
        }
        if (array_key_exists(json_fields::REVERSE, $api_json)) {
            if ($api_json[json_fields::REVERSE] <> '') {
                $this->set_reverse($api_json[json_fields::REVERSE]);
            }
        }
        if (array_key_exists(json_fields::REV_PLURAL, $api_json)) {
            if ($api_json[json_fields::REV_PLURAL] <> '') {
                $this->set_reverse_plural($api_json[json_fields::REV_PLURAL]);
            }
        }

        // the usage and impact var is not expected to be changed via api

        return $usr_msg->is_ok();
    }

    /**
     * function to import the core user sandbox object values from a json string
     * e.g. the share and protection settings
     *
     * @param array $in_ex_json an array with the data of the json object
     * @param user_message $usr_msg to enrich with warnings, problems and solutions including the user who has initiated the import mainly used to add tge code id to the database
     * @param data_object|null $dto cache of the objects imported until now for the primary references
     * @return bool true if everything was fine
     */
    function import_mapper(
        array        $in_ex_json,
        user_message $usr_msg,
        ?data_object $dto = null
    ): bool
    {
        parent::import_mapper($in_ex_json, $usr_msg, $dto);

        if (key_exists(json_fields::NAME, $in_ex_json)) {
            $this->set_name($in_ex_json[json_fields::NAME]);
        }
        if (key_exists(json_fields::DESCRIPTION, $in_ex_json)) {
            if ($in_ex_json[json_fields::DESCRIPTION] <> '') {
                $this->description = $in_ex_json[json_fields::DESCRIPTION];
            }
        }
        if (key_exists(json_fields::CODE_ID, $in_ex_json)) {
            if ($in_ex_json[json_fields::CODE_ID] <> '') {
                $this->set_code_id($in_ex_json[json_fields::CODE_ID], $usr_msg->usr);
            }
        }

        // the usage and impact var is not expected to be changed via import

        return $usr_msg->is_ok();
    }

    /**
     * set the class vars based on a database record
     *
     * @param array|null $db_row is an array with the database values
     * @param string $id_fld the name of the id field
     * @param string $name_fld the name of the name field
     * @return bool true if the verb is loaded and valid
     */
    function row_mapper_verb(
        ?array $db_row,
        string $id_fld = verb_db::FLD_ID,
        string $name_fld = verb_db::FLD_NAME): bool
    {
        $result = parent::row_mapper($db_row, $id_fld);
        if ($result) {
            if (array_key_exists(sql_db::FLD_CODE_ID, $db_row)) {
                if ($db_row[sql_db::FLD_CODE_ID] != null) {
                    $this->set_code_id_db($db_row[sql_db::FLD_CODE_ID]);
                }
            }
            $this->set_name($db_row[$name_fld]);
            if (array_key_exists(verb_db::FLD_PLURAL, $db_row)) {
                $this->set_plural($db_row[verb_db::FLD_PLURAL]);
            }
            if (array_key_exists(verb_db::FLD_REVERSE, $db_row)) {
                $this->set_reverse($db_row[verb_db::FLD_REVERSE]);
            }
            if (array_key_exists(verb_db::FLD_PLURAL_REVERSE, $db_row)) {
                $this->set_reverse_plural($db_row[verb_db::FLD_PLURAL_REVERSE]);
            }
            if (array_key_exists(verb_db::FLD_NAME_FORMULA, $db_row)) {
                $this->set_formula_name($db_row[verb_db::FLD_NAME_FORMULA]);
            }
            if (array_key_exists(sql_db::FLD_DESCRIPTION, $db_row)) {
                $this->description = $db_row[sql_db::FLD_DESCRIPTION];
            }
            if (array_key_exists(sql_db::FLD_USAGE, $db_row)) {
                if ($db_row[sql_db::FLD_USAGE] == null) {
                    $this->usage = null;
                } else {
                    $this->usage = $db_row[sql_db::FLD_USAGE];
                }
            }
            if (array_key_exists(sql_db::FLD_IMPACT, $db_row)) {
                if ($db_row[sql_db::FLD_IMPACT] == null) {
                    $this->impact = null;
                } else {
                    $this->impact = $db_row[sql_db::FLD_IMPACT];
                }
            }
        }
        return $result;
    }


    /*
     * set and get
     */

    /**
     * set the most used object vars with one set statement
     * @param int $id mainly for test creation the database id of the verb
     * @param string $name mainly for test creation the name of the verb
     */
    function set(int $id = 0, string $name = ''): void
    {
        $this->id = $id;
        $this->set_name($name);
    }

    /**
     * @param string|null $name the unique name of the verb
     */
    function set_name(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * set the user of the verb
     *
     * @param user|null $usr the person who wants to access the verb
     * @return void
     */
    function set_user(?user $usr): void
    {
        $this->usr = $usr;
    }

    function set_plural(?string $plural): void
    {
        $this->plural = $plural;
    }

    function get_plural(): ?string
    {
        return $this->plural;
    }

    function set_reverse(?string $reverse): void
    {
        $this->reverse = $reverse;
    }

    function get_reverse(): ?string
    {
        return $this->reverse;
    }

    function set_reverse_plural(?string $reverse_plural): void
    {
        $this->rev_plural = $reverse_plural;
    }

    function get_reverse_plural(): ?string
    {
        return $this->rev_plural;
    }

    function set_formula_name(?string $formula_name): void
    {
        $this->frm_name = $formula_name;
    }

    function get_formula_name(): ?string
    {
        return $this->frm_name;
    }

    /**
     * @return string a unique name for the verb that is also used in the code
     */
    function get_code_id(): string
    {
        if ($this->code_id == null) {
            return '';
        } else {
            return $this->code_id;
        }
    }

    /**
     * @return string the description of the verb
     */
    function get_description(): string
    {
        if ($this->description == null) {
            return '';
        } else {
            return $this->description;
        }
    }

    /**
     * @return user|null the person who wants to see this verb
     */
    function get_user(): ?user
    {
        return $this->usr;
    }

    /**
     * set the value to rank the verbs by usage
     *
     * @param int $usage a higher value moves the verb to the top of the selection list
     * @return void
     */
    function set_usage(int $usage): void
    {
        $this->usage = $usage;
    }

    /**
     * @return int a higher number indicates a higher usage
     */
    function get_usage(): ?int
    {
        return $this->usage;
    }

    /**
     * set the cache value to sort this verb by relevance
     * the impact is calculated based on the formula assigned to the object
     * by the system triple "impact phrase"
     *
     * @param float|null $impact a higher value moves the sandbox object to the top of the selection list
     * @return void
     */
    function set_impact(?float $impact): void
    {
        $this->impact = $impact;
    }

    /**
     * @return float a higher number indicates a higher impact
     */
    function get_impact(): ?float
    {
        return $this->impact;
    }


    /*
     * load
     */

    /**
     * load a verb by the verb name
     * @param string $name the name of the verb
     * @return int the id of the verb found and zero if nothing is found
     */
    function load_by_name(string $name): int
    {
        global $db_con;

        log_debug($name);
        $qp = $this->load_sql_by_name($db_con->sql_creator(), $name);
        return $this->load($qp);
    }

    /**
     * load a verb by the verb name
     * @param string $code_id the code id of the verb
     * @return int the id of the verb found and zero if nothing is found
     */
    function load_by_code_id(string $code_id): int
    {
        global $db_con;

        log_debug($code_id);
        $qp = $this->load_sql_by_code_id($db_con->sql_creator(), $code_id);
        return $this->load($qp);
    }

    /**
     * load a verb from the database
     * @param sql_par $qp the query parameters created by the calling function
     * @return int the id of the object found and zero if nothing is found
     */
    protected function load(sql_par $qp): int
    {
        global $db_con;

        $db_row = $db_con->get1($qp);
        $this->row_mapper_verb($db_row);
        return $this->id();
    }


    /*
     * load sql
     */

    /**
     * create an SQL statement to retrieve a verb by id from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param int $id the id of the user sandbox object
     * @param string $class the name of this class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_id(sql_creator $sc, int $id, string $class = self::class): sql_par
    {
        $lib = new library();
        $class = $lib->class_to_name($class);
        return parent::load_sql_by_id_fwd($sc, $id, $class);
    }

    /**
     * create an SQL statement to retrieve a verb by name from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $name the name of the verb
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_name(sql_creator $sc, string $name, string $class = self::class): sql_par
    {
        $qp = $this->load_sql($sc, sql_db::FLD_NAME, $class);
        $sc->add_where(verb_db::FLD_NAME, $name, sql_par_type::TEXT_OR);
        $sc->add_where(verb_db::FLD_NAME_FORMULA, $name, sql_par_type::TEXT_OR);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a verb by code id from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $code_id the code id of the verb
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_code_id(sql_creator $sc, string $code_id, string $class = self::class): sql_par
    {
        $qp = $this->load_sql($sc, 'code_id', $class);
        $sc->add_where(sql_db::FLD_CODE_ID, $code_id);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create the common part of an SQL statement to retrieve the parameters of a verb from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name of the query use to prepare and call the query
     * @param string $class the name of this class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name, string $class = self::class): sql_par
    {
        $qp = parent::load_sql($sc, $query_name, $class);

        $sc->set_class(self::class);
        $sc->set_name($qp->name);
        $sc->set_fields(verb_db::FLD_NAMES);

        return $qp;
    }


    /*
     * api
     */

    /**
     * create an array for the api json creation
     * differs from the export array by using the internal id instead of the names
     * @param api_type_list|array $typ_lst configuration for the api message e.g. if phrases should be included
     * @param user|null $usr the user for whom the api message should be created which can differ from the session user
     * @return array the filled array used to create the api json message to the frontend
     */
    function api_json_array(api_type_list|array $typ_lst = [], user|null $usr = null): array
    {
        $vars = [];

        $vars[json_fields::NAME] = $this->name();
        $vars[json_fields::CODE_ID] = $this->get_code_id();
        $vars[json_fields::DESCRIPTION] = $this->get_description();
        $vars[json_fields::PLURAL] = $this->get_plural();
        $vars[json_fields::REVERSE] = $this->get_reverse();
        $vars[json_fields::REV_PLURAL] = $this->get_reverse_plural();
        $vars[json_fields::FRM_NAME] = $this->get_formula_name();
        $vars[json_fields::USAGE] = $this->get_usage();
        $vars[json_fields::IMPACT] = $this->get_impact();
        $vars[json_fields::ID] = $this->id();

        return $vars;
    }
    // TODO test set_by_api_json


    /*
     * im- and export
     */

    /**
     * add a verb in the database from an imported json object of external database from
     *
     * @param array $in_ex_json an array with the data of the json object
     * @param user_message $usr_msg to enrich with warnings, problems and solutions
     * @param data_object|null $dto cache of the objects imported until now for the primary references
     * @return bool true if everything was fine
     */
    function import_obj(
        array        $in_ex_json,
        user_message $usr_msg,
        ?data_object $dto = null
    ): bool
    {
        global $db_con;
        global $sys;

        $this->import_mapper($in_ex_json, $usr_msg, $dto);

        // reset all parameters of this verb object but keep the user
        $this->reset(true);

        // TODO Prio 0 switch to a key_exists
        foreach ($in_ex_json as $key => $value) {
            if ($key == json_fields::NAME) {
                $this->name = $value;
            }
            if ($key == json_fields::CODE_ID) {
                if ($value != '') {
                    if ($usr_msg->usr->is_admin() or $usr_msg->usr->is_system()) {
                        $this->code_id = $value;
                    }
                }
            }
            if ($key == json_fields::DESCRIPTION) {
                $this->description = $value;
            }
            if ($key == verb_db::FLD_REVERSE) {
                $this->set_reverse($value);
            }
            if ($key == verb_db::FLD_PLURAL) {
                $this->set_plural($value);
            }
            if ($key == verb_db::FLD_NAME_FORMULA) {
                $this->set_formula_name($value);
            }
            if ($key == verb_db::FLD_PLURAL_REVERSE) {
                $this->set_reverse_plural($value);
            }
        }

        // save the verb in the database
        if ($db_con->is_open()) {
            if ($usr_msg->is_ok()) {
                $this->save($usr_msg);
            } else {
                $lib = new library();
                $usr_msg->add_id_with_vars(msg_id::IMPORT_NOT_SAVED, [
                    msg_id::VAR_CLASS_NAME => $lib->class_to_name($this::class),
                    msg_id::VAR_ID => $this->dsp_id()
                ]);
            }
        }

        return $usr_msg->is_ok();
    }

    /**
     * create an array with the export json fields
     * @param export_type_list|array $exp_typ define the export format
     * @param bool $do_load to switch off the database load for unit tests
     * @return array the filled array used to create the user export json
     */
    function export_json(export_type_list|array $exp_typ = [], bool $do_load = true): array
    {
        $vars = parent::export_json($exp_typ, $do_load);

        if ($this->description <> '') {
            $vars[json_fields::DESCRIPTION] = $this->description;
        }
        if ($this->get_plural() <> '') {
            $vars[json_fields::NAME_PLURAL] = $this->get_plural();
        }
        if ($this->get_reverse() <> '') {
            $vars[json_fields::NAME_REVERSE] = $this->get_reverse();
        }
        if ($this->get_reverse_plural() <> '') {
            $vars[json_fields::NAME_PLURAL_REVERSE] = $this->get_reverse_plural();
        }

        // TODO add the protection type
        /*
        if ($this->protection_id > 0 and $this->protection_id <> $sys->typ_lst->ptc_typ->id(protection_type::NO_PROTECT)) {
            $vars[json_fields::PROTECTION] = $this->protection_type_code_id();
        }
        */

        return $vars;
    }


    /*
     * check
     */

    /**
     * check if this might be added to the database
     * which is for named objects without dependencies the same as db_ready
     * @param user_message $usr_msg to fill with the suggested solutions if something is missing e.g. a linked object
     * @return bool true if the verb can be added to the database
     */
    function can_be_ready(user_message $usr_msg): bool
    {
        return $this->db_ready($usr_msg);
    }

    /**
     * check if the named sandbox object can be added to the database
     * @param user_message $usr_msg empty if all vars of the verb are set and the verb can be stored in the database
     * @return bool true if the verb can be added to the database
     */
    function db_ready(user_message $usr_msg): bool
    {
        if ($this->id() == 0) {
            if ($this->name() == '') {
                $usr_msg->add_id(msg_id::ID_AND_NAME_MISSING);
            }
        }
        return $usr_msg->is_ok();
    }


    /*
     * related
     */

    /**
     * get the term corresponding to this verb name
     * so in this case, if a word or formula with the same name already exists, get it
     */
    private function reload_term(): term
    {
        $trm = new term($this->usr);
        $trm->load_by_name($this->name);
        return $trm;
    }


    /*
     * cast
     */

    /**
     * @returns term the formula object cast into a term object
     */
    function term(): term
    {
        $trm = new term($this);
        $trm->set_id_from_obj($this->id(), self::class);
        $trm->set_name($this->name);
        $trm->set_obj($this);
        return $trm;
    }


    /*
     * sandbox
     */

    /**
     * @returns bool true if no one has used this verb
     */
    private function not_used(): bool
    {
        log_debug('verb->not_used (' . $this->id() . ')');

        global $db_con;
        $result = true;

        // to review: additional check the database foreign keys
        $qp = $this->not_used_sql($db_con);
        $db_row = $db_con->get1($qp);
        $usage = $db_row[sql_db::FLD_USAGE];
        if ($usage > 0) {
            $result = false;
        }

        return $result;
    }

    // TODO to review: additional check the database foreign keys
    function not_used_sql(sql_db $db_con): sql_par
    {
        $qp = new sql_par(verb::class);

        $qp->name .= 'usage';
        $db_con->set_class(word::class);
        $db_con->set_name($qp->name);
        $db_con->set_usr($this->get_user()->id);
        $db_con->set_fields(verb_db::FLD_NAMES);
        $db_con->set_where_std($this->id());
        $qp->sql = $db_con->select_by_set_id();
        $qp->par = $db_con->get_par();

        return $qp;
    }

    // true if no other user has modified the verb
    private function not_changed(): bool
    {
        log_debug('verb->not_changed (' . $this->id() . ') by someone else than the owner (' . $this->get_user()->id . ')');

        global $db_con;
        $result = true;

        $lib = new library();
        /*
        $change_user_id = 0;
        $sql = "SELECT user_id
                  FROM user_verbs
                 WHERE verb_id = ".$this->id."
                   AND user_id <> ".$this->owner_id."
                   AND (excluded <> 1 OR excluded is NULL)";
        //$db_con = new mysql;
        $db_con->usr_id = $this->get_user()->id();
        $change_user_id = $db_con->get1($sql);
        if ($change_user_id > 0) {
          $result = false;
        }
        */

        log_debug('verb->not_changed for ' . $this->id() . ' is ' . $lib->dsp_bool($result));
        return $result;
    }

    // true if no one else has used the verb
    function can_change(): bool
    {
        log_debug('verb->can_change ' . $this->id());
        $lib = new library();
        $can_change = false;
        if ($this->usage == null or $this->usage == 0) {
            $can_change = true;
        }

        log_debug($lib->dsp_bool($can_change));
        return $can_change;
    }


    /*
     * log
     */

    // set the log entry parameter for a new verb
    private function log_add(): change
    {
        log_debug('verb->log_add ' . $this->dsp_id());
        $usr_msg = new user_message();
        $log = new change($this->usr);
        $log->set_action(change_actions::ADD);
        $log->set_table(change_tables::VERB);
        $log->set_field(verb_db::FLD_NAME);
        $log->old_value = null;
        $log->new_value = $this->name;
        $log->row_id = 0;
        $log->add($usr_msg);

        return $log;
    }

    // set the main log entry parameters for updating one verb field
    private function log_upd(): change
    {
        log_debug('verb->log_upd ' . $this->dsp_id() . ' for user ' . $this->get_user()->name);
        $log = new change($this->usr);
        $log->set_action(change_actions::UPDATE);
        $log->set_table(change_tables::VERB);

        return $log;
    }

    // set the log entry parameter to delete a verb
    private function log_del(): change
    {
        log_debug('verb->log_del ' . $this->dsp_id() . ' for user ' . $this->get_user()->name);
        $usr_msg = new user_message();
        $log = new change($this->usr);
        $log->set_action(change_actions::DELETE);
        $log->set_table(change_tables::VERB);
        $log->set_field(verb_db::FLD_NAME);
        $log->old_value = $this->name;
        $log->new_value = null;
        $log->row_id = $this->id();
        $log->add($usr_msg);

        return $log;
    }


    /*
     * save
     */

    // actually update a formula field in the main database record or the user sandbox
    private function save_field_do(sql_db $db_con, $log): user_message
    {
        $usr_msg = new user_message();

        if ($log->new_id > 0) {
            $new_value = $log->new_id;
            $std_value = $log->std_id;
        } else {
            $new_value = $log->new_value;
            $std_value = $log->std_value;
        }
        if ($log->add($usr_msg)) {
            if ($this->can_change()) {
                $db_con->set_class(verb::class);
                if (!$db_con->update_old($this->id(), $log->field(), $new_value)) {
                    $usr_msg->add_id_with_vars(msg_id::VERB_UPDATE_FAILED, [
                        msg_id::VAR_NAME => $log->field(),
                        msg_id::VAR_VALUE => $new_value,
                        msg_id::VAR_ID => $this->dsp_id()
                    ]);
                }

            } else {
                // TODO: create a new verb and request to delete the old
                log_warning('verb->save_field_do creating of a new verb not yet coded');
            }
        }
        return $usr_msg;
    }

    private function save_field_code_id(sql_db $db_con, $db_rec): user_message
    {
        $usr_msg = new user_message();
        if ($db_rec->code_id <> $this->code_id) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->code_id;
            $log->new_value = $this->code_id;
            $log->std_value = $db_rec->code_id;
            $log->row_id = $this->id();
            $log->set_field(sql_db::FLD_CODE_ID);
            $usr_msg = $this->save_field_do($db_con, $log);
        }
        return $usr_msg;
    }


    // set the update parameters for the verb name
    private function save_field_name(sql_db $db_con, $db_rec): user_message
    {
        $usr_msg = new user_message();
        if ($db_rec->name <> $this->name) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->name;
            $log->new_value = $this->name;
            $log->std_value = $db_rec->name;
            $log->row_id = $this->id();
            $log->set_field(verb_db::FLD_NAME);
            $usr_msg = $this->save_field_do($db_con, $log);
        }
        return $usr_msg;
    }

    // set the update parameters for the verb plural
    private function save_field_plural(sql_db $db_con, $db_rec): user_message
    {
        $usr_msg = new user_message();
        if ($db_rec->get_plural() <> $this->get_plural()) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->get_plural();
            $log->new_value = $this->get_plural();
            $log->std_value = $db_rec->get_plural();
            $log->row_id = $this->id();
            $log->set_field(verb_db::FLD_PLURAL);
            $usr_msg = $this->save_field_do($db_con, $log);
        }
        return $usr_msg;
    }

    // set the update parameters for the verb reverse
    private function save_field_reverse(sql_db $db_con, $db_rec): user_message
    {
        $usr_msg = new user_message();
        if ($db_rec->get_reverse() <> $this->get_reverse()) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->get_reverse();
            $log->new_value = $this->get_reverse();
            $log->std_value = $db_rec->get_reverse();
            $log->row_id = $this->id();
            $log->set_field(verb_db::FLD_REVERSE);
            $usr_msg = $this->save_field_do($db_con, $log);
        }
        return $usr_msg;
    }

    // set the update parameters for the verb rev_plural
    private function save_field_rev_plural(sql_db $db_con, $db_rec): user_message
    {
        $usr_msg = new user_message();
        if ($db_rec->get_reverse_plural() <> $this->get_reverse_plural()) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->get_reverse_plural();
            $log->new_value = $this->get_reverse_plural();
            $log->std_value = $db_rec->get_reverse_plural();
            $log->row_id = $this->id();
            $log->set_field(verb_db::FLD_PLURAL_REVERSE);
            $usr_msg = $this->save_field_do($db_con, $log);
        }
        return $usr_msg;
    }

    // set the update parameters for the verb description
    private function save_field_description(sql_db $db_con, $db_rec): user_message
    {
        $usr_msg = new user_message();
        if ($db_rec->description <> $this->description) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->description;
            $log->new_value = $this->description;
            $log->std_value = $db_rec->description;
            $log->row_id = $this->id();
            $log->set_field(sql_db::FLD_DESCRIPTION);
            $usr_msg = $this->save_field_do($db_con, $log);
        }
        return $usr_msg;
    }

    // set the update parameters for the verb description
    private function save_field_formula_name(sql_db $db_con, $db_rec): user_message
    {
        $usr_msg = new user_message();
        if ($db_rec->get_formula_name() <> $this->get_formula_name()) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->get_formula_name();
            $log->new_value = $this->get_formula_name();
            $log->std_value = $db_rec->get_formula_name();
            $log->row_id = $this->id();
            $log->set_field(verb_db::FLD_NAME_FORMULA);
            $usr_msg = $this->save_field_do($db_con, $log);
        }
        return $usr_msg;
    }

    // save all updated verb fields excluding the name, because already done when adding a verb
    private function save_fields(sql_db $db_con, $db_rec): user_message
    {
        $usr_msg = new user_message();
        $usr_msg->add($this->save_field_code_id($db_con, $db_rec));
        $usr_msg->add($this->save_field_plural($db_con, $db_rec));
        $usr_msg->add($this->save_field_reverse($db_con, $db_rec));
        $usr_msg->add($this->save_field_rev_plural($db_con, $db_rec));
        $usr_msg->add($this->save_field_description($db_con, $db_rec));
        $usr_msg->add($this->save_field_formula_name($db_con, $db_rec));
        log_debug('verb->save_fields all fields for ' . $this->dsp_id() . ' has been saved');
        return $usr_msg;
    }

    // check if the id parameters are supposed to be changed
    private function save_id_if_updated(sql_db $db_con, sandbox $db_rec, sandbox $std_rec): string
    {
        $result = '';
        /*
            TODO:
            if ($db_rec->name <> $this->name) {
              // check if target link already exists
              zu_debug('verb->save_id_if_updated check if target link already exists '.$this->dsp_id().' (has been "'.$db_rec->dsp_id().'")');
              $db_chk = clone $this;
              $db_chk->set_id(0); // to force the load by the id fields
              $db_chk->load_standard();
              if ($db_chk->id() > 0) {
                if (UI_CAN_CHANGE_VIEW_COMPONENT_NAME) {
                  // ... if yes request to delete or exclude the record with the id parameters before the change
                  $to_del = clone $db_rec;
                  $result .= $to_del->del($usr_msg);
                  // .. and use it for the update
                  $this->id = $db_chk->id();
                  $this->set_owner_id($db_chk->owner_id());
                  // force the include again
                  $this->excluded = null;
                  $db_rec->excluded = '1';
                  $this->save_field_excluded ($db_con, $db_rec, $std_rec);
                  zu_debug('verb->save_id_if_updated found a display component link with target ids "'.$db_chk->dsp_id().'", so del "'.$db_rec->dsp_id().'" and add '.$this->dsp_id());
                } else {
                  $result .= 'A view component with the name "'.$this->name.'" already exists. Please use another name.';
                }
              } else {
                if ($this->can_change() AND $this->not_used()) {
                  // in this case change is allowed and done
                  zu_debug('verb->save_id_if_updated change the existing display component link '.$this->dsp_id().' (db "'.$db_rec->dsp_id().'", standard "'.$std_rec->dsp_id().'")');
                  //$this->load_objects();
                  $result .= $this->save_id_fields($db_con, $db_rec, $std_rec);
                } else {
                  // if the target link has not yet been created
                  // ... request to delete the old
                  $to_del = clone $db_rec;
                  $result .= $to_del->del($usr_msg);
                  // .. and create a deletion request for all users ???

                  // ... and create a new display component link
                  $this->set_id(0);
                  $this->set_owner_id($this->get_user()->id());
                  $result .= $this->add($db_con);
                  zu_debug('verb->save_id_if_updated recreate the display component link del "'.$db_rec->dsp_id().'" add '.$this->dsp_id().' (standard "'.$std_rec->dsp_id().'")');
                }
              }
            }
        */
        log_debug('verb->save_id_if_updated for ' . $this->dsp_id() . ' has been done');
        return $result;
    }

    /**
     * create a new verb
     */
    private function add(sql_db $db_con): user_message
    {
        log_debug('verb->add the verb ' . $this->dsp_id());

        $usr_msg = new user_message();

        // log the insert attempt first
        $log = $this->log_add();
        if ($log->id() > 0) {
            // insert the new verb
            $db_con->set_class(verb::class);
            $this->id = $db_con->insert_old(verb_db::FLD_NAME, $this->name);
            if ($this->id() > 0) {
                // update the id in the log
                if (!$log->add_ref($this->id())) {
                    $usr_msg->add_id(msg_id::FAILED_UPDATE_REF);
                    // TODO do rollback or retry?
                } else {

                    // create an empty db_rec element to force saving of all set fields
                    $db_rec = new verb;
                    $db_rec->name = $this->name;
                    $db_rec->usr = $this->usr;
                    // save the verb fields
                    $usr_msg->add($this->save_fields($db_con, $db_rec));
                }

            } else {
                $usr_msg->add_id_with_vars(msg_id::VERB_ADD_FAILED, [msg_id::VAR_NAME => $this->name]);
            }
        }

        return $usr_msg;
    }

    /**
     * check if the user has requested a verb with a preserved name
     * and if yes return a message to the user
     *
     * @param user_message $usr_msg the message object that is enriched in case something went wrong to show the user the problem and the suggested solutions
     * @return bool true if everything has been fine
     */
    protected function check_preserved(user_message $usr_msg): bool
    {
        global $usr;

        // init
        $lib = new library();
        $class_name = $lib->class_to_name($this::class);

        if (!$usr->is_system()) {
            if (in_array($this->name, verbs::RESERVED_WORDS)) {
                // the admin user needs to add the read test word during initial load
                if (!$usr->is_admin()) {
                    $usr_msg->add_id_with_vars(msg_id::NAME_IS_RESERVED_FOR_CLASS, [
                        msg_id::VAR_NAME => $this->name(),
                        msg_id::VAR_CLASS_NAME => $class_name
                    ]);
                }
            }
        }
        return $usr_msg->is_ok();
    }

    /**
     * TODO return a user message object, so that messages to the user like "use another name" does not case a error log entry
     * add or update a verb in the database (or create a user verb if the program settings allow this)
     *
     * @param user_message $usr_msg the message object that is enriched in case something went wrong to show the user the problem and the suggested solutions
     * @param bool|null $use_func if true a predefined function is used that also creates the log entries
     * @return bool true if everything has been fine
     */
    function save(user_message $usr_msg, ?bool $use_func = null): bool
    {
        log_debug($this->dsp_id());

        global $db_con;

        // check the preserved names
        $this->check_preserved($usr_msg);

        // build the database object because the is anyway needed
        $db_con->set_usr($usr_msg->usr->id);
        $db_con->set_class(verb::class);

        // check if a new verb is supposed to be added
        if ($this->id() <= 0) {
            // check if a word, triple or formula with the same name is already in the database
            $this->set_user($usr_msg->usr);
            $trm = $this->reload_term();
            if ($trm->id_obj() > 0 and $trm->type() <> verb::class) {
                $usr_msg->add($trm->id_used_msg($this));
            } else {
                $this->id = $trm->id_obj();
                log_debug('verb->save adding verb name ' . $this->dsp_id() . ' is OK');
            }
        }

        // create a new verb or update an existing
        if ($usr_msg->is_ok()) {
            if ($this->id() <= 0) {
                $usr_msg->add($this->add($db_con));
            } else {
                log_debug('update "' . $this->id() . '"');
                // read the database values to be able to check if something has been changed; done first,
                // because it needs to be done for user and general formulas
                $db_rec = new verb;
                $db_rec->usr = $this->usr;
                $db_rec->load_by_id($this->id());
                log_debug("database verb loaded (" . $db_rec->name . ")");

                // if the name has changed, check if verb, verb or formula with the same name already exists; this should have been checked by the calling function, so display the error message directly if it happens
                if ($db_rec->name <> $this->name) {
                    // check if a verb, formula or verb with the same name is already in the database
                    $trm = $this->reload_term();
                    if ($trm->id_obj() > 0 and $trm->type() <> verb::class) {
                        $usr_msg->add($trm->id_used_msg($this));
                    } else {
                        if ($this->can_change()) {
                            $usr_msg->add($this->save_field_name($db_con, $db_rec));
                        } else {
                            // TODO: create a new verb and request to delete the old
                            log_err('Creating a new verb is not yet possible');
                        }
                    }
                }

                if ($db_rec->code_id <> $this->code_id) {
                    $usr_msg->add($this->save_field_code_id($db_con, $db_rec));
                }

                // if a problem has appeared up to here, don't try to save the values
                // the problem is shown to the user by the calling interactive script
                if ($usr_msg->is_ok()) {
                    $usr_msg->add($this->save_fields($db_con, $db_rec));
                }
            }
        }

        // TODO log internal errors as errors but user warnings as info
        if (!$usr_msg->is_ok()) {
            log_info($usr_msg->get_last_message());
        }

        return $usr_msg->is_ok();
    }


    /*
     * del
     */

    /**
     * exclude or delete a verb
     * @param user_message $usr_msg the message that should be shown to the user if something went wrong or an empty string if everything is fine
     * @return bool true if everything has been fine
     */
    function del(user_message $usr_msg): bool
    {
        log_debug('verb->del');

        global $db_con;

        // reload only if needed
        if ($this->name == '') {
            if ($this->id() > 0) {
                $this->load_by_id($this->id());
            } else {
                log_err('Cannot delete verb, because neither the id or name is given');
            }
        } else {
            if ($this->id() == 0) {
                $this->load_by_name($this->name);
            }
        }

        if ($this->id() > 0) {
            log_debug('verb->del ' . $this->dsp_id());
            if ($this->can_change()) {
                $log = $this->log_del();
                if ($log->id() > 0) {
                    $db_con->usr_id = $this->get_user()->id();
                    $db_con->set_class(verb::class);
                    $usr_msg->add_message_text($db_con->delete_old(verb_db::FLD_ID, $this->id()));
                }
            } else {
                // TODO: create a new verb and request to delete the old
                log_err('Creating a new verb is not yet possible');
            }
        }

        return $usr_msg->is_ok();
    }


    /*
     * sql write
     */

    /**
     * create the sql statement to add a verb to the database
     * always all fields are included in the query to be able to remove overwrites with a null value
     *
     * @param sql_creator $sc with the target db_type set
     * @param user_message $usr_msg collect the messages for the user
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement and the parameter list
     */
    function sql_insert(
        sql_creator   $sc,
        user_message  $usr_msg,
        sql_type_list $sc_par_lst = new sql_type_list()
    ): sql_par
    {
        global $sys;

        // clone the sql parameter list to avoid changing the given list
        $sc_par_lst_used = clone $sc_par_lst;
        // set the sql query type
        $sc_par_lst_used->add(sql_type::INSERT);
        // get the fields and values that are filled and should be written to the db
        $vrb_empty = new verb();
        $vrb_empty->usr = $this->usr;
        $fvt_lst = $this->db_fields_changed($vrb_empty, $usr_msg, $sc_par_lst_used);
        // get the list of all fields that can be changed by the user
        $fld_lst_all = $this->db_fields_all();
        // TODO Prio 1 move the line from here to the end to a sql_write function and move it to the parent object
        // make the query name unique based on the changed fields
        $lib = new library();
        $ext = sql::NAME_SEP . $lib->sql_field_ext($fvt_lst, $fld_lst_all, $usr_msg);
        // create the main query parameter object and set the query name
        $qp = $this->sql_common($sc, $sc_par_lst_used, $ext);
        // log functions must always use named parameters
        $sc_par_lst_used->add(sql_type::NAMED_PAR);
        // list of parameters actually used in order of the function usage
        $par_lst_out = new sql_par_field_list();
        // set some var names to shorten the code lines
        $id_field = $sc->id_field_name();
        $var_name_row_id = $sc->var_name_row_id($sc_par_lst_used);
        // add the change action field to the field list for the log entries
        $fvt_lst->add_field(
            change_action::FLD_ID,
            $sys->typ_lst->cng_act->id(change_actions::ADD),
            type_object::FLD_ID_SQL_TYP
        );
        // init the function body
        $id_fld_new = $sc->var_name_new_id($sc_par_lst_used);
        $sql = $sc->sql_func_start($id_fld_new, $sc_par_lst_used);
        // don't use the log parameter for the sub queries
        $sc_par_lst_sub = $sc_par_lst_used->remove(sql_type::LOG);
        $sc_par_lst_sub->add(sql_type::LIST);
        $sc_par_lst_log = clone $sc_par_lst_sub;
        $sc_par_lst_log->add(sql_type::INSERT_PART);
        // create sql to set the prime key upfront to get the sequence id
        $qp_id = clone $qp;
        $qp_id = $this->sql_insert_key_field($sc, $qp_id, $fvt_lst, $id_fld_new, $usr_msg, $sc_par_lst_sub);
        $par_lst_out->add($qp_id->par_fld);
        $sql .= $qp_id->sql;
        // get the data fields and move the unique db key field to the first entry
        $fld_lst_log = array_intersect($fvt_lst->names(), $fld_lst_all);
        $key_fld_pos = array_search($this->id_field(), $fld_lst_log);
        unset($fld_lst_log[$key_fld_pos]);

        // add the user to the field list so that the id can be used for the log
        $fvt_lst->add_field(
            user_db::FLD_ID,
            $this->get_user()->id(),
            db_object_seq_id::FLD_ID_SQL_TYP
        );

        // create the query parameters for the log entries for the single fields
        $qp_log = $sc->sql_func_log($this::class, $this->get_user(), $fld_lst_log, $fvt_lst, $usr_msg, $sc_par_lst_log);
        $sql .= ' ' . $qp_log->sql;
        $par_lst_out->add_list($qp_log->par_fld_lst);

        // update the fields excluding the unique id
        $fld_lst_chg = $fld_lst_log;
        $key_fld_pos = array_search($this->name_field(), $fld_lst_chg);
        unset($fld_lst_chg[$key_fld_pos]);
        $update_fvt_lst = new sql_par_field_list();
        foreach ($fld_lst_chg as $fld) {
            $update_fvt_lst->add($fvt_lst->get($fld, $usr_msg));
        }
        $sc_update = clone $sc;
        $sc_par_lst_upd = $sc_par_lst_used;
        $sc_par_lst_upd->add(sql_type::UPDATE);
        $sc_par_lst_upd_ex_log = $sc_par_lst_upd->remove(sql_type::LOG);
        $sc_par_lst_upd_ex_log->add(sql_type::SUB);
        $qp_update = $this->sql_common($sc_update, $sc_par_lst_upd_ex_log);

        $qp_update->sql = $sc_update->create_sql_update(
            $id_field, $var_name_row_id, $update_fvt_lst, [], $sc_par_lst_upd_ex_log);
        // add the insert row to the function body
        $sql .= ' ' . $qp_update->sql . ' ';

        if ($sc->db_type == sql_db::POSTGRES) {
            if ($id_fld_new != '') {
                $sql .= sql::RETURN . ' ' . $id_fld_new . '; ';
            }
        }

        // create the query parameters for the actual change
        $qp_chg = clone $qp;

        $sql .= $sc->sql_func_end();

        $qp_chg->sql = $sc->create_sql_insert($par_lst_out, $sc_par_lst_used);

        // merge all together and create the function
        $qp->sql = $qp_chg->sql . $sql . ';';
        $qp->par = $par_lst_out->values();

        // create the call sql statement
        return $sc->sql_call($qp, $qp_chg->name, $par_lst_out);
    }

    /**
     * create the sql statement to add a new named sandbox object e.g. word to the database
     * TODO add qp merge
     *
     * @param sql_creator $sc with the target db_type set
     * @param sql_par $qp
     * @param sql_par_field_list $fvt_lst list of field names, values and sql types additional to the standard id and name fields
     * @param string $id_fld_new
     * @param user_message $usr_msg collect the messages for the user
     * @param sql_type_list $sc_par_lst_sub the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement and the parameter list
     */
    function sql_insert_key_field(
        sql_creator        $sc,
        sql_par            $qp,
        sql_par_field_list $fvt_lst,
        string             $id_fld_new,
        user_message       $usr_msg,
        sql_type_list      $sc_par_lst_sub = new sql_type_list()
    ): sql_par
    {
        // set some var names to shorten the code lines
        $usr_tbl = $sc_par_lst_sub->is_usr_tbl();
        $ext = sql::NAME_SEP . sql_creator::FILE_INSERT;

        // list of parameters actually used in order of the function usage
        $sql = '';
        $fvt_insert = $fvt_lst->get($this->name_field(), $usr_msg);

        // create the sql to insert the row
        $fvt_insert_list = new sql_par_field_list();
        $fvt_insert_list->add($fvt_insert);
        $sc_insert = clone $sc;
        $qp_insert = $this->sql_common($sc_insert, $sc_par_lst_sub, $ext);
        $sc_par_lst_sub->add(sql_type::SELECT_FOR_INSERT);
        if ($sc->db_type == sql_db::MYSQL) {
            $sc_par_lst_sub->add(sql_type::NO_ID_RETURN);
        }
        $qp_insert->sql = $sc_insert->create_sql_insert(
            $fvt_insert_list, $sc_par_lst_sub, true, '', '', '', $id_fld_new);
        $qp_insert->par = [$fvt_insert->value];

        // add the insert row to the function body
        $sql .= ' ' . $qp_insert->sql . '; ';

        // get the new row id for MySQL db
        if ($sc->db_type == sql_db::MYSQL and !$usr_tbl) {
            $sql .= ' ' . sql::LAST_ID_MYSQL . $sc->var_name_row_id($sc_par_lst_sub) . '; ';
        }

        $qp->sql = $sql;
        $qp->par_fld = $fvt_insert;

        return $qp;
    }


    /*
     * sql write fields
     */

    /**
     * TODO Prio 2 maybe move to type object to allow at least admin users to add a type
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
                verb_db::FLD_PLURAL,
                verb_db::FLD_REVERSE,
                verb_db::FLD_PLURAL_REVERSE,
                sql_db::FLD_USAGE,
                sql_db::FLD_IMPACT
            ]
        );
    }

    /**
     * get a list of database field names, values and types that have been updated
     *
     * @param verb|type_object $typ the compare value to detect the changed fields
     * @param user_message $usr_msg the user message object that collects any issues during the sql creation
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list list 3 entry arrays with the database field name, the value and the sql type that have been updated
     */
    function db_fields_changed(
        verb|type_object $typ,
        user_message     $usr_msg,
        sql_type_list    $sc_par_lst = new sql_type_list()
    ): sql_par_field_list
    {
        global $sys;

        $sc = new sql_creator();
        $do_log = $sc_par_lst->incl_log();
        $table_id = $sc->table_id($this::class);

        $lst = parent::db_fields_changed($typ, $usr_msg, $sc_par_lst);
        // TODO move to language forms
        if ($typ->plural !== $this->plural) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . verb_db::FLD_PLURAL,
                    $sys->typ_lst->cng_fld->id($table_id . verb_db::FLD_PLURAL),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                verb_db::FLD_PLURAL,
                $this->plural,
                sql_field_type::NAME,
                $typ->plural
            );
        }
        if ($typ->reverse !== $this->reverse) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . verb_db::FLD_REVERSE,
                    $sys->typ_lst->cng_fld->id($table_id . verb_db::FLD_REVERSE),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                verb_db::FLD_REVERSE,
                $this->reverse,
                sql_field_type::NAME,
                $typ->reverse
            );
        }
        if ($typ->rev_plural !== $this->rev_plural) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . verb_db::FLD_PLURAL_REVERSE,
                    $sys->typ_lst->cng_fld->id($table_id . verb_db::FLD_PLURAL_REVERSE),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                verb_db::FLD_PLURAL_REVERSE,
                $this->rev_plural,
                sql_field_type::NAME,
                $typ->rev_plural
            );
        }
        if ($typ->usage !== $this->usage) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . sql_db::FLD_USAGE,
                    $sys->typ_lst->cng_fld->id($table_id . sql_db::FLD_USAGE),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                sql_db::FLD_USAGE,
                $this->usage,
                sql_db::FLD_USAGE_SQL_TYP,
                $typ->usage
            );
        }
        if ($typ->impact !== $this->impact) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . sql_db::FLD_IMPACT,
                    $sys->typ_lst->cng_fld->id($table_id . sql_db::FLD_IMPACT),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                sql_db::FLD_IMPACT,
                $this->impact,
                sql_db::FLD_IMPACT_SQL_TYP,
                $typ->impact
            );
        }
        return $lst;
    }


    /*
     * sql fields
     */

    private function id_field_typ(string $class): string
    {
        return verb_db::FLD_ID;
    }

    function name_field(): string
    {
        return verb_db::FLD_NAME;
    }

    function all_fields(): array
    {
        return verb_db::FLD_NAMES;
    }


    /*
     * debug
     */

    /**
     * @return string display the unique id fields (used also for debugging)
     */
    function dsp_id(): string
    {
        global $debug;
        $result = parent::dsp_id();
        if ($debug > def::DEBUG_SHOW_USER or $debug == 0) {
            if ($this->get_user() != null) {
                $result .= ' for user ' . $this->get_user()->id . ' (' . $this->get_user()->name . ')';
            }
        }
        return $result;
    }

    function name(): string
    {
        return $this->name;
    }

}
