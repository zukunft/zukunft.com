<?php

/*

    web/sandbox/list.php - the superclass for html list objects
    --------------------

    e.g. used to display phrase, term and figure lists

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

namespace html;

use cfg\library;

class list_dsp
{
    // the protected main var
    protected array $lst;

    // memory vs speed optimize vars
    private array $id_lst;
    private bool $lst_dirty;

    /*
     * construct and map
     */

    function __construct(?string $api_json = null)
    {
        $this->lst = array();

        $this->id_lst = array();
        $this->lst_dirty = false;

        if ($api_json != null) {
            $this->set_from_json($api_json);
        }
    }


    /*
     * set and get
     */

    /**
     * set the vars of these list display objects bases on the api message
     * @param string $json_api_msg an api json message as a string
     * @return void
     */
    function set_from_json(string $json_api_msg): void
    {
        $this->set_from_json_array(json_decode($json_api_msg, true));
    }

    /**
     * set the vars of these list display objects bases on the api json array
     * @param array $json_array an api list json message
     * @return void
     */
    function set_from_json_array(array $json_array): void
    {
        foreach ($json_array as $value) {
            $this->add_obj($this->set_obj_from_json_array($value));
        }
    }

    /**
     * dummy function to be overwritten by the child object
     * @param array $json_array an api single object json message
     * @return object a combine display object with data set based on the given json
     */
    function set_obj_from_json_array(array $json_array): object
    {
        $lib = new library();
        log_err('Unexpect use of set_obj_from_json_array ' . $lib->dsp_array($json_array) . ' of list_dsp object');
        return $lib;
    }

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
    function lst_key(): array
    {
        $result = array();
        foreach ($this->lst as $val) {
            $result[$val->id()] = $val->name();
        }
        return $result;
    }

    /**
     * @returns true if the list has been replaced
     */
    public function set_lst_dirty(): bool
    {
        $this->lst_dirty = true;
        return true;
    }


    /*
     * interface
     */

    /**
     * @return array the json message array to send the updated data to the backend
     * an array is used (instead of a string) to enable combinations of api_array() calls
     */
    function api_array(): array
    {
        $result = array();
        foreach ($this->lst as $obj) {
            $result[] = $obj->api_array();
        }
        return $result;
    }


    /*
     * info
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
     * add a phrase or ... to the list but only if it does not exist
     * @returns bool true if the object has been added
     */
    protected function add_obj(object $obj): bool
    {
        $result = false;
        if (!in_array($obj->id(), $this->id_lst())) {
            $this->lst[] = $obj;
            $this->lst_dirty = true;
            $result = true;
        }
        return $result;
    }

    /**
     * add a phrase or ... to the list also if it is already part of the list
     */
    protected function add_always(object $obj): void
    {
        $this->lst[] = $obj;
        $this->lst_dirty = true;
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

}
