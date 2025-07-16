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

namespace cfg\verb;

include_once MODEL_HELPER_PATH . 'type_object.php';
include_once SHARED_ENUM_PATH . 'messages.php';
include_once DB_PATH . 'sql.php';
include_once DB_PATH . 'sql_creator.php';
include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_par.php';
include_once DB_PATH . 'sql_par_type.php';
include_once MODEL_HELPER_PATH . 'data_object.php';
include_once MODEL_HELPER_PATH . 'db_object.php';
include_once MODEL_LOG_PATH . 'change.php';
include_once MODEL_LOG_PATH . 'change_action.php';
//include_once MODEL_LOG_PATH . 'change_table_list.php';
include_once MODEL_LOG_PATH . 'changes_norm.php';
include_once MODEL_LOG_PATH . 'changes_big.php';
//include_once MODEL_PHRASE_PATH . 'term.php';
include_once MODEL_SANDBOX_PATH . 'sandbox.php';
include_once MODEL_SANDBOX_PATH . 'sandbox_named.php';
include_once MODEL_USER_PATH . 'user.php';
include_once MODEL_USER_PATH . 'user_message.php';
//include_once MODEL_WORD_PATH . 'word.php';
include_once SHARED_ENUM_PATH . 'change_actions.php';
include_once SHARED_ENUM_PATH . 'change_tables.php';
include_once SHARED_TYPES_PATH . 'api_type_list.php';
include_once SHARED_TYPES_PATH . 'verbs.php';
include_once SHARED_PATH . 'json_fields.php';
include_once SHARED_PATH . 'library.php';

use cfg\db\sql;
use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\db\sql_par;
use cfg\db\sql_par_type;
use cfg\helper\data_object;
use cfg\helper\type_object;
use cfg\log\change;
use cfg\phrase\term;
use cfg\sandbox\sandbox;
use cfg\sandbox\sandbox_named;
use cfg\user\user;
use cfg\user\user_message;
use cfg\word\word;
use shared\enum\change_actions;
use shared\enum\change_tables;
use shared\enum\messages as msg_id;
use shared\json_fields;
use shared\library;
use shared\types\api_type_list;
use shared\types\verbs;

class verb extends type_object
{

    // TODO add an easy way to get the name from the code id


    /*
     * database link
     */

    // object specific database and JSON object field names and comments
    const TBL_COMMENT = 'for verbs / triple predicates to use predefined behavior';

    // forward the const to enable usage of $this::CONST_NAME
    const FLD_ID = verb_db::FLD_ID;
    const FLD_NAMES = verb_db::FLD_NAMES;
    const FLD_LST_NAME = verb_db::FLD_LST_NAME;
    const FLD_LST_ALL = verb_db::FLD_LST_ALL;


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
    private int $usage = 0;


    /*
     * construct and map
     */

    function __construct(int $id = 0, string $name = '', ?string $code_id = null)
    {
        parent::__construct($code_id);
        if ($id > 0) {
            $this->set_id($id);
        }
        if ($name != '') {
            $this->set_name($name);
        }
        if ($code_id != '') {
            $this->code_id = $code_id;
        }
    }

    function reset(): void
    {
        parent::reset();
        $this->set_user(null);
        $this->plural = null;
        $this->reverse = null;
        $this->rev_plural = null;
        $this->frm_name = null;
        $this->usage = 0;
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
            if (array_key_exists(sql::FLD_CODE_ID, $db_row)) {
                if ($db_row[sql::FLD_CODE_ID] != null) {
                    $this->set_code_id_db($db_row[sql::FLD_CODE_ID]);
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
            if (array_key_exists(verb_db::FLD_FORMULA, $db_row)) {
                $this->set_formula_name($db_row[verb_db::FLD_FORMULA]);
            }
            if (array_key_exists(sandbox_named::FLD_DESCRIPTION, $db_row)) {
                $this->description = $db_row[sandbox_named::FLD_DESCRIPTION];
            }
            if (array_key_exists(verb_db::FLD_WORDS, $db_row)) {
                if ($db_row[verb_db::FLD_WORDS] == null) {
                    $this->usage = 0;
                } else {
                    $this->usage = $db_row[verb_db::FLD_WORDS];
                }
            }
        }
        return $result;
    }

    /**
     * map a verb api json to this model verb object
     * @param array $api_json the api array with the word values that should be mapped
     * @return user_message the message for the user why the action has failed and a suggested solution
     */
    function api_mapper(array $api_json): user_message
    {
        $usr_msg = parent::api_mapper($api_json);

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

        // the usage var is not expected to be changed via api

        return $usr_msg;
    }

    /**
     * function to import the core user sandbox object values from a json string
     * e.g. the share and protection settings
     *
     * @param array $in_ex_json an array with the data of the json object
     * @param user $usr_req the user who has initiated the import mainly used to add tge code id to the database
     * @param data_object|null $dto cache of the objects imported until now for the primary references
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_mapper_user(
        array       $in_ex_json,
        user        $usr_req,
        data_object $dto = null,
        object      $test_obj = null
    ): user_message
    {
        $usr_msg = parent::import_mapper($in_ex_json, $dto, $test_obj);

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
                $this->set_code_id($in_ex_json[json_fields::CODE_ID], $usr_req);
            }
        }

        return $usr_msg;
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
        $this->set_id($id);
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

    /**
     * set the value to rank the verbs by usage
     *
     * @param int $usage a higher value moves the verb to the top of the selection list
     * @return void
     */
    function set_usage(int $usage): void
    {
        //$this->values = $usage;
    }

    function set_plural(?string $plural): void
    {
        $this->plural = $plural;
    }

    function plural(): ?string
    {
        return $this->plural;
    }

    function set_reverse(?string $reverse): void
    {
        $this->reverse = $reverse;
    }

    function reverse(): ?string
    {
        return $this->reverse;
    }

    function set_reverse_plural(?string $reverse_plural): void
    {
        $this->rev_plural = $reverse_plural;
    }

    function reverse_plural(): ?string
    {
        return $this->rev_plural;
    }

    function set_formula_name(?string $formula_name): void
    {
        $this->frm_name = $formula_name;
    }

    function formula_name(): ?string
    {
        return $this->frm_name;
    }

    /**
     * @return string a unique name for the verb that is also used in the code
     */
    function code_id(): string
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
    function description(): string
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
    function user(): ?user
    {
        return $this->usr;
    }

    /**
     * @return int a higher number indicates a higher usage
     */
    function usage(): int
    {
        return 0;
    }


    /*
     * load
     */

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
        $sc->add_where(verb_db::FLD_FORMULA, $name, sql_par_type::TEXT_OR);
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
        $sc->add_where(sql::FLD_CODE_ID, $code_id);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
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
        $vars[json_fields::CODE_ID] = $this->code_id();
        $vars[json_fields::DESCRIPTION] = $this->description();
        $vars[json_fields::PLURAL] = $this->plural();
        $vars[json_fields::REVERSE] = $this->reverse();
        $vars[json_fields::REV_PLURAL] = $this->reverse_plural();
        $vars[json_fields::FRM_NAME] = $this->formula_name();
        $vars[json_fields::USAGE] = $this->usage();
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
     * @param array $json_obj an array with the data of the json object
     * @param user $usr_req the user how has initiated the import mainly used to prevent any user to gain additional rights
     * @param data_object|null $dto cache of the objects imported until now for the primary references
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_obj(
        array        $json_obj,
        user         $usr_req,
        ?data_object $dto = null,
        object       $test_obj = null
    ): user_message
    {
        global $vrb_cac;

        log_debug();
        $usr_msg = parent::import_db_obj($this, $test_obj);

        // reset all parameters of this verb object but keep the user
        $usr = $this->usr;
        $this->reset();
        $this->set_user($usr);
        foreach ($json_obj as $key => $value) {
            if ($key == json_fields::NAME) {
                $this->name = $value;
            }
            if ($key == json_fields::CODE_ID) {
                if ($value != '') {
                    if ($this->user()->is_admin() or $this->user()->is_system()) {
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
            if ($key == verb_db::FLD_FORMULA) {
                $this->set_formula_name($value);
            }
            if ($key == verb_db::FLD_PLURAL_REVERSE) {
                $this->set_reverse_plural($value);
            }
        }

        // save the verb in the database
        if (!$test_obj) {
            if ($usr_msg->is_ok()) {
                $usr_msg->add($this->save());
            }
        }

        return $usr_msg;
    }

    /**
     * create an array with the export json fields
     * @param bool $do_load to switch off the database load for unit tests
     * @return array the filled array used to create the user export json
     */
    function export_json(bool $do_load = true): array
    {
        $vars = parent::export_json($do_load);

        if ($this->description <> '') {
            $vars[json_fields::DESCRIPTION] = $this->description;
        }
        if ($this->plural() <> '') {
            $vars[json_fields::NAME_PLURAL] = $this->plural();
        }
        if ($this->reverse() <> '') {
            $vars[json_fields::NAME_REVERSE] = $this->reverse();
        }
        if ($this->reverse_plural() <> '') {
            $vars[json_fields::NAME_PLURAL_REVERSE] = $this->reverse_plural();
        }

        // TODO add the protection type
        /*
        if ($this->protection_id > 0 and $this->protection_id <> $ptc_typ_cac->id(protection_type::NO_PROTECT)) {
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
     * @return user_message including suggested solutions
     *       if something is missing e.g. a linked object
     */
    function can_be_ready(): user_message
    {
        return $this->db_ready();
    }

    /**
     * @return user_message empty if all vars of the phrase are set and the phrase can be stored in the database
     */
    function db_ready(): user_message
    {
        return new user_message();
    }


    /*
     * convert functions
     */

    /**
     * get the term corresponding to this verb name
     * so in this case, if a word or formula with the same name already exists, get it
     */
    private function get_term(): term
    {
        $trm = new term($this->usr);
        $trm->load_by_name($this->name);
        return $trm;
    }

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
     * save
     */

    // TODO to review: additional check the database foreign keys
    function not_used_sql(sql_db $db_con): sql_par
    {
        $qp = new sql_par(verb::class);

        $qp->name .= 'usage';
        $db_con->set_class(word::class);
        $db_con->set_name($qp->name);
        $db_con->set_usr($this->user()->id());
        $db_con->set_fields(verb_db::FLD_NAMES);
        $db_con->set_where_std($this->id());
        $qp->sql = $db_con->select_by_set_id();
        $qp->par = $db_con->get_par();

        return $qp;
    }

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
        $used_by_words = $db_row[verb_db::FLD_WORDS];
        if ($used_by_words > 0) {
            $result = false;
        }

        return $result;
    }

    // true if no other user has modified the verb
    private function not_changed(): bool
    {
        log_debug('verb->not_changed (' . $this->id() . ') by someone else than the owner (' . $this->user()->id() . ')');

        global $db_con;
        $result = true;

        /*
        $change_user_id = 0;
        $sql = "SELECT user_id
                  FROM user_verbs
                 WHERE verb_id = ".$this->id."
                   AND user_id <> ".$this->owner_id."
                   AND (excluded <> 1 OR excluded is NULL)";
        //$db_con = new mysql;
        $db_con->usr_id = $this->user()->id();
        $change_user_id = $db_con->get1($sql);
        if ($change_user_id > 0) {
          $result = false;
        }
        */

        log_debug('verb->not_changed for ' . $this->id() . ' is ' . zu_dsp_bool($result));
        return $result;
    }

    // true if no one else has used the verb
    function can_change(): bool
    {
        log_debug('verb->can_change ' . $this->id());
        $can_change = false;
        if ($this->usage == 0) {
            $can_change = true;
        }

        log_debug(zu_dsp_bool($can_change));
        return $can_change;
    }

    // set the log entry parameter for a new verb
    private function log_add(): change
    {
        log_debug('verb->log_add ' . $this->dsp_id());
        $log = new change($this->usr);
        $log->set_action(change_actions::ADD);
        $log->set_table(change_tables::VERB);
        $log->set_field(verb_db::FLD_NAME);
        $log->old_value = null;
        $log->new_value = $this->name;
        $log->row_id = 0;
        $log->add();

        return $log;
    }

    // set the main log entry parameters for updating one verb field
    private function log_upd(): change
    {
        log_debug('verb->log_upd ' . $this->dsp_id() . ' for user ' . $this->user()->name);
        $log = new change($this->usr);
        $log->set_action(change_actions::UPDATE);
        $log->set_table(change_tables::VERB);

        return $log;
    }

    // set the log entry parameter to delete a verb
    private function log_del(): change
    {
        log_debug('verb->log_del ' . $this->dsp_id() . ' for user ' . $this->user()->name);
        $log = new change($this->usr);
        $log->set_action(change_actions::DELETE);
        $log->set_table(change_tables::VERB);
        $log->set_field(verb_db::FLD_NAME);
        $log->old_value = $this->name;
        $log->new_value = null;
        $log->row_id = $this->id();
        $log->add();

        return $log;
    }

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
        if ($log->add()) {
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
            $log->set_field(sql::FLD_CODE_ID);
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
        if ($db_rec->plural() <> $this->plural()) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->plural();
            $log->new_value = $this->plural();
            $log->std_value = $db_rec->plural();
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
        if ($db_rec->reverse() <> $this->reverse()) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->reverse();
            $log->new_value = $this->reverse();
            $log->std_value = $db_rec->reverse();
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
        if ($db_rec->reverse_plural() <> $this->reverse_plural()) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->reverse_plural();
            $log->new_value = $this->reverse_plural();
            $log->std_value = $db_rec->reverse_plural();
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
            $log->set_field(sandbox_named::FLD_DESCRIPTION);
            $usr_msg = $this->save_field_do($db_con, $log);
        }
        return $usr_msg;
    }

    // set the update parameters for the verb description
    private function save_field_formula_name(sql_db $db_con, $db_rec): user_message
    {
        $usr_msg = new user_message();
        if ($db_rec->formula_name() <> $this->formula_name()) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->formula_name();
            $log->new_value = $this->formula_name();
            $log->std_value = $db_rec->formula_name();
            $log->row_id = $this->id();
            $log->set_field(verb_db::FLD_FORMULA);
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
                  $result .= $to_del->del();
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
                  $result .= $to_del->del();
                  // .. and create a deletion request for all users ???

                  // ... and create a new display component link
                  $this->set_id(0);
                  $this->set_owner_id($this->user()->id());
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
            $this->set_id($db_con->insert_old(verb_db::FLD_NAME, $this->name));
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
     * @return user_message
     */
    protected function check_preserved(): user_message
    {
        global $usr;
        global $mtr;

        // init
        $usr_msg = new user_message();
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
        return $usr_msg;
    }

    /**
     * TODO return a user message object, so that messages to the user like "use another name" does not case a error log entry
     * add or update a verb in the database (or create a user verb if the program settings allow this)
     *
     */
    function save(): user_message
    {
        log_debug($this->dsp_id());

        global $db_con;

        // check the preserved names
        $usr_msg = $this->check_preserved();

        // build the database object because the is anyway needed
        $db_con->set_usr($this->user()->id());
        $db_con->set_class(verb::class);

        // check if a new verb is supposed to be added
        if ($this->id() <= 0) {
            // check if a word, triple or formula with the same name is already in the database
            $trm = $this->get_term();
            if ($trm->id_obj() > 0 and $trm->type() <> verb::class) {
                $usr_msg->add($trm->id_used_msg($this));
            } else {
                $this->set_id($trm->id_obj());
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
                    $trm = $this->get_term();
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

        return $usr_msg;
    }

    /**
     * exclude or delete a verb
     * @returns string the message that should be shown to the user if something went wrong or an empty string if everything is fine
     */
    function del(): string
    {
        log_debug('verb->del');

        global $db_con;
        $result = '';

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
                    $db_con->usr_id = $this->user()->id();
                    $db_con->set_class(verb::class);
                    $result = $db_con->delete_old(verb_db::FLD_ID, $this->id());
                }
            } else {
                // TODO: create a new verb and request to delete the old
                log_err('Creating a new verb is not yet possible');
            }
        }

        return $result;
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
        if ($debug > DEBUG_SHOW_USER or $debug == 0) {
            if ($this->user() != null) {
                $result .= ' for user ' . $this->user()->id() . ' (' . $this->user()->name . ')';
            }
        }
        return $result;
    }


    /*
     * display
     */

    function name(): string
    {
        return $this->name;
    }

    // create the HTML code to display the formula name with the HTML link
    function display(?string $back = ''): string
    {
        return '<a href="/http/verb_edit.php?id=' . $this->id() . '&back=' . $back . '">' . $this->name . '</a>';
    }

    // returns the html code to select a verb link type
    // database link must be open
    function dsp_selector($side, $form, $class, $back): string
    {
        global $html_verbs;

        $result = "Verb:";
        $result .= $html_verbs->selector('verb', $form, $this->id(), $class);

        log_debug('admin id ' . $this->id());
        if ($this->user() != null) {
            if ($this->user()->is_admin()) {
                // admin users should always have the possibility to create a new verb / link type
                $result .= \html\btn_add('add new verb', '/http/verb_add.php?back=' . $back);
            }
        }

        log_debug('done verb id ' . $this->id());
        return $result;
    }

}
