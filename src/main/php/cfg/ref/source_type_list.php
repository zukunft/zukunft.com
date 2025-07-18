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

namespace cfg\ref;

include_once MODEL_HELPER_PATH . 'type_list.php';
include_once DB_PATH . 'sql.php';
include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_par.php';
include_once MODEL_SANDBOX_PATH . 'sandbox_named.php';
include_once MODEL_REF_PATH . 'source_type.php';
include_once SHARED_ENUM_PATH . 'source_types.php';
include_once SHARED_PATH . 'library.php';

use cfg\helper\type_list;
use cfg\db\sql;
use cfg\db\sql_db;
use cfg\sandbox\sandbox_named;
use shared\enum\source_types;
use shared\library;

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
                $type_code_id = strval($db_entry[sql_db::FLD_CODE_ID]);
                $type_name = strval($db_entry[sql_db::FLD_TYPE_NAME]);
                $type_comment = strval($db_entry[sql_db::FLD_DESCRIPTION]);
                $type_obj = new source_type($type_code_id, $type_name, $type_comment);
                $type_obj->set_id($db_entry[self::FLD_ID]);
                //$type_obj->set_url($db_entry[self::FLD_URL]);
                $this->add($type_obj);
            }
        }
        return $this->lst();
    }

    /**
     * adding the source types used for unit tests to the dummy list
     * TODO Prio 3: load from csv
     */
    function load_dummy(): void
    {
        parent::load_dummy();
        $type = new source_type(source_types::XBRL, source_types::XBRL);
        $type->set_id(source_types::XBRL_ID);
        $this->add($type);
        $type = new source_type(source_types::CSV, source_types::CSV);
        $type->set_id(source_types::CSV_ID);
        $this->add($type);
        $type = new source_type(source_types::PDF, source_types::PDF);
        $type->set_id(source_types::PDF_ID);
        $this->add($type);
    }

    /**
     * return the database id of the default source type
     */
    function default_id(): int
    {
        return parent::id(source_types::XBRL);
    }

    /**
     * overwrite the user_type_list get function to be able to return the correct object
     * @param int $id the database id of the expected type
     * @return source_type|null the type object
     */
    function get_by_id(int $id): ?source_type
    {
        global $src_typ_cac;

        $lib = new library();
        $result = null;
        if ($id > 0) {
            if (array_key_exists($id, $src_typ_cac->lst())) {
                $result = $src_typ_cac->get($id);
            } else {
                log_err('Source type with is ' . $id . ' not found in ' . $lib->dsp_array($src_typ_cac->lst()));
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
    global $src_typ_cac;
    $id = $src_typ_cac->id($code_id);
    return $src_typ_cac->get_by_id($id);
}

function get_source_type_id(string $code_id): int
{
    global $src_typ_cac;
    return $src_typ_cac->id($code_id);
}

function get_source_type_by_id(string $id): source_type
{
    global $src_typ_cac;
    return $src_typ_cac->get_by_id($id);
}
