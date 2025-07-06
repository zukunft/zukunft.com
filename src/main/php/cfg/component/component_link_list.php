<?php

/*

    model/view/component_link_list.php - a list of links between a view and a component
    ----------------------------------

    This links list object is used to update or delete a list of links with one SQL statement

    The main sections of this object are
    - construct and map: including the mapping of the db row to this component link list object
    - load:              database access object (DAO) functions
    - load sql:          create the sql statements for loading from the db
    - api:               create an api array for the frontend and set the vars based on a frontend api message
    - im- and export:    create an export object and set the vars from an import object
    - modify:            change potentially all variables of this list object
    - information:       functions to make code easier to read
    - internal:          private functions to make code easier to read


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

    Copyright (c) 1995-2025 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace cfg\component;

include_once MODEL_SANDBOX_PATH . 'sandbox_link_list.php';
include_once DB_PATH . 'sql_creator.php';
include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_par.php';
include_once MODEL_COMPONENT_PATH . 'component_link.php';
include_once MODEL_SANDBOX_PATH . 'sandbox_link.php';
include_once MODEL_USER_PATH . 'user_message.php';
include_once MODEL_VIEW_PATH . 'view.php';

use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\db\sql_par;
use cfg\sandbox\sandbox_link;
use cfg\sandbox\sandbox_link_list;
use cfg\user\user_message;
use cfg\view\view;

class component_link_list extends sandbox_link_list
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
     * interface function to load all component links of the given view
     *
     * @param view $msk if set to get all links for this view
     * @param sql_db|null $db_con_given the database connection as a parameter for the initial load of the system views
     * @return bool true if phrases are found
     */
    function load_by_view(view $msk, ?sql_db $db_con_given = null): bool
    {
        global $db_con;

        $db_con_used = $db_con_given;
        if ($db_con_used == null) {
            $db_con_used = $db_con;
        }

        $qp = $this->load_sql_by_view($db_con_used->sql_creator(), $msk);
        return $this->load_sys($qp, false, $db_con_given);
    }

    /**
     * interface function to load all component links and the components of the given view
     * TODO (speed) combine the sql statements
     *
     * @param view $msk if set to get all links for this view
     * @param sql_db|null $db_con_given the database connection as a parameter for the initial load of the system views
     * @return bool true if phrases are found
     */
    function load_by_view_with_components(view $msk, ?sql_db $db_con_given = null): bool
    {
        if ($this->load_by_view($msk, $db_con_given)) {
            return $this->load_components($db_con_given);
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
     * @param sql_db|null $db_con_given the database connection as a parameter for the initial load of the system views
     * @return bool true if the loading of the component has been successful
     */
    function load_components(?sql_db $db_con_given = null): bool
    {
        $ids = $this->cmp_ids();
        $cmp_lst = new component_list($this->user());
        $result = $cmp_lst->load_by_ids($ids, $db_con_given);
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
     * load sql
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

        $sc->set_class(component_link::class);
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


    /*
     * im- and export
     */

    /**
     * create an array with the export json fields
     * @param bool $do_load true if any missing data should be loaded while creating the array
     * @return array with the json fields
     */
    function export_json(bool $do_load = true): array
    {
        $vars = [];
        foreach ($this->lst() as $lnk) {
            $vars[] = $lnk->export_json($do_load);
        }
        return $vars;
    }


    /*
     * modify
     */

    /**
     * add a view component link to the list without saving it to the database
     * @return true if the link has been added
     */
    function add_by_name(component_link $lnk_to_add): bool
    {
        $added = false;
        if ($this->can_add($lnk_to_add)) {
            $this->add_link_by_key($lnk_to_add);
            $added = true;
        }
        return $added;
    }

    /**
     * delete all loaded view component links e.g. to delete all the links assigned to a view
     * @return user_message
     */
    function del(): user_message
    {
        $usr_msg = new user_message();

        if (!$this->is_empty()) {
            foreach ($this->lst() as $dsp_cmp_lnk) {
                $usr_msg->add($dsp_cmp_lnk->del());
            }
        }
        return new user_message();
    }


    /*
     * information
     */

    /**
     * @return array with all view ids
     */
    function view_ids(): array
    {
        $result = array();
        foreach ($this->lst() as $lnk) {
            $id = $lnk->view()->id();
            if ($id <> 0) {
                if (!in_array($id, $result)) {
                    $result[] = $id;
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
            $id = $lnk->component()->id();
            if ($id <> 0) {
                if (!in_array($id, $result)) {
                    $result[] = $id;
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
            if ($lnk->component() != null) {
                $name = $lnk->component()->name();
                if ($name <> '') {
                    if (!in_array($name, $result)) {
                        $result[] = $name;
                    }
                }
            }
        }
        return $result;
    }


    /*
    * save
    */

    /**
     * simple but slow function to add of update all list items in the database
     * TODO faster mass db update
     *
     * @return user_message the message shown to the user why the action has failed or an empty string if everything is fine
     */
    function save(): user_message
    {
        $usr_msg = new user_message();
        foreach ($this->lst() as $sbx) {
            // save upfront and missing components
            $cmp = $sbx->component();
            if (!$cmp->is_valid()) {
                if ($cmp->db_ready()) {
                    $usr_msg->add($cmp->save());
                }
            }
            // save the link of the view to the component
            $usr_msg->add($sbx->save());
        }
        return $usr_msg;
    }


    /*
      * internal
      */

    /**
     * test if the link at the same position already exists and if yes return false to prevent duplicates
     * overwrites the parent because the same component can be used in a view at different positions
     * but not at the same position
     * @param component_link|sandbox_link $lnk_to_add the link that should be added to the list
     * @return bool true if the link can be added
     */
    protected function can_add(component_link|sandbox_link $lnk_to_add): bool
    {
        $can_add = true;

        if (!$this->is_empty()) {
            foreach ($this->lst() as $lnk) {
                if ($can_add) {
                    if ($lnk->from_id() == $lnk_to_add->from_id()
                        and $lnk->to_id() == $lnk_to_add->to_id()
                        and $lnk->pos() == $lnk_to_add->pos()) {
                        $can_add = false;
                    }
                    if ($lnk->id() == $lnk_to_add->id()
                        and $lnk->id() != 0 and $lnk_to_add->id() != 0
                        and $lnk->id() !== null and $lnk_to_add->id() !== null) {
                        $can_add = false;
                    }
                }
            }
        }
        return $can_add;
    }

}