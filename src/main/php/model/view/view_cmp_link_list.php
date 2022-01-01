<?php

/*

    view_cmp_link_list.php - a list of view component links
    ----------------------

    This links list object is used to update or delete a list of links with one SQL statement

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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2021 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

class view_cmp_link_list extends link_list
{

    /**
     * create an SQL statement to retrieve a list of value phrase links from the database
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param view|null $dsp if set to get all links for this view
     * @param view_cmp|null $cmp if set to get all links for this view component
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_db $db_con, ?view $dsp = null, ?view_cmp $cmp = null): sql_par
    {
        $qp = new sql_par();
        $qp->name = self::class . '_by_';
        $sql_by = '';

        $db_con->set_type(DB_TYPE_VIEW_COMPONENT_LINK);
        if ($dsp != null) {
            if ($dsp->id > 0) {
                $sql_by = view::FLD_ID;
            }
        } elseif ($cmp != null) {
            if ($cmp->id > 0) {
                $sql_by = view_cmp::FLD_ID;
            }
        }
        if ($sql_by == '') {
            log_err('Either the view id or the component id and the user (' . $this->usr->id .
                ') must be set to load a ' . self::class, self::class . '->load_sql');
            $qp->name = '';
        } else {
            $qp->name .= $sql_by;
            $db_con->set_name($qp->name);
            $db_con->set_usr($this->usr->id);
            $db_con->set_fields(view_cmp_link::FLD_NAMES);
            $db_con->set_usr_num_fields(view_cmp_link::FLD_NAMES_NUM_USR);
            if ($dsp != null) {
                $db_con->set_join_fields(array(view::FLD_ID), DB_TYPE_VIEW);
            } else {
                $db_con->set_join_fields(array(view_cmp::FLD_ID), DB_TYPE_VIEW_COMPONENT);
            }
            if ($dsp != null) {
                if ($dsp->id > 0) {
                    $db_con->add_par(sql_db::PAR_INT, $dsp->id);
                    $qp->sql = $db_con->select_by_link_ids(array(view::FLD_ID));
                }
            } elseif ($cmp != null) {
                if ($cmp->id > 0) {
                    $db_con->add_par(sql_db::PAR_INT, $cmp->id);
                    $qp->sql = $db_con->select_by_link_ids(array(view_cmp::FLD_ID));
                }
            }
            $qp->par = $db_con->get_par();
        }

        return $qp;
    }

    /**
     * load all view component links of given view or view component
     *
     * @param view|null $dsp if set to get all links for this view
     * @param view_cmp|null $cmp if set to get all links for this view component
     * @return bool true if value or phrases are found
     */
    private function load(?view $dsp = null, ?view_cmp $cmp = null): bool
    {
        global $db_con;
        $result = false;

        // check the all minimal input parameters
        if ($this->usr->id <= 0) {
            log_err('The user must be set to load ' . self::class, self::class . '->load');
        } else {
            $qp = $this->load_sql($db_con, $dsp, $cmp);
            if ($qp->name != '') {
                $db_rows = $db_con->get($qp);
                if ($db_rows != null) {
                    foreach ($db_rows as $db_row) {
                        $dsp_cmp_lnk = new view_cmp_link($this->usr);
                        $dsp_cmp_lnk->row_mapper($db_row);
                        $this->lst[] = $dsp_cmp_lnk;
                        $result = true;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * interface function to load all phrases linked to a given value
     *
     * @param view $dsp if set to get all links for this view
     * @return bool true if phrases are found
     */
    function load_by_view(view $dsp): bool
    {
        return $this->load($dsp);
    }

    /**
     * interface function to load all values linked to a given phrase
     *
     * @param view_cmp $cmp if set to get all links for this view component
     * @return bool true if phrases are found
     */
    function load_by_component(view_cmp $cmp): bool
    {
        return $this->load(null, $cmp);
    }

    /**
     * delete all loaded view component links e.g. to delete all the links linked to a view
     * @return user_message
     */
    function del(): user_message
    {
        $result = new user_message();

        if ($this->lst != null) {
            foreach ($this->lst as $dsp_cmp_lnk) {
                $result->add($dsp_cmp_lnk->del());
            }
        }
        return new user_message();
    }

    /*
     * extract function
     */

    /**
     * @return array with all view ids
     */
    function view_ids(): array
    {
        $result = array();
        foreach ($this->lst as $lnk) {
            if ($lnk->dsp->id <> 0) {
                if (in_array($lnk->dsp->id, $result)) {
                    $result[] = $lnk->dsp->id;
                }
            }
        }
        return $result;
    }

    /**
     * @return array with all component ids
     */
    function cmp_ids(): array
    {
        $result = array();
        foreach ($this->lst as $lnk) {
            if ($lnk->cmp->id <> 0) {
                if (in_array($lnk->cmp->id, $result)) {
                    $result[] = $lnk->cmp->id;
                }
            }
        }
        return $result;
    }

}