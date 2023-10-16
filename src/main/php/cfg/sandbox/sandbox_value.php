<?php

/*

    model/sandbox/sandbox_value.php - the superclass for handling user specific link objects including the database saving
    -------------------------------

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

namespace cfg;

include_once MODEL_SANDBOX_PATH . 'sandbox.php';

use DateTime;
use Exception;

class sandbox_value extends sandbox
{

    /*
     * object vars
     */

    // database fields only used for the value object
    protected ?float $number; // simply the numeric value
    private ?DateTime $last_update = null; // the time of the last update of fields that may influence the calculated results; also used to detect if the value has been saved


    /*
     * construct and map
     */

    /**
     * all value user specific, that's why the user is always set
     */
    function __construct(user $usr)
    {
        parent::__construct($usr);
        $this->reset();
    }

    function reset(): void
    {
        parent::reset();
        $this->set_number(null);
        $this->set_last_update(null);
    }


    /*
     * set and get
     */

    /**
     * set the numeric value of the user sandbox object
     *
     * @param float|null $number the numeric value that should be saved in the database
     * @return void
     */
    function set_number(?float $number): void
    {
        $this->number = $number;
    }

    /**
     * @return float|null the numeric value
     */
    function number(): ?float
    {
        return $this->number;
    }

    /**
     * set the timestamp of the last update of this value
     *
     * @param DateTime|null $last_update the timestamp when this value has been updated eiter by the user or a calculatio job
     * @return void
     */
    function set_last_update(?DateTime $last_update): void
    {
        $this->last_update = $last_update;
    }

    /**
     * @return DateTime|null the timestamp when the user has last updated the value
     */
    function last_update(): ?DateTime
    {
        return $this->last_update;
    }


    /*
     * information
     */

    /**
     * @return bool true if the value has been at least once saved to the database
     */
    function is_saved(): bool
    {
        if ($this->last_update() == null) {
            return false;
        } else {
            return true;
        }
    }


    /*
     * cast
     */

    /**
     * @param object $api_obj frontend API object filled with the database id
     */
    function fill_api_obj(object $api_obj): void
    {
        parent::fill_api_obj($api_obj);

        $api_obj->set_number($this->number);
    }


    /*
     * save
     */

    /**
     * set the log entry parameter for a new value object
     * for all not named objects like links, this function is overwritten
     * e.g. that the user can see "added formula 'scale millions' to word 'mio'"
     */
    function log_add(): change_log_named
    {
        log_debug($this->dsp_id());

        $log = new change_log_named($this->user());
        $log->action = change_log_action::ADD;
        $log->set_table($this->obj_name . sql_db::TABLE_EXTENSION);
        $log->set_field(change_log_field::FLD_NUMERIC_VALUE);
        $log->old_value = '';
        $log->new_value = $this->number;

        $log->row_id = 0;
        $log->add();

        return $log;
    }

    /**
     * set the log entry parameter to delete a object
     * @returns change_log_link with the object presets e.g. th object name
     */
    function log_del(): change_log_named
    {
        log_debug($this->dsp_id());

        $log = new change_log_named($this->user());
        $log->action = change_log_action::DELETE;
        $log->set_table($this->obj_name . sql_db::TABLE_EXTENSION);
        $log->set_field(change_log_field::FLD_NUMERIC_VALUE);
        $log->old_value = $this->number;
        $log->new_value = '';

        $log->row_id = $this->id;
        $log->add();

        return $log;
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

        return 'The user sandbox save_id_fields does not support ' . $this->obj_type . ' for ' . $this->obj_name;
    }


    /*
     * debug
     */

    /**
     * @return string with the best possible identification for this value mainly used for debugging
     */
    function dsp_id(): string
    {
        $result = $this->dsp_id_short();
        $result .= $this->dsp_id_user();
        return $result;
    }

    /**
     * @return string with the short identification for links
     */
    function dsp_id_short(): string
    {
        $result = $this->dsp_id_entry();
        $result .= parent::dsp_id();
        return $result;
    }

    /**
     * @return string with the short identification for lists
     */
    function dsp_id_entry(): string
    {
        $result = '';
        if (isset($this->grp)) {
            $result .= '"' . $this->grp->name() . '" ';
        }
        if ($this->number() != null) {
            $result .= $this->number();
        } else {
            $result .= 'null';
        }
        return $result;
    }

}


