<?php

/*

  db_object.php - a base object for all model database objects
  -------------


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

class db_object
{

    /*
     * object vars
     */

    // database fields that are used in all objects and that have a specific behavior
    // the database id of the object, which is the same for the standard and the user specific object
    protected ?int $id = null;

    /*
     * construct and map
     */

    /**
     * reset the id to null to indicate that the database object has not been loaded
     */
    function __construct()
    {
        $this->id = null;
    }

    /*
     * set and get
     */

    /**
     * set the unique database id of a database object
     * @param int|null $id mainly for test creation the database id of the database model object
     */
    public function set_id(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int|null the database id which is not 0 if the object has been saved
     * the internal null value is used to detect if database saving has been tried
     */
    public function id(): ?int
    {
        return $this->id;
    }

    /*
     * information
     */

    /**
     * @return bool true if the object has a database id
     */
    public function isset(): bool
    {
        if ($this->id == null) {
            return false;
        } else {
            if ($this->id != 0) {
                return true;
            } else {
                return false;
            }
        }
    }

    /*
     * dummy functions that should always be overwritten by the child
     */

    /**
     * get the name of the database object (only used by named objects)
     *
     * @return string the name from the object e.g. word using the same function as the phrase and term
     */
    public function name(): string
    {
        return 'ERROR: name function not overwritten by child';
    }

    /**
     * load a row from the database selected by id
     * @param int $id the id of the word, triple, formula, verb, view or view component
     * @param string $class the name of the child class from where the call has been triggered
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_id(int $id, string $class = self::class): int
    {
        return 0;
    }

    /**
     * load a row from the database selected by name (only used by named objects)
     * @param string $name the name of the word, triple, formula, verb, view or view component
     * @param string $class the name of the child class from where the call has been triggered
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_name(string $name, string $class = self::class): int
    {
        return 0;
    }

}
