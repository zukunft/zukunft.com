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

namespace cfg;

include_once API_VIEW_PATH . 'component_list.php';
include_once MODEL_SANDBOX_PATH . 'sandbox_list.php';

use api\component_list_api;

class component_list extends sandbox_list
{

    /*
     * construct and map
     */

    /**
     * fill the component list based on a database records
     * @param array $db_rows is an array of an array with the database values
     * @return bool true if at least one component has been loaded
     */
    protected function rows_mapper(array $db_rows): bool
    {
        $result = false;
        if ($db_rows != null) {
            foreach ($db_rows as $db_row) {
                if (is_null($db_row[sandbox::FLD_EXCLUDED]) or $db_row[sandbox::FLD_EXCLUDED] == 0) {
                    $cmp_id = $db_row[component::FLD_ID];
                    if ($cmp_id > 0 and !in_array($cmp_id, $this->ids())) {
                        $cmp = new component($this->user());
                        $cmp->row_mapper_sandbox($db_row);
                        $this->lst[] = $cmp;
                        $result = true;
                    }
                }
            }
        }
        return $result;
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
        foreach ($this->lst as $dsp) {
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
     * set the common SQL query parameters to load a list of components
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $class the name of this class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_db $db_con, string $class = self::class): sql_par
    {
        $qp = new sql_par($class);
        $db_con->set_type(sql_db::TBL_COMPONENT);
        $db_con->set_name($qp->name); // assign incomplete name to force the usage of the user as a parameter
        $db_con->set_usr($this->user()->id());
        $db_con->set_fields(component::FLD_NAMES);
        $db_con->set_usr_fields(component::FLD_NAMES_USR);
        $db_con->set_usr_num_fields(component::FLD_NAMES_NUM_USR);
        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of components by the view id
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param int $id the id of the view to which the components should be loaded
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_view_id(sql_db $db_con, int $id): sql_par
    {
        $qp = $this->load_sql($db_con);
        $qp->name .= 'view_id';
        $db_con->set_name($qp->name);
        $db_con->set_join_fields(
            component_link::FLD_NAMES,
            sql_db::TBL_COMPONENT_LINK,
            component::FLD_ID,
            component::FLD_ID);
        $db_con->set_order(component_link::FLD_ORDER_NBR, '', sql_db::LNK_TBL);
        $qp->sql = $db_con->select_by_join_field(view::FLD_ID, $id);
        $qp->par = $db_con->get_par();

        return $qp;
    }

    /**
     * load the components of a view from the database selected by id
     * @param int $id the id of the word, triple, formula, verb, view or view component
     * @return bool true if at least one component has been loaded
     */
    function load_by_view_id(int $id): bool
    {
        global $db_con;

        log_debug($id);
        $qp = $this->load_sql_by_view_id($db_con, $id);
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
                $this->lst[] = $cmp_to_add;
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
        foreach ($this->lst as $dsp) {
            $exp_components[] = $dsp->export_obj($do_load);
        }
        return $exp_components;
    }

}

