<?php

/*

    model/phrase/group_triple_link.php - only for fast selection of the phrase group assigned to one triple
    -----------------------------------------

    replication of the words linked to a phrase group saved in the triple_ids field

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

namespace cfg\group;

include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_par.php';
include_once DB_PATH . 'sql_creator.php';
include_once DB_PATH . 'sql_par_type.php';
include_once MODEL_HELPER_PATH . 'db_object_seq_id.php';
include_once MODEL_WORD_PATH . 'triple_db.php';

use cfg\db\sql_db;
use cfg\db\sql_par;
use cfg\db\sql_creator;
use cfg\db\sql_par_type;
use cfg\helper\db_object_seq_id;
use cfg\word\triple_db;

class group_link extends db_object_seq_id
{
    // object specific database and JSON object field names
    const FLD_ID = 'group_triple_link_id';

    // all database field names excluding the id
    const FLD_NAMES = array(
        group::FLD_ID,
        triple_db::FLD_ID
    );

    // database fields
    public int $grp_id;    // the phrase group id and not the object to reduce the memory usage
    public int $trp_id;    // the triple id and not the object to reduce the memory usage

    function __construct()
    {
        parent::__construct();
        $this->grp_id = 0;
        $this->trp_id = 0;
    }

    /**
     * map the database fields to one db row to this phrase group triple link object
     *
     * @param array|null $db_row with the data directly from the database
     * @param string $id_fld the name of the id field as set in the child class
     * @return bool true if one phrase group triple link is found
     */
    function row_mapper(?array $db_row, string $id_fld = ''): bool
    {
        $result = parent::row_mapper($db_row, self::FLD_ID);
        if ($result) {
            $this->grp_id = $db_row[group::FLD_ID];
            $this->trp_id = $db_row[triple_db::FLD_ID];
        }
        return $result;
    }


    /*
     * load
     */

    /**
     * create the common part of an SQL statement to get the phrase group triple link from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name of the query use to prepare and call the query
     * @param string $class the name of this class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name, string $class = self::class): sql_par
    {
        $qp = parent::load_sql($sc, $query_name, $class);

        $sc->set_class($class);
        $sc->set_name($qp->name);
        $sc->set_fields(self::FLD_NAMES);

        return $qp;
    }

    /**
     * create an SQL statement to retrieve the phrase group triple links related to a group id
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param group $grp the phrase group which should be used for the selection
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_by_group_id_sql(sql_db $db_con, group $grp): sql_par
    {
        $db_con->set_class(self::class);
        $qp = new sql_par(self::class);

        if ($grp->is_id_set()) {
            $qp->name .= 'grp_id';
            $db_con->add_par(sql_par_type::INT, $grp->id());
        } else {
            log_err('The phrase group id must be set ' .
                'to load a ' . self::class, self::class . '->load_by_group_id_sql');

        }
        $db_con->set_name($qp->name);
        $db_con->set_fields(self::FLD_NAMES);
        $qp->sql = $db_con->select_by_field(group::FLD_ID);
        $qp->par = $db_con->get_par();

        return $qp;
    }

}
