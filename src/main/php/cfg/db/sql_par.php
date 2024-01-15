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
    public string $sql;   // the SQL statement to create a prepared query
    public string $name;  // the unique name of the SQL statement
    public array $par;    // the list of the parameters used for the execution
    public string $ext;   // the extension used e.g. to decide if the index is int or string

    /**
     * @param string $class the name of the calling class used for the unique query name
     * @param bool $is_std true if the standard data for all users should be loaded
     * @param bool $all true if all rows should be loaded
     * @param string $ext the query name extension e.g. to separate the queries by the number of parameters
     * @param sql_table_type $tbl_typ the table extension e.g. to select the table where the data should be saved
     */
    function __construct(
        string $class,
        bool $is_std = false,
        bool $all = false,
        string $ext = '',
        sql_table_type $tbl_typ = sql_table_type::MOST
    )
    {
        $lib = new library();
        if ($ext == '') {
            if ($tbl_typ != sql_table_type::MOST) {
                $ext = $tbl_typ->extension();
            }
        }
        $this->sql = '';
        $class = $lib->class_to_name($class);
        $name = $class . $ext;
        if ($is_std) {
            $this->name = $name . '_std_by_';
        } elseif ($all) {
            $this->name = $name . '_';
        } else {
            $this->name = $name . '_by_';
        }
        $this->par = array();
        $this->ext = $ext;
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
     * combine two sql and the related parameters to one sql statement
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

}

