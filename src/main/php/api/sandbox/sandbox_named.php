<?php

/*

    api/sandbox/user_sandbox_named_api.php - extends the frontend API superclass for named objects such as formulas
    --------------------------------------


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

namespace api;

include_once API_SANDBOX_PATH . 'sandbox.php';

class sandbox_named_api extends sandbox_api
{

    // the json field names in the api json message which is supposed to be the same as the var $id
    const FLD_NAME = 'name';
    const FLD_DESCRIPTION = 'description';

    // the unique name of the object that is shown to the user
    // the name must always be set
    public string $name;

    // the mouse over tooltip for the named object e.g. word, triple, formula, verb, view or component
    public ?string $description = null;


    /*
     * construct and map
     */

    function __construct(int $id = 0, string $name = '')
    {
        parent::__construct($id);
        $this->name = '';

        // set also the name if included in new call
        if ($name <> '') {
            $this->name = $name;
        }

    }


    /*
     * set and get
     */

    function set_name(string $name): void
    {
        $this->name = $name;
    }

    function name(): string
    {
        return $this->name;
    }


    /*
     * logging
     */

    /**
     * @return string best possible identification for this object mainly used for debugging
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
        $result .= '';
        return $result;
    }

}


