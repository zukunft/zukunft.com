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

namespace cfg\view;

include_once MODEL_SANDBOX_PATH . 'sandbox_list_named.php';
include_once DB_PATH . 'sql_creator.php';
include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_par.php';
include_once DB_PATH . 'sql_par_type.php';
include_once MODEL_COMPONENT_PATH . 'component.php';
include_once MODEL_COMPONENT_PATH . 'component_link.php';
include_once MODEL_HELPER_PATH . 'combine_named.php';
include_once MODEL_HELPER_PATH . 'data_object.php';
include_once MODEL_HELPER_PATH . 'type_list.php';
include_once MODEL_SANDBOX_PATH . 'sandbox_link_named.php';
include_once MODEL_SANDBOX_PATH . 'sandbox_named.php';
include_once MODEL_USER_PATH . 'user.php';
include_once MODEL_USER_PATH . 'user_message.php';
include_once MODEL_VIEW_PATH . 'view.php';
include_once MODEL_VIEW_PATH . 'view_type.php';

use cfg\component\component;
use cfg\component\component_link;
use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\db\sql_par;
use cfg\db\sql_par_type;
use cfg\helper\combine_named;
use cfg\helper\data_object;
use cfg\helper\type_list;
use cfg\sandbox\sandbox_link_named;
use cfg\sandbox\sandbox_list_named;
use cfg\sandbox\sandbox_named;
use cfg\user\user;
use cfg\user\user_message;

global $sys_msk_cac;

class view_list extends sandbox_list_named
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

        $typ_lst = new type_list();
        $sc->add_where(
            view::FLD_TYPE,
            implode(',', $typ_lst->view_id_list(view_type::SYSTEM_TYPES)),
            sql_par_type::CONST_NOT_IN);

        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of views
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name extension to make the query name unique
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name = ''): sql_par
    {
        $sc->set_class(view::class);
        $qp = new sql_par(self::class);
        $qp->name .= $query_name;
        $sc->set_name($qp->name); // assign incomplete name to force the usage of the user as a parameter
        $sc->set_usr($this->user()->id());
        $sc->set_fields(view::FLD_NAMES);
        $sc->set_usr_fields(view::FLD_NAMES_USR);
        $sc->set_usr_num_fields(view::FLD_NAMES_NUM_USR);
        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of views by the component id
     * @param sql_creator $sc with the target db_type set
     * @param int $id the id of the component to which the views should be loaded
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_component_id(sql_creator $sc, int $id): sql_par
    {
        $qp = $this->load_sql($sc, 'component_id');
        $sc->set_name($qp->name);
        $sc->set_join_fields(
            component_link::FLD_NAMES,
            component_link::class,
            view::FLD_ID,
            view::FLD_ID);
        $sc->set_order(component_link::FLD_ORDER_NBR, '', sql_db::LNK_TBL);
        $sc->add_where(component::FLD_ID, $id, sql_par_type::INT, sql_db::LNK_TBL);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

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
     * load the views that have a component linked from the database selected by id
     * @param int $id the id of the component
     * @return bool true if at least one component has been loaded
     */
    function load_by_component_id(int $id): bool
    {
        global $db_con;

        log_debug($id);
        $qp = $this->load_sql_by_component_id($db_con->sql_creator(), $id);
        return parent::load($qp);
    }


    /*
     * im- and export
     */

    /**
     * import a list of views from a JSON array object
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
            $msk = new view($this->user());
            $usr_msg->add($msk->import_obj($dsp_json, $usr_req, $dto, $test_obj));
            $this->add($msk);
        }

        return $usr_msg;
    }

    /**
     * save all views of this list
     * TODO create one SQL and commit statement for faster execution
     *
     * @return user_message the message shown to the user why the action has failed or an empty string if everything is fine
     */
    function save(): user_message
    {
        $result = new user_message();
        foreach ($this->lst() as $msk) {
            $result->add($msk->save());
        }
        return $result;
    }

}

