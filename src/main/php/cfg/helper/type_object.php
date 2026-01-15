<?php

/*

    model/helper/type_object.php - the superclass for word, formula and view types
    ----------------------------

    a base type object that can be used to link program code to single objects
    e.g. if a value is classified by a phrase of type percent the value by default is formatted in percent

    types are used to assign coded functionality to a word, formula or view
    a user can create a new type to group words, formulas or views and request new functionality for the group
    types can be renamed by a user and the user change the comment
    it should be possible to translate types on the fly
    on each program start the types are loaded once into an array, because they are not supposed to change during execution


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

namespace Zukunft\ZukunftCom\main\php\cfg\helper;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_HELPER . 'db_object_seq_id.php';
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
// TODO avoid include loops
//include_once paths::EXPORT . 'export_type_list.php';
//include_once paths::MODEL_LANGUAGE . 'language.php';
//include_once paths::MODEL_LANGUAGE . 'language_form.php';
//include_once paths::MODEL_LOG . 'change.php';
//include_once paths::MODEL_LOG . 'change_action.php';
//include_once paths::MODEL_LOG . 'change_table.php';
//include_once paths::MODEL_LOG . 'change_table_field.php';
//include_once paths::MODEL_SANDBOX . 'sandbox_named.php';
//include_once paths::MODEL_SYSTEM . 'pod.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_db.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_ENUM . 'change_actions.php';
include_once paths::SHARED_ENUM . 'messages.php';
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
use Zukunft\ZukunftCom\main\php\cfg\language\language;
use Zukunft\ZukunftCom\main\php\cfg\language\language_form;
use Zukunft\ZukunftCom\main\php\cfg\log\change;
use Zukunft\ZukunftCom\main\php\cfg\log\change_action;
use Zukunft\ZukunftCom\main\php\cfg\log\change_table;
use Zukunft\ZukunftCom\main\php\cfg\log\change_table_field;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_named;
use Zukunft\ZukunftCom\main\php\cfg\system\pod;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_db;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\enum\change_actions;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;

class type_object extends db_object_seq_id
{

    /*
     * database link
     */

    // comments used for the database creation
    // *_SQL_TYP is the sql data type used for the field
    const string TBL_COMMENT = 'for a type to set the predefined behaviour of an object';

    // database and JSON object field names
    const string FLD_ID_COM = 'the database id is also used as the array pointer';
    const sql_field_type FLD_ID_SQL_TYP = sql_field_type::INT_SMALL;
    const string FLD_NAME_COM = 'the unique type name as shown to the user and used for the selection';
    const string FLD_NAME = 'type_name';
    const string FLD_CODE_ID_COM = 'this id text is unique for all code links, is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration';
    const string FLD_DESCRIPTION_COM = 'text to explain the type to the user as a tooltip; to be replaced by a language form entry';

    // type name exceptions
    const string FLD_ACTION = 'change_action_name';
    const string FLD_TABLE = 'change_table_name';
    const string FLD_FIELD = 'change_table_field_name';

    // all database field names excluding the id used to identify if there are some user specific changes
    const array FLD_NAMES = array(
        sql_db::FLD_CODE_ID,
        sql_db::FLD_DESCRIPTION
    );

    // field lists for the table creation
    const array FLD_LST_NAME = array(
        [self::FLD_NAME, sql_field_type::NAME_UNIQUE, sql_field_default::NOT_NULL, sql::INDEX, '', self::FLD_NAME_COM],
    );
    const array FLD_LST_ALL = array(
        [sql_db::FLD_CODE_ID, sql_field_type::NAME_UNIQUE, sql_field_default::NULL, '', '', self::FLD_CODE_ID_COM],
        [sql_db::FLD_DESCRIPTION, sql_db::FLD_DESCRIPTION_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_DESCRIPTION_COM],
    );


    /*
     * object vars
     */

    // the standard fields of a type

    // the unique type name as shown to the user
    public string $name;
    // this id text is unique for all code links and is used for system im- and export
    public ?string $code_id;
    // to explain the type to the user as a tooltip
    public ?string $description = null;


    /*
     * construct and map
     */

    function __construct(?string $code_id, string $name = '', ?string $description = null, int $id = 0)
    {
        parent::__construct();
        $this->id = $id;
        $this->set_name($name);
        $this->set_code_id_db($code_id);
        $this->set_description($description);
    }

    function reset(bool $keep_user = false): void
    {
        $this->id = 0;
        $this->code_id = null;
        $this->name = '';
        $this->description = null;
    }

    /**
     * fill the type object vars based on an array of fields from the database
     * @param array $db_row with the data from the database
     * @param string $class the type class name that should be filled
     * @return bool true if all expected object vars have been set
     */
    function row_mapper_typ_obj(array $db_row, string $class): bool
    {
        $result = parent::row_mapper($db_row, $this->id_field_typ($class));
        // set the id upfront to allow row mapping
        if ($class == language::class and array_key_exists(language::FLD_ID, $db_row)) {
            $this->id = ($db_row[language::FLD_ID]);
        }
        if ($this->id() > 0) {
            $this->code_id = strval($db_row[sql_db::FLD_CODE_ID]);
            $type_name = '';
            if ($class == change_action::class) {
                $type_name = strval($db_row[self::FLD_ACTION]);
            } elseif ($class == change_table::class) {
                $type_name = strval($db_row[self::FLD_TABLE]);
            } elseif ($class == change_table_field::class) {
                $type_name = strval($db_row[self::FLD_FIELD]);
            } elseif ($class == language_form::class) {
                $type_name = strval($db_row[language_form::FLD_NAME]);
            } elseif ($class == language::class) {
                $type_name = strval($db_row[language::FLD_NAME]);
            } else {
                $type_name = strval($db_row[sql_db::FLD_TYPE_NAME]);
            }
            $this->name = $type_name;
            $this->description = strval($db_row[sql_db::FLD_DESCRIPTION]);
            $result = true;
        }
        return $result;
    }

    /**
     * fill the vars with this sandbox object based on the given api json array
     * @param array $api_json the api array with the word values that should be mapped
     * @param user_message $usr_msg if the mapping is incomplete the human-readable message what happened and how to solve it
     * @return bool true if the mapping has been completed successful
     */
    function api_mapper(array $api_json, user_message $usr_msg): bool
    {
        if (array_key_exists(json_fields::ID, $api_json)) {
            $this->id = $api_json[json_fields::ID];
        }
        if (array_key_exists(json_fields::NAME, $api_json)) {
            $this->set_name($api_json[json_fields::NAME]);
        }
        if (array_key_exists(json_fields::DESCRIPTION, $api_json)) {
            if ($api_json[json_fields::DESCRIPTION] <> '') {
                $this->description = $api_json[json_fields::DESCRIPTION];
            }
        }
        return $usr_msg->is_ok();
    }

    /**
     * general part to import a database object from a JSON array object
     *
     * @param array $in_ex_json an array with the data of the json object
     * @param user_message $usr_msg to enrich with warnings, problems and solutions
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

        return $usr_msg->is_ok();
    }


    /*
     * set and get
     */

    /**
     * set the vars of this type object based on json string from the frontend object
     * @param string $api_json with the api message created by the frontend
     * @param user_message $usr_msg with problems and suggested solutions for the user
     * @return bool true if the mapping has been completed successful
     */
    function set_from_api(string $api_json, user_message $usr_msg): bool
    {
        return $this->api_mapper(json_decode($api_json, true), $usr_msg);
    }

    function set_name(string $name): void
    {
        $this->name = $name;
    }

    /**
     * set the unique id to select a single verb by the program
     *r
     * @param string|null $code_id the unique key to select a word used by the system e.g. for the system or configuration
     * @param user $usr the user who has requested the change
     * @return user_message warning message for the user if the permissions are missing
     */
    function set_code_id(?string $code_id, user $usr): user_message
    {
        $usr_msg = new user_message();
        if ($usr->can_set_code_id()) {
            $this->code_id = $code_id;
        } else {
            $lib = new library();
            $usr_msg->add_id_with_vars(msg_id::NOT_ALLOWED_TO, [
                msg_id::VAR_USER_NAME => $usr->name(),
                msg_id::VAR_USER_PROFILE => $usr->profile_code_id(),
                msg_id::VAR_NAME => sql_db::FLD_CODE_ID,
                msg_id::VAR_CLASS_NAME => $lib->class_to_name($this::class)
            ]);
        }
        return $usr_msg;
    }

    /**
     * set the code id without check
     * should only be called by the database mapper function
     */
    function set_code_id_db(?string $code_id): void
    {
        $this->code_id = $code_id;
    }

    function set_description(?string $description): void
    {
        $this->description = $description;
    }

    function name(): string
    {
        return $this->name;
    }

    function get_code_id(): string
    {
        return $this->code_id;
    }

    function get_description(): ?string
    {
        return $this->description;
    }


    /*
     * im- and export
     */

    /**
     * create an array with the export json fields
     * @param export_type_list|array $exp_typ define the export format
     * @param bool $do_load to switch off the database load for unit tests
     * @return array the filled array used to create the user export json
     */
    function export_json(export_type_list|array $exp_typ = [], bool $do_load = true): array
    {
        $vars = [];

        if ($this->name() <> '') {
            $vars[json_fields::NAME] = $this->name();
        }
        if ($this->code_id <> '') {
            $vars[json_fields::CODE_ID] = $this->code_id;
        }
        if ($this->description <> '') {
            $vars[json_fields::DESCRIPTION] = $this->description;
        }

        return $vars;
    }


    /*
     * info
     */

    function is_type(string $type_to_check): bool
    {
        if ($this->code_id == $type_to_check) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * TODO Prio 2 fill
     */
    function is_used(): bool
    {
        return true;
    }


    /*
     * sql create
     */

    /**
     * the sql statement to create the tables of a type object
     *
     * @param sql_creator $sc with the target db_type set
     * @return string the sql statement to create the table
     */
    function sql_table(sql_creator $sc): string
    {
        $sql = $sc->sql_separator();
        // the pod is a type object but the number of pods might be significant higher than the number of types
        if ($this:: class == pod::class) {
            $sql .= $this->sql_table_create($sc);
        } else {
            $sql .= $this->sql_table_create($sc, new sql_type_list([sql_type::KEY_SMALL_INT]));
        }
        return $sql;
    }

    /**
     * the sql statement to create the database indices of a type object
     *
     * @param sql_creator $sc with the target db_type set
     * @return string the sql statement to create the indices
     */
    function sql_index(sql_creator $sc): string
    {
        $sql = $sc->sql_separator();
        $sql .= $this->sql_index_create($sc);
        return $sql;
    }


    /*
     * load (used if the user can request a new type via the GUI)
     */

    /**
     * create an SQL statement to retrieve a type object by id from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param int $id the id of the type object
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_by_id(sql_creator $sc, int $id, string $class = ''): sql_par
    {
        $typ_lst = new type_list();
        $qp = $typ_lst->load_sql($sc, $class, sql_db::FLD_ID);
        $sc->add_where($this->id_field_typ($class), $id);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * synthetic creation of grandparent:: for verb
     * @param sql_creator $sc with the target db_type set
     * @param int $id the id of the type object
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_by_id_fwd(sql_creator $sc, int $id, string $class = ''): sql_par
    {
        return parent::load_sql_by_id($sc, $id);
    }

    /**
     * create an SQL statement to retrieve a type object by name from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $name the name of the source
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_by_name(sql_creator $sc, string $name, string $class = ''): sql_par
    {
        $typ_lst = new type_list();
        $qp = $typ_lst->load_sql($sc, $class, sql_db::FLD_NAME);
        $sc->add_where($this->name_field_typ($class), $name);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a type object by code id from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $code_id the code id of the source
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_by_code_id(sql_creator $sc, string $code_id, string $class = ''): sql_par
    {
        $typ_lst = new type_list();
        $qp = $typ_lst->load_sql($sc, $class, 'code_id');
        $sc->add_where(sql_db::FLD_CODE_ID, $code_id);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * load a type object e.g. phrase type, language or language form from the database
     * @param sql_par $qp the query parameters created by the calling function
     * @param string $class the type class name that should be filled
     * @return int the id of the object found and zero if nothing is found
     */
    protected function load_typ_obj(sql_par $qp, string $class): int
    {
        global $db_con;

        $db_row = $db_con->get1($qp);
        $this->row_mapper_typ_obj($db_row, $class);
        return $this->id();
    }

    private function id_field_typ(string $class): string
    {
        global $db_con;
        return $db_con->get_id_field_name($class);
    }

    private function name_field_typ(string $db_type): string
    {
        global $db_con;
        return $db_con->get_name_field($db_type);
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
        return array_merge($vars, get_object_vars($this));
    }


    /*
     * sql write
     */

    /**
     * create the sql statement to add or update an object in the database
     * all fields are always included in the query to be able to remove overwriting with a null value
     *
     * @param sql_creator $sc with the target db_type set
     * @param user_message $usr_msg collect the messages for the user
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement, and the parameter list
     */
    function sql_write(
        sql_creator      $sc,
        db_object_seq_id $db_row,
        user_message     $usr_msg,
        sql_type_list    $sc_par_lst = new sql_type_list()
    ): sql_par
    {
        global $sys;

        // get a list of all fields that could potentially be updated
        $fld_lst_all = $this->db_fields_all();

        // get the list of all fields that can be changed by the user
        $fvt_lst = $this->db_fields_changed($db_row, $usr_msg, $sc_par_lst);

        // TODO Prio 1 move the line from here to the end to a sql_write function and move it to the parent object
        // make the query name unique based on the changed fields
        $lib = new library();
        $ext = sql::NAME_SEP . $lib->sql_field_ext($fvt_lst, $fld_lst_all, $usr_msg);

        // create the main query parameter object and set the query name
        $qp = $this->sql_common($sc, $sc_par_lst, $ext);

        // log functions must always use named parameters
        $sc_par_lst->add(sql_type::NAMED_PAR);

        // set some var names to shorten the code lines
        $id_fld = $sc->id_field_name();
        if ($sc_par_lst->is_insert()) {
            $var_name_row_id = $sc->var_name_row_id($sc_par_lst);
        } else {
            $var_name_row_id = '_' . $id_fld;
        }

        // add the change action field to the field list for the log entries
        $fvt_lst->add_field(
            change_action::FLD_ID,
            $sys->typ_lst->cng_act->id(change_actions::ADD),
            type_object::FLD_ID_SQL_TYP
        );

        // list of parameters actually used in order of the function usage
        $par_lst_out = new sql_par_field_list();

        // init the function body
        if ($sc_par_lst->is_insert()) {
            $id_fld_new = $sc->var_name_new_id($sc_par_lst);
        } else {
            $id_fld_new = '';
        }
        $sql = $sc->sql_func_start($id_fld_new, $sc_par_lst);

        // don't use the log parameter for the sub queries
        $sc_par_lst_sub = $sc_par_lst->remove(sql_type::LOG);
        $sc_par_lst_sub->add(sql_type::LIST);
        $sc_par_lst_log = clone $sc_par_lst_sub;
        $sc_par_lst_log->add(sql_type::INSERT_PART);
        if ($sc_par_lst->is_insert()) {
            // create sql to set the prime key upfront to get the sequence id
            $qp_id = clone $qp;
            $qp_id = $this->sql_insert_key_field($sc, $qp_id, $fvt_lst, $id_fld_new, $usr_msg, $sc_par_lst_sub);
            $par_lst_out->add($qp_id->par_fld);
            $sql .= $qp_id->sql;
        }

        // get the data fields and move the unique db key field to the first entry
        $fld_lst_log = array_intersect($fvt_lst->names(), $fld_lst_all);
        $key_fld_pos = array_search($this->id_field(), $fld_lst_log);
        unset($fld_lst_log[$key_fld_pos]);

        // add the user to the field list so that the id can be used for the log
        $fvt_lst->add_field(
            user_db::FLD_ID,
            $usr_msg->usr->id(),
            db_object_seq_id::FLD_ID_SQL_TYP
        );

        // create the query parameters for the log entries for the single fields
        if ($sc_par_lst->is_insert()) {
            $qp_log = $sc->sql_func_log($this::class, $usr_msg->usr, $fld_lst_log, $fvt_lst, $usr_msg, $sc_par_lst_log);
        } else {
            $qp_log = $sc->sql_func_log_update($this::class, $usr_msg->usr, $fld_lst_log, $fvt_lst, $sc_par_lst_log, $this->id);
        }
        $sql .= ' ' . $qp_log->sql;
        $par_lst_out->add_list($qp_log->par_fld_lst);

        // add the name field if it is missing and the object should be excluded
        if (!$par_lst_out->has_name($this->name_field())) {
            $table_id = $sc->table_id($this::class);
            $par_lst_out->add_field(
                sql::FLD_LOG_FIELD_PREFIX . $this->name_field(),
                $sys->typ_lst->cng_fld->id($table_id . $this->name_field()),
                change::FLD_FIELD_ID_SQL_TYP
            );
            $par_lst_out->add_field(
                $this->name_field() . change::FLD_OLD_EXT,
                $this->name(),
                sandbox_named::FLD_NAME_SQL_TYP
            );
        }

        // update the fields excluding the unique id
        $fld_lst_chg = $fld_lst_log;
        if ($sc_par_lst->is_insert()) {
            $key_fld_pos = array_search($this->name_field(), $fld_lst_chg);
            unset($fld_lst_chg[$key_fld_pos]);
        }
        $update_fvt_lst = new sql_par_field_list();
        foreach ($fld_lst_chg as $fld) {
            $update_fvt_lst->add($fvt_lst->get($fld, $usr_msg));
        }
        if (!$update_fvt_lst->is_empty()) {
            $sc_update = clone $sc;
            $sc_par_lst_upd = $sc_par_lst;
            $sc_par_lst_upd->add(sql_type::UPDATE);
            $sc_par_lst_upd_ex_log = $sc_par_lst_upd->remove(sql_type::LOG);
            $sc_par_lst_upd_ex_log->add(sql_type::SUB);
            $qp_update = $this->sql_common($sc_update, $sc_par_lst_upd_ex_log);

            $qp_update->sql = $sc_update->create_sql_update(
                $id_fld, $var_name_row_id, $update_fvt_lst, [], $sc_par_lst_upd_ex_log);
            // add the insert row to the function body
            $sql .= ' ' . $qp_update->sql . ' ';
        }

        if ($sc->db_type == sql_db::POSTGRES) {
            if ($id_fld_new != '') {
                $sql .= sql::RETURN . ' ' . $id_fld_new . '; ';
            }
        }

        // create the query parameters for the actual change
        $qp_chg = clone $qp;

        $sql .= $sc->sql_func_end();

        $qp_chg->sql = $sc->create_sql_insert($par_lst_out, $sc_par_lst);

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
     * @return sql_par the SQL insert statement, the name of the SQL statement, and the parameter list
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


    /**
     * create the sql statement to delete type object e.g. a verb
     * but only if it does not have a code_id and is never used
     *
     * @param sql_creator $sc with the target db_type set
     * @param user_message $usr_msg the user message object that collects any issues during the sql creation
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par|null the SQL update statement, the name of the SQL statement, and the parameter list
     */
    function sql_delete(
        sql_creator   $sc,
        user_message  $usr_msg,
        sql_type_list $sc_par_lst = new sql_type_list()
    ): sql_par|null
    {
        $qp = null;
        if ($this->can_delete($usr_msg)) {
            // clone the sql parameter list to avoid changing the given list
            $sc_par_lst_used = clone $sc_par_lst;
            // set the sql query type
            $sc_par_lst_used->add(sql_type::DELETE);
            // set the query name
            $qp = $this->sql_common($sc, $sc_par_lst_used);
            $sc->set_name($qp->name);
            // fields and values that the word has additional to the standard named user sandbox object
            // for a new sandbox object the owner should be set, so remove the user id to force writing the user
            $sbx_empty = $this->clone_reset(true);
            // to get the list of the changed fields,
            // the list of all fields is not needed because only the id fields are written to the log in case of a delete
            $fvt_lst = $sbx_empty->db_fields_changed($this, $usr_msg, $sc_par_lst_used);
            // actual create the sql statement to delete the type object
            // and log who has deleted it and when
            $sc_par_lst_used->add(sql_type::NAMED_PAR);
            $qp = $this->sql_delete_and_log($sc, $qp, $fvt_lst, $usr_msg, $sc_par_lst_used);
        }

        return $qp;
    }

    /**
     * @param sql_creator $sc the sql creator object with the db type set
     * @param sql_par $qp the query parameter with the name already set
     * @param sql_par_field_list $fvt_lst list of field names, values and sql types for the log entry what has been deleted
     * @param user_message $usr_msg the user message object that collects any issues during the sql creation
     * @param sql_type_list $sc_par_lst
     * @return sql_par
     */
    private function sql_delete_and_log(
        sql_creator        $sc,
        sql_par            $qp,
        sql_par_field_list $fvt_lst,
        user_message       $usr_msg,
        sql_type_list      $sc_par_lst = new sql_type_list()
    ): sql_par
    {
        global $sys;
        $table_id = $sc->table_id($this::class);

        // set some var names to shorten the code lines
        $ext = sql::NAME_SEP . sql_creator::FILE_DELETE;
        $id_fld = $sc->id_field_name();
        $id_val = '_' . $id_fld;
        $name_fld = $this->name_field();

        // list of parameters actually used in order of the function usage
        $fvt_lst_out = new sql_par_field_list();

        // init the function body
        $sql = $sc->sql_func_start('', $sc_par_lst);

        // don't use the log parameter for the sub queries
        $sc_par_lst_sub = clone $sc_par_lst;
        $sc_par_lst_sub->add(sql_type::LIST);
        $sc_par_lst_sub->add(sql_type::NAMED_PAR);
        $sc_par_lst_sub->add(sql_type::DELETE_PART);
        $sc_par_lst_log = $sc_par_lst_sub->remove(sql_type::LOG);
        $sc_par_lst_log->add(sql_type::SELECT_FOR_INSERT);

        // create the queries for the log entries
        $func_body_change = '';

        // add the user_id to log who requested the deletion
        $fvt_lst_out->add_field(
            user_db::FLD_ID,
            $usr_msg->usr->id(),
            sql_par_type::INT);

        // add the change_action_id if needed
        $fvt_lst_out->add_field(
            change_action::FLD_ID,
            $sys->typ_lst->cng_act->id(change_actions::DELETE),
            sql_par_type::INT_SMALL);

        // add the field_id of the field actually changed if needed
        $fvt_lst_out->add_field(
            sql::FLD_LOG_FIELD_PREFIX . $name_fld,
            $sys->typ_lst->cng_fld->id($table_id . $name_fld),
            sql_par_type::INT_SMALL);

        // add the db field value of the field actually changed if needed
        $fvt_lst_out->add_field(
            $name_fld,
            $this->name(),
            sql_par_type::TEXT);

        // create the insert log statement
        $sc_log = clone $sc;
        $log = new change($usr_msg->usr);
        $log->set_class($this::class);
        $log->set_field($name_fld);
        $log->old_value = $this->name();
        $log->new_value = null;
        $qp_log = $log->sql_insert_log(
            $sc_log, $sc_par_lst_log, $ext . '_' . $name_fld, '', $name_fld, $id_val);

        // TODO get the fields used in the change log sql from the sql
        $func_body_change .= ' ' . $qp_log->sql . ';';

        // add the row id of the standard table for user overwrites
        $fvt_lst_out->add_field(
            $this->id_field(),
            $this->id(),
            sql_par_type::INT);

        $sql .= ' ' . $func_body_change;

        // create the actual delete or exclude statement
        $sc_delete = clone $sc;
        $sc_par_lst_del = clone $sc_par_lst;
        $sc_par_lst_del->add(sql_type::DELETE);
        $sc_par_lst_del->add(sql_type::NAMED_PAR);
        $qp_delete = $this->sql_common($sc_delete, $sc_par_lst_log);
        $qp_delete->sql = $sc_delete->create_sql_delete(
            $id_fld, $id_val, $sc_par_lst_sub);
        // add the delete statement to the function body
        $sql .= ' ' . $qp_delete->sql . ' ';

        $sql .= $sc->sql_func_end();

        // create the query parameters for the call
        $sc_par_lst_func = clone $sc_par_lst;
        $sc_par_lst_func->add(sql_type::FUNCTION);
        $sc_par_lst_func->add(sql_type::DELETE);
        $sc_par_lst_func->add(sql_type::NO_ID_RETURN);
        if ($sc_par_lst->exclude_sql()) {
            $sc_par_lst_func->add(sql_type::EXCLUDE);
        }
        $qp_func = $this->sql_common($sc_delete, $sc_par_lst_func);
        $qp_func->sql = $sc->create_sql_delete(
            $id_fld, $id_val, $sc_par_lst_func, $fvt_lst_out);
        $qp_func->par = $fvt_lst_out->values();

        // merge all together and create the function
        $qp->sql = $qp_func->sql . ' ' . $sql . ';';
        $qp->par = $fvt_lst_out->values();

        // create the function call
        $qp->call_sql = ' ' . sql::SELECT . ' ' . $qp_func->name . ' (';

        $call_val_str = $fvt_lst_out->par_sql($sc);

        $qp->call_sql .= $call_val_str . ');';

        return $qp;
    }

    protected function can_delete(user_message $usr_msg): bool
    {
        $can_del = false;
        if (!$this->is_used()) {
            if ($this->code_id != null or $this->code_id != '') {
                if ($usr_msg->usr->is_admin() or $usr_msg->usr->is_system()) {
                    $can_del = true;
                } else {
                    $usr_msg->add_id_with_vars(msg_id::CANNOT_DELETE_TYPE_WITH_CODE_IS, [
                        msg_id::VAR_NAME => $this->name(),
                    ]);
                }
            }
        } else {
            // for the system user it should be possible to delete a type
            if ($usr_msg->usr->is_system()) {
                $can_del = true;
            }
        }
        return $can_del;
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
                $this->name_field(),
                sql_db::FLD_CODE_ID,
                sql_db::FLD_DESCRIPTION
            ]
        );
    }

    /**
     * get a list of database field names, values and types that have been updated
     *
     * @param type_object|db_object_seq_id $obj the compare value to detect the changed fields
     * @param user_message $usr_msg the user message object that collects any issues during the sql creation
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list list 3 entry arrays with the database field name, the value and the sql type that have been updated
     */
    function db_fields_changed(
        type_object|db_object_seq_id $obj,
        user_message                 $usr_msg,
        sql_type_list                $sc_par_lst = new sql_type_list()
    ): sql_par_field_list
    {
        global $sys;

        $sc = new sql_creator();
        $do_log = $sc_par_lst->incl_log();
        $table_id = $sc->table_id($this::class);

        $lst = parent::db_fields_changed($obj, $usr_msg, $sc_par_lst);
        if ($obj->name <> $this->name) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . $this->name_field(),
                    $sys->typ_lst->cng_fld->id($table_id . $this->name_field()),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $used_old_name = $obj->name;
            if ($used_old_name == '') {
                $used_old_name = null;
            }
            $lst->add_field(
                $this->name_field(),
                $this->name,
                sandbox_named::FLD_NAME_SQL_TYP,
                $used_old_name
            );
        }
        if ($obj->code_id !== $this->code_id) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . sql_db::FLD_CODE_ID,
                    $sys->typ_lst->cng_fld->id($table_id . sql_db::FLD_CODE_ID),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                sql_db::FLD_CODE_ID,
                $this->code_id,
                sql_field_type::CODE_ID,
                $obj->code_id
            );
        }
        if ($obj->description <> $this->description) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . sql_db::FLD_DESCRIPTION,
                    $sys->typ_lst->cng_fld->id($table_id . sql_db::FLD_DESCRIPTION),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                sql_db::FLD_DESCRIPTION,
                $this->description,
                sql_db::FLD_DESCRIPTION_SQL_TYP,
                $obj->description
            );
        }
        return $lst;
    }


    /*
     * sql fields
     */

    function name_field(): string
    {
        return type_object::FLD_NAME;
    }

    function all_fields(): array
    {
        return type_object::FLD_NAMES;
    }


    /*
     * debug
     */

    /**
     * @return string with the unique database id mainly for child dsp_id() functions
     */
    function dsp_id(): string
    {

        return $this->name . '/' . $this->get_code_id() . parent::dsp_id();
    }

}
