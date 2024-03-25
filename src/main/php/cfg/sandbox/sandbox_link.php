<?php

/*

    model/sandbox/sandbox_link.php - the superclass for handling user specific link objects including the database saving
    ------------------------------

    This superclass should be used by the class word links, formula links and view link

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

namespace cfg;

use cfg\db\sql;
use cfg\db\sql_db;
use cfg\db\sql_par;
use cfg\log\change_action;
use cfg\log\change_action_list;
use cfg\log\change_link;
use Exception;

class sandbox_link extends sandbox
{

    /*
     * database link
     */

    // list of fields that select the objects that should be linked
    // dummy array to enable references here and is overwritten by the child object
    const FLD_LST_LINK = array();
    const FLD_LST_MUST_BUT_STD_ONLY = array();


    /*
     * object vars
     */

    public ?object $fob = null;        // the object from which this linked object is creating the connection
    public ?object $tob = null;        // the object to   which this linked object is creating the connection

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
    }


    /*
     * set and get
     */

    function set_fob(object $fob): void
    {
        $this->fob = $fob;
    }

    function fob(): object
    {
        return $this->fob;
    }

    function set_tob(object $tob): void
    {
        $this->tob = $tob;
    }

    function tob(): object
    {
        return $this->tob;
    }


    /*
     * sql create
     */

    /**
     * create an array with the fields and parameters for the sql table creation of the link object
     * @param bool $usr_table create a second table for the user overwrites
     * @param bool $is_sandbox true if the standard sandbox fields should be included
     * @return array[] with the parameters of the table fields
     */
    protected function sql_all_field_par(bool $usr_table = false, bool $is_sandbox = true): array
    {
        if (!$usr_table) {
            // the primary id field is always the first
            $fields = $this->sql_id_field_par(false);
            // the link fields are not repeated in the user table because they cannot be changed individually
            $fields = array_merge($fields, $this::FLD_LST_LINK);
            // set the owner of the link
            $fields = array_merge($fields, sandbox::FLD_ALL_OWNER);
            // mandatory fields that can be changed the user
            $fields = array_merge($fields, $this::FLD_LST_MUST_BUT_STD_ONLY);
            // fields that can be changed the user but are empty if the user has not done an overwrite
            $fields = array_merge($fields, $this::FLD_LST_USER_CAN_CHANGE);
            $fields = array_merge($fields, $this::FLD_LST_NON_CHANGEABLE);
        } else {
            // the primary id field is always the first
            $fields = $this->sql_id_field_par(true);
            // a user overwrite must always have a user
            $fields = array_merge($fields, sandbox::FLD_ALL_CHANGER);
            // mandatory fields that can be changed the user
            $fields = array_merge($fields, $this::FLD_LST_MUST_BUT_USER_CAN_CHANGE);
            // fields that can be changed the user but are empty if the user has not done an overwrite
            $fields = array_merge($fields, $this::FLD_LST_USER_CAN_CHANGE);
        }
        if ($is_sandbox) {
            $fields = array_merge($fields, sandbox::FLD_LST_ALL);
        }
        return $fields;
    }


    /*
     * loading / database access object (DAO) functions
     */

    /**
     * create an SQL statement to retrieve a user sandbox link by the ids of the linked objects from the database
     *
     * @param sql $sc with the target db_type set
     * @param int $from the subject object id
     * @param int $type the predicate object id
     * @param int $to the object (grammar) object id
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_link(sql $sc, int $from, int $type, int $to, string $class): sql_par
    {
        if ($type > 0) {
            $qp = $this->load_sql($sc, 'link_type_ids', $class);
            $sc->add_where($this->from_field(), $from);
            $sc->add_where($this->type_field(), $type);
        } else {
            $qp = $this->load_sql($sc, 'link_ids', $class);
            $sc->add_where($this->from_field(), $from);
        }
        $sc->add_where($this->to_field(), $to);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * load a named user sandbox object by name
     * @param int $from the subject object id
     * @param int $type the predicate object id
     * @param int $to the object (grammar) object id
     * @param string $class the name of the child class from where the call has been triggered
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_link_id(int $from, int $type = 0, int $to = 0, string $class = ''): int
    {
        global $db_con;

        $lib = new library();
        log_debug($lib->dsp_array(array($from, $type, $to)));
        $qp = $this->load_sql_by_link($db_con->sql_creator(), $from, $type, $to, $class);
        return parent::load($qp);
    }


    /*
     * dummy load related function that are overwritten by the child objects
     */

    /**
     * dummy function for the subject object that should always be overwritten by the child object
     * @return string
     */
    function from_field(): string
    {
        return '';
    }

    /**
     * dummy function for the predicate object that should always be overwritten by the child object
     * @return string
     */
    function type_field(): string
    {
        return '';
    }

    /**
     * dummy function for the object (grammar) object (computer science)
     * that should always be overwritten by the child object
     * @return string
     */
    function to_field(): string
    {
        return '';
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

    /**
     * fill a similar object that is extended with display interface functions
     * @param object $dsp_obj
     *
     * @return void
     */
    function fill_dsp_obj(object $dsp_obj): void
    {
        parent::fill_dsp_obj($dsp_obj);

        if ($this->fob != null) {
            $dsp_obj->fob = $this->fob->dsp_obj();
        }
        if ($this->tob != null) {
            $dsp_obj->tob = $this->tob->dsp_obj();
        }
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
     * @return bool true if all mandatory vars of the object are set to save the object
     */
    function is_valid(): bool
    {
        $result = false;
        if ($this->fob != null and $this->tob != null) {
            if ($this->fob->id() > 0 and $this->tob->id() != 0) {
                $result = true;
            }
        }
        if (!$result) {
            log_warning("The formula link " . $this->dsp_id()
                . " is not unique", "formula_link->load");
        }
        return $result;
    }


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

        $log = new change_link($this->user());
        $log->new_from = $this->fob;
        $log->new_to = $this->tob;

        $log->action = change_action::ADD;
        // TODO add the table exceptions from sql_db
        $log->set_table($this->obj_name . sql_db::TABLE_EXTENSION);
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

        $log = new change_link($this->user());
        $log->action = change_action::DELETE;
        $log->set_table($this->obj_name . sql_db::TABLE_EXTENSION);
        $log->old_from = $this->fob();
        $log->old_to = $this->tob();

        $log->row_id = $this->id();
        $log->add();

        return $log;
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
            array($this->from_name . sql_db::FLD_EXT_ID, $this->to_name . sql_db::FLD_EXT_ID, "user_id"),
            array($this->fob->id, $this->tob->id, $this->user()->id));
    }

    /**
     * create a new link object and log the change
     * @return user_message with status ok
     *                      or if something went wrong
     *                      the message that should be shown to the user
     *                      including suggested solutions
     * TODO do a rollback in case of an error
     */
    function add(): user_message
    {
        log_debug($this->dsp_id());

        global $db_con;
        $result = new user_message();

        // log the insert attempt first
        $log = $this->log_link_add();
        if ($log->id() > 0) {

            // insert the new object and save the object key
            // TODO check that always before a db action is called the db type is set correctly
            $db_con->set_class($this::class);
            $db_con->set_usr($this->user()->id);
            $this->id = $this->add_insert();

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
                    $db_rec->fob = $this->fob;
                    $db_rec->tob = $this->tob;
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
     * @param sandbox_link $db_rec the object data as it is now in the database
     * @return bool true if one of the object id fields have been changed
     */
    function is_id_updated_link(sandbox $db_rec): bool
    {
        $result = False;
        log_debug($this->dsp_id());

        if ($db_rec->fob->id <> $this->fob->id
            or $db_rec->tob->id <> $this->tob->id) {
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
        return 'A ' . $this->obj_name . ' from ' . $this->fob->dsp_id() . ' to ' . $this->tob->dsp_id() . ' already exists.';
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
            $log->old_from = $db_rec->fob;
            $log->new_from = $this->fob;
            $log->std_from = $std_rec->fob;
            $log->old_to = $db_rec->tob;
            $log->new_to = $this->tob;
            $log->std_to = $std_rec->tob;

            $log->row_id = $this->id;
            if ($log->add()) {
                $db_con->set_class($this::class);
                $db_con->set_usr($this->user()->id);
                if (!$db_con->update_old($this->id,
                    array($this->from_name . sql_db::FLD_EXT_ID, $this->from_name . sql_db::FLD_EXT_ID),
                    array($this->fob->id, $this->tob->id))) {
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
            if ($this->fob->id == $obj_to_check->fob->id and
                $this->tob->id == $obj_to_check->tob->id) {
                $result = true;
            }
        } elseif ($obj_to_check::class == triple::class) {
            if (isset($this->fob)
                and isset($this->verb)
                and isset($this->tob)
                and isset($obj_to_check->fob)
                and isset($obj_to_check->verb)
                and isset($obj_to_check->tob)) {
                if ($this->fob->id() == $obj_to_check->fob->id()
                    and $this->verb->id() == $obj_to_check->verb->id()
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
            $db_chk->fob = $this->fob;
            $db_chk->tob = $this->tob;
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
     * internal
     */

    /**
     * dummy function definition that should not be called
     * TODO check why it is called
     * @return string
     */
    protected function check_preserved(): string
    {
        log_warning('The dummy parent method get_similar has been called, which should never happen');
        return '';
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
        if ($this->fob != null or $this->tob != null) {
            if ($this->fob != null) {
                $result .= 'from ' . $this->fob->dsp_id(false) . ' ';
            }
            if ($this->tob != null) {
                $result .= 'to ' . $this->tob->dsp_id(false);
            }
        } else {
            $result .= $this->name() . ' (' . $this->id() . ') of type ' . $this::class;
        }
        $result .= ' as' . parent::dsp_id();
        return $result;
    }

}


