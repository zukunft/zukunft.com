<?php

/*

    web/sandbox/sandbox.php - the superclass for the html frontend of database objects
    -----------------------

    This superclass should be used by the classes word_dsp, formula_dsp, ... to enable user specific values and links


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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace html;

include_once API_SANDBOX_PATH . 'sandbox.php';

use api\sandbox_api;

class db_object_dsp
{

    // fields for the backend link
    public int $id; // the database id of the object, which is the same as the related database object in the backend

    /*
     * construct and map
     */

    function __construct(int $id = 0)
    {
        $this->id = 0;

        // set the id if included in new call
        if ($id <> 0) {
            $this->id = $id;
        }
    }

    /*
     * set and get
     */

    /**
     * set the vars of this object bases on the api json string
     * @param string $json_api_msg an api json message as a string
     * @return void
     */
    function set_from_json(string $json_api_msg): void
    {
        $this->set_from_json_array(json_decode($json_api_msg));
    }

    /**
     * set the vars of this object bases on the api json array
     * @param array $json_array an api json message
     * @return void
     */
    function set_from_json_array(array $json_array): void
    {
        if (array_key_exists(sandbox_api::FLD_ID, $json_array)) {
            $this->set_id($json_array[sandbox_api::FLD_ID]);
        } else {
            log_err('Mandatory field id missing in API JSON ' . json_encode($json_array));
        }
    }

    function set_id(int $id): void
    {
        $this->id = $id;
    }

    function id(): int
    {
        return $this->id;
    }

}


