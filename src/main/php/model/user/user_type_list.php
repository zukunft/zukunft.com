<?php

/*

  user_type_list.php - the superclass for word, formula and view type lists
  ------------------


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
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.
  
  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>
  
  Copyright (c) 1995-2021 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

class user_type_list
{

    // persevered type name and code id for unit and integration tests
    const TEST_NAME = 'System Test Type Name';
    const TEST_TYPE = 'System Test Type Code ID';

    public array $lst = []; // a list of type objects
    public array $hash = []; // hash list with the code id for fast selection

    /**
     * force to reload the type names and translations from the database
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param string $db_type the database name e.g. the table name without s
     * @return array the list of types
     */
    private function load_list(sql_db $db_con, string $db_type): array
    {
        $this->lst = [];
        $db_con->set_type($db_type);
        $db_con->set_fields(array(sql_db::FLD_DESCRIPTION, sql_db::FLD_CODE_ID));
        $sql = $db_con->select_by_id();
        $db_lst = $db_con->get_old($sql);
        if ($db_lst != null) {
            foreach ($db_lst as $db_entry) {
                $type_obj = new user_type();
                if ($db_type == db_cl::LOG_TABLE) {
                    $type_obj->name = $db_entry['change_table_name'];
                } else {
                    $type_obj->name = $db_entry[sql_db::FLD_TYPE_NAME];
                }
                $type_obj->comment = $db_entry[sql_db::FLD_DESCRIPTION];
                $type_obj->code_id = $db_entry[sql_db::FLD_CODE_ID];
                $this->lst[$db_entry[$db_con->get_id_field_name($db_type)]] = $type_obj;
            }
        }
        return $this->lst;
    }

    /**
     * recreate the hash table to get the database id for a code_id
     * @param array $type_list the list of the code_id indexed by the database id
     * @return array with the database ids indexed by the code_id
     */
    function get_hash(array $type_list): array
    {
        $this->hash = [];
        if ($type_list != null) {
            foreach ($type_list as $key => $type) {
                $this->hash[$type->code_id] = $key;
            }
        }
        return $this->hash;
    }

    /**
     * reload a type list from the database e.g. because a translation has changed and fill the hash table
     * @param string $db_type the database table type name to select either word, formula, view, ...
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @return bool true if load was successful
     */
    function load(sql_db $db_con, string $db_type): bool
    {
        $result = false;
        $this->lst = $this->load_list($db_con, $db_type);
        $this->hash = $this->get_hash($this->lst);
        if (count($this->hash) > 0) {
            $result = true;
        }
        return $result;
    }

    /**
     * return the database row id based on the code_id
     *
     * @param string $code_id
     * @return int the database id for the given code_id
     */
    function id(string $code_id): int
    {
        $result = 0;
        if ($code_id != '' and $code_id != null) {
            if (array_key_exists($code_id, $this->hash)) {
                $result = $this->hash[$code_id];
            } else {
                log_err('Type id not found for ' . $code_id . ' in ' . dsp_array_keys($this->hash));
            }
        } else {
            log_debug('Type code id not not set');
        }
        return $result;
    }

    /**
     * return user specific type name based on the database row id
     *
     * @param int|null $id
     * @return string
     */
    function name(?int $id): string
    {
        $result = '';
        if ($id != null) {

            $type = $this->get_by_id($id);
            if ($type != null) {
                $result = $type->name;
            } else {
                log_debug('Type id ' . $id . ' not found');
            }
        }
        return $result;
    }

    /**
     * pick a type from the preloaded object list
     * @param int $id the database id of the expected type
     * @return user_type the type object
     */
    function get_by_id(int $id): user_type
    {
        $result = null;
        if ($id > 0) {
            if (array_key_exists($id, $this->lst)) {
                $result = $this->lst[$id];
            } else {
                log_err('Type with is ' . $id . ' not found in ' . dsp_array($this->lst));
            }
        } else {
            log_debug('Type id not set');
        }
        return $result;
    }

    /**
     * TODO to rename to get and rename get to get_by_id
     */
    function get_by_code_id(string $code_id): user_type
    {
        return $this->get_by_id($this->id($code_id));
    }

    function code_id(int $id): string
    {
        $result = '';
        $type = $this->get_by_id($id);
        if ($type != null) {
            $result = $type->code_id;
        } else {
            log_err('Type code id not found for ' . $id . ' in ' . dsp_array($this->lst));
        }
        return $result;
    }

    /**
     * @return bool true if the list is empty (and a foreach loop will fail)
     */
    function is_empty(): bool
    {
        $result = false;
        if (empty($this->lst)) {
            $result = true;
        }
        return $result;
    }

    /*
     * display functions
     */

    /**
     * @return string the verb list with the internal database ids for debugging
     */
    function dsp_id(): string
    {
        $names = '';
        $ids = '';
        if (!$this->is_empty()) {
            foreach ($this->lst as $key => $type) {
                if ($names != '') {
                    $names .= ', ';
                }
                $names .= '"' . $type->name . '"';

                if ($ids != '') {
                    $ids .= ', ';
                }
                $ids .= $key;
            }
        }
        return $names . ' (' . $ids . ')';
    }



    /*
     * unit test support functions
     */

    /**
     * create dummy type list for the unit tests without database connection
     */
    function load_dummy()
    {
        $this->lst = array();
        $this->hash = array();
        $type = new user_type();
        $type->name = user_type_list::TEST_NAME;
        $type->code_id = user_type_list::TEST_TYPE;
        $this->lst[1] = $type;
        $this->hash[user_type_list::TEST_TYPE] = 1;
    }

}