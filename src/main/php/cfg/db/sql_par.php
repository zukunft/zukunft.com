<?php

/*

    cfg/db/sql_par.php - combine the query name, the sql statement and the parameters in one object
    ------------------


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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

// TODO Check that calling the update function always expects a boolean as return value
// TODO check that $db_con->get and $db_con->get1 always can handle a null row result
// TODO check that for all update and insert statement the user id is set correctly (use word user config as an example)
// TODO mainly for data from the internet use prepared statements to prevent SQL injections

namespace cfg\db;

include_once SHARED_PATH . 'library.php';

use shared\library;

/**
 * a query object to build and fill prepared queries
 */
class sql_par
{
    public string $sql;       // the SQL statement to create a prepared query or function
    public string $name;      // the unique name of the SQL statement
    public array $par;        // the list of the parameters used for this execution
    public string $call_sql;  // the sql call for function sql statements
    public string $call_name; // the sql call name
    public string $call;      // sample call for testing only
    public string $obj_name;  // the name of the first object that has request this SQL statement
    public array $par_name_lst; // the list of the parameters names to reuse already added parameters
    public sql_par_field $par_fld; //
    public sql_par_field_list $par_fld_lst; //
    public string $ext;     // the extension used e.g. to decide if the index is int or string
    public sql_type $typ; // to handle table that does not have a bigint prime index

    /**
     * @param string $class the name of the calling class used for the unique query name
     * @param sql_type_list $sc_par_lst list of sql types e.g. insert or load
     * @param string $ext the query name extension that cannot be created based on $sc_par_lst e.g. to separate the queries by the number of parameters
     * @param string $id_ext the query name extension that indicated how many id fields are used e.g. "_p1"
     */
    function __construct(
        string        $class,
        sql_type_list $sc_par_lst = new sql_type_list([]),
        string        $ext = '',
        string        $id_ext = '')
    {

        // prepare the object values

        // get the relevant part from the class name
        $lib = new library();
        $name = $lib->class_to_name($class);

        // add "_sub" to queries that are part of other queries
        if ($sc_par_lst->is_sub_tbl()) {
            $name .= sql_type::SUB->extension();
        }

        // add the table extension for select queries e.g. "_prime"
        $name .= $sc_par_lst->ext_query();

        // add the number of id fields used
        $name .= $id_ext;

        // add the table extension to get the normal value e.g. "_norm"
        $name .= $sc_par_lst->ext_norm();

        // add "_by" to the query name e.g. "word_by_name" to selects a word by the name
        if ($sc_par_lst->is_select()) {
            $name .= $sc_par_lst->ext_by();
        }

        // add the sql type e.g. "_insert" to query name
        $name .= $sc_par_lst->ext_type();

        // add extension that cannot be created by the sql_type_list e.g. "_0012" for the changed fields
        $name .= $ext;

        // add "_user" to queries the handle user specific values
        if ($sc_par_lst->is_usr_tbl()) {
            $name .= sql::NAME_EXT_USER;
        }

        // set the object values
        $this->sql = '';
        $this->name = $name;
        $this->par = array();
        $this->ext = $ext;
        $this->typ = $sc_par_lst->value_table_type();
        $this->call_sql = '';
        $this->call_name = '';
        $this->call = '';
        $this->obj_name = '';
    }

    /**
     * @return bool true if the query has at least one parameter set
     */
    function has_par(): bool
    {
        if (count($this->par) > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * merge two sql and the related parameters to one sql statement
     *
     * @param sql_par $qp
     * @param bool $unique true if the parameters should be unique
     * @return sql_par
     */
    function merge(sql_par $qp, bool $unique = false): sql_par
    {
        if ($this->sql == '') {
            $this->sql = $qp->sql;
        } else {
            $this->sql .= ' UNION ' . $qp->sql;
        }
        if ($unique) {
            $this->par = array_unique(array_merge($this->par, $qp->par));
        } else {
            $this->par = array_merge($this->par, $qp->par);
        }
        return $this;
    }

    /**
     * combine two sql and the related parameters to one sql statement
     * without the union for a sql function
     *
     * @param sql_par $qp
     * @param bool $unique true if the parameters should be unique
     * @return sql_par
     */
    function combine(sql_par $qp, bool $unique = false): sql_par
    {
        $this->sql .= $qp->sql;
        if ($unique) {
            $this->par = array_unique(array_merge($this->par, $qp->par));
        } else {
            $this->par = array_merge($this->par, $qp->par);
        }
        return $this;
    }

}

