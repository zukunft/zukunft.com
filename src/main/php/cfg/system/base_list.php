<?php

/*

    model/system/base_list.php - the minimal list object used for the list used in the model
    --------------------------

    e.g. used for the ip range list object

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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace cfg;

use cfg\db\sql_db;

class base_list
{
    // the protected main var
    private array $lst;

    // paging vars
    // display and select fields to increase the response time
    private int $offset; // start to display with this id
    public int $limit;   // if not defined, use the default page size

    // memory vs speed optimize vars
    private array $id_pos_lst;
    private bool $lst_dirty;

    /*
     * construct and map
     */

    function __construct(array $lst = array())
    {
        $this->lst = array();

        $this->offset = 0;
        $this->limit = sql_db::ROW_LIMIT;

        $this->id_pos_lst = array();
        $this->lst_dirty = false;

        if (count($lst) > 0) {
            $this->set_lst($lst);
        }
    }

    function reset(): void
    {
        $this->set_lst(array());
    }


    /*
     * set and get
     */

    /**
     * @param string|int $key the key of the lst array
     * @return sandbox|null the found user sandbox object or null if no id is found
     */
    function get(string|int $key): ?object
    {
        return $this->lst[$key];
    }

    /**
     * @return array with the API object of the values
     */
    function api_lst(bool $do_save = true): array
    {
        $api_lst = array();
        foreach ($this->lst as $val) {
            $api_lst[] = $val->api_obj($do_save);
        }

        return $api_lst;
    }

    function set_offset(int $offset): void
    {
        $this->offset = $offset;
    }

    function offset(): int
    {
        return $this->offset;
    }

    /**
     * @return true if the list has been replaced
     */
    function set_lst(array $lst): bool
    {
        $this->lst = $lst;
        $this->set_lst_dirty();
        return true;
    }

    /**
     * @returns array the list of items
     * which is private to make sure the dirty handling always works
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
     * to be called after the lists have been updated
     * but the index list have not yet been updated
     * is overwritten by the child _sandbox_list_named
     */
    protected function set_lst_dirty(): void
    {
        $this->lst_dirty = true;
    }

    /**
     * to be called after the index lists have been updated
     * is overwritten by the child _sandbox_list_named
     */
    protected function set_lst_clean(): void
    {
        $this->lst_dirty = false;
    }


    /*
     * information
     */

    /**
     * @returns int the number of elements in the list
     */
    function count(): int
    {
        if ($this->lst() == null) {
            return 0;
        } else {
            return count($this->lst);
        }
    }

    /**
     * @return bool true if the list is already empty
     */
    function is_empty(): bool
    {
        $result = true;
        if ($this->lst() != null) {
            if (count($this->lst) > 0) {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * @param ?int $limit the max number of ids to show
     * @return array with the database ids of all objects of this list
     */
    function ids(int $limit = null): array
    {
        $result = array();
        $pos = 0;
        foreach ($this->lst as $sbx_obj) {
            if ($pos <= $limit or $limit == null) {
                // use only valid ids
                if ($sbx_obj->id() <> 0) {
                    $result[] = $sbx_obj->id();
                    $pos++;
                }
            }
        }
        return $result;
    }


    /*
     * search
     */

    /**
     * select an item by id
     * TODO use a hash table to speed up
     *
     * @param int $id the unique database id of the object that should be returned
     * @return sandbox|null the found user sandbox object or null if no id is found
     */
    function get_by_id(int $id): ?object
    {
        $lib = new library();
        $key_lst = $this->id_pos_lst();
        if (array_key_exists($id, $key_lst)) {
            $pos = $key_lst[$id];
            return $this->lst[$pos];
        } else {
            log_err($id . ' not found in ' . $lib->dsp_array_keys($key_lst));
            return null;
        }
    }


    /*
     * modify
     */

    /**
     * add an object to the list
     * @returns bool true if the object has been added
     */
    protected function add_obj(object $obj_to_add, bool $allow_duplicates = false): bool
    {
        $result = false;
        if ($allow_duplicates) {
            $this->lst[] = $obj_to_add;
            $this->set_lst_dirty();
            $result = true;
        } else {
            if (!in_array($obj_to_add->id(), $this->ids())) {
                $this->lst[] = $obj_to_add;
                $this->set_lst_dirty();
                $result = true;
            }
        }
        return $result;
    }

    /**
     * unset an object of the list
     * @returns bool true if the object has been added
     */
    protected function unset_by_id(int $id): bool
    {
        $result = false;
        $key_lst = $this->id_pos_lst();
        if (array_key_exists($id, $key_lst)) {
            unset ($this->lst[$id]);
            $result = true;
        }
        return $result;
    }

    /**
     * TODO add a unit test
     * @returns array with all unique ids of this list
     */
    protected function id_pos_lst(): array
    {
        $pos = 0;
        $result = array();
        if ($this->lst_dirty) {
            foreach ($this->lst as $obj) {
                if (!array_key_exists($obj->id(), $result)) {
                    $result[$obj->id()] = $pos;
                    $pos++;
                }
            }
            $this->id_pos_lst = $result;
            $this->lst_dirty = false;
        } else {
            $result = $this->id_pos_lst;
        }
        return $result;
    }


    /*
     * debug
     */

    /**
     * @return string with the first unique id of the list elements
     */
    function dsp_id(): string
    {
        global $debug;
        $result = '';

        // show at least 4 elements by name
        $min_names = $debug;
        if ($min_names < LIST_MIN_NAMES) {
            $min_names = LIST_MIN_NAMES;
        }


        if ($this->lst() != null) {
            $pos = 0;
            foreach ($this->lst() as $db_obj) {
                if ($pos < $min_names) {
                    if ($result <> '') $result .= ' / ';
                    $result .= $db_obj->dsp_id();
                    $pos++;
                }
            }
            $result .= $this->dsp_id_remaining($pos);
        }
        return $result;
    }

    /**
     * @param int $pos the first list id that has not yet been shown
     * @return string a short summary of the remaining ids
     */
    protected function dsp_id_remaining(int $pos): string
    {
        $lib = new library();
        $result = '';

        if (count($this->lst()) > $pos) {
            $result .= ' ... total ' . $lib->dsp_count($this->lst());
        }
        return $result;
    }

}
