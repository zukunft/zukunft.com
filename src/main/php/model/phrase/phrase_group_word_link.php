<?php

/*

    model/phrase/phrase_group_triple.php - only for fast selection of the phrase group assigned to one word
    ------------------------------------

    replication of the words linked to a phrase group saved in the word_ids field

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

namespace model;

include_once MODEL_PHRASE_PATH . 'phrase_group_link.php';

class phrase_group_word_link extends phrase_group_link
{
    // object specific database and JSON object field names
    const FLD_ID = 'phrase_group_triple_id';

    // all database field names excluding the id
    const FLD_NAMES = array(
        phrase_group::FLD_ID,
        word::FLD_ID
    );

    // database fields
    public int $wrd_id;    // the word id and not the object to reduce the memory usage

    function __construct()
    {
        parent::__construct();
        $this->wrd_id = 0;
    }

    function row_mapper_sandbox(array $db_row): bool
    {
        $result = false;
        if ($db_row != null) {
            $this->id = $db_row[self::FLD_ID];
            $this->grp_id = $db_row[phrase_group::FLD_ID];
            $this->wrd_id = $db_row[word::FLD_ID];
            $result = true;
        }
        return $result;
    }


    /*
     * load
     */

    /**
     * create the common part of an SQL statement to get the phrase group triple link from the database
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $query_name the name of the query use to prepare and call the query
     * @param string $class the name of this class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    protected function load_sql(sql_db $db_con, string $query_name, string $class = self::class): sql_par
    {
        $qp = parent::load_sql($db_con, $query_name, $class);

        $db_con->set_type(sql_db::TBL_PHRASE_GROUP_WORD_LINK);
        $db_con->set_name($qp->name);
        $db_con->set_fields(self::FLD_NAMES);

        return $qp;
    }

    /**
     * create an SQL statement to retrieve the phrase group word links related to a group id
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param phrase_group $grp the phrase group which should be used for the selection
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_by_group_id_sql(sql_db $db_con, phrase_group $grp): sql_par
    {
        $db_con->set_type(sql_db::TBL_PHRASE_GROUP_WORD_LINK);
        $qp = new sql_par(self::class);

        if ($grp->id > 0) {
            $qp->name .= 'grp_id';
            $db_con->add_par(sql_db::PAR_INT, $grp->id);
        } else {
            log_err('The phrase group id must be set ' .
                'to load a ' . self::class, self::class . '->load_by_group_id_sql');

        }
        $db_con->set_name($qp->name);
        $db_con->set_fields(self::FLD_NAMES);
        $qp->sql = $db_con->select_by_field(phrase_group::FLD_ID);
        $qp->par = $db_con->get_par();

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a single phrase group word link by the id
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_obj_vars(sql_db $db_con): sql_par
    {
        $db_con->set_type(sql_db::TBL_PHRASE_GROUP_WORD_LINK);
        $qp = new sql_par(self::class);

        if ($this->id > 0) {
            $qp->name .= 'id';
            $db_con->add_par(sql_db::PAR_INT, $this->id);
        } else {
            log_err('The phrase group word id must be set ' .
                'to load a ' . self::class, self::class . '->load_sql');

        }
        $db_con->set_name($qp->name);
        $db_con->set_fields(self::FLD_NAMES);
        $qp->sql = $db_con->select_by_set_id();
        $qp->par = $db_con->get_par();

        return $qp;
    }

    /**
     * load the word to phrase group link from the database
     */
    function load_by_obj_vars(): bool
    {
        global $db_con;
        $qp = $this->load_sql_obj_vars($db_con);
        return $this->row_mapper_sandbox($db_con->get1($qp));
    }

}
