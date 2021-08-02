<?php

/*

  ref_types.php - to link coded functionality to a reference
  -----------------
  
  This file is part of zukunft.com - calc with refs

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

global $ref_types;

class ref_type_list extends user_type_list
{
    // list of the ref types that have a coded functionality
    const DBL_WIKIDATA = "wikidata";
    const DBL_WIKIPEDIA = "wikipedia";

    /**
     * overwrite the user_type_list function to include the specific fields like the url
     */
    function load_types(string $db_type, sql_db $db_con): array
    {
        $this->type_list = [];
        $db_con->set_type($db_type);
        $db_con->set_fields(array(sql_db::FLD_DESCRIPTION, sql_db::FLD_CODE_ID, 'base_url'));
        $sql = $db_con->select();
        $db_lst = $db_con->get($sql);
        if ($db_lst != null) {
            foreach ($db_lst as $db_entry) {
                $type_obj = new ref_type();
                $type_obj->name = $db_entry[sql_db::FLD_TYPE_NAME];
                $type_obj->comment = $db_entry[sql_db::FLD_DESCRIPTION];
                $type_obj->code_id = $db_entry[sql_db::FLD_CODE_ID];
                $type_obj->url = $db_entry['base_url'];
                $this->type_list[$db_entry[$db_con->get_id_field_name($db_type)]] = $type_obj;
            }
        }
        return $this->type_list;
    }

    /**
     * overwrite the general user type list load_by_db function to keep the link to the table type capsuled
     * @param string $db_type the database table type name to select either word, formula, view, ...
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @return bool true if load was successful
     */
    function load_by_db(string $db_type, sql_db $db_con): bool
    {
        $result = false;
        global $ref_types;
        $ref_types = $this->load_types($db_type, $db_con);
        $this->type_hash = parent::get_hash($ref_types);
        if (count($this->type_hash) > 0) {
            $result = true;
        }
        return $result;
    }

    /**
     * overwrite the general user type list load function to keep the link to the table type capsuled
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @return bool true if load was successful
     */
    function load(sql_db $db_con): bool
    {
        return $this->load_by_db(DB_TYPE_REF_TYPE, $db_con);
    }

    /**
     * adding the ref types used for unit tests to the dummy list
     */
    function load_dummy() {
        parent::load_dummy();
        $type = new ref_type();
        $type->name = ref_type_list::DBL_WIKIPEDIA;
        $type->code_id = ref_type_list::DBL_WIKIPEDIA;
        $this->type_list[2] = $type;
        $this->type_hash[ref_type_list::DBL_WIKIPEDIA] = 2;
    }

    /**
     * return the database id of the default ref type
     */
    function default_id(): int {
        return parent::id(ref_type_list::DBL_WIKIPEDIA);
    }

    /**
     * overwrite the user_type_list get function to be able to return the correct object
     * @param int $id the database id of the expected type
     * @return ref_type the type object
     */
    function get(int $id): ref_type
    {
        global $ref_types;
        $result = null;
        if ($id > 0) {
            if (array_key_exists($id, $ref_types->type_list)) {
                $result = $ref_types->type_list[$id];
            } else {
                log_err('Ref type with is ' . $id . ' not found in ' . dsp_array($ref_types->type_list));
            }
        } else {
            log_debug('Ref type id not not set');
        }
        return $result;
    }
}

/**
 * exception to get_type that returns an extended user_type object
 * @param string $code_id the code id that must be unique within the given type
 * @return ref_type the loaded ref type object
 */
function get_ref_type(string $code_id): ref_type
{
    global $ref_types;
    $id = $ref_types->id($code_id);
    return $ref_types->get($id);
}

function get_ref_type_id(string $code_id): int
{
    global $ref_types;
    return $ref_types->id($code_id);
}

function get_ref_type_by_id(string $id): ref_type
{
    global $ref_types;
    return $ref_types->get($id);
}
