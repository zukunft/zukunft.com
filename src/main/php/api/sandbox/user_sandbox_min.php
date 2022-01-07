<?php

/*

    user_sandbox_min.php - the minimal superclass for the frontend API
    --------------------

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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2021 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace sandbox;

use formula;
use formula\formula_min;
use user;
use user_sandbox;
use value;
use value\value_min;
use word;
use word\triple_min;
use word\word_min;
use word_link;
use function log_err;

class user_sandbox_min
{

    // fields for the backend link
    public int $id; // the database id of the object, which is the same as the related database object in the backend

    function __construct()
    {
        $this->id = 0;
    }

    function db_obj(user $usr, string $class): user_sandbox
    {
        $db_obj = null;
        if ($class == word_min::class) {
            $db_obj = new word($usr);
        } elseif ($class == triple_min::class) {
            $db_obj = new word_link($usr);
        } elseif ($class == value_min::class) {
            $db_obj = new value($usr);
        } elseif ($class == formula_min::class) {
            $db_obj = new formula($usr);
        } else {
            log_err('API class "' . $class . '" not yet implemented');
        }
        $db_obj->id = $this->id;
        $db_obj->load();
        return $db_obj;
    }

}


