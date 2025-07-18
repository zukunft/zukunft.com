<?php

/*

    model/sandbox/sandbox_description.php - adding the description and type field to the _sandbox superclass
    -------------------------------------

    The main sections of this object are
    - object vars:       the variables of this sandbox object
    - construct and map: including the mapping of the db row to this sandbox object
    - api:               create an api array for the frontend and set the vars based on a frontend api message


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

namespace cfg\sandbox;

include_once MODEL_SANDBOX_PATH . 'sandbox_link.php';
include_once DB_PATH . 'sql.php';
include_once DB_PATH . 'sql_creator.php';
include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_par.php';
include_once DB_PATH . 'sql_par_field_list.php';
include_once DB_PATH . 'sql_type.php';
include_once DB_PATH . 'sql_type_list.php';
//include_once MODEL_LOG_PATH . 'change_log_list.php';
include_once MODEL_HELPER_PATH . 'data_object.php';
include_once MODEL_USER_PATH . 'user.php';
include_once MODEL_USER_PATH . 'user_message.php';
include_once SHARED_ENUM_PATH . 'messages.php';
include_once SHARED_TYPES_PATH . 'api_type_list.php';
include_once SHARED_PATH . 'json_fields.php';
include_once SHARED_PATH . 'library.php';

use cfg\db\sql;
use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\db\sql_par;
use cfg\db\sql_par_field_list;
use cfg\db\sql_type;
use cfg\db\sql_type_list;
use cfg\helper\data_object;
use cfg\log\change_log_list;
use cfg\user\user;
use cfg\user\user_message;
use shared\enum\messages as msg_id;
use shared\json_fields;
use shared\types\api_type_list;
use shared\library;

class sandbox_link_named extends sandbox_link
{

    /*
     * object vars
     */

    // the word, triple, verb oder formula description that is shown as a mouseover explain to the user
    // if description is NULL the database value should not be updated
    // or for triples the description that may differ from the generic created text
    // e.g. Zurich AG instead of Zurich (Company)
    // if the description is empty the generic created name is used
    protected ?string $name = '';   // simply the object name, which cannot be empty if it is a named object
    public ?string $description = null;

    // database id of the type used for named link user sandbox objects with predefined functionality
    // which is actually only triple
    // repeating _sandbox_typed, because php 8.1 does not yet allow multi extends
    public ?int $type_id = null;


    /*
     * construct and map
     */

    function reset(): void
    {
        parent::reset();
        $this->description = null;
        $this->type_id = null;
    }

    /**
     * map the database fields to the object fields
     * to be extended by the child object
     *
     * @param array|null $db_row with the data directly from the database
     * @param bool $load_std true if only the standard user sandbox object is loaded
     * @param bool $allow_usr_protect false for using the standard protection settings for the default object used for all users
     * @param string $id_fld the name of the id field as set in the child class
     * @param string $name_fld the name of the name field as set in the child class
     * @return bool true if the word is loaded and valid
     */
    function row_mapper_sandbox(
        ?array $db_row,
        bool   $load_std = false,
        bool   $allow_usr_protect = true,
        string $id_fld = '',
        string $name_fld = ''
    ): bool
    {
        $result = parent::row_mapper_sandbox($db_row, $load_std, $allow_usr_protect, $id_fld);
        if ($result) {
            if (array_key_exists($name_fld, $db_row)) {
                if ($db_row[$name_fld] != null) {
                    $this->set_name($db_row[$name_fld]);
                }
            }
            if (array_key_exists(sql_db::FLD_DESCRIPTION, $db_row)) {
                $this->description = $db_row[sql_db::FLD_DESCRIPTION];
            }
        }
        return $result;
    }

    /**
     * set the type based on the api json
     * @param array $api_json the api json array with the values that should be mapped
     */
    function api_mapper(array $api_json): user_message
    {
        global $usr;

        $msg = parent::api_mapper($api_json);

        if (array_key_exists(json_fields::NAME, $api_json)) {
            $this->set_name($api_json[json_fields::NAME]);
        }
        if (array_key_exists(json_fields::DESCRIPTION, $api_json)) {
            if ($api_json[json_fields::DESCRIPTION] <> '') {
                $this->description = $api_json[json_fields::DESCRIPTION];
            }
        }
        if (array_key_exists(json_fields::TYPE, $api_json)) {
            $this->set_type_id($api_json[json_fields::TYPE], $usr);
        }
        return $msg;
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

        $vars[json_fields::NAME] = $this->name();
        $vars[json_fields::DESCRIPTION] = $this->description();
        $vars[json_fields::TYPE] = $this->type_id();

        return $vars;
    }

    /**
     * set the vars of this named link object based on the given json without writing to the database
     * import the name and description of a sandbox link object
     *
     * @param array $in_ex_json an array with the data of the json object
     * @param data_object|null $dto cache of the objects imported until now for the primary references
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_mapper(array $in_ex_json, data_object $dto = null, object $test_obj = null): user_message
    {
        $usr_msg = parent::import_mapper($in_ex_json, $dto, $test_obj);

        // reset of object not needed, because the calling function has just created the object
        // name is not mandatory because might be generated based on the link
        if (key_exists(json_fields::NAME, $in_ex_json)) {
            $this->set_name($in_ex_json[json_fields::NAME]);
        }
        if (key_exists(json_fields::DESCRIPTION, $in_ex_json)) {
            $this->description = $in_ex_json[json_fields::DESCRIPTION];
        }

        return $usr_msg;
    }


    /*
     * set and get
     */

    /**
     * set the name of this named user sandbox link object
     * set and get of the name is needed to use the same function for phrase or term
     *
     * @param string $name the name of this named user sandbox object e.g. word set in the related object
     * @return void
     */
    function set_name(string $name): void
    {
        $this->name = $name;
    }

    /**
     * get the name of the word object
     *
     * @return string the name from the object e.g. word using the same function as the phrase and term
     */
    function name(): string
    {
        return $this->name;
    }

    /**
     * get the name of the word object or null
     *
     * @return string|null the name from the object e.g. word using the same function as the phrase and term
     */
    function name_or_null(): ?string
    {
        if ($this->name == null) {
            return null;
        } else {
            return $this->name();
        }
    }

    /**
     * dummy function that should always be overwritten by the child object
     * @return string
     */
    function name_field(): string
    {
        log_err('function name_field() missing in class ' . $this::class);
        return '';
    }

    /**
     * create a clone and update the name (mainly used for unit testing)
     * but keep the unique db id
     *
     * @param string $name the target name
     * @return $this a clone with the name changed
     */
    function cloned_named(string $name): sandbox_link_named
    {
        $obj_cpy = parent::cloned();
        $obj_cpy->set_id($this->id());
        $obj_cpy->set_fob($this->fob());
        $obj_cpy->set_tob($this->tob());
        $obj_cpy->set_name($name);
        return $obj_cpy;
    }

    /**
     * set the description of this named user sandbox link object which explains the object for the user
     * set and get of the description is needed to use the same function for phrase or term
     *
     * @param string|null $description the name of this named user sandbox object e.g. word set in the related object
     * @return void
     */
    function set_description(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * get the description of the sandbox link object
     * if the object is excluded null is returned
     * to check the value before the exclusion access the var direct via $this->description
     *
     * @return string|null the description from the object e.g. word using the same function as the phrase and term
     */
    function description(): ?string
    {
        if ($this->excluded) {
            return null;
        } else {
            return $this->description;
        }
    }

    /**
     * set the database id of the type
     *
     * @param int|null $type_id the database id of the type
     * @param user $usr_req the user who wants to change the type
     * @return user_message warning message for the user if the permissions are missing
     */
    function set_type_id(?int $type_id, user $usr_req): user_message
    {
        $usr_msg = new user_message();
        if ($usr_req->can_set_type_id()) {
            $this->type_id = $type_id;
        } else {
            $lib = new library();
            $usr_msg->add_id_with_vars(msg_id::NOT_ALLOWED_TO, [
                msg_id::VAR_USER_NAME => $usr_req->name(),
                msg_id::VAR_USER_PROFILE => $usr_req->profile_code_id(),
                msg_id::VAR_NAME => sql::FLD_TYPE_NAME,
                msg_id::VAR_CLASS_NAME => $lib->class_to_name($this::class)
            ]);
        }
        return $usr_msg;
    }

    /**
     * @return int|null the database id of the type
     */
    function type_id(): ?int
    {
        return $this->type_id;
    }


    /*
     * cast
     */

    /**
     * same as in cfg/sandbox/sandbox_named, but php does not yet allow multi extends
     * @param object $api_obj frontend API objects that should be filled with unique object name
     */
    function fill_api_obj(object $api_obj): void
    {
        parent::fill_api_obj($api_obj);

        $api_obj->set_name($this->name());
        $api_obj->description = $this->description;
        $api_obj->set_type_id($this->type_id());
    }


    /*
     * info
     */

    /**
     * check if the named object in the database needs to be updated
     *
     * @param sandbox_link_named|sandbox $db_obj the word as saved in the database
     * @return bool true if this word has infos that should be saved in the database
     */
    function needs_db_update(sandbox_link_named|sandbox $db_obj): bool
    {
        $result = parent::needs_db_update($db_obj);
        if ($this->name != null) {
            if ($this->name != $db_obj->name) {
                $result = true;
            }
        }
        if ($this->description != null) {
            if ($this->description != $db_obj->description) {
                $result = true;
            }
        }
        if ($this->type_id != null) {
            if ($this->type_id != $db_obj->type_id) {
                $result = true;
            }
        }
        return $result;
    }


    /*
     * log read
     */

    /**
     * get the description of the latest change related to this object
     * @param user $usr who has requested to see the change
     * @return string the description of the latest change
     */
    function log_last_msg(user $usr): string
    {
        $log = new change_log_list();
        $log->load_obj_last($this, $usr);
        return $log->first_msg();
    }

    /**
     * get the description of the latest change related to this object and the given field
     * @param user $usr who has requested to see the change
     * @param string $fld the field name to filter the changes
     * @return string the description of the latest change
     */
    function log_last_field_msg(user $usr, string $fld): string
    {
        $log = new change_log_list();
        $log->load_obj_field_last($this, $usr, $fld);
        return $log->first_msg();
    }


    /*
     * save function
     */

    /**
     * set the update parameters for the link object description
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param sandbox_link_named $db_rec the database record before the saving
     * @param sandbox_link_named $std_rec the database record defined as standard because it is used by most users
     * @return string if not empty the message that should be shown to the user
     */
    function save_field_description(sql_db $db_con, sandbox_link_named $db_rec, sandbox_link_named $std_rec): string
    {
        $result = '';
        // if the description is not set, don't overwrite any db entry
        if ($this->description <> Null) {
            if ($this->description <> $db_rec->description) {
                $log = $this->log_upd();
                $log->old_value = $db_rec->description;
                $log->new_value = $this->description;
                $log->std_value = $std_rec->description;
                $log->row_id = $this->id();
                $log->set_field(sql_db::FLD_DESCRIPTION);
                $result = $this->save_field_user($db_con, $log);
            }
        }
        return $result;
    }


    /*
     * sql write
     */

    /**
     * create the sql statement to add a new named sandbox object e.g. word to the database
     * TODO add qp merge
     *
     * @param sql_creator $sc with the target db_type set
     * @param sql_par $qp
     * @param sql_par_field_list $fvt_lst list of field names, values and sql types additional to the standard id and name fields
     * @param string $id_fld_new
     * @param sql_type_list $sc_par_lst_sub the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement and the parameter list
     */
    function sql_insert_key_field(
        sql_creator        $sc,
        sql_par            $qp,
        sql_par_field_list $fvt_lst,
        string             $id_fld_new,
        sql_type_list      $sc_par_lst_sub = new sql_type_list()
    ): sql_par
    {
        // set some var names to shorten the code lines
        $usr_tbl = $sc_par_lst_sub->is_usr_tbl();
        $ext = sql::NAME_SEP . sql_creator::FILE_INSERT;

        // list of parameters actually used in order of the function usage
        $sql = '';

        $qp_lnk = parent::sql_insert_key_field($sc, $qp, $fvt_lst, $id_fld_new, $sc_par_lst_sub);

        // create the sql to insert the row
        $fvt_insert = $fvt_lst->get($this->name_field());
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
        //$sql .= ' ' . $qp_insert->sql . '; ';

        // get the new row id for MySQL db
        if ($sc->db_type == sql_db::MYSQL and !$usr_tbl) {
            $sql .= ' ' . sql::LAST_ID_MYSQL . $sc->var_name_row_id($sc_par_lst_sub) . '; ';
        }

        $qp->sql = $qp_lnk->sql . ' ' . $sql;
        $qp->par_fld_lst = $qp_lnk->par_fld_lst;
        $qp->par_fld = $fvt_insert;

        return $qp;
    }


    /*
     * sql write fields
     */

    /**
     * get a list of all database fields that might be changed of the named link object
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
                $this->name_field(),
                sql_db::FLD_DESCRIPTION
            ]);
    }

    /**
     * get a list of database field names, values and types that have been updated
     * of the object to combine the list with the list of the child object e.g. word
     *
     * @param sandbox|sandbox_link_named $sbx the same named sandbox as this to compare which fields have been changed
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list with the field names of the object and any child object
     */
    function db_fields_changed(
        sandbox|sandbox_link_named $sbx,
        sql_type_list $sc_par_lst = new sql_type_list()
    ): sql_par_field_list
    {
        global $cng_fld_cac;

        $sc = new sql_creator();
        $do_log = $sc_par_lst->incl_log();
        $table_id = $sc->table_id($this::class);

        $lst = parent::db_fields_changed($sbx, $sc_par_lst);
        // for insert statements of user sandbox rows user id fields always needs to be included
        $lst->add_name_and_description($this, $sbx, $do_log, $table_id);
        return $lst;
    }


    /*
     * settings
     */

    /**
     * @return bool true if this sandbox object has a name as unique key
     * final function overwritten by the child object
     */
        function is_named_obj(): bool
    {
        return true;
    }

}