<?php

/*

    model/view/view_list.php - list of predefined system views
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

include_once API_VIEW_PATH . 'view_list.php';
include_once MODEL_SANDBOX_PATH . 'sandbox_list.php';

use api\view_list_api;
use cfg\db\sql_creator;
use cfg\db\sql_par_type;

global $system_views;

class view_list extends sandbox_list
{

    public user $usr;   // the user object of the person for whom the verb list is loaded, so to say the viewer

    /*
     * construct and map
     */

    /**
     * fill the view list based on a database records
     * actually just add the single view object to the parent function
     * TODO check that a similar function is used for all lists
     *
     * @param array $db_rows is an array of an array with the database values
     * @param bool $load_all force to include also the excluded phrases e.g. for admins
     * @return bool true if at least one formula link has been added
     */
    protected function rows_mapper(array $db_rows, bool $load_all = false): bool
    {
        return parent::rows_mapper_obj(new view($this->user()), $db_rows, $load_all);
    }


    /*
     * set and get
     */

    /**
     * set the user of the phrase list
     *
     * @param user $usr the person who wants to access the phrases
     * @return void
     */
    function set_user(user $usr): void
    {
        $this->usr = $usr;
    }

    /**
     * @return user the person who wants to see the phrases
     */
    function user(): user
    {
        return $this->usr;
    }


    /*
     * cast
     */

    /**
     * @return view_list_api the view list object with the display interface functions
     */
    function api_obj(): view_list_api
    {
        $api_obj = new view_list_api();
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
     * add system view filter to
     * the SQL statement to load only the view id and name
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

        $sc->add_where(sql_db::FLD_CODE_ID, '', sql_par_type::IS_NULL);

        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of views
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $class the name of this class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_db $db_con, string $class = self::class): sql_par
    {
        $qp = new sql_par($class);
        $db_con->set_type(sql_db::TBL_VIEW);
        $db_con->set_name($qp->name); // assign incomplete name to force the usage of the user as a parameter
        $db_con->set_usr($this->user()->id());
        $db_con->set_fields(view::FLD_NAMES);
        $db_con->set_usr_fields(view::FLD_NAMES_USR);
        $db_con->set_usr_num_fields(view::FLD_NAMES_NUM_USR);
        return $qp;
    }

    /**
     * load a list of view names
     * @param string $pattern the pattern to filter the views
     * @param int $limit the number of rows to return
     * @param int $offset jump over these number of pages
     * @return bool true if at least one view found
     */
    function load_names(string $pattern = '', int $limit = 0, int $offset = 0): bool
    {
        return parent::load_sbx_names(new view($this->user()), $pattern, $limit, $offset);
    }

    /**
     * add one view to the view list, but only if it is not yet part of the phrase list
     * @param view $dsp_to_add the view that should be added to the list
     */
    function add(view $dsp_to_add): void
    {
        log_debug($dsp_to_add->dsp_id());
        if (!in_array($dsp_to_add->id(), $this->ids())) {
            if ($dsp_to_add->id() <> 0) {
                $this->lst[] = $dsp_to_add;
            }
        } else {
            log_debug($dsp_to_add->dsp_id() . ' not added, because it is already in the list');
        }
    }


    /*
     * im- and export
     */

    /**
     * import a list of views from a JSON array object
     *
     * @param array $json_obj an array with the data of the json object
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_obj(array $json_obj, object $test_obj = null): user_message
    {
        $result = new user_message();
        foreach ($json_obj as $dsp_json) {
            $dsp = new view($this->user());
            $result->add($dsp->import_obj($dsp_json, $test_obj));
            $this->add($dsp);
        }

        return $result;
    }

    /**
     * create a list of views for the export
     * @return array with the reduced results that can be used to create a JSON message
     */
    function export_obj(bool $do_load = true): array
    {
        $exp_views = array();
        foreach ($this->lst as $dsp) {
            $exp_views[] = $dsp->export_obj($do_load);
        }
        return $exp_views;
    }

}

