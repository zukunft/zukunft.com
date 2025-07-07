<?php

/*

    model/sandbox/sandbox_link.php - the superclass for handling user specific link objects including the database saving
    ------------------------------

    This superclass should be used by the class word links, formula links and view link

    The main sections of this object are
    - db const:          const for the database link
    - object vars:       the variables of this word object
    - construct and map: including the mapping of the db row to this word object
    - api:               create an api array for the frontend and set the vars based on a frontend api message
    - set and get:       to capsule the vars from unexpected changes
    - sql create:        to support the initial database setup
    - cast:              create an api object and set the vars from an api json
    - load:              database access object (DAO) functions
    - info:              functions to make code easier to read
    - log:               functions to track the changes
    - save:              manage to update the database
    - sql write:         sql statement creation to write to the database

    TODO add weight with int and 100'000 as 100% because
         humans usually cannot handle more than 100'000 words
         so weight sorted list has a single place for each word


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

include_once MODEL_SANDBOX_PATH . 'sandbox.php';
include_once MODEL_HELPER_PATH . 'combine_named.php';
include_once DB_PATH . 'sql.php';
include_once DB_PATH . 'sql_creator.php';
include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_field_type.php';
include_once DB_PATH . 'sql_par.php';
include_once DB_PATH . 'sql_par_field_list.php';
include_once DB_PATH . 'sql_type.php';
include_once DB_PATH . 'sql_type_list.php';
include_once MODEL_HELPER_PATH . 'db_object_seq_id.php';
//include_once MODEL_FORMULA_PATH . 'formula_link.php';
//include_once MODEL_FORMULA_PATH . 'formula_link_type.php';
include_once MODEL_LOG_PATH . 'change.php';
include_once MODEL_LOG_PATH . 'change_action.php';
include_once MODEL_LOG_PATH . 'change_link.php';
include_once MODEL_LOG_PATH . 'change.php';
//include_once MODEL_REF_PATH . 'ref.php';
//include_once MODEL_WORD_PATH . 'triple.php';
include_once MODEL_USER_PATH . 'user.php';
include_once MODEL_USER_PATH . 'user_message.php';
include_once SHARED_ENUM_PATH . 'change_actions.php';
include_once SHARED_ENUM_PATH . 'messages.php';
include_once SHARED_TYPES_PATH . 'api_type_list.php';
include_once SHARED_PATH . 'json_fields.php';
include_once SHARED_PATH . 'library.php';

use cfg\formula\formula_link;
use cfg\formula\formula_link_type;
use cfg\helper\combine_named;
use cfg\db\sql;
use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\db\sql_field_type;
use cfg\db\sql_par;
use cfg\db\sql_par_field_list;
use cfg\db\sql_type;
use cfg\db\sql_type_list;
use cfg\helper\db_object_seq_id;
use cfg\log\change;
use cfg\log\change_link;
use cfg\ref\ref;
use cfg\word\triple;
use cfg\user\user;
use cfg\user\user_message;
use Exception;
use shared\enum\change_actions;
use shared\enum\messages as msg_id;
use shared\json_fields;
use shared\library;
use shared\types\api_type_list;

class sandbox_link extends sandbox
{

    /*
     * db const
     */

    // list of fields that select the objects that should be linked
    // dummy array to enable references here and is overwritten by the child object
    const FLD_LST_LINK = array();
    const FLD_LST_MUST_BUT_STD_ONLY = array();

    // separator to create a unique key based on the
    const KEY_SEP = '/';
    // to allow the usage of the name key separator within an object name
    const KEY_SEP_ESC = '//';


    /*
     * object vars
     */

    private sandbox_named|combine_named|null $fob = null; // the (F)rom (OB)ject which this linked object is creating the connection
    private sandbox_named|combine_named|string|null $tob = null; // the (T)o (OB)ject which this linked object is creating the connection (can be a string for external keys)

    // database id of the type used for named link user sandbox objects with predefined functionality
    // which is formula link and view component link
    // repeating _sandbox_typed, because php 8.1 does not yet allow multi extends
    public ?int $predicate_id = null;

    // database fields only used for objects that link two objects
    // TODO create a more specific object that covers all the objects that could be linked e.g. linkable_object
    public ?string $from_name = null;  // the name of the from object type e.g. view for component_links
    public ?string $to_name = '';      // the name of the  to  object type e.g. view for component_links


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

        $this->fob = null;
        $this->tob = null;
        $this->predicate_id = null;
    }

    /**
     * fill the vars with this link type sandbox object based on the given api json array
     * @param array $api_json the api array with the word values that should be mapped
     * @return user_message
     */
    function api_mapper(array $api_json): user_message
    {

        $msg = parent::api_mapper($api_json);

        if (array_key_exists(json_fields::PREDICATE, $api_json)) {
            $this->predicate_id = $api_json[json_fields::PREDICATE];
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

        // for triples the predicate is the verb and already included in the vars at this point
        if ($this::class != triple::class) {
            if ($this->predicate_id() != 0) {
                if ($this::class == formula_link::class) {
                    global $frm_lnk_typ_cac;
                    if ($this->predicate_id() != $frm_lnk_typ_cac->id(formula_link_type::DEFAULT)) {
                        $vars[json_fields::PREDICATE] = $this->predicate_id();
                    }
                } else {
                    $vars[json_fields::PREDICATE] = $this->predicate_id();
                }
            }
        }

        return $vars;
    }


    /*
     * set and get
     */

    function set_fob(sandbox_named|combine_named|null $fob): void
    {
        $this->fob = $fob;
    }

    function fob(): sandbox_named|combine_named|null
    {
        return $this->fob;
    }

    /**
     * @return int the id of the linked object
     */
    function from_id(): int
    {
        if ($this->fob == null) {
            return 0;
        } else {
            return $this->fob->id();
        }
    }

    /**
     * @return string|null the name of the linked object
     */
    function from_name(): ?string
    {
        return $this->fob()?->name();
    }

    /**
     * set the database id of the type
     *
     * @param int|null $predicate_id the database id of the type
     * @return void
     */
    function set_predicate_id(?int $predicate_id): void
    {
        $this->predicate_id = $predicate_id;
    }

    /**
     * @return int|null the database id of the type
     */
    function predicate_id(): ?int
    {
        return $this->predicate_id;
    }

    /**
     * to be overwritten by the child objects
     * @return string|null the name of connection type
     */
    function predicate_name(): ?string
    {
        return null;
    }

    function set_tob(sandbox_named|combine_named|string|null $tob): void
    {
        $this->tob = $tob;
    }

    function tob(): sandbox_named|combine_named|string|null
    {
        return $this->tob;
    }

    /**
     * @return int|string the id of the linked object
     * or in case of an external reference the external key as a string
     */
    function to_id(): int|string
    {
        if ($this->tob == null) {
            return 0;
        } else {
            return $this->tob->id();
        }
    }

    /**
     * @return string the name of the linked object
     */
    function to_name(): string
    {
        return $this->tob()?->name();
    }

    /**
     * copy the link objects from this object to the given link
     * used to unset any changes in the link to detect only the changes fields that the user is allowed to change
     *
     * @param sandbox_link $lnk
     * @return sandbox_link
     */
    function set_link_objects(sandbox_link $lnk): sandbox_link
    {
        $lnk->fob = $this->fob;
        $lnk->tob = $this->tob;
        return $lnk;
    }

    /**
     * create a clone but keep the unique db ids
     *
     * @return $this a clone with the name changed
     */
    function cloned(): sandbox_link
    {
        $obj_cpy = $this->clone_reset();
        $obj_cpy->set_id($this->id());
        $obj_cpy->set_fob($this->fob());
        $obj_cpy->set_tob($this->tob());
        return $obj_cpy;
    }

    /**
     * @return string a unique key of the link based on the names of the objects that are linked
     */
    function key(): string
    {
        $from_name = str_replace(self::KEY_SEP, self::KEY_SEP_ESC, $this->from_name());
        $link_name = str_replace(self::KEY_SEP, self::KEY_SEP_ESC, $this->predicate_name());
        $to_name = str_replace(self::KEY_SEP, self::KEY_SEP_ESC, $this->to_name());
        return $from_name . self::KEY_SEP . $link_name . self::KEY_SEP . $to_name;
    }


    /*
     * settings
     */

    /**
     * @return bool true because all child objects use the link type
     */
    function is_link_type_obj(): bool
    {
        return true;
    }


    /*
     * sql create
     */

    /**
     * create an array with the fields and parameters for the sql table creation of the link object
     *
     * @param sql_type_list $sc_par_lst of parameters for the sql creation
     * @return array[] with the parameters of the table fields
     */
    protected function sql_all_field_par(sql_type_list $sc_par_lst): array
    {
        $usr_tbl = $sc_par_lst->is_usr_tbl();
        $use_sandbox = $sc_par_lst->use_sandbox_fields();
        if (!$usr_tbl) {
            // the primary id field is always the first
            $fields = $this->sql_id_field_par(false);
            // the link fields are not repeated in the user table because they cannot be changed individually
            $fields = array_merge($fields, $this::FLD_LST_LINK);
            // set the owner of the link
            $fields = array_merge($fields, sandbox::FLD_ALL_OWNER);
            // mandatory fields that can be changed the user
            $fields = array_merge($fields, $this::FLD_LST_MUST_BUT_STD_ONLY);
            // fields that can be changed the user but are empty if the user has not overwritten the fields
            $fields = array_merge($fields, $this::FLD_LST_USER_CAN_CHANGE);
            $fields = array_merge($fields, $this::FLD_LST_NON_CHANGEABLE);
        } else {
            // the primary id field is always the first
            $fields = $this->sql_id_field_par(true);
            // a user overwrite must always have a user
            $fields = array_merge($fields, sandbox::FLD_ALL_CHANGER);
            // mandatory fields that can be changed the user
            $fields = array_merge($fields, $this::FLD_LST_MUST_BUT_USER_CAN_CHANGE);
            // fields that can be changed the user but are empty if the user has not overwritten the fields
            $fields = array_merge($fields, $this::FLD_LST_USER_CAN_CHANGE);
        }
        if ($use_sandbox) {
            $fields = array_merge($fields, sandbox::FLD_LST_ALL);
        }
        return $fields;
    }


    /*
     * load
     */

    /**
     * load a named user sandbox object by name
     * @param int $from the subject object id
     * @param int $predicate_id the predicate object id
     * @param int|string $to the object (grammar) object id or the unique external key
     * @param string $class the name of the child class from where the call has been triggered
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_link_id(int $from, int $predicate_id = 0, int|string $to = 0, string $class = ''): int
    {
        global $db_con;

        if ($class == '') {
            $class = $this::class;
        }

        $lib = new library();
        log_debug($lib->dsp_array(array($from, $predicate_id, $to)));
        $qp = $this->load_sql_by_link($db_con->sql_creator(), $from, $predicate_id, $to, $class);
        return parent::load($qp);
    }

    /**
     * load the link parameters for all users
     * TODO remove from the child objects
     *
     * @param sql_par|null $qp placeholder to align the function parameters with the parent
     * @return bool true if the standard view component link has been loaded
     */
    function load_standard(?sql_par $qp = null): bool
    {

        global $db_con;
        $result = false;

        $qp = $this->load_standard_sql($db_con->sql_creator());

        if ($qp->has_par()) {
            $db_dsl = $db_con->get1($qp);
            $result = $this->row_mapper_sandbox($db_dsl, true);
            if ($result) {
                $result = $this->load_owner();
            }
        }
        return $result;
    }

    /**
     * create an SQL statement to retrieve a user sandbox link by the ids of the linked objects from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param int $from the subject object id
     * @param int $predicate_id the predicate object id
     * @param int|string $to the object (grammar) object id or the unique external key
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_link(sql_creator $sc, int $from, int $predicate_id, int|string $to, string $class): sql_par
    {
        if ($predicate_id > 0) {
            $qp = $this->load_sql($sc, 'link_type_ids', $class);
            $sc->add_where($this->from_field(), $from);
            $sc->add_where($this->type_field(), $predicate_id);
        } else {
            $qp = $this->load_sql($sc, 'link_ids', $class);
            $sc->add_where($this->from_field(), $from);
        }
        $sc->add_where($this->to_field(), $to);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }


    /*
     * info
     */

    /**
     * @return bool true if the object value are valid for identifying a unique link
     */
    function is_unique(): bool
    {
        $result = false;
        if ($this->id() > 0) {
            $result = true;
        } else {
            if ($this->fob != null and $this->tob != null) {
                if ($this->fob->id() > 0 and $this->tob->id() > 0) {
                    $result = true;
                }
            }
        }
        return $result;
    }

    /**
     * check if the triple might be added to the database
     * if all related objects have been added to the database
     * @return user_message including suggested solutions
     *       if something is missing e.g. a linked object
     */
    function can_be_ready(): user_message
    {
        $usr_msg = parent::db_ready();

        if ($this->fob == null) {
            $usr_msg->add_id_with_vars(msg_id::FROM_MISSING,
                [msg_id::VAR_NAME => $this->dsp_id()]);
        } else {
            $usr_msg->add($this->fob->can_be_ready());
        }
        if ($this->fob == null) {
            $usr_msg->add_id_with_vars(msg_id::TO_MISSING,
                [msg_id::VAR_NAME => $this->dsp_id()]);
        } else {
            // a reference have only an external key but not a target object
            if ($this::class != ref::class) {
                $usr_msg->add($this->tob->can_be_ready());
            }
        }
        return $usr_msg;
    }

    /**
     * returns ok message if this link e.g. triple can be added to the database
     * if e.g. the database id of the from or the to object is missing
     *         first the linked object needs to be added to the database
     * @return user_message the explanation why the link cannot yet be added to the database
     */
    function db_ready(): user_message
    {
        $usr_msg = parent::db_ready();

        if ($this->fob == null) {
            $usr_msg->add_id_with_vars(msg_id::FROM_MISSING,
                [msg_id::VAR_NAME => $this->dsp_id()]);
        } else {
            if (!$this->fob->is_valid()) {
                $usr_msg->add_id_with_vars(msg_id::FROM_ZERO_ID,
                    [msg_id::VAR_NAME => $this->dsp_id()]);

            }
        }
        if ($this->tob == null) {
            $usr_msg->add_id_with_vars(msg_id::TO_MISSING,
                [msg_id::VAR_NAME => $this->dsp_id()]);
        } else {
            if (!$this->tob->is_valid()) {
                $usr_msg->add_id_with_vars(msg_id::FROM_ZERO_ID,
                    [msg_id::VAR_NAME => $this->dsp_id()]);

            }
        }
        return $usr_msg;
    }

    /**
     * @return bool true if the triple object probably has already been added to the database
     *              false e.g. if some parameters are missing
     */
    function is_valid(): bool
    {
        if ($this->id() != 0 and $this->name() != '') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * check if the named object in the database needs to be updated
     *
     * @param sandbox_link $db_obj the word as saved in the database
     * @return bool true if this word has infos that should be saved in the database
     */
    function needs_db_update_linked(sandbox_link $db_obj): bool
    {
        $result = parent::needs_db_update_sandbox($db_obj);
        if ($this->fob->id() != 0) {
            if ($this->fob->id() != $db_obj->fob->id()) {
                $result = true;
            }
        }
        if ($this->tob->id() != 0) {
            if ($this->tob->id() != $db_obj->tob->id()) {
                $result = true;
            }
        }
        return $result;
    }


    /*
     * cast
     */

    /**
     * @param object $api_obj frontend API objects that should be filled with unique object name
     */
    function fill_api_obj(object $api_obj): void
    {
        parent::fill_api_obj($api_obj);

        if ($this->predicate_id() != 0) {
            $api_obj->set_predicate_id($this->predicate_id());
        }
    }

    /*
    /**
     * set the vars of the minimal api object based on this link object
     * @param object $api_obj frontend API object filled with the database id
     *
     * @return void
    function fill_api_obj(object $api_obj): void
    {
        parent::fill_api_obj($api_obj);

        if ($this->fob != null) {
            $api_obj->fob = $this->fob->api_obj();
        }
        if ($this->tob != null) {
            $api_obj->tob = $this->tob->api_obj();
        }
    }
    */


    /*
     * log
     */

    /**
     * set the log entry parameter for a new link object
     * for all not named objects like links, this function is overwritten
     * e.g. that the user can see "added formula 'scale millions' to word 'mio'"
     * @returns change_link with the object presets e.g. th object name
     */
    function log_link_add(): change_link
    {
        log_debug($this->dsp_id());
        $lib = new library();

        $log = new change_link($this->user());
        $log->new_from = $this->fob;
        $log->new_to = $this->tob;

        $log->set_action(change_actions::ADD);
        // TODO add the table exceptions from sql_db
        $tbl_name = $lib->class_to_name($this::class);
        $log->set_table($tbl_name . sql_db::TABLE_EXTENSION);
        $log->row_id = 0;
        $log->add();

        return $log;
    }

    /**
     * set the log entry parameter to delete a object
     * @returns change_link with the object presets e.g. th object name
     */
    function log_del_link(): change_link
    {
        log_debug($this->dsp_id());
        $lib = new library();

        $log = new change_link($this->user());
        $log->set_action(change_actions::DELETE);
        $tbl_name = $lib->class_to_name($this::class);
        $log->set_table($tbl_name . sql_db::TABLE_EXTENSION);
        $log->old_from = $this->fob();
        $log->old_to = $this->tob();

        $log->row_id = $this->id();
        $log->add();

        return $log;
    }

    /**
     * TODO for normal fields use the change log, but for link changes use the link log
     * @return change|change_link the object that is used to log the user changes
     */
    function log_object(): change|change_link
    {
        return new change($this->user());
    }


    /*
     * save
     */

    /**
     * create a new link object
     * @returns int the id of the creates object
     */
    function add_insert(): int
    {
        global $db_con;
        $db_con->set_class(self::class);
        return $db_con->insert_old(
            array($this->from_name . sql_db::FLD_EXT_ID, $this->to_name . sql_db::FLD_EXT_ID, user::FLD_ID),
            array($this->fob()->id(), $this->tob()->id(), $this->user()->id()));
    }

    /**
     * create a new link object and log the change
     * @param bool $use_func if true a predefined function is used that also creates the log entries
     * @return user_message with status ok
     *                      or if something went wrong
     *                      the message that should be shown to the user
     *                      including suggested solutions
     * TODO do a rollback in case of an error
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
            $log = $this->log_link_add();
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
                    $db_con->set_class($this::class);
                    $db_con->set_usr($this->user()->id());
                    $this->set_id($this->add_insert());
                }

                // save the object fields if saving the key was successful
                if ($this->id() > 0) {
                    log_debug($this::class . ' ' . $this->dsp_id() . ' has been added');
                    // update the id in the log
                    if (!$log->add_ref($this->id())) {
                        $usr_msg->add_id(msg_id::FAILED_UPDATE_REF);
                        // TODO do rollback or retry?
                    } else {
                        //$usr_msg->add_message_text($this->set_owner($new_owner_id));

                        // create an empty db_rec element to force saving of all set fields
                        $db_rec = clone $this;
                        $db_rec->reset();
                        $db_rec->fob = $this->fob;
                        $db_rec->tob = $this->tob;
                        $db_rec->set_user($this->user());
                        $std_rec = clone $db_rec;
                        // save the object fields
                        $usr_msg->add($this->save_all_fields($db_con, $db_rec, $std_rec));
                    }

                } else {
                    $usr_msg->add_id_with_vars(msg_id::FAILED_ADD_LOGGING_ERROR, [
                        msg_id::VAR_CLASS_NAME => $this::class,
                        msg_id::VAR_ID => $this->dsp_id()
                    ]);
                }
            }
        }

        return $usr_msg;
    }

    /**
     * check if the id parameters are supposed to be changed
     * TODO add the link type for word links
     * @param sandbox_link $db_rec the object data as it is now in the database
     * @return bool true if one of the object id fields have been changed
     */
    function is_id_updated_link(sandbox $db_rec): bool
    {
        $result = False;
        log_debug($this->dsp_id());

        if ($db_rec->fob->id() <> $this->fob->id()
            or $db_rec->tob->id() <> $this->tob->id()) {
            $result = True;
            // TODO check if next line is needed
            // $this->reset_objects();
        }

        return $result;
    }

    /**
     * @return string text that tells the user that the change would create a duplicate
     */
    function msg_id_already_used(): string
    {
        $lib = new library();
        $class_name = $lib->class_to_name($this::class);
        return 'A ' . $class_name . ' from ' . $this->fob->dsp_id() . ' to ' . $this->tob->dsp_id() . ' already exists.';
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
    function save_id_fields_link(sql_db $db_con, sandbox $db_rec, sandbox $std_rec): string
    {
        $result = '';
        log_debug($this->dsp_id());

        if ($this->is_id_updated_link($db_rec)) {
            log_debug('to ' . $this->dsp_id() . ' from ' . $db_rec->dsp_id() . ' (standard ' . $std_rec->dsp_id() . ')');

            $log = $this->log_upd_link();
            $log->old_from = $db_rec->fob();
            $log->new_from = $this->fob();
            $log->std_from = $std_rec->fob();
            $log->old_to = $db_rec->tob();
            $log->new_to = $this->tob();
            $log->std_to = $std_rec->tob();

            $log->row_id = $this->id();
            if ($log->add()) {
                $db_con->set_class($this::class);
                $db_con->set_usr($this->user()->id());
                if (!$db_con->update_old($this->id(),
                    array($this->from_name . sql_db::FLD_EXT_ID, $this->from_name . sql_db::FLD_EXT_ID),
                    array($this->fob()->id(), $this->tob()->id()))) {
                    $result .= 'update from link to ' . $this->from_name . 'failed';
                }
            }
        }
        log_debug('for ' . $this->dsp_id() . ' done');
        return $result;
    }

    /**
     * check if the unique key (not the db id) of two user sandbox object is the same if the object type is the same, so the simple case
     * @param object $obj_to_check the object used for the comparison
     * @return bool true if the objects represent the same link
     */
    function is_same_std(object $obj_to_check): bool
    {
        $result = false;
        if (isset($this->fob)
            and isset($this->tob)
            and isset($obj_to_check->fob)
            and isset($obj_to_check->tob)) {
            if ($this->fob->id() == $obj_to_check->fob->id() and
                $this->tob->id() == $obj_to_check->tob->id()) {
                $result = true;
            }
        } elseif ($obj_to_check::class == triple::class) {
            if (isset($this->fob)
                and $this->has_verb()
                and isset($this->tob)
                and isset($obj_to_check->fob)
                and $obj_to_check->has_verb()
                and isset($obj_to_check->tob)) {
                if ($this->fob->id() == $obj_to_check->fob->id()
                    and $this->predicate_id() == $obj_to_check->predicate_id()
                    and $this->tob->id() == $obj_to_check->tob->id()) {
                    $result = true;
                }
            }
        }
        return $result;
    }

    /**
     * check if an object with the unique key already exists
     * returns null if no similar object is found
     * or returns the object with the same unique key that is not the actual object
     * any warning or error message needs to be created in the calling function
     * e.g. if the user tries to create a formula named "millions"
     *      but a word with the same name already exists, a term with the word "millions" is returned
     *      in this case the calling function should suggest the user to name the formula "scale millions"
     *      to prevent confusion when writing a formula where all words, phrases, verbs and formulas should be unique
     * @returns string a filled object that links the same objects
     *                 or a sandbox object with id() = 0 if nothing similar has been found
     */
    function get_similar(): sandbox
    {
        $result = new sandbox($this->user());

        // check potential duplicate by name
        // check for linked objects
        if (!isset($this->fob) or !isset($this->tob)) {
            log_err('The linked objects for ' . $this->dsp_id() . ' are missing.', '_sandbox->get_similar');
        } else {
            $db_chk = clone $this;
            $db_chk->reset();
            $db_chk->set_fob($this->fob());
            $db_chk->set_tob($this->tob());
            $db_chk->set_predicate_id($this->predicate_id());
            if ($db_chk->load_standard()) {
                if ($db_chk->id() > 0) {
                    log_debug('the ' . $this->fob->name() . ' "' . $this->fob->name() . '" is already linked to "' . $this->tob->name() . '" of the standard linkspace');
                    $result = $db_chk;
                }
            }
            // check with the user link space
            $db_chk->set_user($this->user());
            if ($db_chk->load_by_link_id($this->fob->id(), 0, $this->tob->id(), $this::class)) {
                if ($db_chk->id() > 0) {
                    log_debug('the ' . $this->fob->name() . ' "' . $this->fob->name() . '" is already linked to "' . $this->tob->name() . '" of the user linkspace');
                    $result = $db_chk;
                }
            }
        }

        return $result;
    }


    /*
     * sql write
     */

    /**
     * create the sql statement to add a new link sandbox object e.g. triple to the database
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

        // init the function body
        $id_field = $sc->id_field_name();

        // get the parameters used for the table key
        $fvt_from = $fvt_lst->get($this->from_field());
        $fvt_type = $fvt_lst->get($this->type_field());
        $fvt_to = $fvt_lst->get($this->to_field());

        // create the list of parameters in order of the function usage
        $fvt_insert_list = new sql_par_field_list();
        $fvt_insert_list->add_id_part($fvt_from);
        $fvt_insert_list->add_id_part($fvt_type);
        if ($fvt_to->id == null) {
            // for the external reference key
            $fvt_insert_list->add($fvt_to);
        } else {
            $fvt_insert_list->add_id_part($fvt_to);
        }

        // create the sql to insert the row
        $sql = '';
        $sc_insert = clone $sc;
        $qp_insert = $this->sql_common($sc_insert, $sc_par_lst_sub, $ext);;
        $sc_par_lst_sub->add(sql_type::SELECT_FOR_INSERT);
        if ($sc->db_type == sql_db::MYSQL) {
            $sc_par_lst_sub->add(sql_type::NO_ID_RETURN);
        }
        $qp_insert->sql = $sc_insert->create_sql_insert(
            $fvt_insert_list, $sc_par_lst_sub, true, '', '', '', $id_fld_new);
        $qp_insert->par = [$fvt_from->value, $fvt_type->value, $fvt_to->value];

        // add the insert row to the function body
        $sql .= ' ' . $qp_insert->sql . '; ';

        // get the new row id for MySQL db
        if ($sc->db_type == sql_db::MYSQL and !$usr_tbl) {
            $sql .= ' ' . sql::LAST_ID_MYSQL . $sc->var_name_row_id($sc_par_lst_sub) . '; ';
        }

        /*
        $fvt_split_list = new sql_par_field_list();
        $fvt_split_list->add_with_split($fvt_from);
        $fvt_split_list->add_with_split($fvt_type);
        $fvt_split_list->add_with_split($fvt_to);
        */

        $qp->sql = $sql;
        $qp->par_fld_lst = $fvt_insert_list;

        return $qp;
    }

    /**
     * @return sql_par_field_list with the text values of the linked items for the log
     */
    function sql_key_fields_text(sql_par_field_list $fvt_lst): sql_par_field_list
    {
        $fvt_lst_out = new sql_par_field_list();
        $from_name = $fvt_lst->get_value($this->from_field());
        $type_name = $fvt_lst->get_value($this->type_field());
        $to_name = $fvt_lst->get_value($this->to_field());
        if ($this->is_excluded()) {
            $from_name = null;
            $type_name = null;
            $to_name = null;
        }
        $fvt_lst_out->add_field(
            change_link::FLD_NEW_FROM_TEXT,
            $from_name,
            sql_field_type::NAME
        );
        $fvt_lst_out->add_field(
            change_link::FLD_NEW_LINK_TEXT,
            $type_name,
            sql_field_type::NAME
        );
        $fvt_lst_out->add_field(
            change_link::FLD_NEW_TO_TEXT,
            $to_name,
            sql_field_type::NAME
        );
        return $fvt_lst_out;
    }

    /**
     * @param sql_par_field_list $fvt_lst list of all
     * @return sql_par_field_list with the text values of the linked items for the log
     */
    function sql_key_fields_text_old(sql_par_field_list $fvt_lst): sql_par_field_list
    {
        $fvt_lst_out = new sql_par_field_list();
        $from_name = $fvt_lst->get_old($this->from_field());
        $type_name = $fvt_lst->get_old($this->type_field());
        $to_name = $fvt_lst->get_old($this->to_field());
        $fvt_lst_out->add_field(
            change_link::FLD_OLD_FROM_TEXT,
            $from_name,
            sql_field_type::NAME
        );
        $fvt_lst_out->add_field(
            change_link::FLD_OLD_LINK_TEXT,
            $type_name,
            sql_field_type::NAME
        );
        $fvt_lst_out->add_field(
            change_link::FLD_OLD_TO_TEXT,
            $to_name,
            sql_field_type::NAME
        );
        return $fvt_lst_out;
    }

    /**
     * @return sql_par_field_list with the text values of the linked items for the log
     */
    function sql_key_fields_id(sql_par_field_list $fvt_lst): sql_par_field_list
    {
        $fvt_lst_out = new sql_par_field_list();
        $from_id = $fvt_lst->get_id($this->from_field());
        $type_id = $fvt_lst->get_id($this->type_field());
        $to_id = $fvt_lst->get_id($this->to_field());
        if ($this->is_excluded()) {
            $from_id = null;
            $type_id = null;
            $to_id = null;
        }
        $fvt_lst_out->add_field(
            change_link::FLD_NEW_FROM_ID,
            $from_id,
            sql_field_type::INT
        );
        $fvt_lst_out->add_field(
            change_link::FLD_NEW_LINK_ID,
            $type_id,
            sql_field_type::INT_SMALL
        );
        $fvt_lst_out->add_field(
            change_link::FLD_NEW_TO_ID,
            $to_id,
            sql_field_type::INT
        );
        return $fvt_lst_out;
    }

    /**
     * @return sql_par_field_list with the text values of the linked items for the log
     */
    function sql_key_fields_id_old(sql_par_field_list $fvt_lst): sql_par_field_list
    {
        $fvt_lst_out = new sql_par_field_list();
        $from_id = $fvt_lst->get_old_id($this->from_field());
        $type_id = $fvt_lst->get_old_id($this->type_field());
        $to_id = $fvt_lst->get_old_id($this->to_field());
        $fvt_lst_out->add_field(
            change_link::FLD_OLD_FROM_ID,
            $from_id,
            sql_field_type::INT
        );
        $fvt_lst_out->add_field(
            change_link::FLD_OLD_LINK_ID,
            $type_id,
            sql_field_type::INT_SMALL
        );
        if ($to_id != null) {
            $fvt_lst_out->add_field(
                change_link::FLD_OLD_TO_ID,
                $to_id,
                sql_field_type::INT
            );
        }
        return $fvt_lst_out;
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
    function db_all_fields_link(sql_type_list $sc_par_lst): array
    {
        $usr_tbl = $sc_par_lst->is_usr_tbl();
        if ($usr_tbl) {
            return [$this::FLD_ID,
                user::FLD_ID
            ];
        } else {
            return [$this::FLD_ID,
                user::FLD_ID,
                $this->from_field(),
                $this->to_field()
            ];
        }
    }

    /**
     * get a list of database field names, values and types that have been updated
     * of the object to combine the list with the list of the child object e.g. word
     *
     * @param sandbox|sandbox_link $sbx the same named sandbox as this to compare which fields have been changed
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list with the field names of the object and any child object
     */
    function db_fields_changed(
        sandbox|sandbox_link $sbx,
        sql_type_list        $sc_par_lst = new sql_type_list()
    ): sql_par_field_list
    {
        global $cng_fld_cac;

        $lst = new sql_par_field_list();
        $sc = new sql_creator();
        $usr_tbl = $sc_par_lst->is_usr_tbl();
        $is_insert = $sc_par_lst->is_insert();
        $is_delete = $sc_par_lst->is_delete();
        $do_log = $sc_par_lst->incl_log();
        $table_id = $sc->table_id($this::class);

        // for insert statements of user sandbox rows user id fields always needs to be included
        if ($usr_tbl and $is_insert) {
            $lst->add_id_and_user($this);
        } else {
            $lst->add_user($this, $sbx, $do_log, $table_id);
        }
        // the link type cannot be changed by the user, because this would be another link
        if (!$usr_tbl) {
            if ($sbx->from_id() <> $this->from_id()) {
                if ($do_log) {
                    $lst->add_field(
                        sql::FLD_LOG_FIELD_PREFIX . $this->from_field(),
                        $cng_fld_cac->id($table_id . $this->from_field()),
                        change::FLD_FIELD_ID_SQL_TYP
                    );
                }
                // TODO Prio 2: move "from_" to a const and or function
                $lst->add_link_field(
                    $this->from_field(),
                    'from_' . $this->fob()?->name_field(),
                    $this->fob(),
                    $sbx->fob()
                );
            }
            if ($sbx->to_id() <> $this->to_id()) {
                if ($do_log) {
                    $lst->add_field(
                        sql::FLD_LOG_FIELD_PREFIX . $this->to_field(),
                        $cng_fld_cac->id($table_id . $this->to_field()),
                        change::FLD_FIELD_ID_SQL_TYP
                    );
                }
                // e.g. for external references
                if ($this->tob == null and $sbx->tob == null) {
                    $lst->add_field(
                        $this->to_field(),
                        $this->to_value(),
                        sql_field_type::TEXT,
                        $sbx->to_value()
                    );
                } else {
                    // TODO Prio 2: move "to_" to a const and or function
                    $lst->add_link_field(
                        $this->to_field(),
                        'to_' . $this->tob()?->name_field(),
                        $this->tob(),
                        $sbx->tob()
                    );
                }
            }
        } else {
            // add the from and to fields even if the objects are the same in case of an insert exclude or delete to identify the rows
            $from_fld = '';
            $to_fld = '';
            if ($is_insert) {
                $from_fld = $this->fob()?->name_field();
                if ($this->tob() == null) {
                    // e.g. for references the external key
                    $to_fld = $this->to_field();
                } else {
                    if (is_string($this->tob())) {
                        // e.g. for references the external key
                        $to_fld = $this->to_field();
                    } else {
                        $to_fld = $this->tob()->name_field();
                    }
                }
            }
            if ($is_delete) {
                $from_fld = $sbx->fob()?->name_field();
                if ($sbx->tob() == null) {
                    // e.g. for references the external key
                    $to_fld = $sbx->to_field();
                } else {
                    if (is_string($sbx->tob())) {
                        // e.g. for references the external key
                        $to_fld = $sbx->to_field();
                    } else {
                        $to_fld = $sbx->tob()->name_field();
                    }
                }
            }
            if ($is_insert or $is_delete) {
                if ($from_fld == $to_fld) {
                    $from_fld = sql::FROM_FLD_PREFIX . $from_fld;
                    $to_fld = sql::TO_FLD_PREFIX . $to_fld;
                }
                // TODO check how to handle if the standard
                if ($this->is_excluded() and !$sbx->is_excluded() or $is_delete) {
                    if ($do_log) {
                        $lst->add_field(
                            sql::FLD_LOG_FIELD_PREFIX . $this->from_field(),
                            $cng_fld_cac->id($table_id . $this->from_field()),
                            change::FLD_FIELD_ID_SQL_TYP
                        );
                    }
                    $lst->add_link_field(
                        $this->from_field(),
                        $from_fld,
                        null,
                        $sbx->fob()
                    );
                    if ($do_log) {
                        $lst->add_field(
                            sql::FLD_LOG_FIELD_PREFIX . $this->to_field(),
                            $cng_fld_cac->id($table_id . $this->to_field()),
                            change::FLD_FIELD_ID_SQL_TYP
                        );
                    }
                    if ($this::class == ref::class) {
                        $lst->add_field(
                            $this->to_field(),
                            null,
                            sandbox_named::FLD_NAME_SQL_TYP,
                            $sbx->to_value(),
                            $to_fld,
                            null,
                            null,
                            db_object_seq_id::FLD_ID_SQL_TYP
                        );
                    } else {
                        $lst->add_link_field(
                            $this->to_field(),
                            $to_fld,
                            null,
                            $sbx->tob()
                        );
                    }
                } elseif (!$this->is_excluded() and $sbx->is_excluded()) {
                    if ($do_log) {
                        $lst->add_field(
                            sql::FLD_LOG_FIELD_PREFIX . $this->from_field(),
                            $cng_fld_cac->id($table_id . $this->from_field()),
                            change::FLD_FIELD_ID_SQL_TYP
                        );
                    }
                    $lst->add_link_field(
                        $this->from_field(),
                        $from_fld,
                        $this->fob(),
                        null
                    );
                    if ($do_log) {
                        $lst->add_field(
                            sql::FLD_LOG_FIELD_PREFIX . $this->to_field(),
                            $cng_fld_cac->id($table_id . $this->to_field()),
                            change::FLD_FIELD_ID_SQL_TYP
                        );
                    }
                    if ($this::class == ref::class) {
                        $lst->add_field(
                            $this->to_field(),
                            $this->tob(),
                            sandbox_named::FLD_NAME_SQL_TYP,
                            null,
                            $to_fld,
                            $this->tob(),
                            null,
                            db_object_seq_id::FLD_ID_SQL_TYP
                        );
                    } else {
                        $lst->add_link_field(
                            $this->to_field(),
                            $to_fld,
                            $this->tob(),
                            null
                        );
                    }
                }
            }
        }
        return $lst;
    }


    /*
     * internal
     */

    /**
     * dummy function definition that should not be called
     * TODO check why it is called
     * @return user_message
     */
    protected function check_save(): user_message
    {
        log_warning('The dummy parent method get_similar has been called, which should never happen');
        return new user_message();
    }


    /**
     * @return bool true if this sandbox object links two objects (final function)
     */
    function is_link_obj(): bool
    {
        return true;
    }

    /**
     * @return bool true if this sandbox object has a name as unique key (final function)
     */
    function is_named_obj(): bool
    {
        return false;
    }


    /*
     * sql write
     */

    /**
     * create the sql statement to add a new sandbox link object to the database
     * always all fields are included in the query to be able to remove overwrites with a null value
     * TODO check first the query name and skip the sql building if not needed
     *
     * @param sql_creator $sc with the target db_type set
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement and the parameter list
     */
    function sql_insert(
        sql_creator   $sc,
        sql_type_list $sc_par_lst = new sql_type_list()
    ): sql_par
    {
        // clone the sql parameter list to avoid changing the given list
        $sc_par_lst_used = clone $sc_par_lst;
        // set the sql query type
        $sc_par_lst_used->add(sql_type::INSERT);
        // fields and values that the word has additional to the standard named user sandbox object
        $lnk_empty = $this->clone_reset();
        // for a new component link the owner should be set, so remove the user id to force writing the user
        $lnk_empty->set_user($this->user()->clone_reset());
        // for linked user db rows, use the link fields of the standard row, because the link itself cannot be changed by the user
        if ($sc_par_lst_used->is_usr_tbl()) {
            $lnk_empty = $this->set_link_objects($lnk_empty);
        }
        // get the list of the changed fields
        $fvt_lst = $this->db_fields_changed($lnk_empty, $sc_par_lst_used);
        // get the list of all fields that can be changed by the user
        $all_fields = $this->db_fields_all($sc_par_lst_used);
        // create either the prepared sql query or a sql function that includes the logging of the changes
        return parent::sql_insert_switch($sc, $fvt_lst, $all_fields, $sc_par_lst_used);
    }

    /**
     * create the sql statement to update a sandbox link object in the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param sandbox $db_row the word with the database values before the update
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement and the parameter list
     */
    function sql_update(
        sql_creator   $sc,
        sandbox       $db_row,
        sql_type_list $sc_par_lst = new sql_type_list()
    ): sql_par
    {
        // clone the sql parameter list to avoid changing the given list
        $sc_par_lst_used = clone $sc_par_lst;
        // set the sql query type
        $sc_par_lst_used->add(sql_type::UPDATE);
        // get the field names, values and parameter types that have been changed
        // and that needs to be updated in the database
        // the db_* child function call the corresponding parent function
        // including the sql parameters for logging
        $fld_lst = $this->db_fields_changed($db_row, $sc_par_lst_used);
        $all_fields = $this->db_fields_all($sc_par_lst_used);
        // unlike the db_* function the sql_update_* parent function is called directly
        return $this::sql_update_switch($sc, $fld_lst, $all_fields, $sc_par_lst_used);
    }


    /*
     * debug
     */

    /**
     * @return string with the best possible identification for this object mainly used for debugging
     */
    function dsp_id(): string
    {
        $result = '';
        if ($this->fob() != null) {
            $result .= 'from ' . $this->fob()->dsp_id(false) . ' ';
        }
        if ($this->tob() != null) {
            $result .= 'to ' . $this->tob()->dsp_id(false);
        }

        $result .= ' as' . parent::dsp_id();
        return $result;
    }

    /**
     * @return string with the ids of the link e.g. 1/2/3
     */
    function link_id(): string
    {
        return $this->from_id() . '/' . $this->predicate_id() . '/' . $this->to_id();

    }

}


