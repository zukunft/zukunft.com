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

namespace cfg\ref;

include_once DB_PATH . 'sql.php';
include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_par.php';
include_once MODEL_HELPER_PATH . 'type_list.php';
include_once MODEL_REF_PATH . 'ref_type.php';
include_once MODEL_SANDBOX_PATH . 'sandbox_named.php';

use cfg\db\sql;
use cfg\db\sql_db;
use cfg\helper\type_list;
use cfg\sandbox\sandbox_named;

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
                $type_comment = strval($db_entry[sql_db::FLD_DESCRIPTION]);
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
     * TODO Prio 3: load from csv
     */
    function load_dummy(): void
    {
        $this->reset();
        // read the corresponding names and description from the internal config csv files
        $this->read_from_config_csv($this);
    }

    /**
     * return the database id of the default ref type
     */
    function default_id(): int
    {
        return parent::id(ref_type::WIKIPEDIA);
    }

}

