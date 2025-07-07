<?php

/*

    model/view/component_list.php - list of predefined system components
    ------------------------

    The main sections of this object are
    - construct and map: including the mapping of the db row to this component link list object
    - load:              database access object (DAO) functions
    - load sql:          create the sql statements for loading from the db
    - api:               create an api array for the frontend and set the vars based on a frontend api message
    - im- and export:    create an export object and set the vars from an import object
    - modify:            change potentially all variables of this list object


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

namespace cfg\component;

include_once MODEL_SANDBOX_PATH . 'sandbox_list.php';
include_once DB_PATH . 'sql.php';
include_once DB_PATH . 'sql_creator.php';
include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_par.php';
include_once DB_PATH . 'sql_par_type.php';
include_once MODEL_SANDBOX_PATH . 'sandbox_list_named.php';
include_once MODEL_SANDBOX_PATH . 'sandbox_named.php';
include_once MODEL_SANDBOX_PATH . 'sandbox_link_named.php';
include_once MODEL_HELPER_PATH . 'data_object.php';
include_once MODEL_HELPER_PATH . 'combine_named.php';
include_once MODEL_HELPER_PATH . 'type_list.php';
include_once MODEL_USER_PATH . 'user.php';
include_once MODEL_USER_PATH . 'user_message.php';
include_once MODEL_VIEW_PATH . 'view.php';
include_once MODEL_VIEW_PATH . 'view_db.php';
include_once SHARED_HELPER_PATH . 'CombineObject.php';
include_once SHARED_HELPER_PATH . 'IdObject.php';
include_once SHARED_HELPER_PATH . 'TextIdObject.php';
include_once SHARED_TYPES_PATH . 'component_type.php';

use cfg\db\sql;
use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\db\sql_par;
use cfg\db\sql_par_type;
use cfg\helper\data_object;
use cfg\sandbox\sandbox_list_named;
use cfg\sandbox\sandbox_named;
use cfg\sandbox\sandbox_link_named;
use cfg\helper\combine_named;
use cfg\helper\type_list;
use cfg\user\user;
use cfg\user\user_message;
use cfg\view\view;
use cfg\view\view_db;
use shared\helper\CombineObject;
use shared\helper\IdObject;
use shared\helper\TextIdObject;
use shared\types\component_type as comp_type_shared;

class component_list extends sandbox_list_named
{

    /*
     * construct and map
     */

    /**
     * fill the component list based on a database records
     * @param array $db_rows is an array of an array with the database values
     * @param bool $load_all force to include also the excluded phrases e.g. for admins
     * @return bool true if at least one component has been loaded
     */
    protected function rows_mapper(array $db_rows, bool $load_all = false): bool
    {
        return parent::rows_mapper_obj(new component($this->user()), $db_rows, $load_all);
    }


    /*
     * load
     */

    /**
     * load a list of component names
     * @param string $pattern the pattern to filter the components
     * @param int $limit the number of rows to return
     * @param int $offset jump over these number of pages
     * @return bool true if at least one component found
     */
    function load_names(string $pattern = '', int $limit = 0, int $offset = 0): bool
    {
        return parent::load_sbx_names(new component($this->user()), $pattern, $limit, $offset);
    }

    /**
     * load the components selected by the id
     *
     * @param array $ids of components ids that should be loaded
     * @param sql_db|null $db_con_given the database connection as a parameter for the initial load of the system views
     * @return bool true if at least one component has been loaded
     */
    function load_by_ids(array $ids, ?sql_db $db_con_given = null): bool
    {
        global $db_con;

        $db_con_used = $db_con_given;
        if ($db_con_used == null) {
            $db_con_used = $db_con;
        }

        $qp = $this->load_sql_by_ids($db_con_used->sql_creator(), $ids);
        return $this->load_sys($qp, false, $db_con_used);
    }

    /**
     * load the components of a view from the database selected by id
     * @param int $id the id of the view
     * @return bool true if at least one component has been loaded
     */
    function load_by_view_id(int $id): bool
    {
        global $db_con;

        log_debug($id);
        $qp = $this->load_sql_by_view_id($db_con->sql_creator(), $id);
        return parent::load($qp);
    }


    /*
     * load sql
     */

    /**
     * add system component filter to
     * the SQL statement to load only the view id and name
     * to exclude the system component from the user selection
     *
     * @param sql_creator $sc with the target db_type set
     * @param sandbox_named|sandbox_link_named|combine_named $sbx the single child object
     * @param string $pattern the pattern to filter the views
     * @param int $limit the number of rows to return
     * @param int $offset jump over these number of pages
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_names(
        sql_creator                                    $sc,
        sandbox_named|sandbox_link_named|combine_named $sbx,
        string                                         $pattern = '',
        int                                            $limit = 0,
        int                                            $offset = 0
    ): sql_par
    {
        $qp = $this->load_sql_names_pre($sc, $sbx, $pattern, $limit, $offset);

        $typ_lst = new type_list();
        $sc->add_where(
            component_db::FLD_TYPE,
            implode(',', $typ_lst->component_id_list(comp_type_shared::SYSTEM_TYPES)),
            sql_par_type::CONST_NOT_IN);

        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a list of sources from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param array $ids component ids that should be loaded
     * @param int $limit the number of rows to return
     * @param int $offset jump over these number of pages
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_ids(
        sql_creator $sc,
        array       $ids,
        int         $limit = 0,
        int         $offset = 0
    ): sql_par
    {
        $qp = $this->load_sql($sc, 'ids');
        $sc->add_where(component::FLD_ID, $ids);
        $sc->set_order(component::FLD_ID, sql::ORDER_ASC);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of components by the view id
     * @param sql_creator $sc the db connection object as a function parameter for unit testing
     * @param int $id the id of the view to which the components should be loaded
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_view_id(sql_creator $sc, int $id): sql_par
    {
        $qp = $this->load_sql($sc, 'view_id');
        $sc->set_name($qp->name);
        $sc->set_join_fields(
            component_link::FLD_NAMES,
            component_link::class,
            component::FLD_ID,
            component::FLD_ID);
        $sc->add_where(view_db::FLD_ID, $id, null, sql_db::LNK_TBL);
        $sc->set_order(component_link::FLD_ORDER_NBR, '', sql_db::LNK_TBL);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * set the common SQL query parameters to load a list of components
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name of the query use to prepare and call the query
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name): sql_par
    {
        $sc->set_class(component::class);
        $qp = new sql_par(self::class);
        $qp->name .= $query_name;
        $sc->set_name($qp->name); // assign incomplete name to force the usage of the user as a parameter
        $sc->set_usr($this->user()->id());
        $sc->set_fields(component::FLD_NAMES);
        $sc->set_usr_fields(component::FLD_NAMES_USR);
        $sc->set_usr_num_fields(component::FLD_NAMES_NUM_USR);
        return $qp;
    }


    /*
     * search
     */

    /**
     * overwrite of the parent function just to add the component as a return type
     * find an object from the loaded list by name using the hash
     * should be cast by the child function get_by_name
     *
     * @param string $name the unique name of the object that should be returned
     * @param bool $use_all force to include also the excluded names e.g. for import
     * @return component|CombineObject|IdObject|TextIdObject|null the found user sandbox object or null if no name is found
     */
    function get_by_name(string $name, bool $use_all = false): component|CombineObject|IdObject|TextIdObject|null
    {
        return parent::get_by_name($name, $use_all);
    }


    /*
     * im- and export
     */

    /**
     * import a list of components from a JSON array object
     *
     * @param array $json_obj an array with the data of the json object
     * @param user $usr_req the user how has initiated the import mainly used to prevent any user to gain additional rights
     * @param data_object|null $dto cache of the objects imported until now for the primary references
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_obj(
        array        $json_obj,
        user         $usr_req,
        ?data_object $dto = null,
        object       $test_obj = null
    ): user_message
    {
        $usr_msg = new user_message();
        foreach ($json_obj as $dsp_json) {
            $cmp = new component($this->user());
            $usr_msg->add($cmp->import_obj($dsp_json, $usr_req, $dto, $test_obj));
            $this->add($cmp);
        }

        return $usr_msg;
    }

    /**
     * create an array with the export json fields
     * @param bool $do_load true if any missing data should be loaded while creating the array
     * @return array with the json fields
     */
    function export_json(bool $do_load = true): array
    {
        $cmp_lst = [];
        foreach ($this->lst() as $cmp) {
            $cmp_lst[] = $cmp->export_json($do_load);
        }
        return $cmp_lst;
    }

    /**
     * save all components of this list
     * TODO create one SQL and commit statement for faster execution
     *
     * @return user_message the message shown to the user why the action has failed or an empty string if everything is fine
     */
    function save(): user_message
    {
        $result = new user_message();
        foreach ($this->lst() as $cmp) {
            $result->add($cmp->save());
        }
        return $result;
    }

}

