<?php

/*

    model/helper/combine_object.php - parent object to combine two or four sandbox objects
    -------------------------------

    e.g. to combine value and result to figure
    or word and triple to phrase
    or word, triple, verb and formula to term

    TODO use it for figure
    TODO use it for phrase
    TODO use it for term


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

use cfg\db\sql_db;
use cfg\result\result;
use cfg\value\value;
use shared\library;

class combine_object
{

    /*
     * object vars
     */

    protected word|triple|verb|formula|value|result|null $obj;


    /*
     * construct and map
     */

    /**
     * a combine object always covers an existing object
     * e.g. used to combine word and triple to a phrase
     * @param word|triple|verb|formula|value|result|null $obj the object that should be covered by a common interface
     */
    function __construct(word|triple|verb|formula|value|result|null $obj)
    {
        $this->set_obj($obj);
    }


    /*
     * set and get
     */

    function set_obj(word|triple|verb|formula|value|result|sandbox_named|null $obj): void
    {
        $this->obj = $obj;
    }

    function obj(): object
    {
        return $this->obj;
    }

    function isset(): bool
    {
        return $this->obj()->isset();
    }


    /*
     * information
     */

    /**
     * @return string the field name of the unique id of the combine database view
     */
    function id_field(): string
    {
        $lib = new library();
        return $lib->class_to_name($this::class) . sql_db::FLD_EXT_ID;
    }

}
