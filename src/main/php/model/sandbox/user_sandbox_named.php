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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2021 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

class user_sandbox_named extends user_sandbox
{
    /**
     * reset the search values of this object
     * needed to search for the standard object, because the search is work, value, formula or ... specific
     */
    function reset()
    {
        parent::reset();

        $this->name = '';
    }

    /**
     * create the SQL to load the single default value always by the id or name
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_standard_sql(sql_db $db_con, string $class): sql_par
    {
        $qp = new sql_par($class, true);
        if ($this->id != 0) {
            $qp->name .= 'id';
        } elseif ($this->name != '') {
            $qp->name .= 'name';
        } else {
            log_err('Either the id or name must be set to get a named user sandbox object');
        }

        $db_con->set_name($qp->name);
        $db_con->set_usr($this->usr->id);
        if ($this->id != 0) {
            $db_con->add_par(sql_db::PAR_INT, $this->id);
            $qp->sql = $db_con->select_by_id();
        } else {
            $db_con->add_par(sql_db::PAR_TEXT, $this->name);
            $qp->sql = $db_con->select_by_name();
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

        if ($this->id == 0 and $this->name == '') {
            log_err('The ' . $class . ' id or name must be set to load ' . $class, $class . '->load_standard');
        } else {
            $db_row = $db_con->get1($qp);
            $result = $this->row_mapper($db_row, false);
        }
        return $result;
    }

    /**
     * @return object frontend API object filled with unique object name
     */
    function fill_min_obj(object $min_obj): object
    {
        parent::fill_min_obj($min_obj);

        $min_obj->name = $this->name;

        return $min_obj;
    }

    /**
     * fill a similar object that is extended with display interface functions
     *
     * @return object the object fill with all user sandbox value
     */
    function fill_dsp_obj(object $dsp_obj): object
    {
        parent::fill_dsp_obj($dsp_obj);

        $dsp_obj->name = $this->name;

        return $dsp_obj;
    }

    /**
     * return best possible identification for this object mainly used for debugging
     */
    function dsp_id(): string
    {
        $result = '';
        if ($this->name <> '') {
            $result .= '"' . $this->name . '"';
            if ($this->id > 0) {
                $result .= ' (' . $this->id . ')';
            }
        } else {
            $result .= $this->id;
        }
        if (isset($this->usr)) {
            $result .= ' for user ' . $this->usr->id . ' (' . $this->usr->name . ')';
        }
        return $result;
    }

    /**
     * set the log entry parameter for a new named object
     * for all not named objects like links, this function is overwritten
     * e.g. that the user can see "added formula 'scale millions' to word 'mio'"
     */
    function log_add(): user_log_named
    {
        log_debug($this->obj_name . '->log_add ' . $this->dsp_id());

        $log = new user_log_named;
        $log->field = $this->obj_name . '_name';
        $log->old_value = '';
        $log->new_value = $this->name;

        $log->usr = $this->usr;
        $log->action = 'add';
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
        log_debug($this->obj_name . '->log_del ' . $this->dsp_id());

        $log = new user_log_named;
        $log->field = $this->obj_name . '_name';
        $log->old_value = $this->name;
        $log->new_value = '';

        $log->usr = $this->usr;
        $log->action = 'del';
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
                if ($this->obj_name == DB_TYPE_WORD) {
                    if (in_array($this->name, word::RESERVED_WORDS)) {
                        // the admin user needs to add the read test word during initial load
                        if ($usr->is_admin() and $this->name != word::TN_READ) {
                            $result = '"' . $this->name . '" is a reserved name for system testing. Please use another name';
                        }
                    }
                } elseif ($this->obj_name == DB_TYPE_PHRASE) {
                    if (in_array($this->name, phrase::RESERVED_PHRASES)) {
                        $result = '"' . $this->name . '" is a reserved phrase name for system testing. Please use another name';
                    }
                } elseif ($this->obj_name == DB_TYPE_FORMULA) {
                    if (in_array($this->name, formula::RESERVED_FORMULAS)) {
                        $result = '"' . $this->name . '" is a reserved formula name for system testing. Please use another name';
                    }
                } elseif ($this->obj_name == DB_TYPE_VIEW) {
                    if (in_array($this->name, view::RESERVED_VIEWS)) {
                        $result = '"' . $this->name . '" is a reserved view name for system testing. Please use another name';
                    }
                } elseif ($this->obj_name == DB_TYPE_VIEW_COMPONENT) {
                    if (in_array($this->name, view_cmp::RESERVED_VIEW_COMPONENTS)) {
                        $result = '"' . $this->name . '" is a reserved view component name for system testing. Please use another name';
                    }
                } elseif ($this->obj_name == DB_TYPE_SOURCE) {
                    if (in_array($this->name, source::RESERVED_SOURCES)) {
                        // the admin user needs to add the read test source during initial load
                        if ($usr->is_admin() and $this->name != source::TN_READ) {
                            $result = '"' . $this->name . '" is a reserved source name for system testing. Please use another name';
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
        log_debug($this->obj_name . '->add ' . $this->dsp_id());

        global $db_con;
        $result = new user_message();

        // log the insert attempt first
        $log = $this->log_add();
        if ($log->id > 0) {

            // insert the new object and save the object key
            // TODO check that always before a db action is called the db type is set correctly
            $db_con->set_type($this->obj_name);
            $db_con->set_usr($this->usr->id);
            $this->id = $db_con->insert(array($this->obj_name . '_name', "user_id"), array($this->name, $this->usr->id));

            // save the object fields if saving the key was successful
            if ($this->id > 0) {
                log_debug($this->obj_name . '->add ' . $this->obj_type . ' ' . $this->dsp_id() . ' has been added');
                // update the id in the log
                if (!$log->add_ref($this->id)) {
                    $result->add_message('Updating the reference in the log failed');
                    // TODO do rollback or retry?
                } else {
                    //$result->add_message($this->set_owner($new_owner_id));

                    // create an empty db_rec element to force saving of all set fields
                    $db_rec = clone $this;
                    $db_rec->reset();
                    $db_rec->name = $this->name;
                    $db_rec->usr = $this->usr;
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
        log_debug($this->obj_name . '->is_id_updated ' . $this->dsp_id());

        log_debug($this->obj_name . '->is_id_updated compare name ' . $db_rec->name . ' with ' . $this->name);
        if ($db_rec->name <> $this->name) {
            $result = True;
        }

        log_debug($this->obj_name . '->is_id_updated -> (' . zu_dsp_bool($result) . ')');
        return $result;
    }

    /**
     * @return string text that request the user to use another name
     */
    function msg_id_already_used(): string
    {
        return 'A ' . $this->obj_name . ' with the name "' . $this->name . '" already exists. Please use another name.';
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
        log_debug($this->obj_name . '->save_id_fields ' . $this->dsp_id());

        if ($this->is_id_updated($db_rec)) {
            log_debug($this->obj_name . '->save_id_fields to ' . $this->dsp_id() . ' from ' . $db_rec->dsp_id() . ' (standard ' . $std_rec->dsp_id() . ')');

            $log = $this->log_upd_field();
            $log->old_value = $db_rec->name;
            $log->new_value = $this->name;
            $log->std_value = $std_rec->name;
            $log->field = $this->obj_name . '_name';

            $log->row_id = $this->id;
            if ($log->add()) {
                $db_con->set_type($this->obj_name);
                $db_con->set_usr($this->usr->id);
                if (!$db_con->update($this->id,
                    array($this->obj_name . '_name'),
                    array($this->name))) {
                    $result .= 'update of name to ' . $this->name . 'failed';
                }
            }
        }
        log_debug($this->obj_name . '->save_id_fields for ' . $this->dsp_id() . ' done');
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
                if ($this->obj_name == DB_TYPE_WORD or $this->obj_name == DB_TYPE_PHRASE or $this->obj_name == DB_TYPE_FORMULA or $this->obj_name == DB_TYPE_VERB) {
                    if ($this->name == $obj_to_check->name) {
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
     * @return user_sandbox a filled object that has the same name
     */
    function get_similar(): user_sandbox
    {
        $result = new user_sandbox_named($this->usr);

        // check potential duplicate by name
        // for words and formulas it needs to be checked if a term (word, verb or formula) with the same name already exist
        // for verbs the check is inside the verbs class because verbs are not part of the user sandbox
        if ($this->obj_name == DB_TYPE_WORD or $this->obj_name == DB_TYPE_FORMULA) {
            $similar_trm = $this->term();
            if ($similar_trm != null) {
                if ($similar_trm->obj != null) {
                    $result = $similar_trm->obj;
                    if (!$this->is_similar_named($result)) {
                        log_err($this->dsp_id() . ' is supposed to be similar to ' . $result->dsp_id() . ', but it seems not');
                    }
                }
            }
        } else {
            // used for view, view_component, source, ...
            $db_chk = clone $this;
            $db_chk->reset();
            $db_chk->usr = $this->usr;
            $db_chk->name = $this->name;
            // check with the standard namespace
            if ($db_chk->load_standard()) {
                if ($db_chk->id > 0) {
                    log_debug($this->obj_name . '->get_similar "' . $this->dsp_id() . '" has the same name is the already existing "' . $db_chk->dsp_id() . '" of the standard namespace');
                    $result = $db_chk;
                }
            }
            // check with the user namespace
            $db_chk->usr = $this->usr;
            if ($db_chk->load()) {
                if ($db_chk->id > 0) {
                    log_debug($this->obj_name . '->get_similar "' . $this->dsp_id() . '" has the same name is the already existing "' . $db_chk->dsp_id() . '" of the user namespace');
                    $result = $db_chk;
                }
            }
        }

        return $result;
    }

}


