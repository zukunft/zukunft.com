<?php

/*

    model/ref/ref_list.php - al list of ref objects
    ----------------------

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

namespace cfg;

global $refs;

use api\ref\ref as ref_api;
use cfg\db\sql_db;

class ref_list extends type_list
{

    private ?user $usr = null; // the user object of the person for whom the ref list is loaded, so to say the viewer

    // search and load fields
    public ?array $ids = array(); // list of the ref ids to load a list from the database

    /*
     * construct and map
     */

    /**
     * define the settings for this ref list object
     * @param user|null $usr the user who requested to see the ref list
     */
    function __construct(?user $usr = null)
    {
        $this->set_user($usr);
    }


    /*
     * set and get
     */

    /**
     * set the user of the ref list
     *
     * @param user|null $usr the person who wants to access the refs
     * @return void
     */
    function set_user(?user $usr): void
    {
        $this->usr = $usr;
    }

    /**
     * @return user|null the person who wants to see the refs
     */
    function user(): ?user
    {
        return $this->usr;
    }

    /*
     * load
     */

    /**
     * force to reload the complete list of refs from the database
     *
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param string $class the class of the related object e.g. phrase_type or formula_type
     * @return array the list of types
     */
    private function load_list(sql_db $db_con, string $class): array
    {
        global $usr;
        $this->reset();
        $qp = $this->load_sql_all($db_con->sql_creator(), $class);
        $db_lst = $db_con->get($qp);
        if ($db_lst != null) {
            foreach ($db_lst as $db_row) {
                $ref = new ref($usr);
                $ref->row_mapper_sandbox($db_row);
                $this->lst()[$db_row[$db_con->get_id_field_name($class)]] = $ref;
            }
        }
        return $this->lst();
    }

    /**
     * force to reload the complete list of refs from the database
     *
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param string $class the database name e.g. the table name without s
     * @return bool true if at least one ref has been loaded
     */
    function load(sql_db $db_con, string $class = sql_db::TBL_VERB): bool
    {
        $result = false;
        $this->set_lst($this->load_list($db_con, $class));
        if ($this->count() > 0) {
            $result = true;
        }
        return $result;

    }

    /**
     * adding the refs used for unit tests to the dummy list
     */
    function load_dummy(): void
    {
        global $usr;
        $type = new ref($usr);
        $type->set_id(1);
        $type->set_name(ref_api::TN_READ);
        $type->code_id = ref_api::TN_READ;
        $this->add($type);
    }


    /*
     * extract
     */

    /**
     * @retur array the list of the ref ids
     */
    function ids(): array
    {
        $result = array();
        if ($this->lst() != null) {
            foreach ($this->lst() as $ref) {
                if ($ref->id()  > 0) {
                    $result[] = $ref->id() ;
                }
            }
        }
        // fallback solution if the load is not yet called e.g. for unit testing
        if (count($result) <= 0) {
            if (count($this->ids) > 0) {
                $result = $this->ids;
            }
        }
        return $result;
    }

}