<?php

/*

    shared/helper/ListOfIdNamedObjects.php - the list of objects that have a unique name and a unique database id
    --------------------------------------

    This is the parent list object for back and frontend.
    Has an array with the id for faster return of single objects by the database id.
    TODO Prio 3 check if valkey/redis db is faster


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

namespace Zukunft\ZukunftCom\main\php\shared\helper;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_ENUM . 'value_types.php';
include_once paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\enum\value_types;
use Zukunft\ZukunftCom\main\php\shared\library;

class ListOfIdNamedObjects extends ListOfIdObjects
{

    /*
     *  object vars
     */

    // memory vs speed optimize vars for faster finding the list position by the unique name
    private array $name_pos_lst;
    private bool $lst_name_dirty;


    /*
     * construct and map
     */

    function __construct(array $lst = [])
    {
        // create the name hash only if needed
        $this->name_pos_lst = [];
        parent::__construct($lst);
    }

    function reset(): void
    {
        parent::reset();

        // create the name hash only if needed
        $this->name_pos_lst = [];
        $this->lst_name_dirty = false;
    }

    function clone_reset(): ListOfIdNamedObjects
    {
        $lst = clone $this;
        $lst->reset();
        return $lst;
    }


    /*
     * set and get
     */

    /**
     * @return true if the list has been replaced
     */
    function set_lst(array $lst): bool
    {
        $this->lst_name_dirty = true;
        return parent::set_lst($lst);
    }

    /**
     * to be called after the lists have been updated
     * should be overwritten by child objects that have an additional hash
     */
    protected function set_hash_dirty(): void
    {
        $this->lst_name_dirty = true;
        parent::set_hash_dirty();
    }

    /**
     * to be called after the index/hash lists have been updated
     * should be overwritten by child objects that have an additional hash
     */
    protected function set_hash_clean(): void
    {
        $this->lst_name_dirty = false;
        parent::set_hash_clean();
    }


    /*
     * info
     */

    /**
     * get the first names from the list e.g. to show it to humans
     *
     * @param ?int $limit the max number of ids to show
     * @return array with the database ids of all objects of this list
     */
    function names(?int $limit = null): array
    {
        if ($limit == null and !$this->lst_name_dirty) {
            $result = array_keys($this->name_pos_lst);
        } else {
            $result = array();
            $pos = 0;
            foreach ($this->lst() as $sbx_obj) {
                if ($pos <= $limit or $limit == null) {
                    // use only valid ids
                    if ($sbx_obj->name() <> 0) {
                        $result[] = $sbx_obj->name();
                        $pos++;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * true if the name exists in this type list
     * @param string $name the name of the type
     * @return bool true if the name exists in this type list
     */
    function has_name(string $name): bool
    {
        return array_key_exists($name, $this->name_pos_lst());
    }


    /*
     * search
     */

    /**
     * select an item by name
     * TODO add unit tests
     *
     * @param string $name the unique name of the object that should be returned
     * @return object|null the found named object or null if no id is found
     */
    function get_by_name(string $name): object|null
    {
        $key_lst = $this->name_pos_lst();
        if (array_key_exists($name, $key_lst)) {
            $pos = $key_lst[$name];
            return $this->lst()[$pos];
        } else {
            $lib = new library();
            log_info($name . ' not found in ' . $lib->dsp_array_keys($key_lst));
            return null;
        }
    }

    function id_by_name(string $name): int
    {
        $obj = $this->get_by_name($name);
        if ($obj != null) {
            return $obj->id();
        } else {
            return 0;
        }
    }


    /*
     * filter
     */

    /**
     * get all objects that are not in the given list based on the name
     * TODO Prio 0 add unit test
     *
     * @param ListOfIdNamedObjects|ListOfIdObjects $lst the list to compare with
     * @return ListOfIdNamedObjects|ListOfIdObjects the list of objects that are only in this list
     */
    function diff_by_name(ListOfIdNamedObjects|ListOfIdObjects $lst): ListOfIdNamedObjects|ListOfIdObjects
    {
        $result = $this->clone_reset();
        foreach ($this->lst() as $obj) {
            if (!$lst->get_by_name($obj->name())) {
                $result->add_obj($obj);
            }
        }
        return $result;
    }


    /*
     * modify
     */

    /**
     * add an object to the list or fill up the object with the same name
     *
     * @param IdObject|TextIdObject|CombineObject $obj_to_add an object with a unique database id that should be added to the list
     * @param bool $allow_duplicates set it to true if duplicate db id should be allowed
     * @param Message $msg to report which entry is double
     * @returns bool false if the object has not been added
     */
    function add_obj_by_name(
        IdObject|TextIdObject|CombineObject $obj_to_add,
        bool                                $allow_duplicates = false,
        Message                             $msg = new Message()
    ): bool
    {
        // check boolean first because in_array might take longer
        if ($allow_duplicates) {
            $this->add_direct($obj_to_add);
            $this->set_hash_dirty();
        } else {
            if (!$this->has_name($obj_to_add->name())) {
                $this->add_direct($obj_to_add);
            } else {
                $msg->add(msg_id::LIST_DOUBLE_ENTRY, [
                    msg_id::VAR_NAME => $obj_to_add->dsp_id(),
                    msg_id::VAR_CLASS_NAME => $obj_to_add::class
                ]);
            }
        }
        return $msg->is_ok();
    }

    /**
     * add the object to the list without duplicate check
     * and add the id to the id hash
     *
     * @param type_object|IdObject|TextIdObject|CombineObject|value_types $obj_to_add
     * @return void
     */
    protected function add_direct(type_object|IdObject|TextIdObject|CombineObject|value_types $obj_to_add): void
    {
        if (!$this->lst_name_dirty) {
            $this->name_pos_lst[$obj_to_add->name()] = count($this->lst());
        }
        parent::add_direct($obj_to_add);
    }

    /**
     * remove / unset a named object of the list
     * and set the cache to dirty
     *
     * @param string $name the unique name of the entry
     * @returns bool true if the object has been added
     */
    function unset_by_name(string $name): bool
    {
        $result = false;
        $key = $this->key_by_name($name);
        while ($key !== null) {
            $this->set_hash_dirty();
            if (parent::unset($key)) {
                $result = true;
                $key = $this->key_by_name($name);
            } else {
                $key = null;
            }

        }
        return $result;
    }

    private function key_by_name(string $name): int|null
    {
        $key = null;
        $pos_lst = $this->name_pos_lst();
        if (array_key_exists($name, $pos_lst)) {
            $key = $pos_lst[$name];
        }
        return $key;
    }

    /**
     * TODO add a unit test
     * @returns array with all unique ids of this list with the keys within this list
     */
    protected function name_pos_lst(): array
    {
        if ($this->lst_name_dirty) {
            $this->set_name_pos_lst();
        }
        return $this->name_pos_lst;
    }

    /**
     * recreated the name hast array
     * @return void
     */
    protected function set_name_pos_lst(): void
    {
        $this->name_pos_lst = [];
        foreach ($this->lst() as $key => $obj) {
            if (!array_key_exists($obj->name(), $this->name_pos_lst)) {
                $this->name_pos_lst[$obj->name()] = $key;
            }
        }
        $this->lst_name_dirty = false;
    }

}
