<?php

/*

    model/sandbox/sandbox_link_typed.php - adding the type field to the user sandbox link named superclass
    ------------------------------------

    similar to sandbox_link_with_type, but for links with name


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

use api\api;

include_once MODEL_SANDBOX_PATH . 'sandbox_link_named.php';

class sandbox_link_typed extends sandbox_link_named
{

    /*
     * object vars
     */

    // database id of the type used for named link user sandbox objects with predefined functionality
    // which is actually only triple
    // repeating _sandbox_typed, because php 8.1 does not yet allow multi extends
    public ?int $type_id = null;


    /*
     * construct and map
     */

    /**
     * reset the type of the link object
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


    /*
     * settings
     */

    /**
     * @return bool true because all child objects use the link type
     */
    function is_link_type_obj(): bool
    {
        return true;
    }


    /*
     * preloaded
     */

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
     * set the type based on the api json
     * @param array $api_json the api json array with the values that should be mapped
     */
    function set_by_api_json(array $api_json): user_message
    {
        $msg = parent::set_by_api_json($api_json);

        foreach ($api_json as $key => $value) {
            if ($key == api::FLD_TYPE) {
                $this->set_type_id($value);
            }
        }
        return $msg;
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
     * @param sandbox_link_typed $db_obj the word as saved in the database
     * @return bool true if this word has infos that should be saved in the datanase
     */
    function needs_db_update_typed(sandbox_link_typed $db_obj): bool
    {
        $result = parent::needs_db_update_named($db_obj);
        if ($this->type_id != null) {
            if ($this->type_id != $db_obj->type_id) {
                $result = true;
            }
        }
        return $result;
    }

}