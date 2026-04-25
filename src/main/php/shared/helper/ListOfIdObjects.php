<?php

/*

    shared/helper/ListOfIdObjects.php - the list of objects that have a unique database id
    ---------------------------------

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

include_once paths::MODEL_CONST . 'def.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_ENUM . 'value_types.php';
include_once paths::SHARED_HELPER . 'CombineObject.php';
include_once paths::SHARED_HELPER . 'IdObject.php';
include_once paths::SHARED_HELPER . 'ListOf.php';
include_once paths::SHARED_HELPER . 'TextIdObject.php';
include_once paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_object_seq_id;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_named;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\enum\value_types;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_web;

class ListOfIdObjects extends ListOf
{

    /*
     *  object vars
     */

    // memory vs speed optimize vars for faster finding the list position by the database id
    private array $id_pos_lst;
    private bool $hash_dirty;


    /*
     * construct and map
     */

    function __construct(array $lst = [])
    {
        $this->id_pos_lst = [];
        $this->set_hash_dirty();
        parent::__construct($lst);
    }

    function reset(): void
    {
        parent::reset();

        $this->id_pos_lst = [];
        $this->hash_dirty = false;
    }

    function clone_reset(): ListOfIdObjects
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
        $this->set_hash_dirty();
        return parent::set_lst($lst);
    }

    /**
     * to be called after the lists have been updated,
     * but the index list has not yet been updated
     * is overwritten by the child objects that have an additional hash
     * e.g. ListOfIdNamedObjects, sandbox_list_named, sandbox_link_list and sandbox_value_list
     */
    protected function set_hash_dirty(): void
    {
        $this->hash_dirty = true;
    }

    /**
     * @return true if at least one of the hash tables is not updated
     */
    protected function is_dirty(): bool
    {
        return $this->hash_dirty;
    }

    /**
     * to be called after the index lists have been updated
     * is overwritten by the child _sandbox_list_named
     */
    protected function set_hash_clean(): void
    {
        $this->hash_dirty = false;
    }


    /*
     * info
     */

    /**
     * get the first ids from the list e.g. to show it to humans
     *
     * @param ?int $limit the max number of ids to show
     * @return array with the database ids of all objects of this list
     */
    function ids(?int $limit = null): array
    {
        if ($limit == null and !$this->hash_dirty) {
            $result = array_keys($this->id_pos_lst);
        } else {
            $result = array();
            $pos = 0;
            foreach ($this->lst() as $sbx_obj) {
                if ($pos <= $limit or $limit == null) {
                    // use only valid ids
                    if ($sbx_obj->id() <> 0) {
                        $result[] = $sbx_obj->id();
                        $pos++;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * true if the id exists in this list
     * @param string $id the database id of the list item to check
     * @return bool true if the id exists in this list
     */
    function has_id(string $id): bool
    {
        return array_key_exists($id, $this->id_pos_lst());
    }


    /*
     * search
     */

    /**
     * select an item by id
     * TODO add unit tests
     *
     * @param int|string $id the unique database id of the object that should be returned
     * @return object|null the found user sandbox object or null if no id is found
     */
    function get(int|string $id): object|null
    {
        $key_lst = $this->id_pos_lst();
        if (array_key_exists($id, $key_lst)) {
            $pos = $key_lst[$id];
            return $this->lst()[$pos];
        } else {
            $lib = new library();
            log_info($id . ' not found in ' . $lib->dsp_array_keys($key_lst));
            return null;
        }
    }


    /*
     * filter
     */

    /**
     * get all objects that are not in the given list
     *
     * @param ListOfIdObjects $lst the list to compare with
     * @return ListOfIdObjects the list of objects that are only in this list
     */
    function diff(ListOfIdObjects $lst): ListOfIdObjects
    {
        $result = $this->clone_reset();
        foreach ($this->lst() as $obj) {
            if (!$lst->get($obj->id())) {
                $result->add_obj($obj);
            }
        }
        return $result;
    }

    /**
     * return the first object as a function for easy adding of exceptions
     *
     * @return word|verb|IdObject|TextIdObject|CombineObject|null the first object of the list
     */
    function get_first_object(): word|verb|IdObject|TextIdObject|CombineObject|null
    {
        return $this->lst()[0] ?? null;
    }

    /**
     * return the second object as a function for easy adding of exceptions
     *
     * @return word|IdObject|TextIdObject|CombineObject|null the second object of the list
     */
    function get_second_object(): word|IdObject|TextIdObject|CombineObject|null
    {
        return $this->lst()[1] ?? null;
    }


    /*
     * modify
     */

    /**
     * add an object to the list
     *
     * @param IdObject|TextIdObject|CombineObject $obj_to_add an object with a unique database id that should be added to the list
     * @param bool $allow_duplicates set it to true if duplicate db id should be allowed
     * @param Message $msg to report which entry is double
     * @returns bool false if the object has not been added
     */
    function add_obj(
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
            if (!$this->has_id($obj_to_add->id())) {
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
     * remove / unset an object of the list
     * and set the cache to dirty
     *
     * @param int|string $id the unique database id of the entry
     * @returns bool true if the object has been added
     */
    function unset_by_id(int|string $id): bool
    {
        $result = false;
        $key = $this->key_by_id($id);
        while ($key !== null) {
            $this->set_hash_dirty();
            if (parent::unset($key)) {
                $result = true;
                $key = $this->key_by_id($id);
            } else {
                $key = null;
            }

        }
        return $result;
    }

    private function key_by_id(int|string $id): int|null
    {
        $key = null;
        $id_pos_lst = $this->id_pos_lst();
        if (array_key_exists($id, $id_pos_lst)) {
            $key = $id_pos_lst[$id];
        }
        return $key;
    }

    /**
     * add the object to the list without duplicate check
     * and add the id to the id hash
     *
     * @param IdObject|TextIdObject|CombineObject|value_types $obj_to_add
     * @return void
     */
    protected function add_direct(IdObject|TextIdObject|CombineObject|value_types $obj_to_add): void
    {
        if (!$this->is_dirty()) {
            $this->id_pos_lst[$obj_to_add->id()] = count($this->lst());
        }
        parent::add_direct($obj_to_add);
    }

    /**
     * TODO add a unit test
     * @returns array with all unique ids of this list with the keys within this list
     */
    protected function id_pos_lst(): array
    {
        if ($this->hash_dirty) {
            $this->set_id_pos_lst();
        }
        return $this->id_pos_lst;
    }

    protected function set_id_pos_lst(): void
    {
        $this->id_pos_lst = [];
        foreach ($this->lst() as $key => $obj) {
            if (!array_key_exists($obj->id(), $this->id_pos_lst)) {
                $this->id_pos_lst[$obj->id()] = $key;
            }
        }
        $this->hash_dirty = false;
    }


    /*
     * overwrite
     */

    /**
     * add an object to the list that does
     * not yet have a database id
     * but has a name
     * or in second priority linked objects
     *
     * @param db_object_seq_id|null $to_add the object that should be added
     * @param bool $allow_duplicates true if the list can contain the same entry twice e.g. for the components
     * @param Message $msg to report which entry is double
     * @returns bool true if the object has been added
     */
    function add_by_key(
        db_object_seq_id|null $to_add,
        bool                  $allow_duplicates = false,
        Message               $msg = new Message()
    ): bool
    {
        $msg->add_err(msg_id::MISSING_FUNCTION_OVERWRITE, [
            msg_id::VAR_FUNCTION_NAME => 'add_by_key',
            msg_id::VAR_CLASS_NAME => $this::class
        ]);
        return false;
    }

    /**
     * add an object to the list that does
     * not yet have a database id
     * but has linked objects
     *
     * @param db_object_seq_id|null $to_add the object that should be added
     * @param bool $allow_duplicates true if the list can contain the same entry twice e.g. for the components
     * @param Message $msg to report which entry is double
     * @returns bool true if the object has been added
     */
    function add_by_link(
        db_object_seq_id|null $to_add,
        bool                  $allow_duplicates = false,
        Message               $msg = new Message()
    ): bool
    {
        $msg->add_err(msg_id::MISSING_FUNCTION_OVERWRITE, [
            msg_id::VAR_FUNCTION_NAME => 'add_by_link',
            msg_id::VAR_CLASS_NAME => $this::class
        ]);
        return false;
    }


    /**
     * TODO Prio 3 rename to remove
     *
     * unset an object of the list
     * TODO move to ListOfIdObjects ? And if not, explain in a comment why
     *
     * @param int|string $key the unique id of the entry
     * @returns bool true if the object has been added
     */
    protected function unset(int|string $key): bool
    {
        $this->set_hash_dirty();
        return parent::unset($key);
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
        if ($min_names < def::LIST_MIN_NAMES) {
            $min_names = def::LIST_MIN_NAMES;
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
