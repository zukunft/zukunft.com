<?php

/*

    model/ref/source_type_list.php - to link coded functionality to a source
    ------------------------------

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
include_once MODEL_REF_PATH . 'source_type.php';

global $source_types;

class source_type_list extends type_list
{

    /*
     * database link
     */

    // object specific database and JSON object field names
    const FLD_ID = 'source_type_id';
    //const FLD_URL = 'base_url';

    /**
     * overwrite the user_type_list function to include the specific fields like the url
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param string $class the database name e.g. the table name without s
     * @return array the list of source types
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
                $type_obj = new source_type($type_code_id, $type_name, $type_comment);
                $type_obj->set_id($db_entry[self::FLD_ID]);
                //$type_obj->url = $db_entry[self::FLD_URL];
                $this->add($type_obj);
            }
        }
        return $this->lst();
    }

    /**
     * adding the source types used for unit tests to the dummy list
     */
    function load_dummy(): void
    {
        parent::load_dummy();
        $type = new source_type(source_type::XBRL, source_type::XBRL);
        $type->set_id(2);
        $this->add($type);
        $type = new source_type(source_type::CSV, source_type::CSV);
        $type->set_id(3);
        $this->add($type);
        $type = new source_type(source_type::PDF, source_type::PDF);
        $type->set_id(4);
        $this->add($type);
    }

    /**
     * return the database id of the default source type
     */
    function default_id(): int
    {
        return parent::id(source_type::XBRL);
    }

    /**
     * overwrite the user_type_list get function to be able to return the correct object
     * @param int $id the database id of the expected type
     * @return source_type|null the type object
     */
    function get_by_id(int $id): ?source_type
    {
        global $source_types;

        $lib = new library();
        $result = null;
        if ($id > 0) {
            if (array_key_exists($id, $source_types->lst())) {
                $result = $source_types->get($id);
            } else {
                log_err('Source type with is ' . $id . ' not found in ' . $lib->dsp_array($source_types->lst()));
            }
        } else {
            log_debug('Source type id not not set');
        }
        return $result;
    }
}

/**
 * exception to get_type that returns an extended user_type object
 * @param string $code_id the code id that must be unique within the given type
 * @return source_type|null the loaded source type object
 */
function get_source_type(string $code_id): ?source_type
{
    global $source_types;
    $id = $source_types->id($code_id);
    return $source_types->get_by_id($id);
}

function get_source_type_id(string $code_id): int
{
    global $source_types;
    return $source_types->id($code_id);
}

function get_source_type_by_id(string $id): source_type
{
    global $source_types;
    return $source_types->get_by_id($id);
}
