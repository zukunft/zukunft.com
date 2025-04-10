<?php

/*

    shared/helper/ListOfIdObjects.php - the list of objects that have a unique database id
    ---------------------------------


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

namespace shared\helper;

include_once MODEL_USER_PATH . 'user_message.php';
include_once SHARED_ENUM_PATH . 'messages.php';
include_once SHARED_HELPER_PATH . 'ListOf.php';
include_once SHARED_PATH . 'library.php';

use cfg\user\user_message;
use shared\enum\messages as msg_id;
use shared\library;

class ListOfIdObjects extends ListOf
{

    /*
     *  object vars
     */

    // memory vs speed optimize vars for faster finding the list position by the database id
    private array $id_pos_lst;
    private bool $lst_dirty;


    /*
     * construct and map
     */

    function __construct(array $lst = [])
    {
        parent::__construct();

        $this->id_pos_lst = [];
        $this->lst_dirty = false;
    }


    /*
     * set and get
     */

    /**
     * @return true if the list has been replaced
     */
    function set_lst(array $lst): bool
    {
        $this->set_lst_dirty();
        return parent::set_lst($lst);
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
     * info
     */

    /**
     * @param ?int $limit the max number of ids to show
     * @return array with the database ids of all objects of this list
     */
    function ids(int $limit = null): array
    {
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
        return $result;
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
    function get_by_id(int|string $id): object|null
    {
        $lib = new library();
        $key_lst = $this->id_pos_lst();
        if (array_key_exists($id, $key_lst)) {
            $pos = $key_lst[$id];
            return $this->lst()[$pos];
        } else {
            log_info($id . ' not found in ' . $lib->dsp_array_keys($key_lst));
            return null;
        }
    }


    /*
     * modify
     */

    /**
     * add an object to the list
     *
     * @param IdObject|TextIdObject|CombineObject $obj_to_add an object with a unique database id that should be added to the list
     * @param bool $allow_duplicates set it to true if duplicate db id should be allowed
     * @returns user_message if adding failed or something is strange the messages for the user with the suggested solutions
     */
    function add_obj(
        IdObject|TextIdObject|CombineObject $obj_to_add,
        bool $allow_duplicates = false
    ): user_message
    {
        $usr_msg = new user_message();

        // check boolean first because in_array might take longer
        if ($allow_duplicates) {
            $this->add_direct($obj_to_add);
            $this->set_lst_dirty();
        } else {
            if (!in_array($obj_to_add->id(), $this->ids())) {
                $this->add_direct($obj_to_add);
                $this->set_lst_dirty();
            } else {
                $usr_msg->add_id(msg_id::LIST_DOUBLE_ENTRY);
            }
        }
        return $usr_msg;
    }

    /**
     * TODO add a unit test
     * @returns array with all unique ids of this list with the keys within this list
     */
    protected function id_pos_lst(): array
    {
        $result = array();
        if ($this->lst_dirty) {
            foreach ($this->lst() as $key => $obj) {
                if (!array_key_exists($obj->id(), $result)) {
                    $result[$obj->id()] = $key;
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
