<?php

/*

    model/sandbox/sandbox_description.php - adding the description field to the _sandbox superclass
    -------------------------------------

    This superclass should be used by the classes words, formula, ... su that users can link predefied behavier

    The main sections of this object are
    - object vars:       the variables of this word object
    - construct and map: including the mapping of the db row to this word object
    - set and get:       to capsule the vars from unexpected changes
    - cast:              create an api object and set the vars from an api json
    - information:       functions to make code easier to read
    - save:              manage to update the database


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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace cfg;

include_once MODEL_SANDBOX_PATH . 'sandbox_named.php';

use cfg\db\sql_db;

class sandbox_typed extends sandbox_named
{

    /*
     * object vars
     */

    // database id of the type used for named user sandbox objects with predefined functionality
    // such as words, formulas, values, terms and view component links
    // because all types are preloaded with the database id the name and code id can fast be received
    // the id of the source type, view type, view component type or word type
    // e.g. to classify measure words
    // the id of the source type, view type, view component type or word type e.g. to classify measure words
    // or the formula type to link special behavior to special formulas like "this" or "next"
    public ?int $type_id = null;


    /*
     * construct and map
     */

    function reset(): void
    {
        parent::reset();
        $this->type_id = null;
    }


    /*
     * set and get
     */

    /**
     * set the database id of the type
     *
     * @param int|null $type_id the database id of the type
     * @return void
     */
    function set_type_id(?int $type_id): void
    {
        $this->type_id = $type_id;
    }

    /**
     * @return int|null the database id of the type
     */
    function type_id(): ?int
    {
        return $this->type_id;
    }

    /**
     * dummy function that should be overwritten by the child object
     * @return string the name of the object type
     */
    function type_name(): string
    {
        $msg = 'ERROR: the type name function should have been overwritten by the child object';
        return log_err($msg);
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

        $api_obj->set_type_id($this->type_id());
    }

    /**
     * @param object $dsp_obj frontend API objects that should be filled with unique object name
     */
    function fill_dsp_obj(object $dsp_obj): void
    {
        parent::fill_api_obj($dsp_obj);

        $dsp_obj->set_type_id($this->type_id());
    }


    /*
     * information
     */

    /**
     * check if the typed object in the database needs to be updated
     *
     * @param sandbox_typed $db_obj the word as saved in the database
     * @return bool true if this word has infos that should be saved in the datanase
     */
    function needs_db_update_typed(sandbox_typed $db_obj): bool
    {
        $result = parent::needs_db_update_named($db_obj);
        if ($this->type_id != null) {
            if ($this->type_id != $db_obj->type_id) {
                $result = true;
            }
        }
        return $result;
    }


    /*
     * save - write to database
     */

    /**
     * save all updated source fields excluding the name, because already done when adding a source
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param source $db_rec the database record before the saving
     * @param source $std_rec the database record defined as standard because it is used by most users
     * @return string if not empty the message that should be shown to the user
     */
    function save_fields_typed(sql_db $db_con, sandbox_typed $db_rec, sandbox_typed $std_rec): string
    {
        $result = parent::save_fields_named($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_type($db_con, $db_rec, $std_rec);
        return $result;
    }

}