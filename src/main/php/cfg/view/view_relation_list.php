<?php

/*

    model/view/view_relation_list.php - a list of view to view relations
    ---------------------------------

    This links list object is used to update or delete a list of links with one SQL statement

    The main sections of this object are
    - construct and map: including the mapping of the db row to this view relation list object
    - load:              database access object (DAO) functions
    - load sql:          create the sql statements for loading from the db
    - api:               create an api array for the frontend and set the vars based on a frontend api message
    - im- and export:    create an export object and set the vars from an import object
    - modify:            change potentially all variables of this list object
    - info:              functions to make code easier to read
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

namespace Zukunft\ZukunftCom\main\php\cfg\view;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_SANDBOX . 'sandbox_link_list.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_par_type.php';
include_once paths::EXPORT . 'export_type_list.php';
include_once paths::MODEL_SANDBOX . 'sandbox_link.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::MODEL_VIEW . 'view.php';
include_once paths::MODEL_VIEW . 'view_db.php';
include_once paths::MODEL_VIEW . 'view_relation.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_type;
use Zukunft\ZukunftCom\main\php\cfg\export\export_type_list;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_link;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_link_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;

class view_relation_list extends sandbox_link_list
{

    /*
     * construct and map
     */

    /**
     * map only the valid view relations
     *
     * @param array|null $db_rows with the data directly from the database
     * @param bool $load_all force to include also the excluded phrases e.g. for admins
     * @return bool true if the view relation is loaded and valid
     */
    protected function rows_mapper(?array $db_rows, bool $load_all = false): bool
    {
        return parent::rows_mapper_obj(new view_relation($this->get_user()), $db_rows, $load_all);
    }


    /*
     * load
     */

    /**
     * interface function to load all view relations of the given view
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


    /*
     * load sql
     */

    /**
     * set the SQL query parameters to load all components linked to a view
     * @param sql_creator $sc with the target db_type set
     * @param view $msk the id of the view to which the components should be loaded
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_by_view(sql_creator $sc, view $msk): sql_par
    {
        $qp = $this->load_sql($sc, view_db::FLD_ID);
        if ($msk->id() > 0) {
            $sc->add_where(view_relation::FLD_FROM, $msk->id(), sql_par_type::INT_OR);
            $sc->add_where(view_relation::FLD_TO, $msk->id(), sql_par_type::INT_OR);
            $sc->set_join_usr_fields(view_db::FLD_NAMES_USR_ALL, view::class, view_relation::FLD_FROM, '', true);
            $sc->set_join_usr_fields(view_db::FLD_NAMES_USR_ALL, view::class, view_relation::FLD_TO, '', true);
            $qp->sql = $sc->sql();
        } else {
            $qp->name = '';
        }
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * set the common part of the SQL query view relations
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name of the selection fields to make the query name unique
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name): sql_par
    {
        $qp = new sql_par(self::class);
        $qp->name .= $query_name;

        $sc->set_class(view_relation::class);
        $sc->set_name($qp->name); // assign incomplete name to force the usage of the user as a parameter
        $sc->set_usr($this->get_user()->id);
        $sc->set_fields(view_relation::FLD_NAMES);
        $sc->set_usr_fields(view_relation::FLD_NAMES_USR);
        return $qp;
    }


    /*
     * im- and export
     */

    /**
     * create an array with the export json fields
     * @param export_type_list|array $exp_typ define the export format
     * @param bool $do_load true if any missing data should be loaded while creating the array
     * @return array with the json fields
     */
    function export_json(export_type_list|array $exp_typ = [], bool $do_load = true): array
    {
        $vars = [];
        foreach ($this->lst() as $lnk) {
            $vars[] = $lnk->export_json($exp_typ, $do_load);
        }
        return $vars;
    }


    /*
     * modify
     */

    /**
     * add a view relation to the list without saving it to the database
     * @return true if the link has been added
     */
    function add_by_name(view_relation $lnk_to_add, user_message $usr_msg): bool
    {
        $added = false;
        if ($this->can_add($lnk_to_add)) {
            $this->add_link_by_key($lnk_to_add);
            $added = true;
        }
        return $added;
    }

    /**
     * delete all loaded view relations e.g. to delete all the links assigned to a view
     * @param user_message $usr_msg the message for the user why deleting this view relation has failed and a suggested solution
     * @return bool true if the view relations have been deleted
     */
    function del(user_message $usr_msg): bool
    {
        if (!$this->is_empty()) {
            foreach ($this->lst() as $dsp_cmp_lnk) {
                $dsp_cmp_lnk->del($usr_msg);
            }
        }
        return $usr_msg->is_ok();
    }


    /*
     * info
     */

    /**
     * @return array with all view ids
     */
    function view_ids(): array
    {
        $result = array();
        foreach ($this->lst() as $lnk) {
            $id = $lnk->get_view()->id();
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
    function names(bool $ignore_excluded = false, ?int $limit = null): array
    {
        $result = array();
        foreach ($this->lst() as $lnk) {
            if ($lnk->get_component() != null) {
                $name = $lnk->get_component()->name($ignore_excluded);
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
     * @param user_message $usr_msg the message shown to the user why the action has failed or an empty string if everything is fine
     * @return bool true if everything has been fine
     */
    function save(user_message $usr_msg): bool
    {
        foreach ($this->lst() as $sbx) {
            // for each item of a list an empty user_message statement should be used
            // so that an issue in one item does not prevent other item from being saved
            $msk_rel_usr_msg = $usr_msg->clone_reset();
            // save upfront and missing components
            $cmp = $sbx->get_component();
            if (!$cmp->is_valid()) {
                if ($cmp->db_ready($msk_rel_usr_msg)) {
                    $cmp->save($msk_rel_usr_msg);
                }
            }
            // save the link of the view to the component
            $sbx->save($msk_rel_usr_msg);
            // collect the user message for a consolidated list for the user
            $usr_msg->merge($msk_rel_usr_msg);
        }
        return $usr_msg->is_ok();
    }


    /*
      * internal
      */

    /**
     * test if the link at the same position already exists and if yes return false to prevent duplicates
     * overwrites the parent because the same component can be used in a view at different positions
     * but not at the same position
     * @param view_relation|sandbox_link $lnk_to_add the link that should be added to the list
     * @return bool true if the link can be added
     */
    protected function can_add(view_relation|sandbox_link $lnk_to_add): bool
    {
        $can_add = true;

        if (!$this->is_empty()) {
            foreach ($this->lst() as $lnk) {
                if ($can_add) {
                    if ($lnk->from_id() == $lnk_to_add->from_id()
                        and $lnk->to_id() == $lnk_to_add->to_id()
                        and $lnk->get_pos() == $lnk_to_add->get_pos()) {
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