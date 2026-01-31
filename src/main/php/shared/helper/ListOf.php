<?php

/*

    shared/helper/ListOf.php - a list of specific objects until php allows specific arrays
    ------------------------


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

use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\enum\value_types;
use Zukunft\ZukunftCom\main\php\shared\library;

class ListOf
{

    /*
     *  object vars
     */

    // the protected main var
    // to avoid adding or removing of objects
    // without updating the related index object
    private array $lst;


    /*
     * construct and map
     */

    function __construct(array $lst = [])
    {
        $this->lst = [];

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
     * TODO check if a more specific return object can be used
     * get one object of the list by the key
     * @param string|int $key the key of the lst array
     * @param user_message|null $usr_msg to report missing keys
     * @return IdObject|TextIdObject|CombineObject|null the found user sandbox object or null if no id is found
     */
    function get(
        string|int        $key,
        user_message|null $usr_msg = null
    ): IdObject|TextIdObject|CombineObject|null
    {
        if (array_key_exists($key, $this->lst)) {
            return $this->lst[$key];
        } else {
            $lib = new library();
            $usr_msg?->add_id_with_vars(msg_id::MISSING_KEY, [
                msg_id::VAR_NAME => $key,
                msg_id::VAR_CLASS_NAME => $lib->class_to_name($this::class),
            ]);
            return null;
        }
    }

    /**
     * @return true if the list has been replaced
     */
    function set_lst(array $lst): bool
    {
        $this->lst = $lst;
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


    /*
     * info
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


    /*
     * modify
     */

    /**
     * TODO Prio 3 if sandbox add function is always renamed to db_insert, rename to add
     * add an object to the list and by default avoid duplicates
     * in most cases overwritten by the child objects that are e.g. unique by the database id
     *
     * @param IdObject|TextIdObject|CombineObject $obj_to_add an object with a unique database id that should be added to the list
     * @param bool $allow_duplicates set it to true if duplicate db id should be allowed
     * @param user_message $usr_msg to report which entry is double
     * @returns user_message if adding failed or something is strange, the messages for the user with the suggested solutions
     */
    function add_obj(
        IdObject|TextIdObject|CombineObject $obj_to_add,
        bool                                $allow_duplicates = false,
        user_message                   $usr_msg = new user_message()
    ): user_message
    {
        // check boolean first because in_array might take longer
        if ($allow_duplicates) {
            $this->add_direct($obj_to_add);
        } else {
            if (!in_array($obj_to_add, $this->lst())) {
                $this->add_direct($obj_to_add);
            } else {
                $usr_msg->add_id_with_vars(msg_id::LIST_DOUBLE_ENTRY, [
                    msg_id::VAR_NAME => $obj_to_add->dsp_id(),
                    msg_id::VAR_CLASS_NAME => $obj_to_add::class
                ]);
            }
        }
        return $usr_msg;
    }

    /**
     * add the object to the list without duplicate check,
     * but including the updating the hash tables that are not dirty
     * are expected to be overwritten by all children that have a hash table
     *
     * @param IdObject|TextIdObject|CombineObject|value_types $obj_to_add
     * @return void
     */
    protected function add_direct(IdObject|TextIdObject|CombineObject|value_types $obj_to_add): void
    {
        $this->lst[] = $obj_to_add;
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
        $result = false;
        $key_lst = array_keys($this->lst);
        if (array_key_exists($key, $key_lst)) {
            unset ($this->lst[$key]);
            $result = true;
        }
        return $result;
    }

}
