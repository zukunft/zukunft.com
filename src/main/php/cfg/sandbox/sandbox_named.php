<?php

/*

    model/sandbox/sandbox_named.php - the superclass for handling user specific named objects including the database saving
    -------------------------------

    This superclass should be used by the classes words, formula, ... to enable user specific values and links

    The main sections of this object are
    - db const:          const for the database link
    - object vars:       the variables of this word object
    - construct and map: including the mapping of the db row to this word object
    - api:               create an api array for the frontend and set the vars based on a frontend api message
    - set and get:       to capsule the vars from unexpected changes
    - modify:            change potentially all variables of this sandbox object
    - cast:              create an api object and set the vars from an api json
    - load:              database access object (DAO) functions
    - load sql:          create the sql statements for loading from the db
    - im- and export:    create an export object and set the vars from an import object
    - information:       functions to make code easier to read
    - log read:          read related log messages
    - log write:         write changes to the log table
    - add:               insert a new row the database
    - save helper:       to support updating the database
    - sql write:         sql statement creation to write to the database
    - sql write fields:  field list for writing to the database
    - internal:          e.g. to generate the name based on the link
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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace cfg\sandbox;

include_once MODEL_SANDBOX_PATH . 'sandbox.php';

include_once SHARED_ENUM_PATH . 'messages.php';
include_once DB_PATH . 'sql.php';
include_once DB_PATH . 'sql_creator.php';
include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_field_type.php';
include_once DB_PATH . 'sql_par.php';
include_once DB_PATH . 'sql_par_field_list.php';
include_once DB_PATH . 'sql_par_type.php';
include_once DB_PATH . 'sql_type.php';
include_once DB_PATH . 'sql_type_list.php';
include_once MODEL_HELPER_PATH . 'data_object.php';
include_once MODEL_HELPER_PATH . 'db_object_seq_id.php';
//include_once MODEL_FORMULA_PATH . 'formula.php';
include_once MODEL_LOG_PATH . 'change.php';
include_once MODEL_LOG_PATH . 'change_action.php';
//include_once MODEL_LOG_PATH . 'change_link.php';
//include_once MODEL_LOG_PATH . 'change_log_list.php';
//include_once MODEL_PHRASE_PATH . 'phrase.php';
//include_once MODEL_PHRASE_PATH . 'term.php';
//include_once MODEL_WORD_PATH . 'triple.php';
include_once MODEL_USER_PATH . 'user.php';
include_once MODEL_USER_PATH . 'user_message.php';
include_once MODEL_VERB_PATH . 'verb.php';
//include_once MODEL_WORD_PATH . 'word.php';
include_once SHARED_ENUM_PATH . 'change_actions.php';
include_once SHARED_TYPES_PATH . 'api_type_list.php';
include_once SHARED_PATH . 'json_fields.php';
include_once SHARED_PATH . 'library.php';

use cfg\db\sql;
use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\db\sql_field_type;
use cfg\db\sql_par;
use cfg\db\sql_par_field_list;
use cfg\db\sql_par_type;
use cfg\db\sql_type;
use cfg\db\sql_type_list;
use cfg\formula\formula;
use cfg\helper\data_object;
use cfg\helper\db_object_seq_id;
use cfg\log\change;
use cfg\log\change_link;
use cfg\log\change_log_list;
use cfg\phrase\phrase;
use cfg\phrase\term;
use cfg\user\user;
use cfg\user\user_message;
use cfg\verb\verb;
use cfg\word\triple;
use cfg\word\word;
use Exception;
use shared\enum\change_actions;
use shared\enum\messages as msg_id;
use shared\json_fields;
use shared\library;
use shared\types\api_type_list;

class sandbox_named extends sandbox
{

    /*
     * db const
     */

    // object specific database and JSON object field names
    // *_SQL_TYP is the sql data type used for the field
    const FLD_NAME = 'name';
    const FLD_NAME_SQL_TYP = sql_field_type::NAME; // in many cases overwritten by NAME_UNIQUE
    const FLD_DESCRIPTION = 'description';
    const FLD_DESCRIPTION_SQL_TYP = sql_field_type::TEXT;


    /*
     * object vars
     */

    // database fields only used for objects that have a name
    protected string $name = '';        // simply the object name, which cannot be empty if it is a named object
    public ?string $description = null; // the object description that is shown as a mouseover explain to the user
    //                                     if description is NULL the database value should not be updated


    /*
     * construct and map
     */

    /**
     * reset the search values of this object
     * needed to search for the standard object, because the search is work, value, formula or ... specific
     */
    function reset(): void
    {
        parent::reset();

        $this->set_name('');
        $this->description = null;
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
            $this->set_name($db_row[$name_fld]);
            if (array_key_exists(self::FLD_DESCRIPTION, $db_row)) {
                $this->description = $db_row[self::FLD_DESCRIPTION];
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
        $msg = parent::api_mapper($api_json);

        foreach ($api_json as $key => $value) {
            if ($key == json_fields::NAME) {
                $this->set_name($value);
            }
            if ($key == json_fields::DESCRIPTION) {
                if ($value <> '') {
                    $this->description = $value;
                }
            }
        }
        return $msg;
    }

    /**
     * function to import the core user sandbox object values from a json string
     * e.g. the share and protection settings
     *
     * @param array $in_ex_json an array with the data of the json object
     * @param data_object|null $dto cache of the objects imported until now for the primary references
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_mapper(array $in_ex_json, data_object $dto = null, object $test_obj = null): user_message
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

        $vars[json_fields::NAME] = $this->name();
        $vars[json_fields::DESCRIPTION] = $this->description();

        return $vars;
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
        $vars[json_fields::NAME] = $this->name();
        if ($this->description <> '') {
            $vars[json_fields::DESCRIPTION] = $this->description;
        }
        return $vars;
    }


    /*
     * set and get
     */

    /**
     * set the most used object vars with one set statement
     * @param int $id mainly for test creation the database id of the named user sandbox object
     * @param string $name mainly for test creation the name of the named user sandbox object
     * @param string $type_code_id the code id of the predefined object type only used by some child objects
     */
    function set(int $id = 0, string $name = '', string $type_code_id = ''): void
    {
        parent::set_id($id);

        if ($name != '') {
            $this->set_name($name);
        }
    }

    /**
     * set the name of this named user sandbox object
     * set and get of the name is needed to use the same function for phrase or term
     *
     * @param string $name the name of this named user sandbox object e.g. word set in the related object
     * @return user_message
     */
    function set_name(string $name): user_message
    {
        $usr_msg = new user_message();
        if (trim($name) <> $name) {
            $usr_msg->add_id_with_vars(msg_id::TRIM_NAME,
                [msg_id::VAR_NAME => $name]);
            $name = trim($name);
        }
        $this->name = $name;
        return $usr_msg;
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
     * set the description of this named user sandbox object which explains the object for the user
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
     * get the description of the sandbox object
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
     * create a clone and update the name (mainly used for unit testing)
     * but keep the id a unique db id
     *
     * @param string $name the target name
     * @return $this a clone with the name changed
     */
    function cloned(string $name): sandbox_named
    {
        $obj_cpy = $this->clone_reset();
        $obj_cpy->set_id($this->id());
        $obj_cpy->set_name($name);
        return $obj_cpy;
    }


    /*
     * modify
     */

    /**
     * fill this sandbox object based on the given object
     * if the given description is not set (null) the description is not removed
     * if the given description is an empty string (not null) the description is removed
     *
     * @param sandbox_named|db_object_seq_id $sbx sandbox object with the values that should be updated e.g. based on the import
     * @return user_message a warning in case of a conflict e.g. due to a missing change time
     */
    function fill(sandbox_named|db_object_seq_id $sbx): user_message
    {
        $usr_msg = parent::fill($sbx);
        if ($sbx->name() != null) {
            $this->set_name($sbx->name());
        }
        if ($sbx->description() != null) {
            $this->set_description($sbx->description());
        }
        return $usr_msg;
    }


    /*
     * cast
     */

    /**
     * get the term corresponding to this word or formula name
     * so in this case, if a formula or verb with the same name already exists, get it
     * @return term
     */
    function term(): term
    {
        $trm = new term($this->user());
        $trm->set_id($this->id());
        $trm->set_obj($this);
        return $trm;
    }

    /**
     * get the term corresponding to this word or formula name
     * so in this case, if a formula or verb with the same name already exists, get it
     * @return term
     */
    function get_term(): term
    {
        $trm = new term($this->user());
        $trm->load_by_name($this->name());
        return $trm;
    }

    /**
     * @param object $api_obj frontend API objects that should be filled with unique object name
     */
    function fill_api_obj(object $api_obj): void
    {
        parent::fill_api_obj($api_obj);

        $api_obj->set_name($this->name());
        $api_obj->description = $this->description;
    }


    /*
     * load
     */

    /**
     * load a named user sandbox object by name
     * @param string $name the name of the word, triple, formula, verb, view or view component
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_name(string $name): int
    {
        global $db_con;

        log_debug($name);
        $qp = $this->load_sql_by_name($db_con->sql_creator(), $name);
        return parent::load($qp);
    }

    /**
     * only to suppress the polymorphic warning and to be overwritten by the child objects
     * @param string $code_id
     * @return int zero if not overwritten by the child object to indicate the internal error
     */
    function load_by_code_id(string $code_id): int
    {
        log_err($this::class . ' does not have a load_by_code_id function');
        return 0;
    }

    /**
     * load the object parameters for all users
     * @param sql_par|null $qp the query parameter created by the function of the child object e.g. word->load_standard
     * @return bool true if the standard object has been loaded
     */
    function load_standard(?sql_par $qp = null): bool
    {
        global $db_con;
        $result = false;

        if ($this->id() == 0 and $this->name() == '') {
            log_err('The ' . $this::class . ' id or name must be set to load ' . $this::class, $this::class . '->load_standard');
        } else {
            $db_row = $db_con->get1($qp);
            $result = $this->row_mapper_sandbox($db_row, true);
        }
        return $result;
    }


    /*
     * load sql
     */

    /**
     * create an SQL statement to retrieve a term by name from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $name the name of the term and the related word, triple, formula or verb
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_name(sql_creator $sc, string $name): sql_par
    {
        $qp = $this->load_sql($sc, sql_db::FLD_NAME);
        $sc->add_where($this->name_field(), $name, sql_par_type::TEXT_USR);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create the SQL to load the single default value always by the id or name
     * @param sql_creator $sc with the target db_type set
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_standard_sql(sql_creator $sc): sql_par
    {
        $qp = new sql_par($this::class, new sql_type_list([sql_type::NORM]));
        if ($this->id() != 0) {
            $qp->name .= sql_db::FLD_ID;
        } elseif ($this->name() != '') {
            $qp->name .= sql_db::FLD_NAME;
        } else {
            log_err('Either the id or name must be set to get a named user sandbox object');
        }

        $sc->set_name($qp->name);
        $sc->set_usr($this->user()->id());
        if ($this->id() != 0) {
            $sc->add_where($this->id_field(), $this->id());
        } else {
            $sc->add_where($this->name_field(), $this->name());
        }
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }


    /*
     * information
     */

    /**
     * check if the named object in the database needs to be updated
     *
     * @param sandbox_named $db_obj the word as saved in the database
     * @return bool true if this word has infos that should be saved in the database
     */
    function needs_db_update_named(sandbox_named $db_obj): bool
    {
        $result = parent::needs_db_update_sandbox($db_obj);
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
        return $result;
    }

    /**
     * check if the named sandbox object can be added to the database
     * @return user_message including suggested solutions
     *       if e.g. the id and the name is something
     */
    function db_ready(): user_message
    {
        $usr_msg = parent::db_ready();
        if ($this->id() == 0) {
            if ($this->name() == '') {
                $usr_msg->add_id(msg_id::ID_AND_NAME_MISSING);
            }
        }
        return $usr_msg;
    }

    /**
     * @return bool true if the triple object probably has been added to the database
     *              false e.g. if some parameters ar missing
     */
    function is_valid(): bool
    {
        if ($this->id() != 0 and $this->name() != '') {
            return true;
        } else {
            return false;
        }
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
     * log write
     */

    /**
     * set the log entry parameter for a new named object
     * for all not named objects like links, this function is overwritten
     * e.g. that the user can see "added formula 'scale millions' to word 'mio'"
     */
    function log_add(): change
    {
        log_debug($this->dsp_id());
        $lib = new library();
        $tbl_name = $lib->class_to_name($this::class);

        $log = new change($this->user());
        // TODO add the table exceptions from sql_db
        $log->set_action(change_actions::ADD);
        $log->set_table($tbl_name . sql_db::TABLE_EXTENSION);
        $log->set_field($tbl_name . '_name');
        $log->set_user($this->user());
        $log->old_value = null;
        $log->new_value = $this->name();
        $log->row_id = 0;
        $log->add();

        return $log;
    }

    /**
     * set the log entry parameter to delete an object
     * @returns change_link with the object presets e.g. th object name
     */
    function log_del(): change
    {
        log_debug($this->dsp_id());
        $lib = new library();
        $tbl_name = $lib->class_to_name($this::class);

        $log = new change($this->user());
        $log->set_action(change_actions::DELETE);
        $log->set_table($tbl_name . sql_db::TABLE_EXTENSION);
        $log->set_field($tbl_name . '_name');
        $log->old_value = $this->name();
        $log->new_value = null;

        $log->row_id = $this->id();
        $log->add();

        return $log;
    }


    /*
     * add
     */

    /**
     * create a new named object
     *
     * @param bool $use_func if true a predefined function is used that also creates the log entries
     * @return user_message with status ok
     *                      or if something went wrong
     *                      the message that should be shown to the user
     *                      including suggested solutions
     * TODO do a rollback in case of an
     * TODO used prepared sql_insert for all fields
     * TODO use optional sql insert with log
     * TODO use prepared sql insert
     */
    function add(bool $use_func = false): user_message
    {
        log_debug($this->dsp_id());

        global $db_con;
        $usr_msg = new user_message();

        if ($use_func) {
            $sc = $db_con->sql_creator();
            $qp = $this->sql_insert($sc, new sql_type_list([sql_type::LOG]));
            $ins_msg = $db_con->insert($qp, 'add and log ' . $this->dsp_id());
            if ($ins_msg->is_ok()) {
                $this->set_id($ins_msg->get_row_id());
            }
            $usr_msg->add($ins_msg);
        } else {

            // log the insert attempt first
            $log = $this->log_add();
            if ($log->id() > 0) {

                // insert the new object and save the object key
                // TODO check that always before a db action is called the db type is set correctly
                if ($this->sql_write_prepared()) {
                    $sc = $db_con->sql_creator();
                    $qp = $this->sql_insert($sc);
                    $ins_msg = $db_con->insert($qp, 'add ' . $this->dsp_id());
                    if ($ins_msg->is_ok()) {
                        $this->set_id($ins_msg->get_row_id());
                    }
                } else {
                    $lib = new library();
                    $class_name = $lib->class_to_name($this::class);
                    $db_con->set_class($this::class);
                    $db_con->set_usr($this->user()->id());
                    $this->set_id(
                        $db_con->insert_old(
                            array($class_name . '_name', user::FLD_ID), array($this->name, $this->user()->id())));
                }

                // save the object fields if saving the key was successful
                if ($this->id() > 0) {
                    log_debug($this::class . ' ' . $this->dsp_id() . ' has been added');
                    // update the id in the log
                    if (!$log->add_ref($this->id())) {
                        $usr_msg->add_message('Updating the reference in the log failed');
                        // TODO do rollback or retry?
                    } else {
                        //$usr_msg->add_message($this->set_owner($new_owner_id));

                        // TODO all all objects to the potential used of the prepared sql function with log
                        if (!$this->sql_write_prepared()) {
                            // create an empty db_rec element to force saving of all set fields
                            $db_rec = clone $this;
                            $db_rec->reset();
                            $db_rec->name = $this->name();
                            $db_rec->set_user($this->user());
                            $std_rec = clone $db_rec;
                            // save the object fields
                            $usr_msg->add($this->save_all_fields($db_con, $db_rec, $std_rec));
                        }
                    }

                } else {
                    $lib = new library();
                    $class_name = $lib->class_to_name($this::class);
                    $usr_msg->add_message('Adding ' . $class_name . ' ' . $this->dsp_id() . ' failed due to logging error.');
                }
            }
        }

        return $usr_msg;
    }


    /*
     * save helper
     */

    /**
     * preform the pre save checks which means
     * for these named objects check if the user has requested to use a preserved name
     * and if yes return a message and a suggested solution to the user
     *
     * @return user_message
     */
    protected function check_save(): user_message
    {
        return $this->check_preserved();
    }

    /**
     * check if the user has requested to use a preserved name for the sandbox object
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
        $msg_res = $mtr->txt(msg_id::IS_RESERVED);
        $msg_for = $mtr->txt(msg_id::RESERVED_NAME);
        $lib = new library();
        $class_name = $lib->class_to_name($this::class);

        // system users are always allowed to add objects e.g. for the system views
        if (!$usr->is_system()) {
            if (in_array($this->name(), $this->reserved_names())) {
                // the admin user needs to add the read test objects during initial load
                if ($usr->is_admin() and !in_array($this->name(), $this->fixed_names())) {
                    $usr_msg->add_message('"' . $this->name() . '" ' . $msg_res . ' ' . $class_name . ' ' . $msg_for);
                }
            }
        }
        return $usr_msg;
    }

    /**
     * @return array with the reserved names of the child object
     */
    protected function reserved_names(): array
    {
        log_err('The dummy parent method reserved_names has been called, which should never happen');
        return [];
    }

    /**
     * @return array with the fixed names of the child object for db read testing
     */
    protected function fixed_names(): array
    {
        log_err('The dummy parent method fixed_names has been called, which should never happen');
        return [];
    }

    /**
     * check if the id parameters are supposed to be changed
     * TODO add the link type for word links
     * @param sandbox $db_rec the object data as it is now in the database
     * @return bool true if one of the object id fields have been changed
     */
    function is_id_updated(sandbox $db_rec): bool
    {
        $result = False;
        log_debug($this->dsp_id());

        log_debug('compare name ' . $db_rec->name() . ' with ' . $this->name());
        if ($db_rec->name() <> $this->name()) {
            $result = True;
        }

        log_debug(zu_dsp_bool($result));
        return $result;
    }

    /**
     * @return string text that request the user to use another name
     */
    function msg_id_already_used(): string
    {
        $lib = new library();
        return 'A ' . $lib->class_to_name($this::class) . ' with the name "' . $this->name() . '" already exists. Please use another name.';
    }

    /**
     * set the update parameters for the named object description
     * similar to the function with the same name in sandbox_link_named,
     * but because php 8.1 does not yet allow extends parent_class_a, parent_class_b needs to be repeated
     *
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param sandbox_named $db_rec the database record before the saving
     * @param sandbox_named $std_rec the database record defined as standard because it is used by most users
     * @return user_message the message that should be shown to the user in case something went wrong
     */
    function save_field_description(sql_db $db_con, sandbox_named $db_rec, sandbox_named $std_rec): user_message
    {
        $usr_msg = new user_message();
        // if the description is not set, don't overwrite any db entry
        if ($this->description <> Null) {
            if ($this->description <> $db_rec->description) {
                $log = $this->log_upd();
                $log->old_value = $db_rec->description;
                $log->new_value = $this->description;
                $log->std_value = $std_rec->description;
                $log->row_id = $this->id();
                $log->set_field(self::FLD_DESCRIPTION);
                $usr_msg->add($this->save_field_user($db_con, $log));
            }
        }
        return $usr_msg;
    }

    /**
     * save all updated source fields excluding the name, because already done when adding a source
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param sandbox_named $db_rec the database record before the saving
     * @param sandbox_named $std_rec the database record defined as standard because it is used by most users
     * @return user_message the message that should be shown to the user in case something went wrong
     */
    function save_fields_named(sql_db $db_con, sandbox_named $db_rec, sandbox_named $std_rec): user_message
    {
        $usr_msg = $this->save_field_description($db_con, $db_rec, $std_rec);
        $usr_msg->add($this->save_field_excluded($db_con, $db_rec, $std_rec));
        return $usr_msg;
    }

    /**
     * updated the object id fields (e.g. for a word or formula the name, and for a link the linked ids)
     * should only be called if the user is the owner and nobody has used the display component link
     * @param sql_db $db_con the active database connection
     * @param sandbox $db_rec the database record before the saving
     * @param sandbox $std_rec the database record defined as standard because it is used by most users
     * @returns string either the id of the updated or created source or a message to the user with the reason, why it has failed
     * @throws Exception
     */
    function save_id_fields(sql_db $db_con, sandbox $db_rec, sandbox $std_rec): string
    {
        $result = '';
        log_debug($this->dsp_id());
        $lib = new library();
        $tbl_name = $lib->class_to_name($this::class);

        if ($this->is_id_updated($db_rec)) {
            log_debug('to ' . $this->dsp_id() . ' from ' . $db_rec->dsp_id() . ' (standard ' . $std_rec->dsp_id() . ')');

            $log = $this->log_upd_field();
            $log->old_value = $db_rec->name();
            $log->new_value = $this->name();
            $log->std_value = $std_rec->name();
            $log->set_field($tbl_name . '_name');

            $log->row_id = $this->id();
            if ($log->add()) {
                // TODO activate when the prepared SQL is ready to use
                // only do the update here if the update is not done with one sql statement at the end
                if ($this->sql_write_prepared()) {
                    $qp = $this->sql_update($db_con->sql_creator(), $db_rec, new sql_type_list());
                    $usr_msg = $db_con->update($qp, $this::class . ' update name');
                    $result = $usr_msg->get_message();
                } else {
                    $db_con->set_class($this::class);
                    $db_con->set_usr($this->user()->id());
                    if (!$db_con->update_old($this->id(),
                        array($tbl_name . '_name'),
                        array($this->name))) {
                        $result .= 'update of name to ' . $this->name() . 'failed';
                    }
                }
            }
        }
        log_debug('for ' . $this->dsp_id() . ' done');
        return $result;
    }

    /**
     * check if the unique key (not the db id) of two user sandbox object is the same if the object type is the same, so the simple case
     * @param object $obj_to_check the object used for the comparison
     * @return bool true if the objects have the same unique name
     */
    function is_same_std(object $obj_to_check): bool
    {
        if ($this->name == $obj_to_check->name) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * just to double-check if the get similar function is working correctly
     * so if the formulas "millions" is compared with the word "millions" this function returns true
     * in short: if two objects are similar by this definition, they should not be both in the database
     * @param null|object $obj_to_check the object used for the comparison
     * @return bool true if the objects represent the same
     */
    function is_similar_named(?object $obj_to_check): bool
    {
        $result = false;
        if ($obj_to_check != null) {
            if ($this::class == $obj_to_check::class) {
                $result = $this->is_same_std($obj_to_check);
            } else {
                // create a synthetic unique index over words, phrase, verbs and formulas
                if ($this::class == word::class or $this::class == phrase::class or $this::class == formula::class or $this::class == verb::class) {
                    if ($this->name == $obj_to_check->name()) {
                        $result = true;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * TODO call this check also if a named sandbox object is renamed
     * check if an object with the unique key already exists
     * returns null if no similar object is found
     * or returns the object with the same unique key that is not the actual object
     * any warning or error message needs to be created in the calling function
     * e.g. if the user tries to create a formula named "millions"
     *      but a word with the same name already exists, a term with the word "millions" is returned
     *      in this case the calling function should suggest the user to name the formula "scale millions"
     *      to prevent confusion when writing a formula where all words, phrases, verbs and formulas should be unique
     * @return sandbox a filled object that has the same name
     *                 or a sandbox object with id() = 0 if nothing similar has been found
     */
    function get_similar(): sandbox
    {
        $result = new sandbox_named($this->user());

        // check potential duplicate by name
        // for words and formulas it needs to be checked if a term (word, verb or formula) with the same name already exist
        // for verbs the check is inside the verbs class because verbs are not part of the user sandbox
        if ($this::class == word::class
            or $this::class == verb::class
            or $this::class == triple::class
            or $this::class == formula::class) {
            $similar_trm = $this->get_term();
            if ($similar_trm->id_obj() > 0) {
                $result = $similar_trm->obj();
                if (!$this->is_similar_named($result)) {
                    log_err($this->dsp_id() . ' is supposed to be similar to ' . $result->dsp_id() . ', but it seems not');
                }
            } else {
                $similar_trp = new triple($this->user());
                $similar_trp->load_by_name_generated($this->name());
                if ($similar_trp->id() > 0) {
                    $similar_trp->load_objects();
                    log_debug($this->dsp_id() . ' has the same name is the standard name of the triple "' . $similar_trp->dsp_id() . '"');
                    $result = $similar_trp;
                }
            }
        } else {
            // used for view, component, source, ...
            $db_chk = clone $this;
            $db_chk->reset();
            $db_chk->set_user($this->user());
            $db_chk->name = $this->name();
            // check with the standard namespace
            if ($db_chk->load_standard()) {
                if ($db_chk->id() > 0) {
                    log_debug($this->dsp_id() . ' has the same name is the already existing "' . $db_chk->dsp_id() . '" of the standard namespace');
                    $result = $db_chk;
                }
            }
            // check with the user namespace
            $db_chk->set_user($this->user());
            if ($this::class == change::class) {
                // TODO check if it is working with build in tests
                if ($db_chk->load_by_id($this->id())) {
                    if ($db_chk->id() > 0) {
                        log_debug($this->dsp_id() . ' has the same name is the already existing "' . $db_chk->dsp_id() . '" of the user namespace');
                        $result = $db_chk;
                    }
                }
            } else {
                if ($this->name() != '') {
                    if ($db_chk->load_by_name($this->name())) {
                        if ($db_chk->id() > 0) {
                            log_debug($this->dsp_id() . ' has the same name is the already existing "' . $db_chk->dsp_id() . '" of the user namespace');
                            $result = $db_chk;
                        }
                    }
                } else {
                    log_err('The name must be set to check if a similar object exists');
                }
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
     * TODO check if it can be merged with the sandbox function
     *
     * @param sql_creator $sc with the target db_type set
     * @param sql_par_field_list $fvt_lst list of field names, values and sql types additional to the standard id and name fields
     * @param array $fld_lst_all list of field names of the given object
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement and the parameter list
     */
    function sql_insert_switch(
        sql_creator        $sc,
        sql_par_field_list $fvt_lst,
        array              $fld_lst_all = [],
        sql_type_list      $sc_par_lst = new sql_type_list()): sql_par
    {
        // make the query name unique based on the changed fields
        $lib = new library();
        $ext = sql::NAME_SEP . $lib->sql_field_ext($fvt_lst, $fld_lst_all);

        // create the main query parameter object and set the query name
        $qp = $this->sql_common($sc, $sc_par_lst, $ext);

        if ($sc_par_lst->incl_log()) {
            // log functions must always use named parameters
            $sc_par_lst->add(sql_type::NAMED_PAR);
            $qp = $this->sql_insert_with_log($sc, $qp, $fvt_lst, $fld_lst_all, $sc_par_lst);
        } else {
            // add the child object specific fields and values
            $qp->sql = $sc->create_sql_insert($fvt_lst);
            $qp->par = $fvt_lst->db_values();
        }

        return $qp;
    }

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
        $fvt_insert = $fvt_lst->get($this->name_field());

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
     * get a list of all database fields that might be changed by the user
     * excluding the internal fields e.g. the database id
     * field list must be corresponding to the db_fields_changed fields
     *
     * @param sql_type_list $sc_par_lst only used for link objects
     * @return array list of all database field names that have been updated
     */
    function db_fields_all(sql_type_list $sc_par_lst = new sql_type_list()): array
    {
        return [
            $this::FLD_ID,
            user::FLD_ID,
            $this->name_field(),
            self::FLD_DESCRIPTION
        ];
    }

    /**
     * get a list of database field names, values and types that have been updated
     * of the object to combine the list with the list of the child object e.g. word
     *
     * @param sandbox $sbx the same named sandbox as this to compare which fields have been changed
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list with the field names of the object and any child object
     */
    function db_fields_changed(
        sandbox       $sbx,
        sql_type_list $sc_par_lst = new sql_type_list()
    ): sql_par_field_list
    {
        $lst = new sql_par_field_list();
        $sc = new sql_creator();
        $usr_tbl = $sc_par_lst->is_usr_tbl();
        $is_insert = $sc_par_lst->is_insert();
        $do_log = $sc_par_lst->incl_log();
        $table_id = $sc->table_id($this::class);

        // for insert statements of user sandbox rows user id fields always needs to be included
        if ($is_insert and $usr_tbl) {
            $lst->add_id_and_user($this);
        } else {
            $lst->add_user($this, $sbx, $do_log, $table_id);
        }
        $lst->add_name_and_description($this, $sbx, $do_log, $table_id);
        return $lst;
    }


    /*
     * internal
     */

    /**
     * @return bool true if this sandbox object has a name as unique key (final function)
     */
    function is_named_obj(): bool
    {
        return true;
    }


    /*
     * debug
     */

    /**
     * @param bool $full false if a short version e.g. for lists should be returned
     * @return string the best possible identification for this object mainly used for debugging
     */
    function dsp_id(bool $full = true): string
    {
        $result = '';
        if ($this->name() <> '') {
            $result .= '"' . $this->name() . '"';
        }
        $result .= parent::dsp_id();
        if ($full) {
            $result .= $this->dsp_id_user();
        }
        return $result;
    }

}


