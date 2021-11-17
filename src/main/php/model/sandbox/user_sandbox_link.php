<?php

/*

    user_sandbox_link.php - the superclass for handling user specific link objects including the database saving
    ---------------------

    This superclass should be used by the classes word links, formula links and view link


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

class user_sandbox_link extends user_sandbox
{

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
     * create a new link object
     * returns the id of the creates object
     * TODO do a rollback in case of an error
     */
    function add(): string
    {
        log_debug($this->obj_name . '->add ' . $this->dsp_id());

        global $db_con;
        $result = '';

        // log the insert attempt first
        $log = $this->log_link_add();
        if ($log->id > 0) {

            // insert the new object and save the object key
            // TODO check that always before a db action is called the db type is set correctly
            $db_con->set_type($this->obj_name);
            $db_con->set_usr($this->usr->id);
            $this->id = $db_con->insert(array($this->from_name . '_id', $this->to_name . '_id', "user_id", 'order_nbr'), array($this->fob->id, $this->tob->id, $this->usr->id, $this->order_nbr));

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
                    $db_rec->fob = $this->fob;
                    $db_rec->tob = $this->tob;
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

    /**
     * set the update parameters for the value excluded
     * returns false if something has gone wrong
     */
    function save_field_excluded($db_con, $db_rec, $std_rec): string
    {
        log_debug($this->obj_name . '->save_field_excluded ' . $this->dsp_id());
        $result = '';

        if ($db_rec->excluded <> $this->excluded) {
            if ($this->excluded == 1) {
                $log = $this->log_del();
            } else {
                $log = $this->log_link_add();
            }
            $new_value = $this->excluded;
            $std_value = $std_rec->excluded;
            $log->field = self::FLD_EXCLUDED;
            // similar to $this->save_field_do
            if ($this->can_change()) {
                $db_con->set_type($this->obj_name);
                $db_con->set_usr($this->usr->id);
                if (!$db_con->update($this->id, $log->field, $new_value)) {
                    $result .= 'excluding of ' . $this->obj_name . ' failed';
                }
            } else {
                if (!$this->has_usr_cfg()) {
                    if (!$this->add_usr_cfg()) {
                        $result = 'creation of user sandbox to exclude failed';
                    }
                }
                if ($result == '') {
                    $db_con->set_type(DB_TYPE_USER_PREFIX . $this->obj_name);
                    $db_con->set_usr($this->usr->id);
                    if ($new_value == $std_value) {
                        if (!$db_con->update($this->id, $log->field, Null)) {
                            $result .= 'include of ' . $this->obj_name . ' for user failed';
                        }
                    } else {
                        if (!$db_con->update($this->id, $log->field, $new_value)) {
                            $result .= 'excluding of ' . $this->obj_name . ' for user failed';
                        }
                    }
                    if (!$this->del_usr_cfg_if_not_needed()) {
                        $result .= ' and user sandbox cannot be cleaned';
                    }
                }
            }
        }
        return $result;
    }

}


