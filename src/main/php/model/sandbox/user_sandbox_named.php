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
    function log_add(): user_log
    {
        log_debug($this->obj_name . '->log_add ' . $this->dsp_id());

        $log = new user_log;
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
     * create a new named object
     * returns the id of the creates object
     * TODO do a rollback in case of an error
     */
    function add(): string
    {
        log_debug($this->obj_name . '->add ' . $this->dsp_id());

        global $db_con;
        $result = '';

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
                    $result .= 'Updating the reference in the log failed';
                    // TODO do rollback or retry?
                } else {
                    //$result .= $this->set_owner($new_owner_id);

                    // create an empty db_rec element to force saving of all set fields
                    $db_rec = clone $this;
                    $db_rec->reset();
                    $db_rec->name = $this->name;
                    $db_rec->usr = $this->usr;
                    $std_rec = clone $db_rec;
                    // save the object fields
                    $result .= $this->save_fields($db_con, $db_rec, $std_rec);
                }

            } else {
                $result .= 'Adding ' . $this->obj_type . ' ' . $this->dsp_id() . ' failed due to logging error.';
            }
        }

        return $result;
    }

}


