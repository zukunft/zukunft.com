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

use cfg\db\sql;
use cfg\db\sql_db;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\db\sql_table_type;
use cfg\db\sql_par;
use cfg\group\group;
use cfg\result\result;
use cfg\value\value;

class db_object
{

    // dummy const to be overwritten by the child objects
    // description of the table for the sql table creation
    const TBL_COMMENT = '';
    // list of the table fields for the standard read query
    const FLD_NAMES = array();

    // field lists for the table creation overwritten by the child object or grand child for extra fields
    const FLD_LST_ALL = array();
    const FLD_LST_EXTRA= array();
    // list of fields that MUST be set by one user
    const FLD_LST_MUST_BE_IN_STD = array();
    // list of must fields that CAN be changed by the user
    const FLD_LST_MUST_BUT_USER_CAN_CHANGE = array();
    // fields that CAN be changed by the user with the parameters for the table creation
    const FLD_LST_USER_CAN_CHANGE = array();
    // fields that CANNOT be changed by the user with the parameters for the table creation
    const FLD_LST_NON_CHANGEABLE = array();


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
     * @param sql $sc with the target db_type set
     * @param bool $usr_table true if the table should save the user specific changes
     * @param array $fields array with all fields and all parameter for the table creation in a two-dimensional array
     * @param string $tbl_comment if given the comment that should be added to the sql create table statement
     * @param bool $is_sandbox true if the standard sandbox fields should be included
     * @return string the sql statement to create the table
     */
    function sql_table_create(
        sql    $sc,
        bool   $usr_table = false,
        array  $fields = [],
        string $tbl_comment = '',
        bool   $is_sandbox = true
    ): string
    {
        if ($sc->get_table() == '') {
            $sc->set_class($this::class, $usr_table);
        }
        if ($fields == []) {
            $fields = $this->sql_all_field_par($usr_table, $is_sandbox);
        }
        if ($tbl_comment == '') {
            $tbl_comment = $this::TBL_COMMENT;
        }
        return $sc->table_create($fields, '', $tbl_comment, $this::class, $usr_table);
    }

    /**
     * the name of the sql table for this (or a child) object
     *
     * @param sql $sc with the target db_type set
     * @param bool $usr_table true if the table should save the user specific changes
     * @param bool $is_sandbox true if the standard sandbox fields should be included
     * @return string the sql statement to create the table
     */
    function sql_truncate_create(sql $sc, bool $usr_table = false, bool $is_sandbox = true): string
    {
        if ($sc->get_table() == '') {
            $sc->set_class($this::class, $usr_table);
        }
        return 'TRUNCATE ' . $sc->get_table() . ' CASCADE; ';
    }

    /**
     * the sql statement to create the indices for this (or a child) object
     *
     * @param sql $sc with the target db_type set
     * @param bool $usr_table true if the table should save the user specific changes
     * @param array $fields array with all fields and all parameter for the table creation in a two-dimensional array
     * @param bool $is_sandbox true if the standard sandbox fields should be included
     * @return string the sql statement to create the table
     */
    function sql_index_create(sql $sc, bool $usr_table = false, array $fields = [], bool $is_sandbox = true): string
    {
        if ($sc->get_table() == '') {
            $sc->set_class($this::class, $usr_table);
        }
        if ($fields == []) {
            $fields = $this->sql_all_field_par($usr_table, $is_sandbox);
        }
        return $sc->index_create($fields);
    }

    /**
     * the sql statement to create the foreign keys for this (or a child) object
     *
     * @param sql $sc with the target db_type set
     * @param bool $usr_table true if the table should save the user specific changes
     * @param array $fields array with all fields and all parameter for the table creation in a two-dimensional array
     * @param bool $is_sandbox true if the standard sandbox fields should be included
     * @return string the sql statement to create the table
     */
    function sql_foreign_key_create(sql $sc, bool $usr_table = false, array $fields = [], bool $is_sandbox = true): string
    {
        if ($sc->get_table() == '') {
            $sc->set_class($this::class, $usr_table);
        }
        if ($fields == []) {
            $fields = $this->sql_all_field_par($usr_table, $is_sandbox);
        }
        return $sc->foreign_key_create($fields);
    }

    /**
     * @param bool $usr_table create a second table for the user overwrites
     * @param bool $is_sandbox true if the standard sandbox fields should be included
     * @return array[] with the parameters of the table fields
     */
    protected function sql_all_field_par(bool $usr_table = false, bool $is_sandbox = true): array
    {
        $fields = [];
        if (!$usr_table) {
            if ($is_sandbox) {
                $fields = sandbox::FLD_ALL_OWNER;
                $fields = array_merge($fields, $this::FLD_LST_MUST_BE_IN_STD);
            } else {
                $fields = $this::FLD_LST_ALL;
                $fields = array_merge($fields, $this::FLD_LST_EXTRA);
            }
        } else {
            $fields = sandbox::FLD_ALL_CHANGER;
            $fields = array_merge($fields, $this::FLD_LST_MUST_BUT_USER_CAN_CHANGE);
        }
        $fields = array_merge($fields, $this::FLD_LST_USER_CAN_CHANGE);
        if (!$usr_table) {
            $fields = array_merge($fields, $this::FLD_LST_NON_CHANGEABLE);
        }
        if ($is_sandbox) {
            $fields = array_merge($fields, sandbox::FLD_LST_ALL);
        }
        return $fields;
    }


    /*
     * load
     */

    /**
     * parent function to create the common part of an SQL statement for group, value and result tables
     * child object sets the table and fields in the db sql builder
     *
     * @param sql $sc with the target db_type set
     * @param string $query_name the name of the selection fields to make the query name unique
     * @param string $class the name of the child class from where the call has been triggered
     * @param string $ext the query name extension e.g. to differentiate queries based on 1,2, or more phrases
     * @param sql_table_type $tbl_typ the table name extension e.g. to switch between standard and prime values
     * @param bool $usr_tbl true if a db row should be added to the user table
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    public function load_sql_multi(
        sql            $sc,
        string         $query_name,
        string         $class,
        string         $ext = '',
        sql_table_type $tbl_typ = sql_table_type::MOST,
        bool           $usr_tbl = false
    ): sql_par
    {
        $lib = new library();
        $tbl_name = $lib->class_to_name($class);
        $qp = new sql_par($tbl_name, false, false, $ext, $tbl_typ);
        $qp->name .= $query_name;
        $sc->set_class($class, $usr_tbl, $tbl_typ->extension());
        $sc->set_name($qp->name);
        $sc->set_fields($this::FLD_NAMES);

        return $qp;
    }

    /**
     * parent function to create the common part of an SQL statement
     * child object sets the table and fields in the db sql builder
     *
     * @param sql $sc with the target db_type set
     * @param string $query_name the name of the selection fields to make the query name unique
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql $sc, string $query_name): sql_par
    {
        return $this->load_sql_multi($sc, $query_name, $this::class);
    }

    /**
     * create an SQL statement to retrieve a user sandbox object by id from the database
     *
     * @param sql $sc with the target db_type set
     * @param int|string $id the id of the user sandbox object
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_id_str(sql $sc, int|string $id, string $class = self::class): sql_par
    {
        $ext = '';
        if ($class == group::class
            or $class == value::class
            or $class == result::class) {
            $grp = new group(new user());
            $grp->set_id($id);
            $typ = $grp->table_type();
            $ext = $grp->table_extension();
            $qp = $this->load_sql_multi($sc, sql_db::FLD_ID, $class, $ext, $typ);
        } else {
            $qp = $this->load_sql($sc, sql_db::FLD_ID);
        }

        $sc->add_where($this->id_field(), $id);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * load one database row e.g. group (where the id might be a string) from the database
     * @param sql_par $qp the query parameters created by the calling function
     * @return bool false if no database row has been found
     *                    which means that no user has changed the standard group settings
     */
    protected function load_without_id_return(sql_par $qp): bool
    {
        global $db_con;

        $db_row = $db_con->get1($qp);
        return $this->row_mapper($db_row);
    }


    /*
     * information
     */

    /**
     * function that can be overwritten by the child object
     * e.g. if the object name does not match the generated id field name
     * e.g. to group_id for values and results
     * @return string|array the field name(s) of the prime database index of the object
     */
    function id_field(): string|array
    {
        $lib = new library();
        return $lib->class_to_name($this::class) . sql_db::FLD_EXT_ID;
    }

}
