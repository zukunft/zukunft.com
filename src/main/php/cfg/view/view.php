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

include_once WEB_VIEW_PATH . 'view.php';
include_once MODEL_VIEW_PATH . 'component.php';
include_once MODEL_VIEW_PATH . 'component_list.php';
include_once MODEL_VIEW_PATH . 'component_link.php';
include_once MODEL_VIEW_PATH . 'view_cmp_dsp.php'; // TODO move to web namespace
include_once SERVICE_EXPORT_PATH . 'view_exp.php';
include_once SERVICE_EXPORT_PATH . 'view_cmp_exp.php';

use api\view_api;
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
    const FLD_DESCRIPTION = 'description';
    // the JSON object field names
    const FLD_COMPONENT = 'components';

    // all database field names excluding the id
    const FLD_NAMES = array(
        sql_db::FLD_CODE_ID
    );
    // list of the user specific database field names
    const FLD_NAMES_USR = array(
        self::FLD_DESCRIPTION
    );
    // list of the user specific database field names
    const FLD_NAMES_NUM_USR = array(
        self::FLD_TYPE,
        self::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );
    // all database field names excluding the id used to identify if there are some user specific changes
    const ALL_SANDBOX_FLD_NAMES = array(
        self::FLD_DESCRIPTION,
        self::FLD_TYPE,
        self::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );


    /*
     * object vars
     */

    // database fields additional to the user sandbox fields for the view component
    public ?string $code_id = null;   // to select internal predefined views

    // in memory only fields
    public ?component_list $cmp_lst;  // array of the view component objects in correct order
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
        parent::__construct($usr);
        $this->obj_name = sql_db::TBL_VIEW;

        $this->rename_can_switch = UI_CAN_CHANGE_VIEW_NAME;
        $this->cmp_lst = null;
        $this->cmp_lnk_lst = null;
    }

    function reset(): void
    {
        parent::reset();

        $this->type_id = null;
        $this->code_id = '';

        $this->cmp_lst = null;
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
     * @return bool true if the view is loaded and valid
     */
    function row_mapper_sandbox(
        ?array $db_row,
        bool   $load_std = false,
        bool   $allow_usr_protect = true,
        string $id_fld = self::FLD_ID): bool
    {
        $result = parent::row_mapper_sandbox($db_row, $load_std, $allow_usr_protect, self::FLD_ID);
        if ($result) {
            $this->set_name($db_row[self::FLD_NAME]);
            $this->description = $db_row[self::FLD_DESCRIPTION];
            $this->type_id = $db_row[self::FLD_TYPE];
            $this->code_id = $db_row[sql_db::FLD_CODE_ID];
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
     * @return component_list the list of components of this view
     */
    function cmp_lst(): component_list
    {
        return $this->cmp_lst;
    }

    /**
     * @return int the number of components of this view
     */
    public function components(): int
    {
        return $this->cmp_lst()->count();
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
        if ($this->cmp_lst != null) {
            $api_obj->components = $this->cmp_lst->api_obj();
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
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_standard_sql(sql_db $db_con, string $class = self::class): sql_par
    {
        $db_con->set_type(sql_db::TBL_VIEW);
        $db_con->set_fields(array_merge(
            self::FLD_NAMES,
            self::FLD_NAMES_USR,
            self::FLD_NAMES_NUM_USR,
            array(user::FLD_ID)
        ));

        return parent::load_standard_sql($db_con, $class);
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
        $qp = $this->load_standard_sql($db_con);
        $result = parent::load_standard($qp, $class);

        if ($result) {
            $result = $this->load_owner();
        }
        return $result;
    }

    /**
     * create the common part of an SQL statement to retrieve the parameters of a view from the database
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    protected function load_sql(sql_db $db_con, string $query_name, string $class = self::class): sql_par
    {
        $db_con->set_type(sql_db::TBL_VIEW);
        return parent::load_sql_fields(
            $db_con, $query_name, $class,
            self::FLD_NAMES,
            self::FLD_NAMES_USR,
            self::FLD_NAMES_NUM_USR
        );
    }

    /**
     * create an SQL statement to retrieve a view by code id from the database
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $code_id the code id of the view
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_code_id(sql_db $db_con, string $code_id, string $class): sql_par
    {
        $qp = $this->load_sql($db_con, 'code_id', $class);
        $db_con->add_par(sql_db::PAR_TEXT, $code_id);
        $qp->sql = $db_con->select_by_code_id();
        $qp->par = $db_con->get_par();

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
        $qp = $this->load_sql_by_code_id($db_con, $code_id, $class);
        return parent::load($qp);
    }

    // TODO review and add a unit test
    function load_by_phrase_sql(sql_db $db_con, phrase $phr): sql_par
    {
        $qp = parent::load_sql_obj_vars($db_con, self::class);

        // sql to get the id of the most often used view
        $db_con_tmp = new sql_db();
        $db_con_tmp->set_type(sql_db::TBL_VIEW);
        $db_con->set_name($qp->name);
        $db_con_tmp->set_usr($this->user()->id());
        $db_con_tmp->set_where_std($phr->id());
        $sql = "SELECT u.view_id, count(u.user_id) AS users
                       FROM words w 
                  LEFT JOIN user_words u ON u.word_id = w.word_id 
                      WHERE w.word_id = " . $db_con_tmp->par_name() . "
                   GROUP BY u.view_id
                      LIMIT 1";

        // load all parameters of the view with one sql statement
        $db_con->set_type(sql_db::TBL_VIEW);
        $db_con->set_usr($this->user()->id());
        $db_con->set_fields(self::FLD_NAMES);
        $db_con->set_usr_fields(self::FLD_NAMES_USR);
        $db_con->set_usr_num_fields(self::FLD_NAMES_NUM_USR);
        //$db_con->set_from($sql);
        //$qp->sql = $db_con->select_by_sub_id();
        $qp->par = $db_con->get_par();

        return $qp;
    }

    /**
     * load the suggested view for a phrase
     * @param phrase $phr the phrase for which the most often used view should be loaded
     * @return bool true if at least one view is found
     */
    function load_by_phrase(phrase $phr): bool
    {
        global $db_con;

        $qp = $this->load_by_phrase_sql($db_con, $phr);
        $db_view = $db_con->get1($qp);
        return $this->row_mapper_sandbox($db_view);
    }

    /**
     * create an SQL statement to retrieve all view components of a view
     * TODO check if it can be combined with load_sql from view_cmp_link_list
     * TODO make the order user specific
     *
     * @param sql_db $db_con as a function parameter for unit testing
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_components_sql(sql_db $db_con): sql_par
    {
        $qp = parent::load_sql_obj_vars($db_con, component::class);
        if ($this->id != 0) {
            $qp->name .= 'view_id';
        } elseif ($this->name != '') {
            $qp->name .= 'name';
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
        $db_con->add_par(sql_db::PAR_INT, $this->id);
        $db_con->set_order(component_link::FLD_ORDER_NBR);
        $qp->sql = $db_con->select_by_field_list(array(view::FLD_ID));
        $qp->par = $db_con->get_par();

        return $qp;
    }

    /**
     * load all parts of this view for this user
     * @return bool false if a technical error on loading has occurred; an empty list if fine and returns true
     */
    function load_components_old(): bool
    {
        log_debug($this->dsp_id());

        global $db_con;
        $result = true;

        $db_con->usr_id = $this->user()->id();
        $qp = $this->load_components_sql($db_con);
        $db_lst = $db_con->get($qp);
        $this->cmp_lst = new component_list($this->user());
        if ($db_lst != null) {
            foreach ($db_lst as $db_entry) {
                // this is only for the view of the active user, so a direct exclude can be done
                if ((is_null($db_entry[self::FLD_EXCLUDED]) or $db_entry[self::FLD_EXCLUDED] == 0)
                    and (is_null($db_entry[self::FLD_EXCLUDED . '2']) or $db_entry[self::FLD_EXCLUDED . '2'] == 0)) {
                    $new_entry = new component_dsp_old($this->user());
                    $new_entry->id = $db_entry[component::FLD_ID];
                    $new_entry->owner_id = $db_entry[user::FLD_ID];
                    $new_entry->order_nbr = $db_entry[component_link::FLD_ORDER_NBR];
                    $new_entry->name = $db_entry[component::FLD_NAME];
                    $new_entry->word_id_row = $db_entry[component::FLD_ROW_PHRASE . '2'];
                    $new_entry->link_type_id = $db_entry[component::FLD_LINK_TYPE . '2'];
                    $new_entry->type_id = $db_entry[component::FLD_TYPE . '2'];
                    $new_entry->formula_id = $db_entry[formula::FLD_ID . '2'];
                    $new_entry->word_id_col = $db_entry[component::FLD_COL_PHRASE . '2'];
                    $new_entry->word_id_col2 = $db_entry[component::FLD_COL2_PHRASE . '2'];
                    if (!$new_entry->load_phrases()) {
                        $result = false;
                    }
                    $this->cmp_lst->add($new_entry);
                }
            }
        }
        log_debug($this->cmp_lst->count() . ' loaded for ' . $this->dsp_id());

        return $result;
    }

    /**
     * load all parts of this view for this user
     * @return bool false if a technical error on loading has occurred; an empty list if fine and returns true
     */
    function load_components(): bool
    {
        log_debug($this->dsp_id());

        $this->cmp_lst = new component_list($this->user());
        $result = $this->cmp_lst->load_by_view_id($this->id());
        log_debug($this->cmp_lst->count() . ' loaded for ' . $this->dsp_id());

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

    function id_field(): string
    {
        return self::FLD_ID;
    }

    function name_field(): string
    {
        return self::FLD_NAME;
    }

    function all_sandbox_fields(): array
    {
        return self::ALL_SANDBOX_FLD_NAMES;
    }


    /*
     * display function
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
            $pos = $this->components() + 1;
        }
        if ($pos != null) {
            if ($this->cmp_lst == null) {
                $this->cmp_lst = new component_list($this->user());
            }
            $this->cmp_lst->add($cmp);
            if (!$test_obj) {
                $cmp->save();
                $cmp_lnk = new component_link($this->user());
                $cmp_lnk->fob->set_id($this->id());
                $cmp_lnk->tob->set_id($cmp->id());
                $cmp_lnk->order_nbr = $pos;
                $cmp_lnk->pos_type_id = 0;
                $cmp_lnk->pos_code = '';
                $cmp_lnk->save();
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
            $cmp = new component_dsp_old($this->user());
            $cmp->id = $component_id;
            $cmp->load_obj_vars();
            $cmp_lnk = new component_link($this->user());
            $cmp_lnk->fob = $this;
            $cmp_lnk->tob = $cmp;
            $cmp_lnk->load_obj_vars();
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
            $cmp = new component_dsp_old($this->user());
            $cmp->id = $component_id;
            $cmp->load_obj_vars();
            $cmp_lnk = new component_link($this->user());
            $cmp_lnk->fob = $this;
            $cmp_lnk->tob = $cmp;
            $cmp_lnk->load_obj_vars();
            $result .= $cmp_lnk->move_down();
        }
        return $result;
    }

    /**
     * create a selection page where the user can select a view that should be used for a word
     */
    function selector_page($wrd_id, $back): string
    {
        log_debug($this->id . ',' . $wrd_id);

        global $db_con;
        $result = '';

        /*
        $sql = "SELECT view_id, view_name
                  FROM views
                 WHERE code_id IS NULL
              ORDER BY view_name;";
              */
        $sql = sql_lst_usr("view", $this->user());
        $call = '/http/view.php?words=' . $wrd_id;
        $field = 'new_id';

        //$db_con = New mysql;
        $db_con->usr_id = $this->user()->id();
        $dsp_lst = $db_con->get_old($sql);
        foreach ($dsp_lst as $dsp) {
            $view_id = $dsp['id'];
            $view_name = $dsp['name'];
            if ($view_id == $this->id) {
                $result .= '<b><a href="' . $call . '&' . $field . '=' . $view_id . '">' . $view_name . '</a></b> ';
            } else {
                $result .= '<a href="' . $call . '&' . $field . '=' . $view_id . '">' . $view_name . '</a> ';
            }
            $call_edit = '/http/view_edit.php?id=' . $view_id . '&word=' . $wrd_id . '&back=' . $back;
            $result .= \html\btn_edit('design the view', $call_edit) . ' ';
            $call_del = '/http/view_del.php?id=' . $view_id . '&word=' . $wrd_id . '&back=' . $back;
            $result .= \html\btn_del('delete the view', $call_del) . ' ';
            $result .= '<br>';
        }

        log_debug('done');
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
                    $cmp->import_obj($json_cmp, $test_obj);
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
        if ($this->cmp_lst != null) {
            foreach ($this->cmp_lst->lst() as $cmp) {
                $result->components[] = $cmp->export_obj();
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
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_user_changes(sql_db $db_con, string $class = self::class): sql_par
    {
        $db_con->set_type(sql_db::TBL_VIEW);
        $db_con->set_fields(array_merge(
            self::FLD_NAMES_USR,
            self::FLD_NAMES_NUM_USR
        ));
        return parent::load_sql_user_changes($db_con, $class);
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
