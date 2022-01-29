<?php

/*

    user_sandbox_link.php - the superclass for handling user specific link objects including the database saving
    ---------------------

    This superclass should be used by the class word links, formula links and view link


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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

class user_sandbox_link extends user_sandbox
{

    public ?object $fob = null;        // the object from which this linked object is creating the connection
    public ?object $tob = null;        // the object to   which this linked object is creating the connection

    /**
     * reset the search values of this object
     * needed to search for the standard object, because the search is work, value, formula or ... specific
     */
    function reset()
    {
        parent::reset();

        $this->fob = null;
        $this->tob = null;
    }

    /**
     * fill a similar object that is extended with display interface functions
     *
     * @return object the object fill with all user sandbox value
     */
    function fill_dsp_obj(object $dsp_obj): object
    {
        parent::fill_dsp_obj($dsp_obj);

        $dsp_obj->fob = $this->fob;
        $dsp_obj->tob = $this->tob;

        return $dsp_obj;
    }

    /**
     * @return bool true if the object value are valid for identifying a unique link
     */
    function is_unique(): bool
    {
        $result = false;
        if ($this->id > 0) {
            $result = true;
        } else {
            if ($this->fob != null and $this->tob != null) {
                if ($this->fob->id > 0 and $this->tob->id > 0) {
                    $result = true;
                }
            }
        }
        return $result;
    }

    /**
     * return best possible identification for this object mainly used for debugging
     */
    function dsp_id(): string
    {
        $result = '';
        if (isset($this->fob) or isset($this->tob)) {
            if (isset($this->fob)) {
                $result .= 'from ' . $this->fob->dsp_id() . ' ';
            }
            if (isset($this->tob)) {
                $result .= 'to ' . $this->tob->dsp_id();
            }
            $result .= ' of type ';
        } else {
            $result .= $this->name . ' (' . $this->id . ') of type ';
        }
        $result .= $this->obj_name . ' ' . $this->obj_type;
        if (isset($this->usr)) {
            $result .= ' for user ' . $this->usr->id . ' (' . $this->usr->name . ')';
        }
        return $result;
    }

    /**
     * set the log entry parameter for a new link object
     * for all not named objects like links, this function is overwritten
     * e.g. that the user can see "added formula 'scale millions' to word 'mio'"
     * @returns user_log_link with the object presets e.g. th object name
     */
    function log_link_add(): user_log_link
    {
        log_debug($this->obj_name . '->log_add ' . $this->dsp_id());

        $log = new user_log_link;
        $log->new_from = $this->fob;
        $log->new_to = $this->tob;

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
    function log_del_link(): user_log_link
    {
        log_debug($this->obj_name . '->log_del ' . $this->dsp_id());

        $log = new user_log_link;
        $log->old_from = $this->fob;
        $log->old_to = $this->tob;

        $log->usr = $this->usr;
        $log->action = 'del';
        $log->table = $this->obj_name . 's';
        $log->row_id = $this->id;
        $log->add();

        return $log;
    }

    /**
     * create a new link object
     * @returns int the id of the creates object
     */
    function add_insert(): int {
        global $db_con;
        return $db_con->insert(
            array($this->from_name . '_id', $this->to_name . '_id', "user_id"),
            array($this->fob->id, $this->tob->id, $this->usr->id));
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
        log_debug($this->obj_name . '->add ' . $this->dsp_id());

        global $db_con;
        $result = new user_message();

        // log the insert attempt first
        $log = $this->log_link_add();
        if ($log->id > 0) {

            // insert the new object and save the object key
            // TODO check that always before a db action is called the db type is set correctly
            $db_con->set_type($this->obj_name);
            $db_con->set_usr($this->usr->id);
            $this->id = $this->add_insert();

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
                    $db_rec->fob = $this->fob;
                    $db_rec->tob = $this->tob;
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
     * @param user_sandbox_link $db_rec the object data as it is now in the database
     * @return bool true if one of the object id fields have been changed
     */
    function is_id_updated_link(user_sandbox $db_rec): bool
    {
        $result = False;
        log_debug($this->obj_name . '->is_id_updated ' . $this->dsp_id());

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
     * @param user_sandbox $db_rec the database record before the saving
     * @param user_sandbox $std_rec the database record defined as standard because it is used by most users
     * @returns string either the id of the updated or created source or a message to the user with the reason, why it has failed
     * @throws Exception
     */
    function save_id_fields_link(sql_db $db_con, user_sandbox $db_rec, user_sandbox $std_rec): string
    {
        $result = '';
        log_debug($this->obj_name . '->save_id_fields_link ' . $this->dsp_id());

        if ($this->is_id_updated_link($db_rec)) {
            log_debug($this->obj_name . '->save_id_fields_link to ' . $this->dsp_id() . ' from ' . $db_rec->dsp_id() . ' (standard ' . $std_rec->dsp_id() . ')');

            $log = $this->log_upd_link();
            $log->old_from = $db_rec->fob;
            $log->new_from = $this->fob;
            $log->std_from = $std_rec->fob;
            $log->old_to = $db_rec->tob;
            $log->new_to = $this->tob;
            $log->std_to = $std_rec->tob;

            $log->row_id = $this->id;
            if ($log->add()) {
                $db_con->set_type($this->obj_name);
                $db_con->set_usr($this->usr->id);
                if (!$db_con->update($this->id,
                    array($this->from_name . '_id', $this->from_name . '_id'),
                    array($this->fob->id, $this->tob->id))) {
                    $result .= 'update from link to ' . $this->from_name . 'failed';
                }
            }
        }
        log_debug($this->obj_name . '->save_id_fields_link for ' . $this->dsp_id() . ' done');
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
     */
    function get_similar(): user_sandbox
    {
        $result = new user_sandbox($this->usr);

        // check potential duplicate by name
        // check for linked objects
        if (!isset($this->fob) or !isset($this->tob)) {
            log_err('The linked objects for ' . $this->dsp_id() . ' are missing.', 'user_sandbox->get_similar');
        } else {
            $db_chk = clone $this;
            $db_chk->reset();
            $db_chk->fob = $this->fob;
            $db_chk->tob = $this->tob;
            if ($db_chk->load_standard()) {
                if ($db_chk->id > 0) {
                    log_debug($this->obj_name . '->get_similar the ' . $this->fob->name . ' "' . $this->fob->name . '" is already linked to "' . $this->tob->name . '" of the standard linkspace');
                    $result = $db_chk;
                }
            }
            // check with the user linkspace
            $db_chk->usr = $this->usr;
            if ($db_chk->load()) {
                if ($db_chk->id > 0) {
                    log_debug($this->obj_name . '->get_similar the ' . $this->fob->name . ' "' . $this->fob->name . '" is already linked to "' . $this->tob->name . '" of the user linkspace');
                    $result = $db_chk;
                }
            }
        }

        return $result;
    }

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

}


