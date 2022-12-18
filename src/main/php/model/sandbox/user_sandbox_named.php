<?php

/*

    user_sandbox_named.php - the superclass for handling user specific named objects including the database saving
    ---------------------

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

use api\formula_api;
use api\phrase_api;
use api\source_api;
use api\view_api;
use api\view_cmp_api;
use api\word_api;

class user_sandbox_named extends user_sandbox
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

    /*
     * set and get
     */

    /**
     * set the most used object vars with one set statement
     * @param int $id mainly for test creation the database id of the named user sandbox object
     * @param string $name mainly for test creation the name of the named user sandbox object
     * @param string $type_code_id the code id of the predefined object type only used by some child objects
     */
    public function set(int $id = 0, string $name = '', string $type_code_id = ''): void
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
    public function set_name(string $name): void
    {
        $this->name = $name;
    }

    /**
     * get the name of the word object
     *
     * @return string the name from the object e.g. word using the same function as the phrase and term
     */
    public function name(): string
    {
        return $this->name;
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
        $trm->obj = $this;
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
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_standard_sql(sql_db $db_con, string $class = self::class): sql_par
    {
        $qp = new sql_par($class, true);
        if ($this->id != 0) {
            $qp->name .= 'id';
        } elseif ($this->name() != '') {
            $qp->name .= 'name';
        } else {
            log_err('Either the id or name must be set to get a named user sandbox object');
        }

        $db_con->set_name($qp->name);
        $db_con->set_usr($this->user()->id);
        if ($this->id != 0) {
            $db_con->add_par(sql_db::PAR_INT, $this->id);
            $qp->sql = $db_con->select_by_set_id();
        } else {
            $db_con->add_par(sql_db::PAR_TEXT, $this->name);
            $qp->sql = $db_con->select_by_set_name();
        }
        $qp->par = $db_con->get_par();

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
            $result = $this->row_mapper($db_row, false);
        }
        return $result;
    }

    /**
     * create an SQL statement to retrieve a term by name from the database
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $name the name of the term and the related word, triple, formula or verb
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_name(sql_db $db_con, string $name, string $class): sql_par
    {
        $qp = $this->load_sql($db_con, 'name', $class);
        $db_con->set_where_name($name, $this->name_field());
        $qp->sql = $db_con->select_by_set_id();
        $qp->par = $db_con->get_par();

        return $qp;
    }

    /**
     * load a named user sandbox object by name
     * @param string $name the name of the word, triple, formula, verb, view or view component
     * @param string $class the name of the child class from where the call has been triggered
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_name(string $name, string $class = self::class): int
    {
        global $db_con;

        log_debug($name);
        $qp = $this->load_sql_by_name($db_con, $name, $class);
        return parent::load($qp);
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
     * debug
     */

    /**
     * @return string the best possible identification for this object mainly used for debugging
     */
    function dsp_id(): string
    {
        $result = '';
        if ($this->name() <> '') {
            $result .= '"' . $this->name() . '"';
            if ($this->id > 0) {
                $result .= ' (' . $this->id . ')';
            }
        } else {
            $result .= $this->id;
        }
        if ($this->user()->is_set()) {
            $result .= ' for user ' . $this->user()->id . ' (' . $this->user()->name . ')';
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
    function log_add(): user_log_named
    {
        log_debug($this->dsp_id());

        $log = new user_log_named;
        $log->field = $this->obj_name . '_name';
        $log->old_value = '';
        $log->new_value = $this->name();

        $log->usr = $this->user();
        $log->action = user_log::ACTION_ADD;
        // TODO add the table exceptions from sql_db
        $log->table = $this->obj_name . 's';
        $log->row_id = 0;
        $log->add();

        return $log;
    }

    /**
     * set the log entry parameter to delete a object
     * @returns user_log_link with the object presets e.g. th object name
     */
    function log_del(): user_log_named
    {
        log_debug($this->dsp_id());

        $log = new user_log_named;
        $log->field = $this->obj_name . '_name';
        $log->old_value = $this->name();
        $log->new_value = '';

        $log->usr = $this->user();
        $log->action = user_log::ACTION_DELETE;
        $log->table = $this->obj_name . 's';
        $log->row_id = $this->id;
        $log->add();

        return $log;
    }

    /**
     * check if this object uses any preserved names and if return a message to the user
     *
     * @return string
     */
    protected function check_preserved(): string
    {
        global $usr;

        $result = '';
        if (!$usr->is_system()) {
            if ($this->obj_type == user_sandbox::TYPE_NAMED) {
                if ($this->obj_name == sql_db::TBL_WORD) {
                    if (in_array($this->name, word_api::RESERVED_WORDS)) {
                        // the admin user needs to add the read test word during initial load
                        if ($usr->is_admin() and $this->name() != word_api::TN_READ) {
                            $result = '"' . $this->name() . '" is a reserved name for system testing. Please use another name';
                        }
                    }
                } elseif ($this->obj_name == sql_db::TBL_PHRASE) {
                    if (in_array($this->name, phrase_api::RESERVED_PHRASES)) {
                        $result = '"' . $this->name() . '" is a reserved phrase name for system testing. Please use another name';
                    }
                } elseif ($this->obj_name == sql_db::TBL_FORMULA) {
                    if (in_array($this->name, formula_api::RESERVED_FORMULAS)) {
                        $result = '"' . $this->name() . '" is a reserved formula name for system testing. Please use another name';
                    }
                } elseif ($this->obj_name == sql_db::TBL_VIEW) {
                    if (in_array($this->name, view_api::RESERVED_VIEWS)) {
                        $result = '"' . $this->name() . '" is a reserved view name for system testing. Please use another name';
                    }
                } elseif ($this->obj_name == sql_db::TBL_VIEW_COMPONENT) {
                    if (in_array($this->name, view_cmp_api::RESERVED_VIEW_COMPONENTS)) {
                        $result = '"' . $this->name() . '" is a reserved view component name for system testing. Please use another name';
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
     * create a new named object
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
        $log = $this->log_add();
        if ($log->id > 0) {

            // insert the new object and save the object key
            // TODO check that always before a db action is called the db type is set correctly
            $db_con->set_type($this->obj_name);
            $db_con->set_usr($this->user()->id);
            $this->id = $db_con->insert(array($this->obj_name . '_name', "user_id"), array($this->name, $this->user()->id));

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
     * @param user_sandbox $db_rec the object data as it is now in the database
     * @return bool true if one of the object id fields have been changed
     */
    function is_id_updated(user_sandbox $db_rec): bool
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
     * set the update parameters for the word description
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param user_sandbox_named $db_rec the database record before the saving
     * @param user_sandbox_named $std_rec the database record defined as standard because it is used by most users
     * @return string if not empty the message that should be shown to the user
     */
    function save_field_description(sql_db $db_con, user_sandbox_named $db_rec, user_sandbox_named $std_rec): string
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
                $log->field = self::FLD_DESCRIPTION;
                $result = $this->save_field_do($db_con, $log);
            }
        }
        return $result;
    }

    /**
     * save all updated source fields excluding the name, because already done when adding a source
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param user_sandbox_named $db_rec the database record before the saving
     * @param user_sandbox_named $std_rec the database record defined as standard because it is used by most users
     * @return string if not empty the message that should be shown to the user
     */
    function save_fields_named(sql_db $db_con, user_sandbox_named $db_rec, user_sandbox_named $std_rec): string
    {
        $result = $this->save_field_description($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_excluded($db_con, $db_rec, $std_rec);
        return $result;
    }

    /**
     * updated the object id fields (e.g. for a word or formula the name, and for a link the linked ids)
     * should only be called if the user is the owner and nobody has used the display component link
     * @param sql_db $db_con the active database connection
     * @param user_sandbox $db_rec the database record before the saving
     * @param user_sandbox $std_rec the database record defined as standard because it is used by most users
     * @returns string either the id of the updated or created source or a message to the user with the reason, why it has failed
     * @throws Exception
     */
    function save_id_fields(sql_db $db_con, user_sandbox $db_rec, user_sandbox $std_rec): string
    {
        $result = '';
        log_debug($this->dsp_id());

        if ($this->is_id_updated($db_rec)) {
            log_debug('to ' . $this->dsp_id() . ' from ' . $db_rec->dsp_id() . ' (standard ' . $std_rec->dsp_id() . ')');

            $log = $this->log_upd_field();
            $log->old_value = $db_rec->name();
            $log->new_value = $this->name();
            $log->std_value = $std_rec->name();
            $log->field = $this->obj_name . '_name';

            $log->row_id = $this->id;
            if ($log->add()) {
                $db_con->set_type($this->obj_name);
                $db_con->set_usr($this->user()->id);
                if (!$db_con->update($this->id,
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
     * check if an object with the unique key already exists
     * returns null if no similar object is found
     * or returns the object with the same unique key that is not the actual object
     * any warning or error message needs to be created in the calling function
     * e.g. if the user tries to create a formula named "millions"
     *      but a word with the same name already exists, a term with the word "millions" is returned
     *      in this case the calling function should suggest the user to name the formula "scale millions"
     *      to prevent confusion when writing a formula where all words, phrases, verbs and formulas should be unique
     * @return user_sandbox|null a filled object that has the same name
     *                            or null if nothing similar has been found
     */
    function get_similar(): ?user_sandbox
    {
        $result = new user_sandbox_named($this->user());

        // check potential duplicate by name
        // for words and formulas it needs to be checked if a term (word, verb or formula) with the same name already exist
        // for verbs the check is inside the verbs class because verbs are not part of the user sandbox
        if ($this->obj_name == sql_db::TBL_WORD
            or $this->obj_name == sql_db::TBL_TRIPLE
            or $this->obj_name == sql_db::TBL_FORMULA) {
            $similar_trm = $this->get_term();
            if ($similar_trm->id_obj() > 0) {
                $result = $similar_trm->obj;
                if (!$this->is_similar_named($result)) {
                    log_err($this->dsp_id() . ' is supposed to be similar to ' . $result->dsp_id() . ', but it seems not');
                }
            }
        } else {
            // used for view, view_component, source, ...
            $db_chk = clone $this;
            $db_chk->reset();
            $db_chk->set_user($this->user());
            $db_chk->name = $this->name();
            // check with the standard namespace
            if ($db_chk->load_standard()) {
                if ($db_chk->id > 0) {
                    log_debug($this->dsp_id() . ' has the same name is the already existing "' . $db_chk->dsp_id() . '" of the standard namespace');
                    $result = $db_chk;
                }
            }
            // check with the user namespace
            $db_chk->set_user($this->user());
            if ($this->obj_name == sql_db::TBL_WORD
                or $this->obj_name == sql_db::TBL_SOURCE) {
                if ($this->name() != '') {
                    if ($db_chk->load_by_name($this->name())) {
                        if ($db_chk->id() > 0) {
                            log_debug($this->dsp_id() . ' has the same name is the already existing "' . $db_chk->dsp_id() . '" of the user namespace');
                            $result = $db_chk;
                        }
                    }
                } else {
                    log_err('The name must be set to check if a similar obejct exists');
                }
            } else {
                // for all other objects still use the deprecated load_by_vars method
                if ($db_chk->load_obj_vars()) {
                    if ($db_chk->id > 0) {
                        log_debug($this->dsp_id() . ' has the same name is the already existing "' . $db_chk->dsp_id() . '" of the user namespace');
                        $result = $db_chk;
                    }
                }
            }
        }

        if ($result->id() != 0) {
            return $result;
        } else {
            return null;
        }
    }

}


