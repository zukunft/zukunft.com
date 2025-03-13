<?php

/*

    shared/helper/CombineObject.php - parent object to combine two or more db objects
    -------------------------------


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

namespace shared\helper;

class CombineObject
{

    /*
     * object vars
     */

    protected IdObject|TextIdObject|null $obj;


    /*
     * construct and map
     */

    /**
     * a combine object always covers an existing object
     * e.g. used to combine word and triple to a phrase
     * @param IdObject|TextIdObject|null $obj the object that should be covered by a common interface
     */
    function __construct(IdObject|TextIdObject|null $obj)
    {
        $this->set_obj($obj);
    }


    /**
     * reset the vars of this object
     * used to search for the standard object, because the search is word, value, formula or ... specific
     */
    function reset(): void
    {
        $this->set_id(0);
    }


    /*
     * set and get
     */

    function set_obj(IdObject|TextIdObject|null $obj): void
    {
        $this->obj = $obj;
    }

    function obj(): IdObject|TextIdObject|null
    {
        return $this->obj;
    }

    /**
     * set the unique database id of a database object
     * @param int $id used in the row mapper and to set a dummy database id for unit tests
     */
    function set_id(int $id): void
    {
        $this->obj()->set_id($id);
    }

    /**
     * @return int the database id which is not 0 if the object has been saved
     * the internal null value is used to detect if database saving has been tried
     */
    function id(): int
    {
        return $this->obj()->id();
    }


    /*
     * debug
     */

    function dsp_id(): string
    {
        return $this->id();
    }
}
