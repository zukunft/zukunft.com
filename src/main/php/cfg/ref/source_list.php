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

namespace cfg;

include_once API_REF_PATH . 'source_list.php';
include_once MODEL_SANDBOX_PATH . 'sandbox_list_named.php';

use api\ref\source_list as source_list_api;
use cfg\db\sql;
use cfg\db\sql_par;
use cfg\db\sql_par_type;

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
     * cast
     */

    /**
     * @return source_list_api the word list object with the display interface functions
     */
    function api_obj(): source_list_api
    {
        $api_obj = new source_list_api(array());
        foreach ($this->lst() as $src) {
            $api_obj->add($src->api_obj());
        }
        return $api_obj;
    }

    /**
     * @returns string the api json message for the object as a string
     */
    function api_json(): string
    {
        return $this->api_obj()->get_json();
    }


    /*
     * load
     */

    /**
     * create the common part of an SQL statement to retrieve a list of sources from the database
     * uses the source view which includes only the main fields
     *
     * @param sql $sc with the target db_type set
     * @param string $query_name the name of the query use to prepare and call the query
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    private function load_sql(sql $sc, string $query_name): sql_par
    {
        $qp = new sql_par(self::class);
        $qp->name .= $query_name;

        $sc->set_class(source::class);
        $sc->set_name($qp->name);

        $sc->set_fields(source::FLD_NAMES);
        $sc->set_usr_fields(source::FLD_NAMES_USR);
        $sc->set_usr_num_fields(source::FLD_NAMES_NUM_USR);

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a list of sources from the database
     *
     * @param sql $sc with the target db_type set
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_ids(sql $sc, array $ids): sql_par
    {
        $qp = $this->load_sql($sc, 'ids');
        $sc->add_where(source::FLD_ID, $ids);
        $sc->set_order(source::FLD_ID, sql::ORDER_ASC);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a list of sources from the database
     * uses the erm view which includes only the main fields
     *
     * @param sql $sc with the target db_type set
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_like(sql $sc, string $pattern = ''): sql_par
    {
        $qp = $this->load_sql($sc, 'name_like');
        $sc->add_where(source::FLD_NAME, $pattern, sql_par_type::LIKE_R);
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
            if ($src->id() != 0) {
                $this->add($src);
                $result = true;
            }
        }

        return $result;
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
     * modification
     */

    /**
     * add one source to the source list, but only if it is not yet part of the source list
     * @param source|null $src_to_add the source backend object that should be added
     * @returns bool true the source has been added
     */
    function add(?source $src_to_add): bool
    {
        return parent::add_named_obj($src_to_add);
    }


}