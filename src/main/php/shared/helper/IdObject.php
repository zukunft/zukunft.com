<?php

/*

    shared/helper/IdObject.php - a base object for all database objects which have a unique id based on an int sequence
    --------------------------


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

namespace Zukunft\ZukunftCom\main\php\shared\helper;

class IdObject
{

    /*
     * object vars
     */

    // database fields that are used in all model objects
    // the database id is the unique prime key
    // is private because some objects like group have a complex id which needs a id() function
    public int $id {
        // get @return int the database id which is not 0 if the object has been saved
        // the internal null value is used to detect if database saving has been tried
        get {
            return $this->id;
        }
        // set the unique database id of a database object
        // @param int $id used in the row mapper and to set a dummy database id for unit tests
        set {
            $this->id = $value;
            $this->set_modified();
        }
    }
    // true if the database entry needs to be updated
    private bool $modified = true;


    /*
     * construct and map
     */

    /**
     * reset the id to null to indicate that the database object has not been loaded
     */
    function __construct()
    {
        $this->id = 0;
        $this->modified = true;
    }

    /**
     * reset the vars of this object
     * used to search for the standard object, because the search is word, value, formula or ... specific
     */
    function reset(): void
    {
        $this->id = 0;
        $this->modified = true;
    }

    /**
     * clone this object and all linked objects
     * @return $this a complete clone including a clone of all child objects
     */
    function clone_all(): IdObject
    {
        return clone $this;
    }

    /**
     * just the reset the modified field
     * to be overwritten and called by the child object
     *
     * @param array|null $db_row with the data directly from the database
     * @param string $id_fld the name of the id field as set in the child class
     * @return bool true if the user sandbox object is loaded and valid
     */
    function row_mapper(?array $db_row, string $id_fld = ''): bool
    {
        $this->modified = false;
        return false;
    }


    /*
     * set and get
     */

    /**
     * the id as a function to be able to overwrite the function in combine objects like phrase or term
     * @return int the database id which is not 0 if the object has been saved
     * the internal null value is used to detect if database saving has been tried
     */
    function id(): int
    {
        return $this->id;
    }


    /*
     * modify
     */

    /**
     * check if the object in the database needs to be updated
     *
     * @param IdObject $db_obj e.g. the word as saved in the database
     * @return bool true if this object has infos that should be saved in the database
     */
    function needs_db_update(IdObject $db_obj): bool
    {
        return $this->modified;
    }

    /**
     * TODO to be called once by all child setter hooks
     */
    function set_modified(): void
    {
        $this->modified = true;
    }

}
