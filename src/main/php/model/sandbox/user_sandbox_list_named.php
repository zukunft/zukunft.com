<?php

/*

    model/sandbox/user_sandbox_list.php - a base object for a list of user sandbox objects
    -----------------------------------


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

class user_sandbox_list_named extends sandbox_list
{

    // memory vs speed optimize vars
    private array $name_pos_lst;
    private bool $lst_name_dirty;

    /*
     * construct and map
     */

    /**
     * @param array $lst object array that could be set with the construction
     * the parent constructor is called after the reseting of lst_name_dirty to enable setting by adding the list
     */
    function __construct(user $usr, array $lst = array())
    {
        $this->name_pos_lst = array();
        $this->lst_name_dirty = false;

        parent::__construct($usr, $lst);
    }


    /*
     * get and set
     */

    /**
     * to be called after the lists have been updated
     * but the index list have not yet been updated
     */
    protected function set_lst_dirty(): void
    {
        parent::set_lst_dirty();
        $this->lst_name_dirty = true;
    }

    /**
     * to be called after the lists have been updated
     * but the index list have not yet been updated
     */
    protected function set_lst_clean(): void
    {
        parent::set_lst_clean();
        $this->lst_name_dirty = false;
    }


    /*
     * search functions
     */

    /**
     * find an object from the loaded list by name using the hash
     * should be cast by the child function get_by_name
     *
     * @param string $name the unique name of the object that should be returned
     * @return object|null the found user sandbox object or null if no name is found
     */
    public function get_obj_by_name(string $name): ?object
    {
        $key_lst = $this->name_pos_lst();
        $pos = $key_lst[$name];
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
     * add a named object to the list
     * @returns bool true if the object has been added
     */
    public function add_obj(object $obj): bool
    {
        $result = false;
        if (!in_array($obj->name(), $this->name_pos_lst())) {
            $result = parent::add_obj($obj);
        }
        return $result;
    }

    /**
     * @returns array with all unique names of this list
     */
    protected function name_pos_lst(): array
    {
        $pos = 0;
        $result = array();
        if ($this->lst_name_dirty) {
            foreach ($this->lst as $obj) {
                if (!in_array($obj->name(), $result)) {
                    $result[$obj->name()] = $pos;
                    $pos++;
                }
            }
            $this->name_pos_lst = $result;
            $this->lst_name_dirty = false;
        } else {
            $result = $this->name_pos_lst;
        }
        return $result;
    }

}
