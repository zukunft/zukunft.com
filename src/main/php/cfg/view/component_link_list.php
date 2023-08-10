<?php

/*

    model/view/component_link_list.php - a list of links between a view and a component
    ----------------------------------

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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace cfg;

include_once DB_PATH . 'sql_par_type.php';

use cfg\db\sql_creator;
use cfg\db\sql_par_type;

class component_link_list extends sandbox_list
{

    /*
     * construct and map
     */

    /**
     * map only the valid view component links
     *
     * @param array|null $db_rows with the data directly from the database
     * @param bool $load_all force to include also the excluded phrases e.g. for admins
     * @return bool true if the view component link is loaded and valid
     */
    protected function rows_mapper(?array $db_rows, bool $load_all = false): bool
    {
        return parent::rows_mapper_obj(new component_link($this->user()), $db_rows, $load_all);
    }


    /*
     * load
     */

    /**
     * set the common part of the SQL query component links
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name of the selection fields to make the query name unique
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name): sql_par
    {
        $qp = new sql_par(self::class);
        $qp->name .= $query_name;

        $sc->set_type(sql_db::TBL_COMPONENT_LINK);
        $sc->set_name($qp->name); // assign incomplete name to force the usage of the user as a parameter
        $sc->set_usr($this->user()->id());
        $sc->set_fields(component_link::FLD_NAMES);
        $sc->set_usr_num_fields(component_link::FLD_NAMES_NUM_USR);
        return $qp;
    }

    /**
     * set the SQL query parameters to load all components linked to a view
     * @param sql_creator $sc with the target db_type set
     * @param int $msk_id the id of the view to which the components should be loaded
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_view_id(sql_creator $sc, int $msk_id): sql_par
    {
        $qp = $this->load_sql($sc, 'msk_id');
        if ($msk_id > 0) {
            $sc->add_where(view::FLD_ID, $msk_id);
            $sc->set_join_fields(component::FLD_NAMES, sql_db::TBL_COMPONENT);
            // TODO add the user component fields
            $qp->sql = $sc->sql();
        } else {
            $qp->name = '';
        }
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * set the SQL query parameters to load all views linked to a component
     * @param sql_creator $sc with the target db_type set
     * @param int $cmp_id the id of the component to which the views should be loaded
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_component_id(sql_creator $sc, int $cmp_id): sql_par
    {
        $qp = $this->load_sql($sc, 'cmp_id');
        if ($cmp_id > 0) {
            $sc->add_where(view::FLD_ID, $cmp_id);
            $sc->set_join_fields(view::FLD_NAMES, sql_db::TBL_VIEW);
            // TODO add the user view fields
            $qp->sql = $sc->sql();
        } else {
            $qp->name = '';
        }
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * interface function to load all components linked to a given view
     *
     * @param view $dsp if set to get all links for this view
     * @return bool true if phrases are found
     */
    function load_by_view(view $dsp): bool
    {
        global $db_con;
        $qp = $this->load_sql_by_view_id($db_con->sql_creator(), $dsp->id());
        return $this->load($qp);
    }

    /**
     * interface function to load all views linked to a given component
     *
     * @param component $cmp if set to get all links for this view
     * @return bool true if phrases are found
     */
    function load_by_component(component $cmp): bool
    {
        global $db_con;
        $qp = $this->load_sql_by_component_id($db_con->sql_creator(), $cmp->id());
        return $this->load($qp);
    }

    /**
     * create an SQL statement to retrieve a list of view component links from the database
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param view|null $dsp if set to get all links for this view
     * @param component|null $cmp if set to get all links for this view component
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by(sql_db $db_con, ?view $dsp = null, ?component $cmp = null): sql_par
    {
        $db_con->set_type(sql_db::TBL_COMPONENT_LINK);
        $qp = new sql_par(self::class);
        $sql_by = '';

        if ($dsp != null) {
            if ($dsp->id() > 0) {
                $sql_by = view::FLD_ID;
            }
        } elseif ($cmp != null) {
            if ($cmp->id() > 0) {
                $sql_by = component::FLD_ID;
            }
        }
        if ($sql_by == '') {
            log_err('Either the view id or the component id and the user (' . $this->user()->id() .
                ') must be set to load a ' . self::class, self::class . '->load_sql');
            $qp->name = '';
        } else {
            $qp->name .= $sql_by;
            $db_con->set_name($qp->name);
            $db_con->set_usr($this->user()->id());
            $db_con->set_fields(component_link::FLD_NAMES);
            $db_con->set_usr_num_fields(component_link::FLD_NAMES_NUM_USR);
            if ($dsp != null) {
                $db_con->set_join_fields(array(view::FLD_ID), sql_db::TBL_VIEW);
            } else {
                $db_con->set_join_fields(array(component::FLD_ID), sql_db::TBL_COMPONENT);
            }
            if ($dsp != null) {
                if ($dsp->id() > 0) {
                    $db_con->add_par(sql_par_type::INT, $dsp->id());
                    $qp->sql = $db_con->select_by_field_list(array(view::FLD_ID));
                }
            } elseif ($cmp != null) {
                if ($cmp->id() > 0) {
                    $db_con->add_par(sql_par_type::INT, $cmp->id());
                    $qp->sql = $db_con->select_by_field_list(array(component::FLD_ID));
                }
            }
            $qp->par = $db_con->get_par();
        }

        return $qp;
    }

    /**
     * interface function to load all phrases linked to a given value
     *
     * @param view $dsp if set to get all links for this view
     * @return bool true if phrases are found
     */
    function load_by_view_old(view $dsp): bool
    {
        global $db_con;
        $qp = $this->load_sql_by($db_con, $dsp, null);
        return $this->load($qp);
    }

    /**
     * interface function to load all values linked to a given phrase
     *
     * @param component $cmp if set to get all links for this view component
     * @return bool true if phrases are found
     */
    function load_by_component_old(component $cmp): bool
    {
        global $db_con;
        $qp = $this->load_sql_by($db_con, null, $cmp);
        return $this->load($qp);
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
            if ($lnk->fob != null) {
                if ($lnk->fob->id() <> 0) {
                    if (!in_array($lnk->fob->id(), $result)) {
                        $result[] = $lnk->fob->id();
                    }
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
            if ($lnk->tob->id() <> 0) {
                if (in_array($lnk->tob->id(), $result)) {
                    $result[] = $lnk->tob->id();
                }
            }
        }
        return $result;
    }

}