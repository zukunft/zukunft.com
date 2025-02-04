<?php

/*

    model/helper/db_object_seq_id.php - a base object for all database objects which have a unique id based on an extra long key e.g. 512bit key
    ---------------------------------


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

class TextIdObject
{

    /*
     * object vars
     */

    // database fields that are used in all model objects
    // the database id is the unique prime key
    // is private because some objects like group have a complex id which needs a id() function
    private string|int $id;


    /*
     * construct and map
     */

    /**
     * reset the id to null to indicate that the database object has not been loaded
     */
    function __construct()
    {
        $this->set_id(0);
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

    /**
     * set the unique database id of a database object
     * @param string|int $id used in the row mapper and to set a dummy database id for unit tests
     */
    function set_id(string|int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string|int the database id which is not 0 if the object has been saved
     * the internal null value is used to detect if database saving has been tried
     */
    function id(): string|int
    {
        return $this->id;
    }

}
