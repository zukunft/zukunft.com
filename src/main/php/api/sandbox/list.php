<?php

/*

    api/sandbox/list.php - the minimal list object used for the api
    --------------------

    e.g. used for the value and formula result api object

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

use JsonSerializable;

class list_api implements JsonSerializable
{
    // the protected main var
    private array $lst;

    // memory vs speed optimize vars
    private array $id_lst;
    private bool $lst_dirty;

    /*
     * construct and map
     */

    function __construct(array $lst)
    {
        $this->lst = array();

        $this->id_lst = array();
        $this->lst_dirty = false;

        if (count($lst) > 0) {
            $this->lst = $lst;
        }
    }


    /*
     * set and get
     */

    /**
     * @returns true if the list has been replaced
     */
    function set_lst(array $lst): bool
    {
        $this->lst = $lst;
        $this->set_lst_dirty();
        return true;
    }

    /**
     * @returns array the protected list of values or formula results
     */
    function lst(): array
    {
        return $this->lst;
    }

    /**
     * @returns array with the names on the db keys
     */
    function db_id_list(): array
    {
        $result = array();
        foreach ($this->lst as $obj) {
            $result[$obj->id()] = $obj->name();
        }
        return $result;
    }

    /**
     * @returns true if the list has been replaced
     */
    protected function set_lst_dirty(): bool
    {
        $this->lst_dirty = true;
        return true;
    }


    /*
     * information
     */

    /**
     * @returns int the number of objects of the protected list
     */
    function count(): int
    {
        return count($this->lst);
    }

    /**
     * @returns true if the list does not contain any object
     */
    function is_empty(): bool
    {
        if ($this->count() <= 0) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * modify functions
     */

    /**
     * add a phrase or ... to the list
     * @param object $obj the api object that should be added
     * @param bool $allow_duplicates true if the list can contain the same entry twice e.g. for the components
     * @returns bool true if the object has been added
     */
    protected function add_obj(object $obj, bool $allow_duplicates = false): bool
    {
        $result = false;
        if (!in_array($obj->id(), $this->id_lst()) or $allow_duplicates) {
            $this->lst[] = $obj;
            $this->lst_dirty = true;
            $result = true;
        }
        return $result;
    }

    /**
     * @returns array with all unique ids of this list
     */
    protected function id_lst(): array
    {
        $result = array();
        if ($this->lst_dirty) {
            foreach ($this->lst as $val) {
                if (!in_array($val->id(), $result)) {
                    $result[] = $val->id();
                }
            }
            $this->id_lst = $result;
            $this->lst_dirty = false;
        } else {
            $result = $this->id_lst;
        }
        return $result;
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
     * @return array with the value vars including the protected vars
     */
    function jsonSerialize(): array
    {
        $vars = [];
        foreach ($this->lst as $obj) {
            $vars[] = $obj->jsonSerialize();
        }
        return $vars;
    }

}
