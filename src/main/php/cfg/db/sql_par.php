<?php

/*

  sql_par.php - combine the query name, the sql statement and the parameters in one object
  -----------
  

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
  
  Copyright (c) 1995-2018 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

// TODO Check that calling the update function always expects a boolean as return value
// TODO check that $db_con->get and $db_con->get1 always can handle a null row result
// TODO check that for all update and insert statement the user id is set correctly (use word user config as an example)
// TODO mainly for data from the internet use prepared statements to prevent SQL injections

namespace cfg\db;

use cfg\group\group_id;
use cfg\library;

/**
 * a query object to build and fill prepared queries
 */
class sql_par
{
    public string $sql;     // the SQL statement to create a prepared query
    public string $name;    // the unique name of the SQL statement
    public array $par;      // the list of the parameters used for the execution
    public array $par_name_lst; // the list of the parameters names to reuse already added parameters
    public array $par_type_lst; // the list of the parameters types
    public string $ext;     // the extension used e.g. to decide if the index is int or string
    public sql_type $typ; // to handle table that does not have a bigint prime index

    /**
     * @param string $class the name of the calling class used for the unique query name
     * @param array $sc_par_lst list of sql types e.g. insert or load
     * @param string $ext the query name extension e.g. to separate the queries by the number of parameters
     */
    function __construct(
        string $class,
        array  $sc_par_lst = [],
        string $ext = '')
    {
        // convert sql types to single parameter
        $is_std = false;
        $all = false;
        $tbl_typ = sql_type::MOST;
        foreach ($sc_par_lst as $sql_type) {
            if ($sql_type == sql_type::NORM) {
                $is_std = true;
            }
            if ($sql_type == sql_type::COMPLETE) {
                $all = true;
            }
        }
        if ($ext == '') {
            $ext = '';
            foreach ($sc_par_lst as $sql_type) {
                $ext .= $sql_type->extension();
                if ($sql_type == sql_type::PRIME or $sql_type == sql_type::BIG) {
                    $tbl_typ = $sql_type;
                }
            }
        }

        $lib = new library();
        $this->sql = '';
        $class_name = $lib->class_to_name($class);
        $name = $class_name . $ext;
        if ($is_std) {
            $this->name = $name . '_by_';
        } elseif ($all) {
            $this->name = $name . '_';
        } else {
            $sc = new sql();
            if (!$sc->is_cur_not_l($sc_par_lst)) {
                $this->name = $name . '_by_';
            } else {
                $this->name = $name;
            }
        }
        $this->par = array();
        $this->ext = $ext;
        $this->typ = $tbl_typ;
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

