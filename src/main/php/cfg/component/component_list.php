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

use cfg\const\paths;

include_once paths::MODEL_SANDBOX . 'sandbox_list.php';
include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_par_type.php';
include_once paths::MODEL_SANDBOX . 'sandbox_list_named.php';
include_once paths::MODEL_SANDBOX . 'sandbox_named.php';
include_once paths::MODEL_SANDBOX . 'sandbox_link_named.php';
include_once paths::MODEL_HELPER . 'data_object.php';
include_once paths::MODEL_HELPER . 'combine_named.php';
include_once paths::MODEL_HELPER . 'type_list.php';
include_once paths::MODEL_IMPORT . 'import.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::MODEL_VIEW . 'view.php';
include_once paths::MODEL_VIEW . 'view_db.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_HELPER . 'CombineObject.php';
include_once paths::SHARED_HELPER . 'IdObject.php';
include_once paths::SHARED_HELPER . 'TextIdObject.php';
include_once paths::SHARED_TYPES . 'component_type.php';

use cfg\db\sql;
use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\db\sql_par;
use cfg\db\sql_par_type;
use cfg\helper\data_object;
use cfg\import\import;
use cfg\sandbox\sandbox_list_named;
use cfg\sandbox\sandbox_named;
use cfg\sandbox\sandbox_link_named;
use cfg\helper\combine_named;
use cfg\helper\type_list;
use cfg\user\user;
use cfg\user\user_message;
use cfg\view\view_db;
use shared\const\triples;
use shared\const\words;
use shared\enum\messages as msg_id;
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
     * set the SQL query parameters to load a list of components by the names
     * TODO use name_field() function to avoid overwrites
     * @param sql_creator $sc with the target db_type set
     * @param array $names a list of strings with the word names
     * @param string $fld the name of the name field
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_names(
        sql_creator $sc,
        array $names,
        string $fld = component_db::FLD_NAME
    ): sql_par
    {
        return parent::load_sql_by_names($sc, $names, $fld);
    }

    /**
     * create an SQL statement to retrieve a list of components from the database
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
     * im- and export
     */

    /**
     * import a list of components from a JSON array object
     *
     * @param array $json_obj an array with the data of the json object
     * @param data_object|null $dto cache of the objects imported until now for the primary references
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_obj(
        array        $json_obj,
        ?data_object $dto = null,
        object       $test_obj = null
    ): user_message
    {
        $usr_msg = new user_message();
        foreach ($json_obj as $dsp_json) {
            $cmp = new component($this->user());
            $usr_msg->add($cmp->import_obj($dsp_json, $dto, $test_obj));
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
     * add or update all components to the database
     * starting with the $cache that contains the words, triples, verbs
     * add the components that does not yet have a database id
     * similar to triple_list->save_with_cache but using the term_list
     *
     * @param import|null $imp the import object with the filename and the estimated time of arrival
     * @return user_message the message shown to the user why the action has failed or an empty string if everything is fine
     */
    function save(import $imp = null): user_message
    {
        global $cfg;

        $usr_msg = new user_message();

        $load_per_sec = $cfg->get_by([words::COMPONENTS, words::LOAD, triples::OBJECTS_PER_SECOND, triples::EXPECTED_TIME, words::IMPORT], 1);
        $save_per_sec = $cfg->get_by([words::COMPONENTS, words::STORE, triples::OBJECTS_PER_SECOND, triples::EXPECTED_TIME, words::IMPORT], 1);
        $upd_per_sec = $cfg->get_by([words::COMPONENTS, words::UPDATE, triples::OBJECTS_PER_SECOND, triples::EXPECTED_TIME, words::IMPORT], 1);
        $del_per_sec = $cfg->get_by([words::COMPONENTS, words::DELETE, triples::OBJECTS_PER_SECOND, triples::EXPECTED_TIME, words::IMPORT], 1);
        $max_frm_levels = $cfg->get_by([words::COMPONENTS, triples::MAX_LEVELS, words::IMPORT], 99);

        if ($this->is_empty()) {
            log_info('no components to save');
        } else {

            // repeat filling the database id to the component list
            // and adding missing components to the database
            // until it is clear that a component is missing
            $frm_added = true;
            $level = 0;
            $db_lst_all = new component_list($this->user());
            $add_lst = new component_list($this->user());
            while ($frm_added and $level < $max_frm_levels) {
                $frm_added = false;
                $usr_msg->unset_added_depending();

                // get the components that needs to be added
                // TODO check if other list save function are using the cache instead of this here
                $chk_lst = clone $this;
                $load_lst = $chk_lst->missing_ids();

                // load the components by name from the database that does not yet have a database id
                $step_time = $load_lst->count() / $load_per_sec;
                $imp->step_start(msg_id::LOAD, component::class, $load_lst->count(), $step_time);
                $db_lst = new component_list($this->user());
                // force to load all names including the components excluded by the user to potential include the components due to the import
                // TODO add load_all = true also to the other objects
                $db_lst->load_by_names($load_lst->names(true), true);
                $imp->step_end($load_lst->count(), $load_per_sec);

                // fill up the overall db list with db value for later detection of the components that needs to be updated
                $db_lst_all->merge($db_lst);

                // fill up the loaded list with db value to select only the components that really needs to be inserted
                $load_lst->fill_by_name($db_lst, true, false);

                // select the components that are ready to be added to the database
                $load_lst = $load_lst->get_ready($usr_msg, $imp->file_name);

                // get the components that still needs to be added
                // TODO check if other list save function are using the cache instead of this here
                $add_lst = $load_lst->missing_ids();

                // create any missing sql insert functions and insert the missing components
                if (!$add_lst->is_empty()) {

                    $step_time = $add_lst->count() / $save_per_sec;
                    $imp->step_start(msg_id::SAVE, component::class, $add_lst->count(), $step_time);
                    $usr_msg->add($add_lst->insert($db_lst_all, true, $imp, component::class));
                    if ($add_lst->count() > 0) {
                        $usr_msg->set_added_depending();
                        $frm_added = true;
                    }
                    $imp->step_end($add_lst->count(), $save_per_sec);
                }

                $level++;
            }

            // reload the id of the components added with the last run
            // TODO use the insert message instead to increase speed
            $db_lst = new component_list($this->user());
            if (!$add_lst->is_empty()) {
                $db_lst->load_by_names($add_lst->names(true), true);
            }

            // fill up the overall db list with db value for later detection of the components that needs to be updated
            $db_lst_all->merge($db_lst);


            // create any missing sql update functions and update the components
            $usr_msg->add($this->update($db_lst_all, true, $imp, component::class, $upd_per_sec));


            // fill up the main list with the components to check if anything is missing
            $this->fill_by_name($db_lst_all, true);


            // create any missing sql delete functions and delete unused sandbox objects
            $usr_msg->add($this->delete($db_lst_all, true, $imp, component::class, $del_per_sec));

        }

        return $usr_msg;
    }

    /**
     * get a list of components that are ready to be added to the database
     * TODO Prio 2 move to
     * @return component_list list of the components that have an id or a name
     */
    function get_ready(user_message $usr_msg, string $file_name = ''): component_list
    {
        $cmp_lst = new component_list($this->user());
        foreach ($this->lst() as $cmp) {
            $cmp_msg = $cmp->db_ready();
            if ($cmp_msg->is_ok()) {
                $cmp_lst->add_by_name($cmp);
            } else {
                $usr_msg->add($cmp_msg);
                $usr_msg->add_id_with_vars(msg_id::IMPORT_FORMULA_NOT_READY, [
                    msg_id::VAR_FILE_NAME => $file_name,
                    msg_id::VAR_FORMULA => $cmp->dsp_id(),
                ]);
            }
        }
        return $cmp_lst;
    }

}

