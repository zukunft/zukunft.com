<?php

/*

  view.php - the main display object
  --------
  
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
  
  Copyright (c) 1995-2022 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

use api\view_api;
use export\view_exp;
use export\exp_obj;

class view extends user_sandbox_named
{
    /*
     * database link
     */

    // the database and JSON object field names used only for views
    const FLD_ID = 'view_id';
    const FLD_NAME = 'view_name';
    const FLD_TYPE = 'view_type_id';
    const FLD_CODE_ID = 'code_id';
    const FLD_COMMENT = 'comment';
    // the JSON object field names
    const FLD_COMPONENT = 'view_components';

    // all database field names excluding the id
    const FLD_NAMES = array(
        self::FLD_CODE_ID
    );
    // list of the user specific database field names
    const FLD_NAMES_USR = array(
        self::FLD_COMMENT
    );
    // list of the user specific database field names
    const FLD_NAMES_NUM_USR = array(
        self::FLD_TYPE,
        self::FLD_EXCLUDED,
        user_sandbox::FLD_SHARE,
        user_sandbox::FLD_PROTECT
    );

    /*
     * code links
     */

    // list of the view used by the program that are never supposed to be changed
    const START = "start";
    const WORD = "word";
    const WORD_ADD = "word_add";
    const WORD_EDIT = "word_edit";
    const WORD_DEL = "word_del";
    const WORD_FIND = "word_find";
    const VALUE_DISPLAY = "value";
    const VALUE_ADD = "value_add";
    const VALUE_EDIT = "value_edit";
    const VALUE_DEL = "value_del";
    const FORMULA_ADD = "formula_add";
    const FORMULA_EDIT = "formula_edit";
    const FORMULA_DEL = "formula_del";
    const FORMULA_EXPLAIN = "formula_explain";
    const FORMULA_TEST = "formula_test";
    const SOURCE_ADD = "source_add";
    const SOURCE_EDIT = "source_edit";
    const SOURCE_DEL = "source_del";
    const VERBS = "verbs";
    const VERB_ADD = "verb_add";
    const VERB_EDIT = "verb_edit";
    const VERB_DEL = "verb_del";
    const LINK_ADD = "triple_add";
    const LINK_EDIT = "triple_edit";
    const LINK_DEL = "triple_del";
    const USER = "user";
    const ERR_LOG = "error_log";
    const ERR_UPD = "error_update";
    const IMPORT = "import";
    // views to edit views
    const ADD = "view_add";
    const EDIT = "view_edit";
    const DEL = "view_del";
    const COMPONENT_ADD = "view_entry_add";
    const COMPONENT_EDIT = "view_entry_edit";
    const COMPONENT_DEL = "view_entry_del";

    /*
     * for system testing
     */

    // persevered view names for unit and integration tests (TN means TEST NAME)
    const TN_ADD = 'System Test View';
    const TN_RENAMED = 'System Test View Renamed';
    const TN_COMPLETE = 'System Test View Complete';
    const TN_TABLE = 'System Test View Table';

    // array of view names that used for testing and remove them after the test
    const RESERVED_VIEWS = array(
        self::TN_ADD,
        self::TN_RENAMED,
        self::TN_COMPLETE,
        self::TN_TABLE
    );

    // array of test view names create before the test
    const TEST_VIEWS = array(
        self::TN_COMPLETE,
        self::TN_TABLE
    );

    /*
     * object vars
     */

    // database fields additional to the user sandbox fields for the view component
    public ?string $comment = null; // the view description that is shown as a mouseover explain to the user
    public ?string $code_id = null;   // to select internal predefined views

    // in memory only fields
    public ?array $cmp_lst = null;  // array of the view component objects in correct order
    public ?string $back = null;    // the calling stack

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
        $this->obj_name = DB_TYPE_VIEW;

        $this->rename_can_switch = UI_CAN_CHANGE_VIEW_NAME;
    }

    function reset(): void
    {
        $this->id = null;
        $this->usr_cfg_id = null;
        $this->owner_id = null;
        $this->excluded = null;

        $this->name = '';

        $this->comment = '';
        $this->type_id = null;
        $this->code_id = '';

        $this->type_name = '';
        $this->cmp_lst = null;
        $this->back = null;
    }

    // TODO check if there is any case where the user fields should not be set
    /**
     * map the database fields to the object fields
     *
     * @param array $db_row with the data directly from the database
     * @param bool $map_usr_fields false for using the standard protection settings for the default view used for all users
     * @param string $id_fld the name of the id field as defined in this child and given to the parent
     * @return bool true if the view is loaded and valid
     */
    function row_mapper(array $db_row, bool $map_usr_fields = true, string $id_fld = self::FLD_ID): bool
    {
        $result = parent::row_mapper($db_row, $map_usr_fields, self::FLD_ID);
        if ($result) {
            $this->name = $db_row[self::FLD_NAME];
            $this->comment = $db_row[self::FLD_COMMENT];
            $this->type_id = $db_row[self::FLD_TYPE];
            $this->code_id = $db_row[self::FLD_CODE_ID];
        }
        return $result;
    }

    /*
     * casting objects
     */

    /**
     * @return view_api frontend API object filled with the relevant data of this object
     */
    function api_obj(): view_api
    {
        $api_obj = new view_api();
        parent::fill_api_obj($api_obj);
        return $api_obj;
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
    function load_standard_sql(sql_db $db_con, string $class = ''): sql_par
    {
        $db_con->set_type(DB_TYPE_VIEW);
        $db_con->set_fields(array_merge(
            self::FLD_NAMES,
            self::FLD_NAMES_USR,
            self::FLD_NAMES_NUM_USR,
            array(sql_db::FLD_USER_ID)
        ));

        return parent::load_standard_sql($db_con, self::class);
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
        $result = parent::load_standard($qp, self::class);

        if ($result) {
            $result = $this->load_owner();
        }
        return $result;
    }

    /**
     * create an SQL statement to retrieve the parameters of a view from the database
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_db $db_con, string $class = ''): sql_par
    {
        $qp = parent::load_sql($db_con, self::class);
        if ($this->id != 0) {
            $qp->name .= 'id';
        } elseif ($this->code_id != '') {
            $qp->name .= sql_db::FLD_CODE_ID;
        } elseif ($this->name != '') {
            $qp->name .= 'name';
        } else {
            log_err('Either the id, code_id or name must be set to get a view');
        }

        $db_con->set_type(DB_TYPE_VIEW);
        $db_con->set_usr($this->usr->id);
        $db_con->set_fields(self::FLD_NAMES);
        $db_con->set_usr_fields(self::FLD_NAMES_USR);
        $db_con->set_usr_num_fields(self::FLD_NAMES_NUM_USR);
        $db_con->set_where_std($this->id, $this->name, $this->code_id);
        $qp->sql = $db_con->select_by_id();
        $qp->par = $db_con->get_par();

        return $qp;
    }

    /**
     * load the missing view parameters from the database
     * based either on the id or the view name
     */
    function load(): bool
    {

        global $db_con;
        $result = false;

        // check the all minimal input parameters
        if (!isset($this->usr)) {
            log_err("The user id must be set to load a view.", "view->load");
        } elseif ($this->id <= 0 and $this->code_id == '' and $this->name == '') {
            log_err("Either the database ID (" . $this->id . "), the name (" . $this->name . ") or the code_id (" . $this->code_id . ") and the user (" . $this->usr->id . ") must be set to load a view.", "view->load");
        } else {

            $sql = $this->load_sql($db_con)->sql;

            if ($db_con->get_where() <> '') {
                $db_view = $db_con->get1_old($sql);
                $this->row_mapper($db_view);
                if ($this->id > 0) {
                    log_debug($this->dsp_id());
                    $result = true;
                }
            }
        }
        return $result;
    }

    /**
     * create an SQL statement to retrieve all view components of a view
     * TODO check if it can be combined with load_sql from view_cmp_link_list
     * TODO make the order user specific
     *
     * @param sql_db $db_con as a function parameter for unit testing
     * @return string the SQL statement base on the parameters set in $this
     */
    function load_components_sql(sql_db $db_con): sql_par
    {
        $qp = parent::load_sql($db_con, 'view_components');
        if ($this->id != 0) {
            $qp->name .= 'view_id';
        } elseif ($this->name != '') {
            $qp->name .= 'name';
        } else {
            log_err("Either the database ID (" . $this->id . "), the view name (" . $this->name . ") or the code_id (" . $this->code_id . ")  must be set to load the components of a view.", "view->load_components_sql");
        }

        $db_con->set_type(DB_TYPE_VIEW_COMPONENT_LINK);
        $db_con->set_usr($this->usr->id);
        $db_con->set_name($qp->name);
        $db_con->set_fields(view_cmp_link::FLD_NAMES);
        $db_con->set_usr_num_fields(view_cmp_link::FLD_NAMES_NUM_USR);
        $db_con->set_join_fields(
            view_cmp::FLD_NAMES,
            DB_TYPE_VIEW_COMPONENT);
        $db_con->set_join_usr_fields(
            array_merge(view_cmp::FLD_NAMES_USR, array(view_cmp::FLD_NAME)),
            DB_TYPE_VIEW_COMPONENT);
        $db_con->set_join_usr_num_fields(
            view_cmp::FLD_NAMES_NUM_USR,
            DB_TYPE_VIEW_COMPONENT);
        $db_con->add_par(sql_db::PAR_INT, $this->id);
        $db_con->set_order(view_cmp_link::FLD_ORDER_NBR);
        $qp->sql = $db_con->select_by_field_list(array(view::FLD_ID));
        $qp->par = $db_con->get_par();

        return $qp;
    }

    /**
     * load all parts of this view for this user
     * @return bool false if a technical error on loading has occurred; an empty list if fine and returns true
     */
    function load_components(): bool
    {
        log_debug($this->dsp_id());

        global $db_con;
        $result = true;

        $db_con->usr_id = $this->usr->id;
        $qp = $this->load_components_sql($db_con);
        $db_lst = $db_con->get($qp);
        $this->cmp_lst = array();
        if ($db_lst != null) {
            foreach ($db_lst as $db_entry) {
                // this is only for the view of the active user, so a direct exclude can be done
                if ((is_null($db_entry[self::FLD_EXCLUDED]) or $db_entry[self::FLD_EXCLUDED] == 0)
                    and (is_null($db_entry[self::FLD_EXCLUDED.'2']) or $db_entry[self::FLD_EXCLUDED.'2'] == 0)) {
                    $new_entry = new view_cmp_dsp_old($this->usr);
                    $new_entry->id = $db_entry[view_cmp::FLD_ID];
                    $new_entry->owner_id = $db_entry[user_sandbox::FLD_USER];
                    $new_entry->order_nbr = $db_entry[view_cmp_link::FLD_ORDER_NBR];
                    $new_entry->name = $db_entry[view_cmp::FLD_NAME];
                    $new_entry->word_id_row = $db_entry[view_cmp::FLD_ROW_PHRASE.'2'];
                    $new_entry->link_type_id = $db_entry[view_cmp::FLD_LINK_TYPE.'2'];
                    $new_entry->type_id = $db_entry[view_cmp::FLD_TYPE.'2'];
                    $new_entry->formula_id = $db_entry[formula::FLD_ID.'2'];
                    $new_entry->word_id_col = $db_entry[view_cmp::FLD_COL_PHRASE.'2'];
                    $new_entry->word_id_col2 = $db_entry[view_cmp::FLD_COL2_PHRASE.'2'];
                    if (!$new_entry->load_phrases()) {
                        $result = false;
                    }
                    $this->cmp_lst[] = $new_entry;
                }
            }
        }
        log_debug(dsp_count($this->cmp_lst) . ' loaded for ' . $this->dsp_id());

        return $result;
    }

    /*
    object display functions
    */

    // TODO review (get the object instead)
    private function type_name()
    {

        global $db_con;

        if ($this->type_id > 0) {
            $sql = "SELECT type_name, description, code_id
                FROM view_types
               WHERE view_type_id = " . $this->type_id . ";";
            //$db_con = new mysql;
            $db_con->usr_id = $this->usr->id;
            $db_type = $db_con->get1_old($sql);
            $this->type_name = $db_type[sql_db::FLD_TYPE_NAME];
        }
        return $this->type_name;
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
    function name(): string
    {
        return '"' . $this->name . '"';
    }

    /*
    component functions
    */

    /**
     * add a new component to this view
     * @param view_cmp $cmp the view component that should be added
     * @param int|null $pos is set the position, where the
     * @return bool true if the new component link has been saved to the database
     */
    function add_cmp(view_cmp $cmp, ?int $pos = null, bool $do_save = true): bool
    {
        $result = false;
        if ($pos != null) {
            $this->cmp_lst[] = $cmp;
            if (count($this->cmp_lst) != $cmp->order_nbr) {
                log_err('View component "' . $cmp->name . '" has been expected to be at position ' . $cmp->order_nbr . ' in ' . $this->name . ', but it is at position ' . dsp_count($this->cmp_lst));
            } else {
                if ($do_save) {
                    $cmp->save();
                    $cmp_lnk = new view_cmp_link($this->usr);
                    $cmp_lnk->dsp->id = $this->id;
                    $cmp_lnk->cmp->id = $cmp->id;
                    $cmp_lnk->order_nbr = $cmp->order_nbr;
                    $cmp_lnk->pos_type_id = 0;
                    $cmp_lnk->pos_code = '';
                    $cmp_lnk->save();
                }
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
    function entry_up($view_component_id): string
    {
        $result = '';
        // check the all minimal input parameters
        if ($view_component_id <= 0) {
            log_err("The view component id must be given to move it.", "view->entry_up");
        } else {
            $cmp = new view_cmp_dsp_old($this->usr);
            $cmp->id = $view_component_id;
            $cmp->load();
            $cmp_lnk = new view_cmp_link($this->usr);
            $cmp_lnk->fob = $this;
            $cmp_lnk->tob = $cmp;
            $cmp_lnk->load();
            $result .= $cmp_lnk->move_up();
        }
        return $result;
    }

    /**
     * move one view component one place down
     */
    function entry_down($view_component_id): string
    {
        $result = '';
        // check the all minimal input parameters
        if ($view_component_id <= 0) {
            log_err("The view component id must be given to move it.", "view->entry_down");
        } else {
            $cmp = new view_cmp_dsp_old($this->usr);
            $cmp->id = $view_component_id;
            $cmp->load();
            $cmp_lnk = new view_cmp_link($this->usr);
            $cmp_lnk->fob = $this;
            $cmp_lnk->tob = $cmp;
            $cmp_lnk->load();
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
        $sql = sql_lst_usr("view", $this->usr);
        $call = '/http/view.php?words=' . $wrd_id;
        $field = 'new_id';

        //$db_con = New mysql;
        $db_con->usr_id = $this->usr->id;
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
    import & export functions
    */

    /**
     * import a view from a JSON object
     * the code_id is not expected to be included in the im- and export because the internal views are not expected to be included in the ex- and import
     *
     * @param array $json_obj an array with the data of the json object
     * @param bool $do_save can be set to false for unit testing
     * @return string an empty string if the import has been successfully saved to the database or the message that should be shown to the user
     */
    function import_obj(array $json_obj, bool $do_save = true): string
    {
        log_debug();
        $result = '';

        // reset the all parameters for the word object but keep the user
        $usr = $this->usr;
        $this->reset();
        $this->usr = $usr;

        // first save the parameters of the view itself
        foreach ($json_obj as $key => $value) {

            if ($key == exp_obj::FLD_NAME) {
                $this->name = $value;
            }
            if ($key == exp_obj::FLD_TYPE) {
                if ($value != '') {
                    $this->type_id = $this->type_id_by_code_id($value);
                }
            }
            if ($key == exp_obj::FLD_DESCRIPTION) {
                $this->comment = $value;
            }
            if ($key == user_type::FLD_CODE_ID) {
                if ($this->usr->is_admin()) {
                    $this->code_id = $value;
                }
            }
        }

        if ($do_save) {
            if ($this->name == '') {
                log_err("Name in view missing");
            } else {
                $result .= $this->save();

                if ($result == '') {
                    // TODO save also the links
                    //$dsp_lnk = new view_component_link();
                    log_debug($this->dsp_id());
                }
            }
        } else {
            log_debug($result);
        }

        // after saving (or remembering) add the view components
        foreach ($json_obj as $key => $value) {
            if ($key == self::FLD_COMPONENT) {
                $json_lst = $value;
                $cmp_pos = 1;
                foreach ($json_lst as $json_cmp) {
                    $cmp = new view_cmp($usr);
                    $cmp->import_obj($json_cmp, $do_save);
                    // on import first add all view components to the view object and save them all at once
                    $this->add_cmp($cmp, $cmp_pos, $do_save);
                    $cmp_pos++;
                }
            }
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
        $result->name = $this->name;
        $result->description = $this->comment;
        $result->type = $this->type_code_id();

        // add the view components used
        if ($do_load) {
            $this->load_components();
        }
        if ($this->cmp_lst != null) {
            foreach ($this->cmp_lst as $cmp) {
                $result->view_components[] = $cmp->export_obj();
            }
        }

        log_debug(json_encode($result));
        return $result;
    }

    /*
    logic functions
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
    saving functions
    */

    /**
     * create a database record to save user specific settings for this view
     */
    function add_usr_cfg(): bool
    {
        global $db_con;
        $result = true;

        log_debug($this->dsp_id());

        if (!$this->has_usr_cfg()) {

            // check again if there ist not yet a record
            $db_con->set_type(DB_TYPE_VIEW, true);
            $db_con->set_usr($this->usr->id);
            $db_con->set_where_std($this->id);
            $sql = $db_con->select_by_id();
            $db_row = $db_con->get1_old($sql);
            if ($db_row != null) {
                $this->usr_cfg_id = $db_row[self::FLD_ID];
            }
            if (!$this->has_usr_cfg()) {
                // create an entry in the user sandbox
                $db_con->set_type(DB_TYPE_USER_PREFIX . DB_TYPE_VIEW);
                $log_id = $db_con->insert(array(self::FLD_ID, user_sandbox::FLD_USER), array($this->id, $this->usr->id));
                if ($log_id <= 0) {
                    log_err('Insert of user_view failed.');
                    $result = false;
                } else {
                    $result = true;
                }
            }
        }
        return $result;
    }

    /**
     * check if the database record for the user specific settings can be removed
     */
    function del_usr_cfg_if_not_needed(): bool
    {
        log_debug('pre check for "' . $this->dsp_id() . ' und user ' . $this->usr->name);

        global $db_con;
        $result = true;

        //if ($this->has_usr_cfg) {

        // check again if there ist not yet a record
        $sql = "SELECT view_id,
                     view_name,
                     comment,
                     view_type_id,
                     excluded
                FROM user_views
               WHERE view_id = " . $this->id . " 
                 AND user_id = " . $this->usr->id . ";";
        //$db_con = New mysql;
        $db_con->usr_id = $this->usr->id;
        $usr_cfg = $db_con->get1_old($sql);
        log_debug('check for "' . $this->dsp_id() . ' und user ' . $this->usr->name . ' with (' . $sql . ')');
        if ($usr_cfg[self::FLD_ID] > 0) {
            if ($usr_cfg[self::FLD_COMMENT] == ''
                and $usr_cfg[self::FLD_TYPE] == Null
                and $usr_cfg[self::FLD_EXCLUDED] == Null) {
                // delete the entry in the user sandbox
                log_debug('any more for "' . $this->dsp_id() . ' und user ' . $this->usr->name);
                $result = $this->del_usr_cfg_exe($db_con);
            }
        }
        //}
        return $result;
    }

    /**
     * set the update parameters for the view comment
     */
    function save_field_comment(sql_db $db_con, user_sandbox $db_rec, user_sandbox $std_rec): string
    {
        $result = '';
        if ($db_rec->comment <> $this->comment) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->comment;
            $log->new_value = $this->comment;
            $log->std_value = $std_rec->comment;
            $log->row_id = $this->id;
            $log->field = self::FLD_COMMENT;
            $result = $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    /**
     * set the update parameters for the view code_id (only allowed for admin)
     */
    function save_field_code_id(sql_db $db_con, user_sandbox $db_rec, user_sandbox $std_rec): string
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
                $log->field = self::FLD_CODE_ID;
                $result = $this->save_field_do($db_con, $log);
            }
        }
        return $result;
    }

    /**
     * set the update parameters for the word type
     */
    function save_field_type(sql_db $db_con, user_sandbox $db_rec, user_sandbox $std_rec): string
    {
        $result = '';
        if ($db_rec->type_id <> $this->type_id) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->type_name();
            $log->old_id = $db_rec->type_id;
            $log->new_value = $this->type_name();
            $log->new_id = $this->type_id;
            $log->std_value = $std_rec->type_name();
            $log->std_id = $std_rec->type_id;
            $log->row_id = $this->id;
            $log->field = self::FLD_TYPE;
            $result = $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    /**
     * save all updated view fields excluding the name, because already done when adding a view
     */
    function save_fields(sql_db $db_con, user_sandbox $db_rec, user_sandbox $std_rec): string
    {
        $result = $this->save_field_comment($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_type($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_excluded($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_code_id($db_con, $db_rec, $std_rec);
        log_debug($this->dsp_id() . ' has been saved');
        return $result;
    }

    /**
     * delete the view component links of linked to this view
     */
    function del_links(): user_message
    {
        $result = new user_message();

        // collect all component links where this view is used
        $lnk_lst = new view_cmp_link_list($this->usr);
        $lnk_lst->load_by_view($this);

        // if there are links, delete if not used by anybody else than the user who has requested the deletion
        // or exclude the links for the user if the link is used by someone else
        if (!$lnk_lst->empty()) {
            $result->add($lnk_lst->del());
        }

        return $result;
    }

}
