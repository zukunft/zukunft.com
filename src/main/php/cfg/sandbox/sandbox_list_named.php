<?php

/*

    model/sandbox/sandbox_list.php - a base object for a list of user sandbox objects
    ------------------------------


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

use cfg\db\sql_creator;
use cfg\db\sql_par_type;

include_once MODEL_SANDBOX_PATH . 'sandbox_list.php';

class sandbox_list_named extends sandbox_list
{

    // memory vs speed optimize vars
    private array $name_pos_lst;
    private bool $lst_name_dirty;

    /*
     * construct and map
     */

    /**
     * @param array $lst object array that could be set with the construction
     * the parent constructor is called after the reset of lst_name_dirty to enable setting by adding the list
     */
    function __construct(user $usr, array $lst = array())
    {
        $this->name_pos_lst = array();
        $this->lst_name_dirty = false;

        parent::__construct($usr, $lst);
    }


    /*
     * set and get
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
    function get_obj_by_name(string $name): ?term
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
     * @param object $obj_to_add the named user sandbox object that should be added
     * @param bool $allow_duplicates true if the list can contain the same entry twice e.g. for the components
     * @returns bool true if the object has been added
     */
    function add_obj(object $obj_to_add, bool $allow_duplicates = false): bool
    {
        $result = false;
        if (!in_array($obj_to_add->name(), $this->name_pos_lst()) or $allow_duplicates) {
            // if a sandbox object has a name, but not (yet) an id, add it nevertheless to the list
            if ($obj_to_add->id() == null) {
                $this->lst[] = $obj_to_add;
                $this->set_lst_dirty();
                $result = true;
            } else {
                $result = parent::add_obj($obj_to_add, $allow_duplicates);
            }
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
