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

class user_sandbox_value extends user_sandbox
{

    /**
     * reset the search values of this object
     * needed to search for the standard object, because the search is work, value, formula or ... specific
     */
    function reset()
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
        if (isset($this->usr)) {
            $result .= ' for user ' . $this->usr->id . ' (' . $this->usr->name . ')';
        }
        return $result;
    }

    /**
     * set the log entry parameter for a new value object
     * for all not named objects like links, this function is overwritten
     * e.g. that the user can see "added formula 'scale millions' to word 'mio'"
     */
    function log_add(): user_log
    {
        log_debug($this->obj_name . '->log_add ' . $this->dsp_id());

        $log = new user_log;
        $log->field = 'word_value';
        $log->old_value = '';
        $log->new_value = $this->number;

        $log->usr = $this->usr;
        $log->action = 'add';
        // TODO add the table exceptions from sql_db
        $log->table = $this->obj_name . 's';
        $log->row_id = 0;
        $log->add();

        return $log;
    }

}


