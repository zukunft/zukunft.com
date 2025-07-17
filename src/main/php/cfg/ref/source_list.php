<?php

/*

    model/source/source_list.php - al list of source objects
    ----------------------------


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

namespace cfg\ref;

include_once MODEL_SANDBOX_PATH . 'sandbox_list_named.php';
include_once DB_PATH . 'sql.php';
include_once DB_PATH . 'sql_creator.php';
include_once DB_PATH . 'sql_par.php';
include_once DB_PATH . 'sql_par_type.php';
include_once MODEL_IMPORT_PATH . 'import.php';
include_once MODEL_REF_PATH . 'source.php';
include_once MODEL_REF_PATH . 'source_db.php';
include_once MODEL_USER_PATH . 'user_message.php';
include_once SHARED_CONST_PATH . 'triples.php';
include_once SHARED_CONST_PATH . 'words.php';
include_once SHARED_ENUM_PATH . 'messages.php';

use cfg\db\sql;
use cfg\db\sql_creator;
use cfg\db\sql_par;
use cfg\db\sql_par_type;
use cfg\import\import;
use cfg\sandbox\sandbox_list_named;
use cfg\ref\source_db;
use cfg\user\user_message;
use shared\enum\messages as msg_id;
use shared\const\triples;
use shared\const\words;

class source_list extends sandbox_list_named
{


    /*
     * construct and map
     */

    /**
     * fill the source list based on a database records
     * actually just set the source object for the parent function
     *
     * @param array|null $db_rows is an array of an array with the database values
     * @param bool $load_all force to include also the excluded sources e.g. for admins
     * @return bool true if at least one source has been added
     */
    protected function rows_mapper(?array $db_rows, bool $load_all = false): bool
    {
        return parent::rows_mapper_obj(new source($this->user()), $db_rows, $load_all);
    }


    /*
     * load
     */

    /**
     * create the common part of an SQL statement to retrieve a list of sources from the database
     * uses the source view which includes only the main fields
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name of the query use to prepare and call the query
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    protected function load_sql(sql_creator $sc, string $query_name): sql_par
    {
        $qp = new sql_par(self::class);
        $qp->name .= $query_name;

        $sc->set_class(source::class);
        $sc->set_name($qp->name);

        $sc->set_fields(source_db::FLD_NAMES);
        $sc->set_usr_fields(source_db::FLD_NAMES_USR);
        $sc->set_usr_num_fields(source_db::FLD_NAMES_NUM_USR);

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a list of sources from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param array $ids an array of source ids which should be loaded
     * @param int $limit the number of rows to return
     * @param int $offset jump over these number of pages
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_ids(
        sql_creator $sc,
        array       $ids,
        int         $limit = 0,
        int         $offset = 0
    ): sql_par
    {
        $qp = $this->load_sql($sc, 'ids');
        $sc->add_where(source_db::FLD_ID, $ids);
        $sc->set_order(source_db::FLD_ID, sql::ORDER_ASC);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a list of sources from the database
     * uses the erm view which includes only the main fields
     *
     * @param sql_creator $sc with the target db_type set
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_like(sql_creator $sc, string $pattern = ''): sql_par
    {
        $qp = $this->load_sql($sc, 'name_like');
        $sc->add_where(source_db::FLD_NAME, $pattern, sql_par_type::LIKE_R);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * load the sources that based on the given query parameters
     * @param sql_par $qp the query parameters created by the calling function
     * @param bool $load_all force to include also the excluded sources e.g. for admins
     * @return bool true if at least one source has been loaded
     */
    protected function load(sql_par $qp, bool $load_all = false): bool
    {
        global $db_con;
        $result = false;

        $src_lst = $db_con->get($qp);
        foreach ($src_lst as $db_row) {
            $src = new source($this->user());
            $src->row_mapper_sandbox($db_row);
            $result = $this->add($src);
        }

        return $result;
    }

    /**
     * load a list of sources by the names
     * @param array $names a named object used for selection e.g. a source type
     * @return bool true if at least one source found
     */
    function load_by_names(array $names = []): bool
    {
        global $db_con;
        $qp = $this->load_sql_by_names($db_con->sql_creator(), $names);
        return $this->load($qp);
    }

    /**
     * load the sources selected by the id
     *
     * @param array $ids of source ids that should be loaded
     * @return bool true if at least one source has been loaded
     */
    function load_by_ids(array $ids): bool
    {
        global $db_con;

        $qp = $this->load_sql_by_ids($db_con->sql_creator(), $ids);
        return $this->load($qp);
    }

    /**
     * set the SQL query parameters to load a list of sources by the names
     * @param sql_creator $sc with the target db_type set
     * @param array $names a list of strings with the word names
     * @param string $fld the name of the name field
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_names(
        sql_creator $sc,
        array $names,
        string $fld = source_db::FLD_NAME
    ): sql_par
    {
        return parent::load_sql_by_names($sc, $names, $fld);
    }

    /**
     * load the sources that matches the given pattern
     * @param string $pattern part of the name that should be used to select the sources
     */
    function load_like(string $pattern): bool
    {
        global $db_con;

        $qp = $this->load_sql_like($db_con->sql_creator(), $pattern);
        return $this->load($qp);
    }


    /*
     * save
     */

    /**
     * store all sources from this list in the database using grouped calls of predefined sql functions
     *
     * @param import $imp the import object with the estimate of the total save time
     * @return user_message in case of an issue the problem description what has failed and a suggested solution
     */
    function save(import $imp): user_message
    {
        // TODO create a test that fields not included in the import message are not updated, but e.g. an empty description is updated
        return parent::save_block_wise($imp, words::SOURCES, source::class, new source_list($this->user()));
    }

}