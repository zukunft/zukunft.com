<?php

/*

    api/sandbox/sandbox.php - the minimal superclass for the frontend API
    ----------------------------

    This superclass should be used by the classes word_min, formula_min, ... to enable user specific values and links


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

namespace api\sandbox;

use api\api;
use JsonSerializable;
use shared\types\protection_type;

class sandbox implements JsonSerializable
{

    // fields for the backend link
    public int|string $id; // the database id of the object, which is the same as the related database object in the backend
    public int|null $share = null; // the share type id; if not set the default share type is assumed
    public int|null $protection = null; // the protection type id; if not set the default protection type (public) is assumed
    public bool $excluded; // to return the id with the excluded flag if an object has been excluded


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
     * TODO move string option only to sandbox_value
     * @param int|string $id
     * @return void
     */
    function set_id(int|string $id): void
    {
        $this->id = $id;
    }

    function id(): int|string
    {
        return $this->id;
    }


    /*
     * interface
     */

    /**
     * @return array the json api message as a text string
     */
    function api_array(): array
    {
        $vars = array();
        $vars[api::FLD_ID] = $this->id();
        return $vars;
    }


    /*
     * interface
     */

    /**
     * @return string the json api message as a text string
     */
    function get_json(): string
    {
        return json_encode($this->jsonSerialize());
    }

    /**
     * @return array with the sandbox vars without empty values that are not needed
     * the message from the backend to the frontend does not need to include empty fields
     * the message from the frontend to the backend on the other side must include empty fields
     * to be able to unset fields in the backend
     */
    function jsonSerialize(): array
    {
        global $share_types;
        global $protection_types;

        $vars = get_object_vars($this);

        // remove vars from the json that have the default value
        if (array_key_exists(api::FLD_SHARE, $vars)) {
            if ($vars[api::FLD_SHARE] == $share_types->default_id()) {
                unset($vars[api::FLD_SHARE]);
            }
        }
        if (array_key_exists(api::FLD_PROTECTION, $vars)) {
            if ($vars[api::FLD_PROTECTION] == $protection_types->default_id()) {
                unset($vars[api::FLD_PROTECTION]);
            }
        }

        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }

}


