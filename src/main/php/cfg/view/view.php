<?php

/*

    model/view/view.php - the main display object
    -------------------

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
include_once WEB_VIEW_PATH . 'view.php';
include_once MODEL_VIEW_PATH . 'component_link.php';
include_once MODEL_COMPONENT_PATH . 'component.php';
include_once MODEL_COMPONENT_PATH . 'component_list.php';
include_once MODEL_VIEW_PATH . 'component_link_list.php';
include_once SERVICE_EXPORT_PATH . 'view_exp.php';
include_once SERVICE_EXPORT_PATH . 'component_exp.php';

use api\view\view as view_api;
use cfg\component\component;
use cfg\db\sql_creator;
use cfg\db\sql_par_type;
use model\export\exp_obj;
use model\export\view_exp;

class view extends sandbox_typed
{

    /*
     * database link
     */

    // the database and JSON object field names used only for views
    const FLD_ID = 'view_id';
    const FLD_NAME = 'view_name';
    const FLD_TYPE = 'view_type_id';
    // the JSON object field names
    const FLD_COMPONENT = 'components';

    // all database field names excluding the id
    const FLD_NAMES = array(
        sql_db::FLD_CODE_ID
    );
    // list of the user specific database field names
    const FLD_NAMES_USR = array(
        sandbox_named::FLD_DESCRIPTION
    );
    // list of the user specific database field names
    const FLD_NAMES_NUM_USR = array(
        self::FLD_TYPE,
        sandbox::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );
    // all database field names excluding the id used to identify if there are some user specific changes
    const ALL_SANDBOX_FLD_NAMES = array(
        sandbox_named::FLD_DESCRIPTION,
        self::FLD_TYPE,
        sandbox::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );


    /*
     * object vars
     */

    // database fields additional to the user sandbox fields for the view component
    public ?string $code_id = null;   // to select internal predefined views

    // in memory only fields
    public ?component_link_list $cmp_lnk_lst;  // all links to the component objects in correct order


    /*
     * construct and map
     */

    /**
     * define the settings for this view object
     * @param user $usr the user who requested to see this view
     */
    function __construct(user $usr)
    {
        $this->reset();

        parent::__construct($usr);
        $this->obj_name = sql_db::TBL_VIEW;
        $this->rename_can_switch = UI_CAN_CHANGE_VIEW_NAME;
    }

    function reset(): void
    {
        parent::reset();

        $this->type_id = null;
        $this->code_id = '';

        $this->cmp_lnk_lst = null;
    }

    // TODO check if there is any case where the user fields should not be set

    /**
     * map the database fields to the object fields
     *
     * @param array|null $db_row with the data directly from the database
     * @param bool $load_std true if only the standard user sandbox object ist loaded
     * @param bool $allow_usr_protect false for using the standard protection settings for the default object used for all users
     * @param string $id_fld the name of the id field as defined in this child and given to the parent
     * @param string $name_fld the name of the name field as defined in this child class
     * @return bool true if the view is loaded and valid
     */
    function row_mapper_sandbox(
        ?array $db_row,
        bool   $load_std = false,
        bool   $allow_usr_protect = true,
        string $id_fld = self::FLD_ID,
        string $name_fld = self::FLD_NAME
    ): bool
    {
        $result = parent::row_mapper_sandbox($db_row, $load_std, $allow_usr_protect, $id_fld, $name_fld);
        if ($result) {
            if (array_key_exists(self::FLD_TYPE, $db_row)) {
                $this->type_id = $db_row[self::FLD_TYPE];
            }
            if (array_key_exists(sql_db::FLD_CODE_ID, $db_row)) {
                $this->code_id = $db_row[sql_db::FLD_CODE_ID];
            }
        }
        return $result;
    }


    /*
     * set and get
     */

    /**
     * set the most used object vars with one set statement
     * @param int $id mainly for test creation the database id of the view
     * @param string $name mainly for test creation the name of the view
     * @param string $type_code_id the code id of the predefined view type
     */
    function set(int $id = 0, string $name = '', string $type_code_id = ''): void
    {
        parent::set($id, $name);

        if ($type_code_id != '') {
            $this->set_type($type_code_id);
        }
    }

    /**
     * set the view type
     *
     * @param string $type_code_id the code id that should be added to this view
     * @return void
     */
    function set_type(string $type_code_id): void
    {
        global $view_types;
        $this->type_id = $view_types->id($type_code_id);
    }

    /**
     * @return string a unique name for the view that is also used in the code
     */
    function code_id(): string
    {
        if ($this->code_id == null) {
            return '';
        } else {
            return $this->code_id;
        }
    }

    /**
     * @return string the description of the view
     */
    function comment(): string
    {
        if ($this->description == null) {
            return '';
        } else {
            return $this->description;
        }
    }

    /**
     * @return component_link_list the list of the linked components of this view
     */
    function component_link_list(): component_link_list
    {
        return $this->cmp_lnk_lst;
    }

    /**
     * @return int the number of linked components of this view
     */
    function component_links(): int
    {
        return $this->component_link_list()->count();
    }


    /*
     * get preloaded information
     */

    /**
     * @return string the name of the view type
     */
    function type_name(): string
    {
        global $view_types;
        return $view_types->name($this->type_id);
    }

    /**
     * get the view type code id based on the database id set in this object
     * @return string
     */
    private function type_code_id(): string
    {
        global $view_types;
        return $view_types->code_id($this->type_id);
    }

    /**
     * get the view type database id based on the code id
     * @param string $code_id
     * @return int
     */
    private function type_id_by_code_id(string $code_id): int
    {
        global $view_types;
        return $view_types->id($code_id);
    }


    /*
     * cast
     */

    /**
     * @return view_api frontend API object filled with the relevant data of this object
     */
    function api_obj(): view_api
    {
        $api_obj = new view_api();

        parent::fill_api_obj($api_obj);

        $api_obj->set_type_id($this->type_id);
        $api_obj->code_id = $this->code_id;
        $api_obj->description = $this->description;
        if ($this->cmp_lnk_lst != null) {
            $api_obj->components = $this->cmp_lnk_lst->api_obj();
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
     * loading
     */

    /**
     * create the SQL to load the default view always by the id
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_standard_sql(sql_creator $sc, string $class = self::class): sql_par
    {
        $sc->set_type($class);
        $sc->set_fields(array_merge(
            self::FLD_NAMES,
            self::FLD_NAMES_USR,
            self::FLD_NAMES_NUM_USR,
            array(user::FLD_ID)
        ));

        return parent::load_standard_sql($sc, $class);
    }

    /**
     * load the view parameters for all users including the user id to know the owner of the standard
     * @param sql_par|null $qp placeholder to align the function parameters with the parent
     * @param string $class the name of this class to be delivered to the parent function
     * @return bool true if the standard view has been loaded
     */
    function load_standard(?sql_par $qp = null, string $class = self::class): bool
    {

        global $db_con;
        $qp = $this->load_standard_sql($db_con->sql_creator());
        $result = parent::load_standard($qp, $class);

        if ($result) {
            $result = $this->load_owner();
        }
        return $result;
    }

    /**
     * create the common part of an SQL statement to retrieve the parameters of a view from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name extension to make the query name unique
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name, string $class = self::class): sql_par
    {
        $sc->set_type($class);
        return parent::load_sql_fields(
            $sc, $query_name,
            self::FLD_NAMES,
            self::FLD_NAMES_USR,
            self::FLD_NAMES_NUM_USR
        );
    }

    /**
     * create an SQL statement to retrieve a view by code id from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $code_id the code id of the view
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_code_id(sql_creator $sc, string $code_id, string $class): sql_par
    {
        $qp = $this->load_sql($sc, 'code_id', $class);
        $sc->add_where(sql_db::FLD_CODE_ID, $code_id);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a view by the phrase from the database
     * TODO include user_view_term_links into the selection
     * TODO take the usage into account for the selection of the view
     *
     * @param sql_creator $sc with the target db_type set
     * @param term $trm the code id of the view
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_term(sql_creator $sc, term $trm, string $class = self::class): sql_par
    {
        $qp = $this->load_sql($sc, 'term', $class);
        $sc->set_join_fields(
            view_term_link::FLD_NAMES,
            sql_db::TBL_VIEW_TERM_LINK,
            view::FLD_ID,
            view::FLD_ID);
        $sc->add_where(sql_db::LNK_TBL . '.' . term::FLD_ID, $trm->id());
        //$sc->set_order(component_link::FLD_ORDER_NBR, '', sql_db::LNK_TBL);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * load a view by code id
     * @param string $code_id the code id of the view
     * @param string $class the name of the child class from where the call has been triggered
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_code_id(string $code_id, string $class = self::class): int
    {
        global $db_con;

        log_debug($code_id);
        $qp = $this->load_sql_by_code_id($db_con->sql_creator(), $code_id, $class);
        return parent::load($qp);
    }

    /**
     * load the suggested view for a phrase
     * @param phrase $phr the phrase for which the most often used view should be loaded
     * @return bool true if at least one view is found
     */
    function load_by_phrase(phrase $phr): bool
    {
        global $db_con;

        $qp = $this->load_sql_by_term($db_con, $phr->term());
        $db_view = $db_con->get1($qp);
        return $this->row_mapper_sandbox($db_view);
    }

    /**
     * load the suggested view for a term
     * @param term $trm the word, triple, verb or formula for which the most often used view should be loaded
     * @return bool true if at least one view is found
     */
    function load_by_term(term $trm): bool
    {
        global $db_con;

        $qp = $this->load_sql_by_term($db_con, $trm);
        $db_view = $db_con->get1($qp);
        return $this->row_mapper_sandbox($db_view);
    }

    /**
     * create an SQL statement to retrieve all view components of a view
     * TODO check if it can be combined with load_sql from component_link_list
     * TODO make the order user specific
     *
     * @param sql_db $db_con as a function parameter for unit testing
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_components_sql(sql_db $db_con): sql_par
    {
        $qp = parent::load_sql_obj_vars($db_con->sql_creator(), component::class);
        if ($this->id != 0) {
            $qp->name .= 'view_id';
        } elseif ($this->name != '') {
            $qp->name .= sql_db::FLD_NAME;
        } else {
            log_err("Either the database ID (" . $this->id . "), the view name (" . $this->name . ") or the code_id (" . $this->code_id . ")  must be set to load the components of a view.", "view->load_components_sql");
        }

        $db_con->set_type(sql_db::TBL_COMPONENT_LINK);
        $db_con->set_usr($this->user()->id());
        $db_con->set_name($qp->name);
        $db_con->set_fields(component_link::FLD_NAMES);
        $db_con->set_usr_num_fields(component_link::FLD_NAMES_NUM_USR);
        $db_con->set_join_usr_fields(
            array_merge(component::FLD_NAMES_USR, array(component::FLD_NAME)),
            sql_db::TBL_COMPONENT);
        $db_con->set_join_usr_num_fields(
            component::FLD_NAMES_NUM_USR,
            sql_db::TBL_COMPONENT);
        $db_con->add_par(sql_par_type::INT, $this->id);
        $db_con->set_order(component_link::FLD_ORDER_NBR);
        $qp->sql = $db_con->select_by_field_list(array(view::FLD_ID));
        $qp->par = $db_con->get_par();

        return $qp;
    }

    /**
     * load all parts of this view for this user
     * @param sql_db|null $db_con_given the database connection as a parameter for the initial load of the system views
     * @return bool false if a technical error on loading has occurred; an empty list if fine and returns true
     */
    function load_components(?sql_db $db_con_given = null): bool
    {
        global $db_con;

        log_debug($this->dsp_id());

        $db_con_used = $db_con_given;
        if ($db_con_used == null) {
            $db_con_used = $db_con;
        }

        $this->cmp_lnk_lst = new component_link_list($this->user());
        $result = $this->cmp_lnk_lst->load_by_view_with_components($this, $db_con_used);
        log_debug($this->cmp_lnk_lst->count() . ' loaded for ' . $this->dsp_id());

        return $result;
    }

    /**
     * just set the class name for the user sandbox function
     * load a view object by database id
     * @param int $id the id of the view
     * @param string $class the view class name
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_id(int $id, string $class = self::class): int
    {
        return parent::load_by_id($id, $class);
    }

    /**
     * just set the class name for the user sandbox function
     * load a view object by name
     * @param string $name the name view
     * @param string $class the view class name
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_name(string $name, string $class = self::class): int
    {
        return parent::load_by_name($name, $class);
    }


    /*
     * load helper
     */

    function name_field(): string
    {
        return self::FLD_NAME;
    }

    function all_sandbox_fields(): array
    {
        return self::ALL_SANDBOX_FLD_NAMES;
    }

    /**
     * @param sql_creator $sc the sql creator without view joins
     * @return sql_creator the sql creator with the view join set
     */
    function set_join(sql_creator $sc): sql_creator
    {
        $sc->set_join_fields(view::FLD_NAMES, sql_db::TBL_VIEW);
        $sc->set_join_usr_fields(view::FLD_NAMES_USR, sql_db::TBL_VIEW);
        $sc->set_join_usr_num_fields(view::FLD_NAMES_NUM_USR, sql_db::TBL_VIEW);
        return $sc;
    }


    /*
     * display
     */

    /**
     * return the html code to display a view name with the link
     */
    function name_linked($wrd, $back): string
    {

        $result = '<a href="/http/view_edit.php?id=' . $this->id;
        if (isset($wrd)) {
            $result .= '&word=' . $wrd->id;
        }
        $result .= '&back=' . $back . '">' . $this->name . '</a>';

        return $result;
    }

    /**
     * display the unique id fields
     */
    function name_dsp(): string
    {
        return '"' . $this->name . '"';
    }


    /*
     * components
     */

    /**
     * add a new component to this view
     * @param component $cmp the view component that should be added
     * @param int|null $pos is set the position, where the
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return string an empty string if the new component link has been saved to the database
     *                or the message that should be shown to the user
     */
    function add_cmp(component $cmp, ?int $pos = null, object $test_obj = null): string
    {
        $result = '';

        // if no position is requested add the component at the end
        if ($pos == null) {
            $pos = $this->component_links() + 1;
        }
        if ($pos != null) {
            if ($this->cmp_lnk_lst == null) {
                $this->cmp_lnk_lst = new component_link_list($this->user());
            }
            if (!$test_obj) {
                $cmp->save();
                $cmp_lnk = new component_link($this->user());
                $cmp_lnk->view()->set_id($this->id());
                $cmp_lnk->component()->set_id($cmp->id());
                $cmp_lnk->order_nbr = $pos;
                $cmp_lnk->pos_type_id = 0;
                $cmp_lnk->pos_code = '';
                $cmp_lnk->save();
                $this->cmp_lnk_lst->add($cmp_lnk->id(), $this, $cmp, $pos);
            } else {
                $this->cmp_lnk_lst->add($pos, $this, $cmp, $pos);
            }
        }
        // compare with the database links and save the differences

        return $result;
    }

    /**
     * move one view component one place up
     * in case of an error the error message is returned
     * if everything is fine an empty string is returned
     */
    function entry_up($component_id): string
    {
        $result = '';
        // check the all minimal input parameters
        if ($component_id <= 0) {
            log_err("The view component id must be given to move it.", "view->entry_up");
        } else {
            $cmp = new component($this->user());
            $cmp->load_by_id($component_id);
            $cmp_lnk = new component_link($this->user());
            $cmp_lnk->load_by_link($this, $cmp);
            $result .= $cmp_lnk->move_up();
        }
        return $result;
    }

    /**
     * move one view component one place down
     */
    function entry_down($component_id): string
    {
        $result = '';
        // check the all minimal input parameters
        if ($component_id <= 0) {
            log_err("The view component id must be given to move it.", "view->entry_down");
        } else {
            $cmp = new component($this->user());
            $cmp->load_by_id($component_id);
            $cmp_lnk = new component_link($this->user());
            $cmp_lnk->load_by_link($this, $cmp);
            $result .= $cmp_lnk->move_down();
        }
        return $result;
    }

    /*
     * im- and export
     */

    /**
     * import a view from a JSON object
     * the code_id is not expected to be included in the im- and export because the internal views are not expected to be included in the ex- and import
     *
     * @param array $in_ex_json an array with the data of the json object
     * @param object|null $test_obj if not null the unit testing object
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_obj(array $in_ex_json, object $test_obj = null): user_message
    {
        log_debug();

        // reset the all parameters for the word object but keep the user
        $usr = $this->user();
        $this->reset();
        $this->set_user($usr);
        $result = parent::import_obj($in_ex_json, $test_obj);

        // first save the parameters of the view itself
        foreach ($in_ex_json as $key => $value) {

            if ($key == exp_obj::FLD_TYPE) {
                if ($value != '') {
                    $type_id = $this->type_id_by_code_id($value);
                    if ($type_id == type_list::CODE_ID_NOT_FOUND) {
                        $result->add_message('view type "' . $value . '" not found');
                    } else {
                        $this->type_id = $type_id;
                    }
                }
            }
            if ($key == exp_obj::FLD_CODE_ID) {
                if ($value != '') {
                    if ($this->user()->is_admin() or $this->user()->is_system()) {
                        $this->code_id = $value;
                    }
                }
            }
        }

        if (!$test_obj) {
            if ($this->name == '') {
                $result->add_message('name in view missing');
            } else {
                $result->add_message($this->save());

                if ($result->is_ok()) {
                    // TODO save also the links
                    //$dsp_lnk = new component_link();
                    log_debug($this->dsp_id());
                }
            }
        }

        // after saving (or remembering) add the view components
        foreach ($in_ex_json as $key => $value) {
            if ($key == self::FLD_COMPONENT) {
                $json_lst = $value;
                $cmp_pos = 1;
                foreach ($json_lst as $json_cmp) {
                    $cmp = new component($usr);
                    // if for the component only the position and name is defined
                    // do not overwrite an existing component
                    // instead just add the existing component
                    if (count($json_cmp) == 2
                        and array_key_exists(exp_obj::FLD_POSITION, $json_cmp)
                        and array_key_exists(exp_obj::FLD_NAME, $json_cmp)) {
                        $cmp->load_by_name($json_cmp[exp_obj::FLD_NAME]);
                        // if the component does not jet exist
                        // nevertheless create the component
                        // but send a warning message
                        if ($cmp->id() <= 0) {
                            log_warning('Component ' . $json_cmp[exp_obj::FLD_NAME]
                                . ' has not yet been created, but is supposed to be at position '
                                . $json_cmp[exp_obj::FLD_POSITION] . ' of a view ');
                            $cmp->import_obj($json_cmp, $test_obj);
                        }
                    } else {
                        $cmp->import_obj($json_cmp, $test_obj);
                    }
                    // on import first add all view components to the view object and save them all at once
                    $result->add_message($this->add_cmp($cmp, $cmp_pos, $test_obj));
                    $cmp_pos++;
                }
            }
        }

        if (!$result->is_ok()) {
            $lib = new library();
            $result->add_message(' when importing ' . $lib->dsp_array($in_ex_json));
        }

        return $result;
    }

    /**
     * export mapper: create an object for the export
     */
    function export_obj(bool $do_load = true): exp_obj
    {
        log_debug($this->dsp_id());
        $result = new view_exp();

        // add the view parameters
        $result->name = $this->name();
        $result->description = $this->description;
        $result->type = $this->type_code_id();

        // add the view components used
        if ($do_load) {
            $this->load_components();
        }
        if ($this->cmp_lnk_lst != null) {
            foreach ($this->cmp_lnk_lst->lst() as $lnk) {
                $result->components[] = $lnk->export_obj();
            }
        }

        log_debug(json_encode($result));
        return $result;
    }


    /*
     * logic functions
     */

    /**
     * true if the view is part of the view element list
     */
    function is_in_list($dsp_lst): bool
    {
        $result = false;

        foreach ($dsp_lst as $dsp_id) {
            log_debug($dsp_id . ' = ' . $this->id . '?');
            if ($dsp_id == $this->id) {
                $result = true;
            }
        }

        return $result;
    }


    /*
     * save
     */

    /**
     * create an SQL statement to retrieve the user changes of the current view
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_user_changes(sql_creator $sc, string $class = self::class): sql_par
    {
        $sc->set_type($class, true);
        $sc->set_fields(array_merge(
            self::FLD_NAMES_USR,
            self::FLD_NAMES_NUM_USR
        ));
        return parent::load_sql_user_changes($sc, $class);
    }

    /**
     * set the update parameters for the view code_id (only allowed for admin)
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param view $db_rec the database record before the saving
     * @param view $std_rec the database record defined as standard because it is used by most users
     * @return string if not empty the message that should be shown to the user
     */
    function save_field_code_id(sql_db $db_con, view $db_rec, view $std_rec): string
    {
        $result = '';
        // special case: do not remove a code id
        if ($this->code_id != '') {
            if ($db_rec->code_id <> $this->code_id) {
                $log = $this->log_upd();
                $log->old_value = $db_rec->code_id;
                $log->new_value = $this->code_id;
                $log->std_value = $std_rec->code_id;
                $log->row_id = $this->id;
                $log->set_field(sql_db::FLD_CODE_ID);
                $result = $this->save_field_user($db_con, $log);
            }
        }
        return $result;
    }

    /**
     * save all updated view fields excluding the name, because already done when adding a view
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param view|sandbox $db_rec the database record before the saving
     * @param view|sandbox $std_rec the database record defined as standard because it is used by most users
     * @return string if not empty the message that should be shown to the user
     */
    function save_fields(sql_db $db_con, view|sandbox $db_rec, view|sandbox $std_rec): string
    {
        $result = parent::save_fields_typed($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_code_id($db_con, $db_rec, $std_rec);
        log_debug('all fields for ' . $this->dsp_id() . ' has been saved');
        return $result;
    }

    /**
     * check if a view name is a reserved system view and if return a message to the user
     *
     * @return string
     */
    protected function check_preserved(): string
    {
        global $usr;

        $result = '';
        // system user are always allowed to add system views
        if (!$usr->is_system()) {
            if (in_array($this->name, view_api::RESERVED_VIEWS)) {
                // the admin user needs to add the read test word during initial load
                if (!$usr->is_admin()) {
                    $result = '"' . $this->name() . '" is a reserved view name for system testing. Please use another name';
                }
            }
        }
        return $result;
    }

    /**
     * delete the view component links of linked to this view
     * @return user_message of the link removal and if needed the error messages that should be shown to the user
     */
    function del_links(): user_message
    {
        $result = new user_message();

        // collect all component links where this view is used
        $lnk_lst = new component_link_list($this->user());
        $lnk_lst->load_by_view($this);

        // if there are links, delete if not used by anybody else than the user who has requested the deletion
        // or exclude the links for the user if the link is used by someone else
        if (!$lnk_lst->is_empty()) {
            $result->add($lnk_lst->del());
        }

        return $result;
    }

}
