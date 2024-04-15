<?php

/*

    model/ref/ref_types.php - to link coded functionality to a reference
    -----------------------

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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace cfg;

use cfg\db\sql;
use cfg\db\sql_db;
use shared\library;

include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_par.php';
include_once MODEL_HELPER_PATH . 'type_list.php';
include_once MODEL_REF_PATH . 'ref_type.php';

global $ref_types;

class ref_type_list extends type_list
{

    /*
     * database link
     */

    // object specific database and JSON object field names
    const FLD_ID = 'ref_type_id';
    const FLD_URL = 'base_url';

    /**
     * overwrite the user_type_list function to include the specific fields like the url
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param string $class the database name e.g. the table name without s
     * @return array the list of reference types
     */
    protected function load_list(sql_db $db_con, string $class): array
    {
        $this->reset();
        $qp = $this->load_sql_all($db_con->sql_creator(), $class);
        $db_lst = $db_con->get($qp);
        if ($db_lst != null) {
            foreach ($db_lst as $db_entry) {
                $type_code_id = strval($db_entry[sql::FLD_CODE_ID]);
                $type_name = strval($db_entry[sql::FLD_TYPE_NAME]);
                $type_comment = strval($db_entry[sandbox_named::FLD_DESCRIPTION]);
                $type_obj = new ref_type($type_code_id, $type_name, $type_comment);
                $type_obj->set_id($db_entry[self::FLD_ID]);
                $type_obj->url = $db_entry[self::FLD_URL];
                // TODO check if still needed
                // $id = $db_entry[$db_con->get_id_field_name($db_type)];
                $this->add($type_obj);
            }
        }
        return $this->lst();
    }

    /**
     * adding the ref types used for unit tests to the dummy list
     */
    function load_dummy(): void
    {
        parent::load_dummy();
        $type = new ref_type(ref_type::WIKIPEDIA, ref_type::WIKIPEDIA);
        $type->set_id(2);
        $this->add($type);
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

        $lib = new library();
        $result = null;
        if ($id > 0) {
            if (array_key_exists($id, $ref_types->lst())) {
                $result = $ref_types->get($id);
            } else {
                log_err('Ref type with is ' . $id . ' not found in ' . $lib->dsp_array($ref_types->lst()));
            }
        } else {
            log_debug('Ref type id not not set');
        }
        return $result;
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
}

