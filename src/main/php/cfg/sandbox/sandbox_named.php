<?php

/*

    model/sandbox/sandbox_named.php - the superclass for handling user specific named objects including the database saving
    -------------------------------

    This superclass should be used by the classes words, formula, ... to enable user specific values and links

    The main sections of this object are
    - db const:          const for the database link
    - object vars:       the variables of this word object
    - construct and map: including the mapping of the db row to this word object
    - set and get:       to capsule the vars from unexpected changes
    - cast:              create an api object and set the vars from an api json
    - load:              database access object (DAO) functions
    - im- and export:    create an export object and set the vars from an import object
    - information:       functions to make code easier to read
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

namespace cfg;

include_once DB_PATH . 'sql_type.php';
include_once DB_PATH . 'sql_type_list.php';
include_once DB_PATH . 'sql_par_type.php';
include_once DB_PATH . 'sql_par_field.php';
include_once DB_PATH . 'sql_par_field_list.php';
include_once MODEL_SANDBOX_PATH . 'sandbox.php';
include_once API_FORMULA_PATH . 'formula.php';
include_once API_PHRASE_PATH . 'phrase.php';
include_once API_REF_PATH . 'source.php';
include_once API_VIEW_PATH . 'view.php';
include_once API_COMPONENT_PATH . 'component.php';
include_once API_WORD_PATH . 'word.php';

use api\component\component as component_api;
use api\formula\formula as formula_api;
use api\phrase\phrase as phrase_api;
use api\ref\source as source_api;
use api\view\view as view_api;
use api\word\word as word_api;
use cfg\component\component;
use cfg\db\sql;
use cfg\db\sql_db;
use cfg\db\sql_field_type;
use cfg\db\sql_par;
use cfg\db\sql_par_field_list;
use cfg\db\sql_par_type;
use cfg\db\sql_type;
use cfg\db\sql_type_list;
use cfg\export\sandbox_exp;
use cfg\log\change;
use cfg\log\change_action;
use cfg\log\change_link;
use Exception;
use shared\library;

class sandbox_named extends sandbox
{

    /*
     * db const
     */

    // object specific database and JSON object field names
    // *_SQLTYP is the sql data type used for the field
    const FLD_NAME = 'name';
    const FLD_NAME_SQLTYP = sql_field_type::NAME; // in many cases overwritten by NAME_UNIQUE
    const FLD_DESCRIPTION = 'description';
    const FLD_DESCRIPTION_SQLTYP = sql_field_type::TEXT;


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
     * @param bool $load_std true if only the standard user sandbox object ist loaded
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
     *
     * @return string|null the description from the object e.g. word using the same function as the phrase and term
     */
    function description(): ?string
    {
        return $this->description;
    }

    /**
     * create a clone and update the name (mainly used for unit testing)
     *
     * @param string $name the target name
     * @return $this a clone with the name changed
     */
    function cloned(string $name): sandbox_named
    {
        $obj_cpy = $this->clone_reset();
        $obj_cpy->set_name($name);
        return $obj_cpy;
    }

    /**
     * create a clone and empty all fields
     *
     * @return $this a clone with the name changed
     */
    function clone_reset(): sandbox_named
    {
        $obj_cpy = clone $this;
        $obj_cpy->reset();
        return $obj_cpy;
    }

    /**
     * @return array with the field names of the object and any child object
     *         is a function and not a const because the id and name fields are a function and php does not yet have final functions
     */
    function field_list_named(): array
    {
        return [
            user::FLD_ID,
            $this->name_field(),
            self::FLD_DESCRIPTION,
            sandbox::FLD_EXCLUDED,
            sandbox::FLD_SHARE,
            sandbox::FLD_PROTECT
        ];
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

    /**
     * fill a similar object that is extended with display interface functions
     * TODO base on the api object and deprecate
     *
     * @param object $dsp_obj the object that should be filled with all user sandbox value
     */
    function fill_dsp_obj(object $dsp_obj): void
    {
        parent::fill_dsp_obj($dsp_obj);

        $dsp_obj->set_name($this->name());
        $dsp_obj->description = $this->description;
    }


    /*
     * load
     */

    /**
     * create the SQL to load the single default value always by the id or name
     * @param sql $sc with the target db_type set
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_standard_sql(sql $sc, string $class = self::class): sql_par
    {
        $qp = new sql_par($class, new sql_type_list([sql_type::NORM]));
        if ($this->id() != 0) {
            $qp->name .= sql_db::FLD_ID;
        } elseif ($this->name() != '') {
            $qp->name .= sql_db::FLD_NAME;
        } else {
            log_err('Either the id or name must be set to get a named user sandbox object');
        }

        $sc->set_name($qp->name);
        $sc->set_usr($this->user()->id);
        if ($this->id != 0) {
            $sc->add_where($this->id_field(), $this->id());
        } else {
            $sc->add_where($this->name_field(), $this->name());
        }
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * load the object parameters for all users
     * @param sql_par|null $qp the query parameter created by the function of the child object e.g. word->load_standard
     * @param string $class the name of the child class from where the call has been triggered
     * @return bool true if the standard object has been loaded
     */
    function load_standard(?sql_par $qp = null, string $class = ''): bool
    {
        global $db_con;
        $result = false;

        if ($this->id == 0 and $this->name() == '') {
            log_err('The ' . $class . ' id or name must be set to load ' . $class, $class . '->load_standard');
        } else {
            $db_row = $db_con->get1($qp);
            $result = $this->row_mapper_sandbox($db_row, true);
        }
        return $result;
    }

    /**
     * create an SQL statement to retrieve a term by name from the database
     *
     * @param sql $sc with the target db_type set
     * @param string $name the name of the term and the related word, triple, formula or verb
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_name(sql $sc, string $name, string $class): sql_par
    {
        $qp = $this->load_sql($sc, sql_db::FLD_NAME, $class);
        $sc->add_where($this->name_field(), $name, sql_par_type::TEXT_USR);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * load a named user sandbox object by name
     * @param string $name the name of the word, triple, formula, verb, view or view component
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_name(string $name): int
    {
        global $db_con;

        log_debug($name);
        $qp = $this->load_sql_by_name($db_con->sql_creator(), $name, $this::class);
        return parent::load($qp);
    }

    /**
     * @return array with the id and name field of the child object
     */
    function main_fields(): array
    {
        return array($this->id_field(), $this->name_field());
    }

    /**
     * dummy function that should always be overwritten by the child object
     * @return string
     */
    function name_field(): string
    {
        return '';
    }


    /*
     * im- and export
     */

    /**
     * function to import the core user sandbox object values from a json string
     * e.g. the share and protection settings
     *
     * @param array $in_ex_json an array with the data of the json object
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_obj(array $in_ex_json, object $test_obj = null): user_message
    {
        $result = parent::import_obj($in_ex_json, $test_obj);
        foreach ($in_ex_json as $key => $value) {
            if ($key == sandbox_exp::FLD_NAME) {
                $this->set_name($value);
            }
            if ($key == sandbox_exp::FLD_DESCRIPTION) {
                if ($value <> '') {
                    $this->description = $value;
                }
            }
        }
        return $result;
    }


    /*
     * information
     */

    /**
     * check if the named object in the database needs to be updated
     *
     * @param sandbox_named $db_obj the word as saved in the database
     * @return bool true if this word has infos that should be saved in the datanase
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


    /*
     * save
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
        $log->action = change_action::ADD;
        $log->set_table($tbl_name . sql_db::TABLE_EXTENSION);
        $log->set_field($tbl_name . '_name');
        $log->set_user($this->user());
        $log->old_value = '';
        $log->new_value = $this->name();
        $log->row_id = 0;
        $log->add();

        return $log;
    }

    /**
     * set the log entry parameter to delete a object
     * @returns change_link with the object presets e.g. th object name
     */
    function log_del(): change
    {
        log_debug($this->dsp_id());
        $lib = new library();
        $tbl_name = $lib->class_to_name($this::class);

        $log = new change($this->user());
        $log->action = change_action::DELETE;
        $log->set_table($tbl_name . sql_db::TABLE_EXTENSION);
        $log->set_field($tbl_name . '_name');
        $log->old_value = $this->name();
        $log->new_value = '';

        $log->row_id = $this->id;
        $log->add();

        return $log;
    }

    /**
     * check if this object uses any preserved names and if return a message to the user
     * TODO move to the single objects
     *
     * @return string
     */
    protected function check_preserved(): string
    {
        global $usr;

        // TODO move to languga based messages
        $msg_res = 'is a reserved';
        $msg_for = 'name for system testing. Please use another name';
        $result = '';
        if (!$usr->is_system()) {
            if ($this->is_named_obj()) {
                if ($this::class == word::class) {
                    if (in_array($this->name, word_api::RESERVED_WORDS)) {
                        // the admin user needs to add the read test word during initial load
                        if (!$usr->is_admin()) {
                            $result = '"' . $this->name() . '" ' . $msg_res . ' ' . $msg_for;
                        }
                    }
                } elseif ($this::class == phrase::class) {
                    if (in_array($this->name, phrase_api::RESERVED_PHRASES)) {
                        $result = '"' . $this->name() . '" ' . $msg_res . ' phrase ' . $msg_for;
                    }
                } elseif ($this::class == formula::class) {
                    if (in_array($this->name, formula_api::RESERVED_FORMULAS)) {
                        if ($usr->is_admin() and $this->name() != formula_api::TN_READ) {
                            $result = '"' . $this->name() . '" ' . $msg_res . ' formula ' . $msg_for;
                        }
                    }
                } elseif ($this::class == view::class) {
                    if (in_array($this->name, view_api::RESERVED_VIEWS)) {
                        if ($usr->is_admin() and $this->name() != view_api::TN_READ) {
                            $result = '"' . $this->name() . '" ' . $msg_res . ' view ' . $msg_for;
                        }
                    }
                } elseif ($this::class == component::class) {
                    if (in_array($this->name, component_api::RESERVED_COMPONENTS)) {
                        if ($usr->is_admin() and $this->name() != component_api::TN_READ) {
                            $result = '"' . $this->name() . '" ' . $msg_res . ' view component ' . $msg_for;
                        }
                    }
                } elseif ($this::class == source::class) {
                    if (in_array($this->name, source_api::RESERVED_SOURCES)) {
                        // the admin user needs to add the read test source during initial load
                        if ($usr->is_admin() and $this->name() != source_api::TN_READ) {
                            $result = '"' . $this->name() . '" ' . $msg_res . ' source ' . $msg_for;
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * create a new named object
     * @return user_message with status ok
     *                      or if something went wrong
     *                      the message that should be shown to the user
     *                      including suggested solutions
     * TODO do a rollback in case of an
     * TODO used prepared sql_insert for all fields
     * TODO use optional sql insert with log
     * TODO use prepared sql insert
     */
    function add(): user_message
    {
        log_debug($this->dsp_id());

        global $db_con;
        $result = new user_message();
        $lib = new library();
        $class_name = $lib->class_to_name($this::class);

        // log the insert attempt first
        $log = $this->log_add();
        if ($log->id() > 0) {

            // insert the new object and save the object key
            // TODO check that always before a db action is called the db type is set correctly
            if ($this->sql_write_prepared()) {
                $sc = $db_con->sql_creator();
                $qp = $this->sql_insert($sc);
                $usr_msg = $db_con->insert($qp, 'add ' . $this->dsp_id());
                if ($usr_msg->is_ok()) {
                    $this->id = $usr_msg->get_row_id();
                }
            } else {
                $db_con->set_class($this::class);
                $db_con->set_usr($this->user()->id);
                $this->id = $db_con->insert_old(array($class_name . '_name', "user_id"), array($this->name, $this->user()->id));
            }

            // save the object fields if saving the key was successful
            if ($this->id > 0) {
                log_debug($this::class . ' ' . $this->dsp_id() . ' has been added');
                // update the id in the log
                if (!$log->add_ref($this->id)) {
                    $result->add_message('Updating the reference in the log failed');
                    // TODO do rollback or retry?
                } else {
                    //$result->add_message($this->set_owner($new_owner_id));

                    // TODO all all objects to the pontential used of the prepared sql function with log
                    if (!$this->sql_write_prepared()) {
                        // create an empty db_rec element to force saving of all set fields
                        $db_rec = clone $this;
                        $db_rec->reset();
                        $db_rec->name = $this->name();
                        $db_rec->set_user($this->user());
                        $std_rec = clone $db_rec;
                        // save the object fields
                        $result->add_message($this->save_fields($db_con, $db_rec, $std_rec));
                    }
                }

            } else {
                $result->add_message('Adding ' . $class_name . ' ' . $this->dsp_id() . ' failed due to logging error.');
            }
        }

        return $result;
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
     * @return string if not empty the message that should be shown to the user
     */
    function save_field_description(sql_db $db_con, sandbox_named $db_rec, sandbox_named $std_rec): string
    {
        $result = '';
        // if the description is not set, don't overwrite any db entry
        if ($this->description <> Null) {
            if ($this->description <> $db_rec->description) {
                $log = $this->log_upd();
                $log->old_value = $db_rec->description;
                $log->new_value = $this->description;
                $log->std_value = $std_rec->description;
                $log->row_id = $this->id;
                $log->set_field(self::FLD_DESCRIPTION);
                $result = $this->save_field_user($db_con, $log);
            }
        }
        return $result;
    }

    /**
     * save all updated source fields excluding the name, because already done when adding a source
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param sandbox_named $db_rec the database record before the saving
     * @param sandbox_named $std_rec the database record defined as standard because it is used by most users
     * @return string if not empty the message that should be shown to the user
     */
    function save_fields_named(sql_db $db_con, sandbox_named $db_rec, sandbox_named $std_rec): string
    {
        $result = $this->save_field_description($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_excluded($db_con, $db_rec, $std_rec);
        return $result;
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

            $log->row_id = $this->id;
            if ($log->add()) {
                // TODO activate when the prepared SQL is ready to use
                // only do the update here if the update is not done with one sql statement at the end
                //if (!$this->sql_write_prepared()) {
                $db_con->set_class($this::class);
                $db_con->set_usr($this->user()->id);
                if (!$db_con->update_old($this->id,
                    array($tbl_name . '_name'),
                    array($this->name))) {
                    $result .= 'update of name to ' . $this->name() . 'failed';
                }
                //}
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
     *
     * @param sql $sc with the target db_type set
     * @param sql_par_field_list $fvt_lst list of field names, values and sql types additional to the standard id and name fields
     * @param array $fld_lst_all list of field names of the given object
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement and the parameter list
     */
    function sql_insert_named(
        sql                $sc,
        sql_par_field_list $fvt_lst,
        array              $fld_lst_all = [],
        sql_type_list      $sc_par_lst = new sql_type_list([])): sql_par
    {

        // TODO deprecate
        $fld_lst = $fvt_lst->names();
        $val_lst = $fvt_lst->values();

        // check the parameters
        $lib = new library();
        if (count($fld_lst) != count($val_lst)) {
            log_err('fields (' . $lib->dsp_array($fld_lst) . ') does not match with values (' . $lib->dsp_array($val_lst) . ')');
        }

        // create the main query parameter object and set the name
        $and_log = $sc_par_lst->and_log();
        $usr_tbl = $sc_par_lst->is_usr_tbl();
        $fld_chg_ext = $lib->sql_field_ext($fld_lst, $fld_lst_all);
        $ext = sql::file_sep . sql::file_insert;
        $ext_sub = $ext;
        if ($and_log) {
            $ext .= sql_type::LOG->extension();
        }
        $ext .= sql::file_sep . $fld_chg_ext;
        $qp = $this->sql_common($sc, $sc_par_lst, $ext);

        if ($sc_par_lst->and_log()) {

            // init the function body
            $sql = $sc->sql_func_start();

            // list of parameters actually used in the function in order of usage
            $par_name_lst = [];
            $par_value_lst = [];
            $par_type_lst = [];

            // don't use the log parameter for the sub queries
            $sc_par_lst_sub = $sc_par_lst->remove(sql_type::LOG);
            $sc_par_lst_sub->add(sql_type::LIST);

            if ($usr_tbl) {
                $insert_tmp_tbl = '';
            } else {
                // create the sql to insert the row
                $key_fld_pos = array_search($this->name_field(), $fld_lst);
                $insert_field = $fld_lst[$key_fld_pos];
                $insert_value = $val_lst[$key_fld_pos];
                $sc_insert = clone $sc;
                $qp_insert = $this->sql_common($sc_insert, $sc_par_lst_sub, $ext_sub);;
                $qp_insert->sql = $sc_insert->create_sql_insert([$insert_field], [$insert_value], [], $sc_par_lst_sub);
                $qp_insert->par = [$insert_value];

                // add the insert row to the function body
                $insert_tmp_tbl = $qp_insert->name;
                $sql .= ' ' . $insert_tmp_tbl . ' ' . sql::AS . ' (';
                $sql .= ' ' . $qp_insert->sql . '), ';
                $par_name_lst[] = $insert_field;
                $par_value_lst[] = $insert_value;
                $par_type_lst[] = sql_par_type::TEXT;
            }
            $id_field = $sc->id_field_name();
            $row_id_val = $insert_tmp_tbl . '.' . $id_field;

            // get the data fields and move the unique db key field to the first entry
            $fld_lst_ex_log = array_intersect($fld_lst, $fld_lst_all);

            if ($usr_tbl) {
                $key_fld_pos = array_search($this->id_field(), $fld_lst_ex_log);
                unset($fld_lst_ex_log[$key_fld_pos]);
                $key_fld_pos = array_search(user::FLD_ID, $fld_lst_ex_log);
                unset($fld_lst_ex_log[$key_fld_pos]);
                $fld_lst_ex_log_and_key = $fld_lst_ex_log;
            } else {
                $key_fld_pos = array_search($this->name_field(), $fld_lst_ex_log);
                unset($fld_lst_ex_log[$key_fld_pos]);
                $fld_lst_ex_log_and_key = array_merge([$insert_field], $fld_lst_ex_log);
            }

            // create the query parameters for the single log entries
            $func_body_change = '';
            foreach ($fld_lst_ex_log_and_key as $fld) {
                if ($func_body_change != '') {
                    $func_body_change .= ', ';
                }
                $log = new change($this->user());
                $log->set_table_by_class($this::class);
                $log->set_field($fld);
                $val_key = array_search($fld, $fld_lst);
                $log->new_value = $val_lst[$val_key];
                $log->old_value = $fvt_lst->get_old($fld);
                // TODO get the id of the new entry and use it in the log
                $sc_log = clone $sc;
                $sc_par_lst_log = $sc_par_lst_sub;
                $sc_par_lst_log->add(sql_type::VALUE_SELECT);
                $sc_par_lst_log->add(sql_type::UPDATE_PART);
                $qp_log = $log->sql_insert(
                    $sc_log, $sc_par_lst_log, $ext_sub . '_' . $fld, $insert_tmp_tbl, $fld, $id_field);

                // TODO get the fields used in the change log sql from the sql
                $func_body_change .= ' ' . $qp_log->name . ' ' . sql::AS . ' (';
                $func_body_change .= ' ' . $qp_log->sql . ')';
                if (!in_array(user::FLD_ID, $par_name_lst)) {
                    $par_name_lst[] = user::FLD_ID;
                    $val_key = array_search(user::FLD_ID, $fld_lst);
                    $par_value_lst[] = $val_lst[$val_key];
                    $par_type_lst[] = sql_par_type::INT;
                }
                if (!in_array(change_action::FLD_ID, $par_name_lst)) {
                    $par_name_lst[] = change_action::FLD_ID;
                    $val_key = array_search(change_action::FLD_ID, $fld_lst);
                    $par_value_lst[] = $val_lst[$val_key];
                    $par_type_lst[] = sql_par_type::INT_SMALL;
                }
                if (!in_array(sql::FLD_LOG_FIELD_PREFIX . $fld, $par_name_lst)) {
                    $par_name_lst[] = sql::FLD_LOG_FIELD_PREFIX . $fld;
                    $val_key = array_search(sql::FLD_LOG_FIELD_PREFIX . $fld, $fld_lst);
                    $par_value_lst[] = $val_lst[$val_key];
                    $par_type_lst[] = sql_par_type::INT_SMALL;
                }
                if (!in_array($fld, $par_name_lst)) {
                    $par_name_lst[] = $fld;
                    $val_key = array_search($fld, $fld_lst);
                    $par_value_lst[] = $val_lst[$val_key];
                    $par_type_lst[] = $sc->get_sql_par_type($val_lst[$val_key]);
                }
                if ($usr_tbl) {
                    if (!in_array($id_field, $par_name_lst)) {
                        $par_name_lst[] = $id_field;
                        $val_key = array_search($id_field, $fld_lst);
                        $par_value_lst[] = $val_lst[$val_key];
                        $par_type_lst[] = $sc->get_sql_par_type($val_lst[$val_key]);
                    }
                }
            }
            $sql .= ' ' . $func_body_change;

            if ($usr_tbl) {
                // insert the value in the user table
                $fld_lst_ex_log_and_key = array_merge([$this->id_field(), user::FLD_ID], $fld_lst_ex_log);
                $insert_values = [];
                foreach ($fld_lst_ex_log_and_key as $fld) {
                    $val_key = array_search($fld, $fld_lst);
                    $insert_values[] = $val_lst[$val_key];
                }
                $sc_insert = clone $sc;
                $qp_insert = $this->sql_common($sc_insert, $sc_par_lst_sub);
                $sc_par_lst_sub->add(sql_type::NO_ID_RETURN);
                $qp_insert->sql = $sc_insert->create_sql_insert($fld_lst_ex_log_and_key, $insert_values, [], $sc_par_lst_sub);
                // add the insert row to the function body and close the with statement with an ;
                $sql .= ' ' . $qp_insert->sql . ';';
            } else {
                // update the fields excluding the unique id
                $update_fields = array_values($fld_lst_ex_log);
                $update_values = [];
                foreach ($fld_lst_ex_log as $fld) {
                    $val_key = array_search($fld, $fld_lst);
                    $update_values[] = $val_lst[$val_key];
                }
                $update_types = [];
                foreach ($update_values as $val) {
                    $update_types[] = $sc->get_sql_par_type($update_values);
                }
                $update_fld_val_typ_lst = [];
                foreach ($update_fields as $key => $field) {
                    $update_fld_val_typ_lst[] = [$field, $update_values[$key], $update_types[$key]];
                }
                $sc_update = clone $sc;
                $sc_par_lst_upd = $sc_par_lst;
                $sc_par_lst_upd->add(sql_type::UPDATE);
                $sc_par_lst_upd_ex_log = $sc_par_lst_upd->remove(sql_type::LOG);
                $sc_par_lst_upd_ex_log->add(sql_type::SUB);
                $qp_update = $this->sql_common($sc_update, $sc_par_lst_upd_ex_log);;
                $update_fvt_lst = new sql_par_field_list();
                $update_fvt_lst->set($update_fld_val_typ_lst);
                $qp_update->sql = $sc_update->create_sql_update(
                    $id_field, $row_id_val, $update_fvt_lst, [], $sc_par_lst_upd_ex_log, true, $insert_tmp_tbl, $id_field);
                // add the insert row to the function body
                $sql .= ' ' . $qp_update->sql . ' ';
            }

            $sql .= $sc->sql_func_end();

            // create the query parameters for the actual change
            $qp_chg = clone $qp;
            $qp_chg->sql = $sc->create_sql_insert($par_name_lst, $par_value_lst, $par_type_lst, $sc_par_lst);
            $qp_chg->par = $val_lst;

            // merge all together and create the function
            $qp->sql = $qp_chg->sql . $sql . ';';

            $qp->call = ' ' . sql::SELECT . ' ' . $qp_chg->name . ' (';
            $i = 0;
            $call_val_str = '';
            $pg_types = $sc->par_types_to_postgres($par_type_lst);

            foreach ($par_value_lst as $par_val) {
                if ($call_val_str != '') {
                    $call_val_str .= ', ';
                }
                $par_typ = $par_type_lst[$i];
                $val_typ = $pg_types[$i];
                if ($par_typ == sql_par_type::TEXT) {
                    $call_val_str .= "'" . $par_val . "'";
                } else {
                    $call_val_str .= $par_val;
                }
                if ($val_typ != '') {
                    $call_val_str .= '::' . $val_typ;
                }
                $i++;
            }
            $qp->call .= $call_val_str . ');';

        } else {
            // add the child object specific fields and values
            $qp->sql = $sc->create_sql_insert($fld_lst, $val_lst);
            $qp->par = $val_lst;
        }

        return $qp;
    }

    /**
     * create the sql statement to change or exclude a named sandbox object e.g. word to the database
     *
     * @param sql $sc with the target db_type set
     * @param sql_par_field_list $fvt_lst list of field names, values and sql types additional to the standard id and name fields
     * @param array $fld_lst_all list of field names of the given object
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL update statement, the name of the SQL statement and the parameter list
     */
    function sql_update_named(
        sql                $sc,
        sql_par_field_list $fvt_lst,
        array              $fld_lst_all = [],
        sql_type_list      $sc_par_lst = new sql_type_list([])): sql_par
    {
        // TODO deprecate
        $val_lst = $fvt_lst->values();

        $lib = new library();
        $and_log = $sc_par_lst->and_log();
        $usr_tbl = $sc_par_lst->is_usr_tbl();
        $fld_lst = $fvt_lst->names();
        $fld_chg_ext = $lib->sql_field_ext($fld_lst, $fld_lst_all);
        $ext = sql::file_sep . sql::file_update;
        if ($and_log) {
            $ext .= sql_type::LOG->extension();
        }
        $ext .= sql::file_sep . $fld_chg_ext;
        $qp = $this->sql_common($sc, $sc_par_lst, $ext);
        if ($and_log) {
            $qp = $this->sql_update_named_and_log($sc, $qp, $fvt_lst, $fld_lst_all, $sc_par_lst);
        } else {
            if ($usr_tbl) {
                $qp->sql = $sc->create_sql_update(
                    [$this->id_field(), user::FLD_ID], [$this->id(), $this->user_id()], $fvt_lst);
            } else {
                $qp->sql = $sc->create_sql_update($this->id_field(), $this->id(), $fvt_lst);
            }
            $qp->par = $val_lst;
        }

        return $qp;
    }

    /**
     * @param sql $sc the sql creator object with the db type set
     * @param sql_par $qp the query parameter with the name already set
     * @param sql_par_field_list $par_lst
     * @param array $fld_lst_all
     * @param sql_type_list $sc_par_lst
     * @return sql_par
     */
    private function sql_update_named_and_log(
        sql                $sc,
        sql_par            $qp,
        sql_par_field_list $par_lst,
        array              $fld_lst_all = [],
        sql_type_list      $sc_par_lst = new sql_type_list([])
    ): sql_par
    {

        // set some var names to shorten the code lines
        $usr_tbl = $sc_par_lst->is_usr_tbl();
        $ext = sql::file_sep . sql::file_insert;
        $id_fld = $sc->id_field_name();
        $id_val = '_' . $id_fld;

        // list of parameters actually used in order of the function usage
        $par_lst_out = new sql_par_field_list();

        // init the function body
        $sql = $sc->sql_func_start();

        // don't use the log parameter for the sub queries
        $sc_par_lst_sub = $sc_par_lst->remove(sql_type::LOG);
        $sc_par_lst_sub->add(sql_type::LIST);

        // get the fields actually changed
        $fld_lst = $par_lst->names();
        $fld_lst_chg = array_intersect($fld_lst, $fld_lst_all);

        // for the user sandbox table remove the primary key fields from the list
        if ($usr_tbl) {
            $key_fld_pos = array_search($id_fld, $fld_lst_chg);
            unset($fld_lst_chg[$key_fld_pos]);
            $key_fld_pos = array_search(user::FLD_ID, $fld_lst_chg);
            unset($fld_lst_chg[$key_fld_pos]);
        }

        // the fields that where the changes should be added to the change log
        $par_lst_chg = $par_lst->intersect($fld_lst_chg);

        // create the queries for the log entries
        $func_body_change = '';
        foreach ($par_lst_chg as $fld) {

            // add the seperator between the single insert log statements
            if ($func_body_change != '') {
                $func_body_change .= ', ';
            }

            // create the insert log statement for the field of the loop
            $log = new change($this->user());
            $log->set_table_by_class($this::class);
            $log->set_field($fld->name);
            $log->old_value = $par_lst->get_old($fld->name);
            $log->new_value = $fld->value;
            // make shure that also overwrites are added to the log
            if ($log->old_value != null or $log->new_value != null) {
                if ($log->old_value == null) {
                    $log->old_value = '';
                }
                if ($log->new_value == null) {
                    $log->new_value = '';
                }
            }

            // TODO get the id of the new entry and use it in the log
            $sc_log = clone $sc;
            $sc_par_lst_log = $sc_par_lst_sub;
            $sc_par_lst_log->add(sql_type::VALUE_SELECT);
            $sc_par_lst_log->add(sql_type::UPDATE_PART);
            $sc_par_lst_log->add(sql_type::SELECT_FOR_INSERT);
            // TODO replace dummy value table with an enum value
            $qp_log = $log->sql_insert(
                $sc_log, $sc_par_lst_log, $ext . '_' . $fld->name, '', $fld->name, $id_fld);

            // TODO get the fields used in the change log sql from the sql
            $func_body_change .= ' ' . $qp_log->name . ' ' . sql::AS . ' (';
            $func_body_change .= ' ' . $qp_log->sql . ')';

            // add the user_id if needed
            $par_lst_out->add_field(
                user::FLD_ID,
                $par_lst->get_value(user::FLD_ID),
                sql_par_type::INT);

            // add the change_action_id if needed
            $par_lst_out->add_field(
                change_action::FLD_ID,
                $par_lst->get_value(change_action::FLD_ID),
                sql_par_type::INT_SMALL);

            // add the field_id of the field actually changed if needed
            $par_lst_out->add_field(
                sql::FLD_LOG_FIELD_PREFIX . $fld->name,
                $par_lst->get_value(sql::FLD_LOG_FIELD_PREFIX . $fld->name),
                sql_par_type::INT_SMALL);

            // add the db field value of the field actually changed if needed
            $par_lst_out->add_field(
                $fld->name . change::FLD_OLD_EXT,
                $par_lst->get_old($fld->name),
                $par_lst->get_type($fld->name));

            // add the field value of the field actually changed if needed
            $par_lst_out->add_field(
                $fld->name,
                $par_lst->get_value($fld->name),
                $par_lst->get_type($fld->name));

            // add the row id of the standard table for user overwrites
            // TODO fix the type
            $par_lst_out->add_field(
                $id_fld,
                $par_lst->get_value($id_fld),
                sql_par_type::INT);
        }
        $sql .= ' ' . $func_body_change;

        if ($usr_tbl) {
            // insert the value in the user table
            $fld_lst_to_log = array_merge([$this->id_field(), user::FLD_ID], $fld_lst_chg);
            $insert_values = [];
            foreach ($fld_lst_to_log as $fld) {
                $insert_values[] = $par_lst->get_value($fld);
            }
            $sc_insert = clone $sc;
            $qp_insert = $this->sql_common($sc_insert, $sc_par_lst_sub);
            $sc_par_lst_sub[] = sql_type::NO_ID_RETURN;
            $qp_insert->sql = $sc_insert->create_sql_insert($fld_lst_to_log, $insert_values, [], $sc_par_lst_sub);
            // add the insert row to the function body and close the with statement with a semicoln
            $sql .= ' ' . $qp_insert->sql . ';';
        } else {
            // update the fields excluding the unique id
            $update_fields = array_values($fld_lst_chg);
            $update_values = [];
            $update_types = [];
            foreach ($fld_lst_chg as $fld) {
                $update_values[] = $par_lst->get_value($fld);
                $update_types[] = $par_lst->get_type($fld);
            }
            $update_fld_val_typ_lst = [];
            foreach ($update_fields as $key => $field) {
                $update_fld_val_typ_lst[] = [$field, $update_values[$key], $update_types[$key]];
            }
            $sc_update = clone $sc;
            $sc_par_lst_upd = new sql_type_list([sql_type::NAMED_PAR, sql_type::UPDATE, sql_type::UPDATE_PART]);
            $sc_par_lst_upd_ex_log = $sc_par_lst_upd->remove(sql_type::LOG);
            $qp_update = $this->sql_common($sc_update, $sc_par_lst_upd_ex_log);;
            $update_fvt_lst = new sql_par_field_list();
            $update_fvt_lst->set($update_fld_val_typ_lst);
            $qp_update->sql = $sc_update->create_sql_update(
                $id_fld, $id_val, $update_fvt_lst, [], $sc_par_lst_upd, true, '', $id_fld);
            // add the insert row to the function body
            $sql .= ' ' . $qp_update->sql . ' ';
        }

        $sql .= $sc->sql_func_end();


        // create the query parameters for the actual change
        $qp_chg = clone $qp;
        $qp_chg->sql = $sc->create_sql_update(
            $id_fld, $id_val, $par_lst_out, [], $sc_par_lst);
        $qp_chg->par = $par_lst->values();

        // merge all together and create the function
        $qp->sql = $qp_chg->sql . $sql . ';';

        $qp->call = ' ' . sql::SELECT . ' ' . $qp_chg->name . ' (';

        $call_val_str = $par_lst_out->par_sql($sc);

        $qp->call .= $call_val_str . ');';

        return $qp;
    }

    /**
     * create the sql statement to delete or exclude a named sandbox object e.g. word to the database
     *
     * @param sql $sc with the target db_type set
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL update statement, the name of the SQL statement and the parameter list
     */
    function sql_delete(
        sql           $sc,
        sql_type_list $sc_par_lst = new sql_type_list([])): sql_par
    {
        $usr_tbl = $sc_par_lst->is_usr_tbl($sc_par_lst);
        $excluded = $sc_par_lst->exclude_sql($sc_par_lst);
        $qp = $this->sql_common($sc, $sc_par_lst);
        $qp->name .= sql::file_sep . sql::file_delete;
        $par_lst = [$this->id()];
        if ($excluded) {
            $qp->name .= '_excluded';
        }
        $sc->set_name($qp->name);
        // delete the user overwrite
        // but if the excluded user overwrites should be deleted the overwrites for all users should be deleted
        if ($usr_tbl and !$excluded) {
            $qp->sql = $sc->create_sql_delete([$this->id_field(), user::FLD_ID], [$this->id(), $this->user_id()], $excluded);
            $par_lst[] = $this->user_id();
        } else {
            $qp->sql = $sc->create_sql_delete($this->id_field(), $this->id(), $excluded);
        }
        $qp->par = $par_lst;

        return $qp;
    }

    /**
     * the common part of the sql_insert and sql_update functions
     * TODO include the sql statements to log the changes
     *
     * @param sql $sc with the target db_type set
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @param string $name_ext the query name extension to differ insert from update
     * @return sql_par prepared sql parameter object with the name set
     */
    private function sql_common(sql $sc, sql_type_list $sc_par_lst = new sql_type_list([]), string $name_ext = ''): sql_par
    {
        $lib = new library();
        $usr_tbl = $sc_par_lst->is_usr_tbl();
        $sc->set_class($this::class, $sc_par_lst);
        $sql_name = $lib->class_to_name($this::class);
        $qp = new sql_par($sql_name);
        $qp->name = $sql_name . $name_ext;
        if ($usr_tbl) {
            $qp->name .= '_user';
        }
        $sc->set_name($qp->name);
        return $qp;
    }


    /*
     * sql write fields
     */

    /**
     * get a list of all database fields that might be changed
     * excluding the internal fields e.g. the database id
     * field list must be corresponding to the db_fields_changed fields
     *
     * @return array list of all database field names that have been updated
     */
    function db_fields_all_named(): array
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
     * @param sandbox_named $sbx the same named sandbox as this to compare which fields have been changed
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list with the field names of the object and any child object
     */
    function db_changed_named_list(sandbox_named $sbx, sql_type_list $sc_par_lst): sql_par_field_list
    {
        global $change_field_list;

        $lst = new sql_par_field_list();
        $sc = new sql();
        $usr_tbl = $sc_par_lst->is_usr_tbl();
        $is_insert = $sc_par_lst->is_insert();
        $do_log = $sc_par_lst->and_log();
        $table_id = $sc->table_id($this::class);

        // for insert statements of user sandbox rows user id fields always needs to be included
        if ($usr_tbl and $is_insert) {
            $lst->add_field(
                $this::FLD_ID,
                $this->id(),
                db_object_seq_id::FLD_ID_SQLTYP
            );
            $lst->add_field(
                user::FLD_ID,
                $this->user_id(),
                db_object_seq_id::FLD_ID_SQLTYP
            );
        } else {
            if ($sbx->user_id() <> $this->user_id()) {
                if ($do_log) {
                    $lst->add_field(
                        sql::FLD_LOG_FIELD_PREFIX . user::FLD_ID,
                        $change_field_list->id($table_id . user::FLD_ID),
                        change::FLD_FIELD_ID_SQLTYP
                    );
                }
                if ($sbx->user_id() == 0) {
                    $old_user_id = null;
                } else {
                    $old_user_id = $sbx->user_id();
                }
                $lst->add_field(
                    user::FLD_ID,
                    $this->user_id(),
                    db_object_seq_id::FLD_ID_SQLTYP,
                    $old_user_id
                );
            }
        }
        if ($sbx->name() <> $this->name()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . $this->name_field(),
                    $change_field_list->id($table_id . $this->name_field()),
                    change::FLD_FIELD_ID_SQLTYP
                );
            }
            if ($sbx->name() == '') {
                $old_name = null;
            } else {
                $old_name = $sbx->name();
            }
            $lst->add_field(
                $this->name_field(),
                $this->name(),
                self::FLD_NAME_SQLTYP,
                $old_name
            );
        }
        if ($sbx->description <> $this->description) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . self::FLD_DESCRIPTION,
                    $change_field_list->id($table_id . self::FLD_DESCRIPTION),
                    change::FLD_FIELD_ID_SQLTYP
                );
            }
            $lst->add_field(
                self::FLD_DESCRIPTION,
                $this->description(),
                self::FLD_DESCRIPTION_SQLTYP,
                $sbx->description
            );
        }
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


