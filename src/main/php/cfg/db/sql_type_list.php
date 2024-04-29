<?php

/*

    cfg/db/sql_type_list.php - a list of parameters to define which sql statement should be created
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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace cfg\db;

class sql_type_list
{
    public array $lst = [];  // a list of sql creation types and parameters

    /**
     * @param array $lst with the initial sql create parameter
     */
    function __construct(array $lst)
    {
        $this->lst = $lst;
    }

    public function set(array $lst): void
    {
        $this->lst = $lst;
    }

    /*
     * modification
     */

    /**
     * remove a type from the list if it has been in the list
     * @param sql_type $type_to_remove the sql creation type that should be removed
     * @return sql_type_list the list without the given parameter
     */
    function remove(sql_type $type_to_remove): sql_type_list
    {
        $result = clone $this;
        if (($key = array_search($type_to_remove, $result->lst)) !== false) {
            unset($result->lst[$key]);
        }
        return $result;
    }

    /**
     * add a type to the list
     * @param sql_type $type the sql creation type that should be added
     * @return void
     */
    function add(sql_type $type): void
    {
        if (!in_array($type, $this->lst)) {
            $this->lst[] = $type;
        }
    }


    /*
     * info
     */

    /**
     * @return bool true if an sql insert statement should be created
     */
    function is_insert(): bool
    {
        return in_array(sql_type::INSERT, $this->lst);
    }

    /**
     * @return bool true if sql is supposed to be part of another sql statement
     */
    function is_sub_tbl(): bool
    {
        return in_array(sql_type::SUB, $this->lst);
    }

    /**
     * @return bool true if sql should point to the user sandbox table
     */
    function is_usr_tbl(): bool
    {
        return in_array(sql_type::USER, $this->lst);
    }

    /**
     * @return bool true if sql is supposed to be part of another sql statement
     */
    public function is_list_tbl(): bool
    {
        return in_array(sql_type::LIST, $this->lst);
    }

    /**
     * @return bool true if sql is part of an update function
     */
    public function is_update_part(): bool
    {
        return in_array(sql_type::UPDATE_PART, $this->lst);
    }

    /**
     * @return bool true if sql is part of an update function
     */
    public function is_delete_part(): bool
    {
        return in_array(sql_type::DELETE_PART, $this->lst);
    }

    /**
     * @return bool true if the new values for an insert statement should be selected
     */
    public function use_select_for_insert(): bool
    {
        return in_array(sql_type::SELECT_FOR_INSERT, $this->lst);
    }

    /**
     * @return bool true if named parameters should be used
     */
    public function use_named_par(): bool
    {
        return in_array(sql_type::NAMED_PAR, $this->lst);
    }

    /**
     * @return bool true if the sql function should be created that also creates the log entries
     */
    public function and_log(): bool
    {
        return in_array(sql_type::LOG, $this->lst);
    }

    /**
     * @return bool true if a sql function should be created that combines a list of sql statements
     */
    public function create_function(): bool
    {
        return in_array(sql_type::FUNCTION, $this->lst);
    }

    /**
     * @return bool true if the type list suggests to exclude the row instead of deleting it
     */
    public function exclude_sql(): bool
    {
        return in_array(sql_type::EXCLUDE, $this->lst);
    }

    /**
     * @return bool true if a smallint as the prime db key e.g. for types
     */
    public function has_key_int_small(): bool
    {
        return in_array(sql_type::KEY_SMALL_INT, $this->lst);
    }

    /**
     * @return bool true if the standard sandbox fields should be added to the sql statement
     */
    public function use_sandbox_fields(): bool
    {
        return in_array(sql_type::SANDBOX, $this->lst);
    }

    /**
     * @return bool true if the standard sandbox fields should be added to the sql statement
     */
    public function no_id_return(): bool
    {
        return in_array(sql_type::NO_ID_RETURN, $this->lst);
    }

    /**
     * @return string the table name extension excluding the user sandbox indication
     */
    public function ext_ex_user(): string
    {
        $ext = '';
        foreach ($this->lst as $sc_par) {
            if ($sc_par != sql_type::USER) {
                $ext .= $sc_par->extension();
            }
        }
        return $ext;
    }

    /**
     * the extension of the table name so excluding the insert, update and delete query name extension
     *
     * @return bool true if an insert, update or delete sql statement should be created
     */
    public function is_cur_not_l(): bool
    {
        $result = false;
        foreach ($this->lst as $sc_par) {
            if ($sc_par == sql_type::INSERT
                or $sc_par == sql_type::UPDATE
                or $sc_par == sql_type::DELETE) {
                $result = true;
            }
        }
        return $result;
    }


    /*
     * extract
     */

    /**
     * @return string the table name extension based on this list
     */
    public function extension(): string
    {
        $ext = '';
        foreach ($this->lst as $sql_type) {
            $ext .= $sql_type->extension();
        }
        return $ext;
    }

    /**
     * @return string with the paraneters in a human-readable format
     */
    public function dsp_id(): string
    {
        return implode(', ', $this->lst);
    }

    /**
     * the extension of the table name so excluding the insert, update and delete query name extension
     *
     * @return string the table name extension excluding the user sandbox indication
     */
    public function tbl_ext_ex_user(): string
    {
        $ext = '';
        foreach ($this->lst as $sc_par) {
            if ($sc_par != sql_type::USER
                and $sc_par != sql_type::INSERT
                and $sc_par != sql_type::UPDATE
                and $sc_par != sql_type::DELETE
                and $sc_par != sql_type::SUB
                and $sc_par != sql_type::LIST) {
                $ext .= $sc_par->extension();
            }
        }
        return $ext;
    }

}

