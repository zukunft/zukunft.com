<?php

/*

    model/sandbox/sandbox_named.php - the superclass for handling user specific named objects including the database saving
    -------------------------------

    This superclass should be used by the classes words, formula, ... to enable user specific values and links


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

namespace cfg;

include_once DB_PATH . 'sql_par_type.php';
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
use cfg\db\sql;
use cfg\db\sql_db;
use cfg\db\sql_par;
use cfg\db\sql_par_type;
use cfg\log\change;
use cfg\log\change_action;
use cfg\log\change_link;
use Exception;
use cfg\export\sandbox_exp;

class sandbox_named extends sandbox
{

    /*
     * database link
     */

    // object specific database and JSON object field names
    const FLD_NAME = 'name';
    const FLD_DESCRIPTION = 'description';


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
     * get a list of database fields that have been updated
     * field list must be corresponding to the db_values_changed fields
     *
     * @param sandbox_named $sbx the same named sandbox as this to compare which fields have been changed
     * @param bool $usr_tbl true if the user table row should be updated
     * @return array with the field names of the object and any child object
     */
    function db_fields_changed_named(sandbox_named $sbx, bool $usr_tbl = false): array
    {
        $result = [];
        // for insert of user sandbox rows user id fields always needs to be included
        if ($usr_tbl) {
            $result[] = $this::FLD_ID;
            $result[] = user::FLD_ID;
        } else {
            if ($sbx->user_id() <> $this->user_id()) {
                $result[] = user::FLD_ID;
            }
        }
        if ($sbx->name() <> $this->name()) {
            $result[] = $this->name_field();
        }
        if ($sbx->description <> $this->description) {
            $result[] = self::FLD_DESCRIPTION;
        }
        return $result;
    }

    /**
     * get a list of database field values that have been updated
     *
     * @param sandbox_named $sbx the same named sandbox as this to compare which field values have been changed
     * @param bool $usr_tbl true if the user table row should be updated
     * @return array with the field values of the object and any child object
     */
    function db_values_changed_named(sandbox_named $sbx, bool $usr_tbl = false): array
    {
        $result = [];
        // for insert of user sandbox rows user id fields always needs to be included
        if ($usr_tbl) {
            $result[] = $this->id();
            $result[] = $this->user_id();
        } else {
            if ($sbx->user_id() <> $this->user_id()) {
                $result[] = $this->user_id();
            }
        }
        if ($sbx->name() <> $this->name()) {
            $result[] = $this->name();
        }
        if ($sbx->description <> $this->description) {
            $result[] = $this->description();
        }
        return $result;
    }

    /**
     * @return array with the field values of the object and any child object
     */
    function value_list_named(): array
    {
        return [
            $this->user()->id(),
            $this->name(),
            $this->description,
            $this->excluded,
            $this->share_id,
            $this->protection_id
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
     * loading
     */

    /**
     * create the SQL to load the single default value always by the id or name
     * @param sql $sc with the target db_type set
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_standard_sql(sql $sc, string $class = self::class): sql_par
    {
        $qp = new sql_par($class, true);
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
     * sql write
     */

    /**
     * create the sql statement to add a new named sandbox object e.g. word to the database
     * TODO add qp merge
     *
     * @param sql $sc with the target db_type set
     * @param array $fld_lst list of field names additional to the standard id and name fields
     * @param array $val_lst list of field values additional to the standard id and name
     * @param array $fld_lst_all list of field names of the given object
     * @param bool $usr_tbl true if the user table row should be added
     * @return sql_par the SQL insert statement, the name of the SQL statement and the parameter list
     */
    function sql_insert_named(
        sql   $sc,
        array $fld_lst = [],
        array $val_lst = [],
        array $fld_lst_all = [],
        bool  $usr_tbl = false,
        bool  $and_log = false): sql_par
    {
        $lib = new library();

        if (count($fld_lst) != count($val_lst)) {
            log_err('fields (' . $lib->dsp_array($fld_lst) . ') does not match with values (' . $lib->dsp_array($val_lst) . ')');
        }
        $fld_chg_ext = $lib->sql_field_ext($fld_lst, $fld_lst_all);
        $ext = sql::file_sep . sql::file_insert . sql::file_sep . $fld_chg_ext;
        $qp = $this->sql_common($sc, $usr_tbl, $ext);
        if ($and_log) {
            $sc_log = clone $sc;
            $i = 0;
            foreach ($fld_lst as $fld) {
                $log = new change($this->user());
                $log->set_table_by_class($this::class);
                $log->set_field($fld);
                $log->new_value = $val_lst[$i];
                $sql_log = $log->sql_insert($sc_log);
            }
        }
        // add the child object specific fields and values
        $qp->sql = $sc->sql_insert($fld_lst, $val_lst);
        $qp->par = $val_lst;

        return $qp;
    }

    /**
     * create the sql statement to add a new named sandbox object e.g. word to the database
     *
     * @param sql $sc with the target db_type set
     * @param array $fld_lst list of field names additional to the standard id and name fields
     * @param array $val_lst list of field values additional to the standard id and name$
     * @param array $fld_lst_all list of field names of the given object
     * @param bool $usr_tbl true if the user table row should be updated
     * @return sql_par the SQL update statement, the name of the SQL statement and the parameter list
     */
    function sql_update_named(
        sql   $sc,
        array $fld_lst = [],
        array $val_lst = [],
        array $fld_lst_all = [],
        bool  $usr_tbl = false): sql_par
    {
        $lib = new library();
        $fld_chg_ext = $lib->sql_field_ext($fld_lst, $fld_lst_all);
        $ext = sql::file_sep . sql::file_update . sql::file_sep . $fld_chg_ext;
        $qp = $this->sql_common($sc, $usr_tbl, $ext);
        if ($usr_tbl) {
            $qp->sql = $sc->sql_update([$this->id_field(), user::FLD_ID], [$this->id(), $this->user_id()], $fld_lst, $val_lst);
        } else {
            $qp->sql = $sc->sql_update($this->id_field(), $this->id(), $fld_lst, $val_lst);
        }
        $qp->par = $val_lst;

        return $qp;
    }

    /**
     * the common part of the sql_insert and sql_update functions
     * TODO include the sql statements to log the changes
     *
     * @param sql $sc with the target db_type set
     * @param bool $usr_tbl true if a db row should be added to the user table
     * @param string $name_ext the query name extension to differ insert from update
     * @return sql_par prepared sql parameter object with the name set
     */
    private function sql_common(sql $sc, bool $usr_tbl = false, string $name_ext = ''): sql_par
    {
        $lib = new library();
        $sc->set_class($this::class, $usr_tbl);
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

        $log = new change($this->user());
        // TODO add the table exceptions from sql_db
        $log->action = change_action::ADD;
        $log->set_table($this->obj_name . sql_db::TABLE_EXTENSION);
        $log->set_field($this->obj_name . '_name');
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

        $log = new change($this->user());
        $log->action = change_action::DELETE;
        $log->set_table($this->obj_name . sql_db::TABLE_EXTENSION);
        $log->set_field($this->obj_name . '_name');
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

        $result = '';
        if (!$usr->is_system()) {
            if ($this->obj_type == sandbox::TYPE_NAMED) {
                if ($this->obj_name == sql_db::TBL_WORD) {
                    if (in_array($this->name, word_api::RESERVED_WORDS)) {
                        // the admin user needs to add the read test word during initial load
                        if (!$usr->is_admin()) {
                            $result = '"' . $this->name() . '" is a reserved name for system testing. Please use another name';
                        }
                    }
                } elseif ($this->obj_name == sql_db::TBL_PHRASE) {
                    if (in_array($this->name, phrase_api::RESERVED_PHRASES)) {
                        $result = '"' . $this->name() . '" is a reserved phrase name for system testing. Please use another name';
                    }
                } elseif ($this->obj_name == sql_db::TBL_FORMULA) {
                    if (in_array($this->name, formula_api::RESERVED_FORMULAS)) {
                        if ($usr->is_admin() and $this->name() != formula_api::TN_READ) {
                            $result = '"' . $this->name() . '" is a reserved formula name for system testing. Please use another name';
                        }
                    }
                } elseif ($this->obj_name == sql_db::TBL_VIEW) {
                    if (in_array($this->name, view_api::RESERVED_VIEWS)) {
                        if ($usr->is_admin() and $this->name() != view_api::TN_READ) {
                            $result = '"' . $this->name() . '" is a reserved view name for system testing. Please use another name';
                        }
                    }
                } elseif ($this->obj_name == sql_db::TBL_COMPONENT) {
                    if (in_array($this->name, component_api::RESERVED_COMPONENTS)) {
                        if ($usr->is_admin() and $this->name() != component_api::TN_READ) {
                            $result = '"' . $this->name() . '" is a reserved view component name for system testing. Please use another name';
                        }
                    }
                } elseif ($this->obj_name == sql_db::TBL_SOURCE) {
                    if (in_array($this->name, source_api::RESERVED_SOURCES)) {
                        // the admin user needs to add the read test source during initial load
                        if ($usr->is_admin() and $this->name() != source_api::TN_READ) {
                            $result = '"' . $this->name() . '" is a reserved source name for system testing. Please use another name';
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @return bool true if for this database and class
     *              a prepared script including the write to the log
     *              for db write should be used
     */
    function sql_use_script_with_log(): bool
    {
        if (in_array($this::class, sql_db::DB_WRITE_LOG_SCRIPT_CLASSES)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool true if for this database and class a prepared script for db write should be used
     */
    function sql_write_prepared(): bool
    {
        if (in_array($this::class, sql_db::DB_WRITE_PREPARED)) {
            return true;
        } else {
            return false;
        }
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

        // log the insert attempt first
        $log = $this->log_add();
        if ($log->id() > 0) {

            // insert the new object and save the object key
            // TODO check that always before a db action is called the db type is set correctly
            $db_con->set_class($this->obj_name);
            $db_con->set_usr($this->user()->id);
            $this->id = $db_con->insert_old(array($this->obj_name . '_name', "user_id"), array($this->name, $this->user()->id));

            // save the object fields if saving the key was successful
            if ($this->id > 0) {
                log_debug($this->obj_type . ' ' . $this->dsp_id() . ' has been added');
                // update the id in the log
                if (!$log->add_ref($this->id)) {
                    $result->add_message('Updating the reference in the log failed');
                    // TODO do rollback or retry?
                } else {
                    //$result->add_message($this->set_owner($new_owner_id));

                    // create an empty db_rec element to force saving of all set fields
                    $db_rec = clone $this;
                    $db_rec->reset();
                    $db_rec->name = $this->name();
                    $db_rec->set_user($this->user());
                    $std_rec = clone $db_rec;
                    // save the object fields
                    $result->add_message($this->save_fields($db_con, $db_rec, $std_rec));
                }

            } else {
                $result->add_message('Adding ' . $this->obj_type . ' ' . $this->dsp_id() . ' failed due to logging error.');
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
        return 'A ' . $this->obj_name . ' with the name "' . $this->name() . '" already exists. Please use another name.';
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

        if ($this->is_id_updated($db_rec)) {
            log_debug('to ' . $this->dsp_id() . ' from ' . $db_rec->dsp_id() . ' (standard ' . $std_rec->dsp_id() . ')');

            $log = $this->log_upd_field();
            $log->old_value = $db_rec->name();
            $log->new_value = $this->name();
            $log->std_value = $std_rec->name();
            $log->set_field($this->obj_name . '_name');

            $log->row_id = $this->id;
            if ($log->add()) {
                $db_con->set_class($this->obj_name);
                $db_con->set_usr($this->user()->id);
                if (!$db_con->update_old($this->id,
                    array($this->obj_name . '_name'),
                    array($this->name))) {
                    $result .= 'update of name to ' . $this->name() . 'failed';
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
            if ($this->obj_name == $obj_to_check->obj_name) {
                $result = $this->is_same_std($obj_to_check);
            } else {
                // create a synthetic unique index over words, phrase, verbs and formulas
                if ($this->obj_name == sql_db::TBL_WORD or $this->obj_name == sql_db::TBL_PHRASE or $this->obj_name == sql_db::TBL_FORMULA or $this->obj_name == sql_db::TBL_VERB) {
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
        if ($this->obj_name == sql_db::TBL_WORD
            or $this->obj_name == sql_db::TBL_VERB
            or $this->obj_name == sql_db::TBL_TRIPLE
            or $this->obj_name == sql_db::TBL_FORMULA) {
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
            if ($this->obj_name == sql_db::TBL_CHANGE) {
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


