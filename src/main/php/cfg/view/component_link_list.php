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

include_once MODEL_SANDBOX_PATH . 'sandbox_list.php';
include_once API_VIEW_PATH . 'component_link_list.php';
include_once DB_PATH . 'sql_creator.php';
include_once DB_PATH . 'sql_par_type.php';

use api\view\component_link_list as component_link_list_api;
use cfg\component\component;
use cfg\component\component_list;
use cfg\db\sql_creator;

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
     * cast
     */

    /**
     * @return component_link_list_api the component list object with the display interface functions
     */
    function api_obj(): component_link_list_api
    {
        $api_obj = new component_link_list_api(array());
        foreach ($this->lst() as $lnk) {
            $api_obj->add($lnk->api_obj());
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
     * set the common part of the SQL query component links
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name of the selection fields to make the query name unique
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name): sql_par
    {
        $qp = new sql_par(self::class);
        $qp->name .= $query_name;

        $sc->set_type(component_link::class);
        $sc->set_name($qp->name); // assign incomplete name to force the usage of the user as a parameter
        $sc->set_usr($this->user()->id());
        $sc->set_fields(component_link::FLD_NAMES);
        $sc->set_usr_num_fields(component_link::FLD_NAMES_NUM_USR);
        return $qp;
    }

    /**
     * set the SQL query parameters to load all components linked to a view
     * @param sql_creator $sc with the target db_type set
     * @param view $msk the id of the view to which the components should be loaded
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_view(sql_creator $sc, view $msk): sql_par
    {
        $qp = $this->load_sql($sc, view::FLD_ID);
        if ($msk->id() > 0) {
            $sc->add_where(view::FLD_ID, $msk->id());
            $sc->set_order(component_link::FLD_ORDER_NBR);
            $sc = (new component($this->user()))->set_join($sc);
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
     * @param component $cmp the id of the component to which the views should be loaded
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_component(sql_creator $sc, component $cmp): sql_par
    {
        $qp = $this->load_sql($sc, component::FLD_ID);
        if ($cmp->id() > 0) {
            $sc->add_where(component::FLD_ID, $cmp->id());
            $sc = (new view($this->user()))->set_join($sc);
            $qp->sql = $sc->sql();
        } else {
            $qp->name = '';
        }
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * interface function to load all component links of the given view
     *
     * @param view $dsp if set to get all links for this view
     * @return bool true if phrases are found
     */
    function load_by_view(view $dsp): bool
    {
        global $db_con;
        $qp = $this->load_sql_by_view($db_con->sql_creator(), $dsp);
        return $this->load($qp);
    }

    /**
     * interface function to load all component links and the components of the given view
     * TODO (speed) combine the sql statements
     *
     * @param view $dsp if set to get all links for this view
     * @return bool true if phrases are found
     */
    function load_by_view_with_components(view $dsp): bool
    {
        if ($this->load_by_view($dsp)) {
            return $this->load_components();
        } else {
            return false;
        }
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
        $qp = $this->load_sql_by_component($db_con->sql_creator(), $cmp);
        return $this->load($qp);
    }

    /**
     * load the components of this list
     * @return bool true if the loading of the component has been successful
     */
    function load_components(): bool
    {
        $ids = $this->cmp_ids();
        $cmp_lst = new component_list($this->user());
        $result = $cmp_lst->load_by_ids($ids);
        if ($result) {
            foreach ($this->lst() as $lnk) {
                $cmp = $cmp_lst->get_by_id($lnk->component()->id());
                if ($cmp != null) {
                    $lnk->set_component($cmp);
                }
            }
        }
        return $result;
    }


    /*
     * modify
     */

    /**
     * add a view component link based on parts to the list without saving it to the database
     * @return true if the link has been added
     */
    function add(int $id, view $msk, component $cmp, int $pos): bool
    {
        $new_lnk = new component_link($this->user());
        $new_lnk->set($id, $msk, $cmp, $pos);
        return $this->add_link($new_lnk);
    }

    /**
     * add a view component link to the list without saving it to the database
     * @return true if the link has been added
     */
    function add_link(component_link $lnk_to_add): bool
    {
        $added = false;
        if ($this->can_add($lnk_to_add)) {
            $this->add_obj($lnk_to_add);
            $added = true;
        }
        return $added;
    }

    /**
     * delete all loaded view component links e.g. to delete all the links linked to a view
     * @return user_message
     */
    function del(): user_message
    {
        $result = new user_message();

        if (!$this->is_empty()) {
            foreach ($this->lst() as $dsp_cmp_lnk) {
                $result->add($dsp_cmp_lnk->del());
            }
        }
        return new user_message();
    }


    /*
     * check
     */

    private function can_add(component_link $lnk_to_add): bool
    {
        $can_add = true;

        if (!$this->is_empty()) {
            foreach ($this->lst() as $lnk) {
                if ($lnk->view()->id() == $lnk_to_add->view()->id()
                    and $lnk->component()->id() == $lnk_to_add->component()->id()
                    and $lnk->pos() == $lnk_to_add->pos()) {
                    $can_add = false;
                }
            }
        }
        return $can_add;
    }


    /*
     * extract
     */

    /**
     * @return array with all view ids
     */
    function view_ids(): array
    {
        $result = array();
        foreach ($this->lst() as $lnk) {
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
        foreach ($this->lst() as $lnk) {
            if ($lnk->tob->id() <> 0) {
                if (!in_array($lnk->tob->id(), $result)) {
                    $result[] = $lnk->tob->id();
                }
            }
        }
        return $result;
    }

    /**
     * @return array with all component names linked usually to one view
     */
    function names(int $limit = null): array
    {
        $result = array();
        foreach ($this->lst() as $lnk) {
            if ($lnk->tob != null) {
                if ($lnk->tob->name() <> '') {
                    if (!in_array($lnk->tob->name(), $result)) {
                        $result[] = $lnk->tob->name();
                    }
                }
            }
        }
        return $result;
    }

}