<?php

/*

    model/ref/source.php - the source object to define a source for values
    ------------------

    a source is always unidirectional
    in many cases a source is just a user base data source without any import
    the automatic import can be based on standard data format e.g. json, XML or HTML

    reference types are preloaded in the frontend whereas source are loaded on demand

    if the import gets more complex or the interface is bidirectional use a reference type
    references are concrete links between a phrase and an external object

    The main sections of this object are
    - db const:          const for the database link
    - object vars:       the variables of this word object
    - construct and map: including the mapping of the db row to this word object
    - api:               create an api array for the frontend and set the vars based on a frontend api message
    - im- and export:    create an export object and set the vars from an import object
    - set and get:       to capsule the vars from unexpected changes
    - preloaded:         select e.g. types from cache
    - cast:              create an api object and set the vars from an api json
    - convert:           convert this word e.g. phrase or term
    - load:              database access object (DAO) functions
    - sql fields:        field names for sql
    - sandbox:           manage the user sandbox
    - save:              manage to update the database
    - sql write:         sql statement creation to write to the database
    - sql write fields:  field list for writing to the database


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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace cfg\ref;

use cfg\const\paths;

include_once paths::MODEL_SANDBOX . 'sandbox_typed.php';
include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_par_field_list.php';
include_once paths::DB . 'sql_type.php';
include_once paths::DB . 'sql_type_list.php';
include_once paths::MODEL_HELPER . 'data_object.php';
include_once paths::MODEL_HELPER . 'db_object_seq_id.php';
include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::MODEL_LOG . 'change.php';
include_once paths::MODEL_REF . 'source_db.php';
include_once paths::MODEL_SANDBOX . 'sandbox.php';
include_once paths::MODEL_SANDBOX . 'sandbox_named.php';
include_once paths::MODEL_SANDBOX . 'sandbox_code_id.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_db.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_CONST . 'sources.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_HELPER . 'CombineObject.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED . 'json_fields.php';

use cfg\db\sql;
use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\db\sql_par;
use cfg\db\sql_par_field_list;
use cfg\db\sql_type;
use cfg\db\sql_type_list;
use cfg\helper\data_object;
use cfg\helper\db_object_seq_id;
use cfg\helper\type_object;
use cfg\log\change;
use cfg\sandbox\sandbox;
use cfg\sandbox\sandbox_code_id;
use cfg\user\user;
use cfg\user\user_db;
use cfg\user\user_message;
use shared\const\sources;
use shared\enum\messages as msg_id;
use shared\helper\CombineObject;
use shared\types\api_type_list;
use shared\json_fields;

class source extends sandbox_code_id
{

    /*
     * db const
     */

    // comments used for the database creation
    const TBL_COMMENT = 'for the original sources for the numeric, time and geo values';

    // forward the const to enable usage of $this::CONST_NAME
    const FLD_ID = source_db::FLD_ID;
    const FLD_NAMES = source_db::FLD_NAMES;
    const FLD_NAMES_USR = source_db::FLD_NAMES_USR;
    const FLD_NAMES_NUM_USR = source_db::FLD_NAMES_NUM_USR;
    const ALL_SANDBOX_FLD_NAMES = source_db::ALL_SANDBOX_FLD_NAMES;
    const FLD_LST_MUST_BE_IN_STD = source_db::FLD_LST_MUST_BE_IN_STD;
    const FLD_LST_MUST_BUT_USER_CAN_CHANGE = source_db::FLD_LST_MUST_BUT_USER_CAN_CHANGE;
    const FLD_LST_USER_CAN_CHANGE = source_db::FLD_LST_USER_CAN_CHANGE;


    /*
     * object vars
     */

    // database fields additional to the user sandbox fields
    // the internet link to the source
    private ?string $url = null;


    /*
     * construct and map
     */

    // define the settings for this source object
    function __construct(user $usr)
    {
        $this->reset();
        parent::__construct($usr);

        $this->rename_can_switch = UI_CAN_CHANGE_SOURCE_NAME;
    }

    function reset(): void
    {
        parent::reset();
        $this->url = null;
    }

    /**
     * map the database object to this source class fields
     *
     * @param array|null $db_row with the data directly from the database
     * @param bool $load_std true if only the standard user sandbox object is loaded
     * @param bool $allow_usr_protect false for using the standard protection settings for the default object used for all users
     * @param string $id_fld the name of the id field as defined in this child and given to the parent
     * @param string $name_fld the name of the name field as defined in this child class
     * @param string $type_fld the name of the type field as defined in this child class
     * @return bool true if the source is loaded and valid
     */
    function row_mapper_sandbox(
        ?array $db_row,
        bool   $load_std = false,
        bool   $allow_usr_protect = true,
        string $id_fld = source_db::FLD_ID,
        string $name_fld = source_db::FLD_NAME,
        string $type_fld = source_db::FLD_TYPE
    ): bool
    {
        $result = parent::row_mapper_sandbox($db_row, $load_std, $allow_usr_protect, $id_fld, $name_fld, $type_fld);
        if ($result) {
            $this->set_url($db_row[source_db::FLD_URL]);
        }
        return $result;
    }

    /**
     * map a source api json to this model source object
     * similar to the import_obj function but using the database id instead of names as the unique key
     * @param array $api_json the api array with the triple values that should be mapped
     * @return user_message the message for the user why the action has failed and a suggested solution
     */
    function api_mapper(array $api_json): user_message
    {
        $msg = parent::api_mapper($api_json);

        if (array_key_exists(json_fields::URL, $api_json)) {
            $this->set_url($api_json[json_fields::URL]);
        }

        return $msg;
    }

    /**
     * set the object vars of this source object based on the import json array
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
        global $src_typ_cac;

        $usr_msg = parent::import_mapper_user($in_ex_json, $usr_req, $dto, $test_obj);

        if (key_exists(json_fields::URL, $in_ex_json)) {
            $this->set_url($in_ex_json[json_fields::URL]);
        }

        return $usr_msg;
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
        $vars[json_fields::URL] = $this->url();
        return $vars;
    }

    /**
     * set the source object vars based on an api json array
     * similar to import_obj but using the database id instead of the names and code id
     * @param array $api_json the api array
     * @return user_message false if a value could not be set
     */
    function save_from_api_msg(array $api_json, bool $do_save = true): user_message
    {
        log_debug();
        $usr_msg = new user_message();

        if (array_key_exists(json_fields::NAME, $api_json)) {
            $this->name = $api_json[json_fields::NAME];
        }
        if (array_key_exists(json_fields::URL, $api_json)) {
            $this->set_url($api_json[json_fields::URL]);
        }
        if (array_key_exists(json_fields::DESCRIPTION, $api_json)) {
            $this->description = $api_json[json_fields::DESCRIPTION];
        }
        if (array_key_exists(json_fields::TYPE, $api_json)) {
            $this->type_id = $api_json[json_fields::TYPE];
        }

        if ($usr_msg->is_ok() and $do_save) {
            $usr_msg->add($this->save());
        }

        return $usr_msg;
    }


    /*
     * im- and export
     */

    /**
     * create an array with the export json fields
     * @param bool $do_load true if any missing data should be loaded while creating the array
     * @return array with the json fields
     */
    function export_json(bool $do_load = true): array
    {
        $vars = parent::export_json($do_load);

        if ($this->url() <> '') {
            $vars[json_fields::URL] = $this->url();
        }

        return $vars;
    }


    /*
     * set and get
     */

    /**
     * set the predefined source type by the given code id or name
     *
     * @param string $code_id_or_name the code id or name of the source type that should be added to this source
     * @param user $usr_req the user who wants to change the type
     * @return user_message a warning if the view type code id is not found
     */
    function set_type(string $code_id_or_name, user $usr_req = new user()): user_message
    {
        global $src_typ_cac;
        if ($src_typ_cac->has_code_id($code_id_or_name)) {
            return parent::set_type_by_code_id(
                $code_id_or_name, $src_typ_cac, msg_id::SOURCE_TYPE_NOT_FOUND, $usr_req);
        } else {
            return parent::set_type_by_name(
                $code_id_or_name, $src_typ_cac, msg_id::SOURCE_TYPE_NOT_FOUND, $usr_req);
        }
    }

    function set_url(?string $url): void
    {
        $this->url = $url;
    }

    function url(): ?string
    {
        return $this->url;
    }


    /*
     * preloaded
     */

    /**
     * @return string|null the code_id of the source type
     */
    function type_code_id(): string|null
    {
        global $src_typ_cac;
        return $src_typ_cac->code_id($this->type_id);
    }

    /**
     * @return string the source type name from the array preloaded from the database
     */
    function type_name(): string
    {
        global $src_typ_cac;

        $type_name = '';
        if ($this->type_id > 0) {
            $type_name = $src_typ_cac->name($this->type_id);
        }
        return $type_name;
    }


    /*
     * load
     */

    /**
     * load the source parameters for all users
     * @param sql_par|null $qp placeholder to align the function parameters with the parent
     * @return bool true if the standard source has been loaded
     */
    function load_standard(?sql_par $qp = null): bool
    {
        global $db_con;
        $qp = $this->load_standard_sql($db_con->sql_creator());
        $result = parent::load_standard($qp);

        if ($result) {
            $result = $this->load_owner();
        }
        return $result;
    }

    /**
     * create the SQL to load the default source always by the id
     *
     * @param sql_creator $sc with the target db_type set
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_standard_sql(sql_creator $sc): sql_par
    {
        $sc->set_class($this::class);
        $sc->set_fields(array_merge(
            source_db::FLD_NAMES,
            source_db::FLD_NAMES_USR,
            source_db::FLD_NAMES_NUM_USR,
            array(user_db::FLD_ID)
        ));

        return parent::load_standard_sql($sc);
    }

    /**
     * create the common part of an SQL statement to retrieve the parameters of a source from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name extension to make the query name unique
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name): sql_par
    {
        $sc->set_class($this::class);
        return parent::load_sql_fields(
            $sc, $query_name,
            source_db::FLD_NAMES,
            source_db::FLD_NAMES_USR,
            source_db::FLD_NAMES_NUM_USR
        );
    }


    /*
     * info
     */

    /**
     * check if the source in the database needs to be updated
     * e.g. for import  if this source has only the name set, the protection should not be updated in the database
     *
     * @param source|CombineObject|db_object_seq_id $db_obj the source as saved in the database
     * @return bool true if this source has infos that should be saved in the database
     */
    function needs_db_update(source|CombineObject|db_object_seq_id $db_obj): bool
    {
        $result = parent::needs_db_update($db_obj);
        if ($this->url() != null) {
            if ($this->url() != $db_obj->url()) {
                $result = true;
            }
        }
        return $result;
    }


    /*
     * sql fields
     */

    function name_field(): string
    {
        return source_db::FLD_NAME;
    }

    function all_sandbox_fields(): array
    {
        return source_db::ALL_SANDBOX_FLD_NAMES;
    }


    /*
     * sandbox
     */

    /**
     * @return bool true if no one has used this source
     */
    function not_used(): bool
    {
        log_debug($this->id());

        // to review: maybe replace by a database foreign key check
        return $this->not_changed();
    }

    /**
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     *                 to check if the source has been changed
     */
    function not_changed_sql(sql_creator $sc): sql_par
    {
        $sc->set_class(source::class);
        return $sc->load_sql_not_changed($this->id(), $this->owner_id());
    }

    /**
     * @return bool true if no other user has modified the source
     */
    function not_changed(): bool
    {
        log_debug($this->dsp_id() . ' by someone else than the owner (' . $this->owner_id() . ')');

        global $db_con;
        $result = true;

        if ($this->id() == 0) {
            log_err('The id must be set to detect if the link has been changed');
        } else {
            $qp = $this->not_changed_sql($db_con->sql_creator());
            $db_row = $db_con->get1($qp);
            $change_user_id = $db_row[user_db::FLD_ID];
            if ($change_user_id > 0) {
                $result = false;
            }
        }
        log_debug('for ' . $this->dsp_id() . ' is ' . zu_dsp_bool($result));
        return $result;
    }

    /**
     * create an SQL statement to retrieve the user changes of the current source
     *
     * @param sql_creator $sc with the target db_type set
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation e.g. standard for values and results
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_user_changes(
        sql_creator   $sc,
        sql_type_list $sc_par_lst = new sql_type_list()
    ): sql_par
    {
        $sc->set_class($this::class, new sql_type_list([sql_type::USER]));
        return parent::load_sql_user_changes($sc, $sc_par_lst);
    }


    /*
     * save
     */

    /**
     * set the update parameters for the source url
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param source $db_rec the database record before the saving
     * @param source $std_rec the database record defined as standard because it is used by most users
     * @return user_message the message that should be shown to the user in case something went wrong
     */
    private function save_field_url(sql_db $db_con, source $db_rec, source $std_rec): user_message
    {
        $usr_msg = new user_message;
        if ($db_rec->url() <> $this->url()) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->url();
            $log->new_value = $this->url();
            $log->std_value = $std_rec->url();
            $log->row_id = $this->id();
            $log->set_field(source_db::FLD_URL);
            $usr_msg->add($this->save_field_user($db_con, $log));
        }
        return $usr_msg;
    }

    /**
     * save all updated source fields excluding the name, because already done when adding a source
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param source|sandbox $db_obj the database record before the saving
     * @param source|sandbox $norm_obj the database record defined as standard because it is used by most users
     * @return user_message the message that should be shown to the user in case something went wrong
     */
    function save_all_fields(sql_db $db_con, source|sandbox $db_obj, source|sandbox $norm_obj): user_message
    {
        $usr_msg = parent::save_fields_typed($db_con, $db_obj, $norm_obj);
        $usr_msg->add($this->save_field_url($db_con, $db_obj, $norm_obj));
        log_debug('all fields for ' . $this->dsp_id() . ' has been saved');
        return $usr_msg;
    }


    /*
     * save helper
     */

    /**
     * @return array with the reserved source names
     */
    protected function reserved_names(): array
    {
        return sources::RESERVED_NAMES;
    }

    /**
     * @return array with the fixed source names for db read testing
     */
    protected function fixed_names(): array
    {
        return sources::FIXED_NAMES;
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
                source_db::FLD_TYPE,
                source_db::FLD_URL,
            ],
            parent::db_fields_all_sandbox()
        );
    }

    /**
     * get a list of database field names, values and types that have been updated
     *
     * @param sandbox|source $sbx the compare value to detect the changed fields
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list list of the database field names that have been updated
     */
    function db_fields_changed(
        sandbox|source $sbx,
        sql_type_list  $sc_par_lst = new sql_type_list()
    ): sql_par_field_list
    {
        global $cng_fld_cac;

        $sc = new sql_creator();
        $do_log = $sc_par_lst->incl_log();
        $table_id = $sc->table_id($this::class);

        $lst = parent::db_fields_changed($sbx, $sc_par_lst);
        if ($sbx->type_id() <> $this->type_id()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . source_db::FLD_TYPE,
                    $cng_fld_cac->id($table_id . source_db::FLD_TYPE),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                source_db::FLD_TYPE,
                $this->type_id(),
                type_object::FLD_ID_SQL_TYP,
                $sbx->type_id()
            );
        }
        if ($sbx->url() <> $this->url()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . source_db::FLD_URL,
                    $cng_fld_cac->id($table_id . source_db::FLD_URL),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                source_db::FLD_URL,
                $this->url(),
                source_db::FLD_URL_SQL_TYP,
                $sbx->url()
            );
        }
        return $lst->merge($this->db_changed_sandbox_list($sbx, $sc_par_lst));
    }

}
