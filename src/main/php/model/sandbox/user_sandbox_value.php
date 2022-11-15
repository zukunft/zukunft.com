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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

class user_sandbox_value extends user_sandbox
{

    /**
     * reset the search values of this object
     * needed to search for the standard object, because the search is work, value, formula or ... specific
     */
    function reset(): void
    {
        parent::reset();

        $this->number = null;
    }

    /**
     * fill a similar object that is extended with display interface functions
     *
     * @return object the object fill with all user sandbox value
     */
    function fill_dsp_obj(object $dsp_obj): object
    {
        parent::fill_dsp_obj($dsp_obj);

        $dsp_obj->number = $this->number;

        return $dsp_obj;
    }

    /**
     * return best possible identification for this object mainly used for debugging
     */
    function dsp_id(): string
    {
        $result = '';
        if (isset($this->grp)) {
            $result .= $this->grp->dsp_id();
        }
        if (isset($this->time_phr)) {
            if ($result <> '') {
                $result .= '@';
            }
            if (gettype($this->time_phr) == 'object') {
                $result .= $this->time_phr->dsp_id();
            }
        }
        if ($this->user()->is_set()) {
            $result .= ' for user ' . $this->user()->id . ' (' . $this->user()->name . ')';
        }
        return $result;
    }

    /**
     * set the log entry parameter for a new value object
     * for all not named objects like links, this function is overwritten
     * e.g. that the user can see "added formula 'scale millions' to word 'mio'"
     */
    function log_add(): user_log_named
    {
        log_debug($this->obj_name . '->log_add ' . $this->dsp_id());

        $log = new user_log_named;
        $log->field = 'word_value';
        $log->old_value = '';
        $log->new_value = $this->number;

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
        log_debug($this->obj_name . '->log_del ' . $this->dsp_id());

        $log = new user_log_named;
        $log->field = 'word_value';
        $log->old_value = $this->number;
        $log->new_value = '';

        $log->usr = $this->user();
        $log->action = user_log::ACTION_DELETE;
        $log->table = $this->obj_name . 's';
        $log->row_id = $this->id;
        $log->add();

        return $log;
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

        $result .= 'The user sandbox save_id_fields does not support ' . $this->obj_type . ' for ' . $this->obj_name;
        return $result;
    }

}


