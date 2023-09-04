<?php

/*

    model/view/component_list.php - list of predefined system components
    ------------------------

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

include_once API_COMPONENT_PATH . 'component_list.php';
include_once API_VIEW_PATH . 'component_link_list.php';
include_once MODEL_SANDBOX_PATH . 'sandbox_list.php';

use api\component\component_list AS component_list_api;
use cfg\combine_named;
use cfg\component_link;
use cfg\db\sql_creator;
use cfg\db\sql_par_type;
use cfg\sandbox_link_named;
use cfg\sandbox_list;
use cfg\sandbox_named;
use cfg\sql_db;
use cfg\sql_par;
use cfg\type_list;
use cfg\user_message;
use cfg\view;

class component_list extends sandbox_list
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
     * cast
     */

    /**
     * @return component_list_api the component list object with the display interface functions
     */
    function api_obj(): component_list_api
    {
        $api_obj = new component_list_api();
        foreach ($this->lst() as $dsp) {
            $api_obj->add($dsp->api_obj());
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
            component::FLD_TYPE,
            implode(',', $typ_lst->component_id_list(component_type::SYSTEM_TYPES)),
            sql_par_type::CONST_NOT_IN);

        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * set the common SQL query parameters to load a list of components
     * @param sql_creator $sc the db connection object as a function parameter for unit testing
     * @param string $class the name of this class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_creator $sc, string $class = self::class): sql_par
    {
        $qp = new sql_par($class);
        $sc->set_type(sql_db::TBL_COMPONENT);
        $sc->set_name($qp->name); // assign incomplete name to force the usage of the user as a parameter
        $sc->set_usr($this->user()->id());
        $sc->set_fields(component::FLD_NAMES);
        $sc->set_usr_fields(component::FLD_NAMES_USR);
        $sc->set_usr_num_fields(component::FLD_NAMES_NUM_USR);
        return $qp;
    }

    /**
     * create an SQL statement to retrieve a list of sources from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_ids(sql_creator $sc, array $ids): sql_par
    {
        $qp = $this->load_sql($sc, 'ids');
        $sc->add_where(component::FLD_ID, $ids);
        $sc->set_order(component::FLD_ID, sql_db::ORDER_ASC);
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
        $qp = $this->load_sql($sc);
        $qp->name .= 'view_id';
        $sc->set_name($qp->name);
        $sc->set_join_fields(
            component_link::FLD_NAMES,
            sql_db::TBL_COMPONENT_LINK,
            component::FLD_ID,
            component::FLD_ID);
        $sc->add_where(sql_db::LNK_TBL . '.' . view::FLD_ID, $id);
        $sc->set_order(component_link::FLD_ORDER_NBR, '', sql_db::LNK_TBL);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

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
     * @return bool true if at least one component has been loaded
     */
    function load_by_ids(array $ids): bool
    {
        global $db_con;

        $qp = $this->load_sql_by_ids($db_con->sql_creator(), $ids);
        return $this->load($qp);
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

    /**
     * TODO check if the position is set and if not set it
     * add one component to the component list, but only if it is not yet part of the component list
     * @param component $cmp_to_add the component that should be added to the list
     */
    function add(component $cmp_to_add): void
    {
        log_debug($cmp_to_add->dsp_id());
        if (!in_array($cmp_to_add->id(), $this->ids())) {
            if ($cmp_to_add->id() <> 0) {
                $this->add_obj($cmp_to_add);
            }
        } else {
            log_debug($cmp_to_add->dsp_id() . ' not added, because it is already in the list');
        }
    }


    /*
     * im- and export
     */

    /**
     * import a list of components from a JSON array object
     *
     * @param array $json_obj an array with the data of the json object
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_obj(array $json_obj, object $test_obj = null): user_message
    {
        $result = new user_message();
        foreach ($json_obj as $dsp_json) {
            $dsp = new component($this->user());
            $result->add($dsp->import_obj($dsp_json, $test_obj));
            $this->add($dsp);
        }

        return $result;
    }

    /**
     * create a list of components for the export
     * @param bool $do_load
     * @return array with the reduced results that can be used to create a JSON message
     */
    function export_obj(bool $do_load = true): array
    {
        $exp_components = array();
        foreach ($this->lst() as $dsp) {
            $exp_components[] = $dsp->export_obj($do_load);
        }
        return $exp_components;
    }

}

