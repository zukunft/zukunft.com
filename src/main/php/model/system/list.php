<?php

/*

    system\base_list.php - the minimal list object used for the list used in the model
    --------------------

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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

class base_list
{
    // the protected main var
    protected array $lst;

    // memory vs speed optimize vars
    private array $id_pos_lst;
    private bool $lst_dirty;

    /*
     * construct and map
     */

    function __construct(array $lst = array())
    {
        $this->lst = array();

        $this->id_pos_lst = array();
        $this->lst_dirty = false;

        if (count($lst) > 0) {
            $this->set_lst($lst);
        }
    }

    /*
     * get and set
     */

    /**
     * @returns true if the list has been replaced
     */
    public function set_lst(array $lst): bool
    {
        $this->lst = $lst;
        $this->set_lst_dirty();
        return true;
    }

    /**
     * @returns array the protected list of values or formula results
     */
    public function lst(): array
    {
        return $this->lst;
    }

    /**
     * @returns array with the names on the db keys
     */
    public function lst_key(): array
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
     * is overwritten by the child user_sandbox_list_named
     */
    protected function set_lst_dirty(): void
    {
        $this->lst_dirty = true;
    }

    /**
     * to be called after the index lists have been updated
     * is overwritten by the child user_sandbox_list_named
     */
    protected function set_lst_clean(): void
    {
        $this->lst_dirty = false;
    }

    /*
     * search functions
     */

    /**
     * @param int $id the unique database id of the object that should be returned
     * @return user_sandbox|null the found user sandbox object or null if no id is found
     */
    public function get_by_id(int $id): ?object
    {
        $key_lst = $this->id_pos_lst();
        $pos = $key_lst[$id];
        if ($pos !== null) {
            return $this->lst[$pos];
        } else {
            return null;
        }
    }


    /*
     * modify functions
     */

    /**
     * add a object to the list
     * @returns bool true if the object has been added
     */
    protected function add_obj(object $obj): bool
    {
        $result = false;
        if (!in_array($obj->id(), $this->id_pos_lst())) {
            $this->lst[] = $obj;
            $this->set_lst_dirty();
            $result = true;
        }
        return $result;
    }

    /**
     * @returns array with all unique ids of this list
     */
    protected function id_pos_lst(): array
    {
        $pos = 0;
        $result = array();
        if ($this->lst_dirty) {
            foreach ($this->lst as $obj) {
                if (!in_array($obj->id(), $result)) {
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

}
