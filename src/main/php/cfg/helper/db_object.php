<?php

/*

    model/helper/db_object.php - a base object for all model database objects which just contains the unique id
    --------------------------


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

use cfg\db\sql_creator;

class db_object
{

    // dummy const to be overwritten by the child objects
    const TBL_COMMENT = '';
    const FLD_NAMES = array();

    /*
     * construct and map
     */

    /**
     * dummy map function to be overwritten by the child object
     *
     * @param array|null $db_row with the data directly from the database
     * @param string $id_fld the name of the id field as set in the child class
     * @return bool true if the user sandbox object is loaded and valid
     */
    function row_mapper(?array $db_row, string $id_fld = ''): bool
    {
        return false;
    }


    /*
     * sql create
     */

    /**
     * the sql statement to create the table for this (or a child) object
     *
     * @param sql_creator $sc ith the target db_type set
     * @return string the sql statement to create the table
     */
    function sql_table(sql_creator $sc): string
    {
        $sc->set_class($this::class);
        return $sc->table_create($this::TBL_COMMENT);
    }


    /*
     * load
     */

    /**
     * parent function to create the common part of an SQL statement for group, value and result tables
     * child object sets the table and fields in the db sql builder
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name of the selection fields to make the query name unique
     * @param string $class the name of the child class from where the call has been triggered
     * @param string $ext the table name extension e.g. to switch between standard and prime values
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    public function load_sql_multi(
        sql_creator $sc,
        string $query_name,
        string $class,
        string $ext = ''): sql_par
    {
        $lib = new library();
        $tbl_name = $lib->class_to_name($class);
        $qp = new sql_par($tbl_name . $ext);
        $qp->name .= $query_name;
        $sc->set_class($class, false, $ext);
        $sc->set_name($qp->name);
        $sc->set_fields($this::FLD_NAMES);

        return $qp;
    }

    /**
     * parent function to create the common part of an SQL statement
     * child object sets the table and fields in the db sql builder
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name of the selection fields to make the query name unique
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name): sql_par
    {
        return $this->load_sql_multi($sc, $query_name, $this::class);
    }

    /**
     * create an SQL statement to retrieve a user sandbox object by id from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param int|string $id the id of the user sandbox object
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_id_str(sql_creator $sc, int|string $id): sql_par
    {
        $qp = $this->load_sql($sc, sql_db::FLD_ID);
        $sc->add_where($this->id_field(), $id);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * load one database row e.g. group (where the id might be a string), word, triple, value, formula, result, view, component or log entry from the database
     * @param sql_par $qp the query parameters created by the calling function
     */
    protected function load_without_id_return(sql_par $qp): void
    {
        global $db_con;

        $db_row = $db_con->get1($qp);
        $this->row_mapper($db_row);
    }


    /*
     * information
     */

    /**
     * function that can be overwritten by the child object
     * e.g. if the object name does not match the generated id field name
     * e.g. to group_id for values and results
     * @return string the field name of the prime database index of the object
     */
    function id_field(): string
    {
        $lib = new library();
        return $lib->class_to_name($this::class) . sql_db::FLD_EXT_ID;
    }

}
