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
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.
  
  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>
  
  Copyright (c) 1995-2022 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

global $ref_types;

class ref_type_list extends user_type_list
{

    /*
     * database link
     */

    // object specific database and JSON object field names
    const FLD_ID = 'ref_type_id';
    const FLD_URL = 'base_url';

    /**
     * overwrite the user_type_list function to create the SQL to load the ref types
     *
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param string $db_type the database name e.g. the table name without s
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_db $db_con, string $db_type): sql_par
    {
        $db_con->set_type($db_type);
        $qp = new sql_par($db_type);
        $qp->name = $db_type;
        $db_con->set_name($qp->name);
        //TODO check if $db_con->set_usr($this->usr->id); is needed
        $db_con->set_fields(array(sql_db::FLD_DESCRIPTION, sql_db::FLD_CODE_ID, self::FLD_URL));
        $db_con->set_page_par(SQL_ROW_MAX);
        $qp->sql = $db_con->select_all();
        $qp->par = $db_con->get_par();
        return $qp;
    }

    /**
     * overwrite the user_type_list function to include the specific fields like the url
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param string $db_type the database name e.g. the table name without s
     * @return void the list of reference types
     */
    private function load_list(sql_db $db_con, string $db_type): void
    {
        $this->lst = array();
        $qp = $this->load_sql($db_con, $db_type);
        $db_lst = $db_con->get($qp);
        if ($db_lst != null) {
            foreach ($db_lst as $db_entry) {
                $type_code_id = strval($db_entry[sql_db::FLD_CODE_ID]);
                $type_name = strval($db_entry[sql_db::FLD_TYPE_NAME]);
                $type_comment = strval($db_entry[sql_db::FLD_DESCRIPTION]);
                $type_obj = new ref_type($type_code_id, $type_name, $type_comment);
                $type_obj->id = $db_entry[self::FLD_ID];
                $type_obj->url = $db_entry[self::FLD_URL];
                $this->lst[$db_entry[$db_con->get_id_field_name($db_type)]] = $type_obj;
            }
        }
    }

    /**
     * overwrite the general user type list load_by_db function to keep the link to the table type capsuled
     * @param string $db_type the database table type name to select either word, formula, view, ...
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @return bool true if load was successful
     */
    function load(sql_db $db_con, string $db_type = DB_TYPE_REF_TYPE): bool
    {
        $result = false;
        $this->load_list($db_con, $db_type);
        $this->hash = parent::get_hash($this->lst);
        if (count($this->hash) > 0) {
            $result = true;
        }
        return $result;
    }

    /**
     * adding the ref types used for unit tests to the dummy list
     */
    function load_dummy(): void
    {
        parent::load_dummy();
        $type = new ref_type(ref_type::WIKIPEDIA, ref_type::WIKIPEDIA);
        $type->id = 2;
        $this->lst[2] = $type;
        $this->hash[ref_type::WIKIPEDIA] = 2;
    }

    /**
     * return the database id of the default ref type
     */
    function default_id(): int
    {
        return parent::id(ref_type::WIKIPEDIA);
    }

    /**
     * overwrite the user_type_list get function to be able to return the correct object
     * @param int $id the database id of the expected type
     * @return ref_type|null the type object
     */
    function get_by_id(int $id): ?ref_type
    {
        global $ref_types;
        $result = null;
        if ($id > 0) {
            if (array_key_exists($id, $ref_types->lst)) {
                $result = $ref_types->lst[$id];
            } else {
                log_err('Ref type with is ' . $id . ' not found in ' . dsp_array($ref_types->lst));
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
 * @return ref_type|null the loaded ref type object
 */
function get_ref_type(string $code_id): ?ref_type
{
    global $ref_types;
    $id = $ref_types->id($code_id);
    return $ref_types->get_by_id($id);
}

function get_ref_type_id(string $code_id): int
{
    global $ref_types;
    return $ref_types->id($code_id);
}

function get_ref_type_by_id(string $id): ref_type
{
    global $ref_types;
    return $ref_types->get_by_id($id);
}
