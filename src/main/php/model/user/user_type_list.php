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

    public array $type_list = [];
    public array $type_hash = [];

    /**
     * force the types to reload the names and translations from the database
     */
    function load_types(string $db_type, sql_db $db_con): array
    {
        $this->type_list = [];
        $db_con->set_type($db_type);
        $db_con->set_fields(array(sql_db::FLD_DESCRIPTION, sql_db::FLD_CODE_ID));
        $sql = $db_con->select();
        $db_lst = $db_con->get($sql);
        if ($db_lst != null) {
            foreach ($db_lst as $db_entry) {
                $type_obj = new user_type();
                $type_obj->name = $db_entry[sql_db::FLD_TYPE_NAME];
                $type_obj->comment = $db_entry[sql_db::FLD_DESCRIPTION];
                $type_obj->code_id = $db_entry[sql_db::FLD_CODE_ID];
                $this->type_list[$db_entry[$db_con->get_id_field_name($db_type)]] = $type_obj;
            }
        }
        return $this->type_list;
    }

    function get_hash(array $type_list): array
    {
        $this->type_hash = [];
        if ($type_list != null) {
            foreach ($type_list as $key => $type) {
                $this->type_hash[$type->code_id] = $key;
            }
        }
        return $this->type_hash;
    }

    /**
     * reload a type list from the database e.g. because a translation has changed and fill the hash table
     * @param string $db_type the database table type name to select either word, formula, view, ...
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @return bool true if load was successful
     */
    function load_by_db(string $db_type, sql_db $db_con): bool
    {
        $result = false;
        $this->type_list = $this->load_types($db_type, $db_con);
        $this->type_hash = $this->get_hash($this->type_list);
        if (count($this->type_hash) > 0) {
            $result = true;
        }
        return $result;
    }

    function id(string $code_id): int
    {
        $result = 0;
        if ($code_id != '' and $code_id != null) {
            if (array_key_exists($code_id, $this->type_hash)) {
                $result = $this->type_hash[$code_id];
            } else {
                log_err('Type id not found for ' . $code_id . ' in ' . dsp_array($this->type_hash));
            }
        } else {
            log_debug('Type code id not not set');
        }
        return $result;
    }

    /**
     * pick a type from the preloaded object list
     * @param int $id the database id of the expected type
     * @return user_type the type object
     */
    function get(int $id): user_type
    {
        $result = null;
        if ($id > 0) {
            if (array_key_exists($id, $this->type_list)) {
                $result = $this->type_list[$id];
            } else {
                log_err('Type with is ' . $id . ' not found in ' . dsp_array($this->type_list));
            }
        } else {
            log_debug('Type id not not set');
        }
        return $result;
    }

    function code_id(int $id): string
    {
        $result = '';
        $type = $this->get($id);
        if ($type != null) {
            $result = $type->code_id;
        } else {
            log_err('Type code id not found for ' . $id . ' in ' . dsp_array($this->type_list));
        }
        return $result;
    }

    /**
     * create dummy type list for the unit tests without database connection
     */
    function load_dummy()
    {
        $this->type_list = array();
        $this->type_hash = array();
        $type = new user_type();
        $type->name = user_type_list::TEST_NAME;
        $type->code_id = user_type_list::TEST_TYPE;
        $this->type_list[1] = $type;
        $this->type_hash[user_type_list::TEST_TYPE] = 1;

    }
}