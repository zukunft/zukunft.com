<?php

/*

    shared/helper/ListOfIdNamedCodeObjects.php - the list of objects that have a unique code_id, name and a unique database id
    ------------------------------------------

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

include_once paths::SHARED_HELPER . 'ListOfIdNamedObjects.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_ENUM . 'value_types.php';
include_once paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\enum\value_types;
use Zukunft\ZukunftCom\main\php\shared\library;

class ListOfIdNamedCodeObjects extends ListOfIdNamedObjects
{

    /*
     *  object vars
     */

    // memory vs speed optimize vars for faster finding the list position by the unique code id
    private array $code_id_pos_lst;
    private bool $lst_code_id_dirty;


    /*
     * construct and map
     */

    function __construct(array $lst = [])
    {
        // create the code id hash only if needed
        $this->code_id_pos_lst = [];
        parent::__construct($lst);
    }

    function reset(): void
    {
        parent::reset();

        // create the code id hash only if needed
        $this->code_id_pos_lst = [];
        $this->lst_code_id_dirty = false;
    }

    function clone_reset(): ListOfIdNamedCodeObjects
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
        $this->lst_code_id_dirty = true;
        return parent::set_lst($lst);
    }

    /**
     * to be called after the lists have been updated
     * should be overwritten by child objects that have an additional hash
     */
    protected function set_hash_dirty(): void
    {
        $this->lst_code_id_dirty = true;
        parent::set_hash_dirty();
    }

    /**
     * to be called after the index/hash lists have been updated
     * should be overwritten by child objects that have an additional hash
     */
    protected function set_hash_clean(): void
    {
        $this->lst_code_id_dirty = false;
        parent::set_hash_clean();
    }


    /*
     * info
     */

    /**
     * get the first code ids from the list e.g. to show it to humans
     *
     * @param ?int $limit the max number of ids to show
     * @return array with the database ids of all objects of this list
     */
    function code_ids(?int $limit = null): array
    {
        if ($limit == null and !$this->lst_code_id_dirty) {
            $result = array_keys($this->code_id_pos_lst);
        } else {
            $result = array();
            $pos = 0;
            foreach ($this->lst() as $sbx_obj) {
                if ($pos <= $limit or $limit == null) {
                    // use only valid ids
                    if ($sbx_obj->code_id <> 0) {
                        $result[] = $sbx_obj->code_id;
                        $pos++;
                    }
                }
            }
        }
        return $result;
    }

    function has_code_id(string $code_id): bool
    {
        return array_key_exists($code_id, $this->code_id_pos_lst());
    }


    /*
     * search
     */

    /**
     * select an item by code id
     * TODO add unit tests
     *
     * @param string $code_id the unique code id of the object that should be returned
     * @return object|null the found code id object or null if no id is found
     */
    function get_by_code_id(string $code_id): object|null
    {
        $key_lst = $this->code_id_pos_lst();
        if (array_key_exists($code_id, $key_lst)) {
            $pos = $key_lst[$code_id];
            return $this->lst()[$pos];
        } else {
            $lib = new library();
            log_info($code_id . ' not found in ' . $lib->dsp_array_keys($key_lst));
            return null;
        }
    }

    function id_by_code_id(string $code_id): int
    {
        $obj = $this->get_by_code_id($code_id);
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
     * get all objects that are not in the given list based on the code id
     * TODO Prio 0 add unit test
     *
     * @param ListOfIdNamedCodeObjects|ListOfIdObjects $lst the list to compare with
     * @return ListOfIdNamedCodeObjects|ListOfIdObjects the list of objects that are only in this list
     */
    function diff_by_code_id(ListOfIdNamedCodeObjects|ListOfIdObjects $lst): ListOfIdNamedCodeObjects|ListOfIdObjects
    {
        $result = $this->clone_reset();
        foreach ($this->lst() as $obj) {
            if (!$lst->get_by_code_id($obj->code_id)) {
                $result->add_obj($obj);
            }
        }
        return $result;
    }


    /*
     * modify
     */

    /**
     * add an object to the list or fill up the object with the same code id
     *
     * @param type_object|IdObject|TextIdObject|CombineObject $obj_to_add an object with a unique database id that should be added to the list
     * @param bool $allow_duplicates set it to true if duplicate db id should be allowed
     * @param Message $msg to report which entry is double
     * @returns bool false if the object has not been added
     */
    function add_obj_by_code_id(
        type_object|IdObject|TextIdObject|CombineObject $obj_to_add,
        bool                                $allow_duplicates = false,
        Message                             $msg = new Message()
    ): bool
    {
        // check boolean first because in_array might take longer
        if ($allow_duplicates) {
            $this->add_direct($obj_to_add);
            $this->set_hash_dirty();
        } else {
            if (!$this->has_code_id($obj_to_add->code_id)) {
                if ($obj_to_add->name == '') {
                    $this->add_direct($obj_to_add);
                } elseif (!$this->has_name($obj_to_add->name)) {
                    $this->add_direct($obj_to_add);
                } else {
                    $msg->add(msg_id::LIST_DOUBLE_ENTRY, [
                        msg_id::VAR_NAME => $obj_to_add->dsp_id(),
                        msg_id::VAR_CLASS_NAME => $obj_to_add::class
                    ]);
                }
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
        if (!$this->lst_code_id_dirty) {
            $this->code_id_pos_lst[$obj_to_add->code_id] = count($this->lst());
        }
        parent::add_direct($obj_to_add);
    }

    /**
     * remove / unset a code id object of the list
     * and set the cache to dirty
     * TODO Prio 0 add unit test
     *
     * @param string $code_id the unique code id of the entry
     * @returns bool true if the object has been added
     */
    function unset_by_code_id(string $code_id): bool
    {
        $result = false;
        $key = $this->key_by_code_id($code_id);
        while ($key !== null) {
            $this->set_hash_dirty();
            if (parent::unset($key)) {
                $result = true;
                $key = $this->key_by_code_id($code_id);
            } else {
                $key = null;
            }

        }
        return $result;
    }

    private function key_by_code_id(string $code_id): int|null
    {
        $key = null;
        $pos_lst = $this->code_id_pos_lst();
        if (array_key_exists($code_id, $pos_lst)) {
            $key = $pos_lst[$code_id];
        }
        return $key;
    }

    /**
     * TODO add a unit test
     * @returns array with all unique ids of this list with the keys within this list
     */
    protected function code_id_pos_lst(): array
    {
        if ($this->lst_code_id_dirty) {
            $this->set_code_id_pos_lst();
        }
        return $this->code_id_pos_lst;
    }

    /**
     * recreated the code id hast array
     * @return void
     */
    protected function set_code_id_pos_lst(): void
    {
        $this->code_id_pos_lst = [];
        foreach ($this->lst() as $key => $obj) {
            if (!array_key_exists($obj->code_id, $this->code_id_pos_lst)) {
                $this->code_id_pos_lst[$obj->code_id] = $key;
            }
        }
        $this->lst_code_id_dirty = false;
    }

}
