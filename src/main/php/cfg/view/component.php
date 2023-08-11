<?php

/*

    model/view/component.php - a single display object like a headline or a table
    -----------------------------

    TODO rename to component (to always use a single word)

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
include_once WEB_VIEW_PATH . 'view_cmp_old.php';

use api\component_api;
use cfg\db\sql_creator;
use cfg\db\sql_par_type;
use model\export\exp_obj;
use model\export\view_cmp_exp;
use html\component_dsp_old;

class component extends sandbox_typed
{

    /*
     * database link
     */

    // the database and JSON object field names used only for view components links
    const FLD_ID = 'component_id';
    const FLD_NAME = 'component_name';
    const FLD_DESCRIPTION = 'description';
    const FLD_TYPE = 'component_type_id';
    const FLD_POSITION = 'position';
    const FLD_UI_MSG_ID = 'ui_msg_code_id';
    const FLD_ROW_PHRASE = 'word_id_row';
    const FLD_COL_PHRASE = 'word_id_col';
    const FLD_COL2_PHRASE = 'word_id_col2';
    const FLD_LINK_TYPE = 'link_type_id';

    // all database field names excluding the id
    const FLD_NAMES = array(
        sql_db::FLD_CODE_ID,
        self::FLD_UI_MSG_ID
    );
    // list of the user specific database field names
    const FLD_NAMES_USR = array(
        sandbox_named::FLD_DESCRIPTION
    );
    // list of the user specific database field names
    const FLD_NAMES_NUM_USR = array(
        self::FLD_TYPE,
        self::FLD_ROW_PHRASE,
        self::FLD_LINK_TYPE,
        formula::FLD_ID,
        self::FLD_COL_PHRASE,
        self::FLD_COL2_PHRASE,
        sandbox::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );
    // all database field names excluding the id used to identify if there are some user specific changes
    const ALL_SANDBOX_FLD_NAMES = array(
        self::FLD_NAME,
        sandbox_named::FLD_DESCRIPTION,
        self::FLD_TYPE,
        self::FLD_ROW_PHRASE,
        self::FLD_LINK_TYPE,
        formula::FLD_ID,
        self::FLD_COL_PHRASE,
        self::FLD_COL2_PHRASE,
        sandbox::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );


    /*
     * object vars
     */

    // database fields additional to the user sandbox fields for the view component
    public ?int $order_nbr = null;          // the position in the linked view
    public ?int $word_id_row = null;        // if the view component uses a related word tree this is the start node
    //                                         e.g. for "company" the start node could be "cash flow statement" to show the cash flow for any company
    public ?int $link_type_id = null;       // the word link type used to build the word tree started with the $start_word_id
    public ?int $formula_id = null;         // to select a formula (no used case at the moment)
    public ?int $word_id_col = null;        // for a table to defined which columns should be used (if not defined by the calling word)
    public ?int $word_id_col2 = null;       // for a table to defined second columns layer or the second axis in case of a chart
    //                                         e.g. for a "company cash flow statement" the "col word" could be "Year"
    //                                              "col2 word" could be "Quarter" to show the Quarters between the year upon request
    public ?string $code_id = null;         // to select a specific system component by the program code
    //                                         the code id cannot be changed by the user
    //                                         so this field is not part of the table user_components
    public ?string $ui_msg_code_id = null;  // to select a user interface language specific message
    //                                         e.g. "add word" or "Wort zufügen"
    //                                         the code id cannot be changed by the user
    //                                         so this field is not part of the table user_components

    // database fields repeated from the component link for a easy to use in memory view object
    public ?int $pos_type = null;           // the position in the linked view

    // linked fields
    public ?object $obj = null;             // the object that should be shown to the user
    public ?word $wrd_row = null;           // the word object for $word_id_row
    public ?word $wrd_col = null;           // the word object for $word_id_col
    public ?word $wrd_col2 = null;          // the word object for $word_id_col2
    public ?formula $frm = null;            // the formula object for $formula_id

    /*
     * construct and map
     */

    /**
     * define the settings for this view component object
     * @param user $usr the user who requested to see this view
     */
    function __construct(user $usr)
    {
        parent::__construct($usr);
        $this->obj_name = sql_db::TBL_COMPONENT;

        $this->rename_can_switch = UI_CAN_CHANGE_VIEW_COMPONENT_NAME;
    }

    /**
     * clear the view component object values
     * @return void
     */
    function reset(): void
    {
        parent::reset();

        $this->order_nbr = null;
        $this->type_id = null;
        $this->word_id_row = null;
        $this->link_type_id = null;
        $this->formula_id = null;
        $this->word_id_col = null;
        $this->word_id_col2 = null;
        $this->wrd_row = null;
        $this->wrd_col = null;
        $this->wrd_col2 = null;
        $this->frm = null;
        $this->code_id = '';
        $this->ui_msg_code_id = '';
    }

    /*
     * api and display object mapper
     */

    /**
     * @return component_api the view component frontend api object
     */
    function api_obj(): object
    {
        $api_obj = new component_api();
        $this->fill_api_obj($api_obj);
        return $api_obj;
    }

    /**
     * @returns string the api json message for the object as a string
     */
    function api_json(): string
    {
        return $this->api_obj()->get_json();
    }

    /**
     * @return component_dsp_old the view component object with the html creation functions
     */
    function dsp_obj(): object
    {
        global $component_types;

        $dsp_obj = new component_dsp_old();

        parent::fill_dsp_obj($dsp_obj);

        $dsp_obj->set_type_id($this->type_id);
        //$dsp_obj->set_type($component_types->get_by_id($this->type_id)->code_id());

        return $dsp_obj;
    }

    /*
     * database mapper
     */

    /**
     * map the database fields to the object fields
     *
     * @param array|null $db_row with the data directly from the database
     * @param bool $load_std true if only the standard user sandbox object ist loaded
     * @param bool $allow_usr_protect false for using the standard protection settings for the default object used for all users
     * @param string $id_fld the name of the id field as defined in this child and given to the parent
     * @param string $name_fld the name of the name field as defined in this child class
     * @return bool true if the view component is loaded and valid
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
            if (array_key_exists(sql_db::FLD_CODE_ID, $db_row)) {
                $this->code_id = $db_row[sql_db::FLD_CODE_ID];
            }
            if (array_key_exists(self::FLD_UI_MSG_ID, $db_row)) {
                $this->ui_msg_code_id = $db_row[self::FLD_UI_MSG_ID];
            }
            if (array_key_exists(self::FLD_TYPE, $db_row)) {
                $this->type_id = $db_row[self::FLD_TYPE];
            }
            if (array_key_exists(self::FLD_ROW_PHRASE, $db_row)) {
                $this->word_id_row = $db_row[self::FLD_ROW_PHRASE];
            }
            if (array_key_exists(self::FLD_LINK_TYPE, $db_row)) {
                $this->link_type_id = $db_row[self::FLD_LINK_TYPE];
            }
            if (array_key_exists(formula::FLD_ID, $db_row)) {
                $this->formula_id = $db_row[formula::FLD_ID];
            }
            if (array_key_exists(self::FLD_COL_PHRASE, $db_row)) {
                $this->word_id_col = $db_row[self::FLD_COL_PHRASE];
            }
            if (array_key_exists(self::FLD_COL2_PHRASE, $db_row)) {
                $this->word_id_col2 = $db_row[self::FLD_COL2_PHRASE];
            }
        }
        return $result;
    }


    /*
     * set and get
     */

    /**
     * set the most used view component vars with one set statement
     * @param int $id mainly for test creation the database id of the view component
     * @param string $name mainly for test creation the name of the view component
     * @param string $type_code_id the code id of the predefined view component type
     */
    function set(int $id = 0, string $name = '', string $type_code_id = ''): void
    {
        parent::set($id, $name);

        if ($type_code_id != '') {
            $this->set_type($type_code_id);
        }
    }

    /**
     * set the view component type
     *
     * @param string $type_code_id the code id that should be added to this view component
     * @return void
     */
    function set_type(string $type_code_id): void
    {
        global $component_types;
        $this->type_id = $component_types->id($type_code_id);
    }


    /*
     * get preloaded information
     */

    /**
     * @return string the name of the view type
     */
    function type_name(): string
    {
        global $component_types;
        return $component_types->name($this->type_id);
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
        $sc->set_type(self::class);
        $sc->set_fields(array_merge(
            self::FLD_NAMES,
            self::FLD_NAMES_USR,
            self::FLD_NAMES_NUM_USR,
            array(user::FLD_ID)
        ));

        return parent::load_standard_sql($sc, $class);
    }

    /**
     * load the view component parameters for all users
     * @param sql_par|null $qp placeholder to align the function parameters with the parent
     * @param string $class the name of this class to be delivered to the parent function
     * @return bool true if the standard view component has been loaded
     */
    function load_standard(?sql_par $qp = null, string $class = self::class): bool
    {
        global $db_con;
        $qp = $this->load_standard_sql($db_con->sql_creator());
        $result = parent::load_standard($qp, $class);

        if ($result) {
            $result = $this->load_owner();
        }
        if ($result) {
            $result = $this->load_phrases();
        }
        return $result;
    }

    /**
     * create the common part of an SQL statement to retrieve the parameters of a view component from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name extension to make the query name unique
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    protected function load_sql(sql_creator $sc, string $query_name, string $class = self::class): sql_par
    {
        return parent::load_sql_usr_num($sc, $this, $query_name);
    }

    /**
     * load the related word and formula objects
     * @return bool false if a technical error on loading has occurred; an empty list if fine and returns true
     */
    function load_phrases(): bool
    {
        $result = true;
        $this->load_wrd_row();
        $this->load_wrd_col();
        $this->load_wrd_col2();
        $this->load_formula();
        log_debug('done for ' . $this->dsp_id());
        return $result;
    }

    //
    function load_wrd_row(): string
    {
        $result = '';
        if ($this->word_id_row > 0) {
            $wrd_row = new word($this->user());
            $wrd_row->load_by_id($this->word_id_row, word::class);
            $this->wrd_row = $wrd_row;
            $result = $wrd_row->name();
        }
        return $result;
    }

    /**
     * used for a table component
     * load the word object that defines the column names
     * e.g. "year" to display the yearly values
     * @return string the name of the loaded word
     */
    function load_wrd_col(): string
    {
        $result = '';
        if ($this->word_id_col > 0) {
            $wrd_col = new word($this->user());
            $wrd_col->load_by_id($this->word_id_col, word::class);
            $this->wrd_col = $wrd_col;
            $result = $wrd_col->name();
        }
        return $result;
    }

    //
    function load_wrd_col2(): string
    {
        $result = '';
        if ($this->word_id_col2 > 0) {
            $wrd_col2 = new word($this->user());
            $wrd_col2->load_by_id($this->word_id_col2, word::class);
            $this->wrd_col2 = $wrd_col2;
            $result = $wrd_col2->name();
        }
        return $result;
    }

    // load the related formula and returns the name of the formula
    function load_formula(): string
    {
        $result = '';
        if ($this->formula_id > 0) {
            $frm = new formula($this->user());
            $frm->load_by_id($this->formula_id, formula::class);
            $this->frm = $frm;
            $result = $frm->name();
        }
        return $result;
    }

    /**
     * just set the class name for the user sandbox function
     * load a view component object by database id
     * @param int $id the id of the view component
     * @param string $class the view component class name
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_id(int $id, string $class = self::class): int
    {
        $id = parent::load_by_id($id, $class);
        if ($this->id > 0) {
            $this->load_phrases();
        }
        return $id;
    }

    /**
     * just set the class name for the user sandbox function
     * load a view component object by name
     * @param string $name the name view component
     * @param string $class the view component class name
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_name(string $name, string $class = self::class): int
    {
        $id = parent::load_by_name($name, $class);
        if ($this->id > 0) {
            $this->load_phrases();
        }
        return $id;
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
     * TODO use a set_join function for all not simple sql joins
     * @param sql_creator $sc the sql creator without component joins
     * @return sql_creator the sql creator with the components join set
     */
    function set_join(sql_creator $sc): sql_creator
    {
        $sc->set_join_fields(component::FLD_NAMES, sql_db::TBL_COMPONENT);
        $sc->set_join_usr_fields(component::FLD_NAMES_USR, sql_db::TBL_COMPONENT);
        $sc->set_join_usr_num_fields(component::FLD_NAMES_NUM_USR, sql_db::TBL_COMPONENT);
        return $sc;
    }

    /**
     * get the view component type database id based on the code id
     * @param string $code_id
     * @return int
     */
    private function type_id_by_code_id(string $code_id): int
    {
        global $component_types;
        return $component_types->id($code_id);
    }

    /**
     * list of all view ids that are directly assigned to this view component
     */
    function assign_dsp_ids(): array
    {
        $result = array();

        if ($this->id > 0 and $this->user() != null) {
            $lst = new component_link_list($this->user());
            $lst->load_by_component($this);
            $result = $lst->view_ids();
        } else {
            log_err("The user id must be set to list the component links.", "component->assign_dsp_ids");
        }

        return $result;
    }

    /**
     * return the html code to display a view name with the link
     */
    function name_linked(string $back = ''): string
    {

        return '<a href="/http/component_edit.php?id=' . $this->id . '&back=' . $back . '">' . $this->name . '</a>';
    }


    /*
     * im- and export
     */

    /**
     *  */
    /**
     * import a view component from a JSON object
     * @param array $in_ex_json an array with the data of the json object
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_obj(array $in_ex_json, object $test_obj = null): user_message
    {
        $result = parent::import_obj($in_ex_json, $test_obj);

        foreach ($in_ex_json as $key => $value) {

            if ($key == self::FLD_POSITION) {
                $this->order_nbr = $value;
            }
            if ($key == exp_obj::FLD_TYPE) {
                if ($value != '') {
                    if ($this->user()->is_admin() or $this->user()->is_system()) {
                        $this->type_id = $this->type_id_by_code_id($value);
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
            if ($key == exp_obj::FLD_UI_MSG_ID) {
                if ($value != '') {
                    if ($this->user()->is_admin() or $this->user()->is_system()) {
                        $this->ui_msg_code_id = $value;
                    }
                }
            }
        }

        if (!$test_obj) {
            if ($result->is_ok()) {
                $result->add_message($this->save());
            } else {
                log_debug('not saved because ' . $result->get_last_message());
            }
        }

        return $result;
    }

    /**
     * fill the component export object to create a json
     * which does not include the internal database id
     */
    function export_obj(bool $do_load = true): exp_obj
    {
        log_debug('component->export_obj ' . $this->dsp_id());
        $result = new view_cmp_exp();

        // add the component parameters
        $this->load_phrases();
        if ($this->order_nbr >= 0) {
            $result->position = $this->order_nbr;
        }
        $result->name = $this->name();
        if ($this->type_name() <> '') {
            $result->type = $this->type_name();
        }
        $result->code_id = $this->code_id;
        $result->ui_msg_code_id = $this->ui_msg_code_id;
        if (isset($this->wrd_row)) {
            $result->row = $this->wrd_row->name();
        }
        if (isset($this->wrd_col)) {
            $result->column = $this->wrd_col->name();
        }
        if (isset($this->wrd_col2)) {
            $result->column2 = $this->wrd_col2->name();
        }
        if ($this->description <> '') {
            $result->description = $this->description;
        }

        log_debug(json_encode($result));
        return $result;
    }

    /*
    display functions
    */

    function name(): string
    {
        return $this->name;
    }

// not used at the moment
    /*  private function link_type_name() {
        if ($this->type_id > 0) {
          $sql = "SELECT type_name
                    FROM component_types
                   WHERE component_type_id = ".$this->type_id.";";
          $db_con = new mysql;
          $db_con->usr_id = $this->user()->id();
          $db_type = $db_con->get1($sql);
          $this->type_name = $db_type[sql_db::FLD_TYPE_NAME];
        }
        return $this->type_name;
      } */

    /*
      to link and unlink a component
    */

    /**
     * returns the next free order number for a new view component
     */
    function next_nbr(int $view_id): int
    {
        log_debug('component->next_nbr for view "' . $view_id . '"');

        global $db_con;

        $result = 1;
        if ($view_id == '' or $view_id == Null or $view_id == 0) {
            log_err('Cannot get the next position, because the view_id is not set', 'component->next_nbr');
        } else {
            $vcl = new component_link($this->user());
            $result = $vcl->max_pos_by_view($view_id);

            // if nothing is found, assume one as the next free number
            if ($result <= 0) {
                $result = 1;
            } else {
                $result++;
            }
        }

        log_debug($result);
        return $result;
    }

    // set the log entry parameters for a value update
    function log_link($dsp): bool
    {
        log_debug('component->log_link ' . $this->dsp_id() . ' to "' . $dsp->name . '"  for user ' . $this->user()->id());
        $log = new change_log_link($this->user());
        $log->action = change_log_action::ADD;
        $log->set_table(change_log_table::VIEW_LINK);
        $log->new_from = clone $this;
        $log->new_to = clone $dsp;
        $log->row_id = $this->id;
        $result = $log->add_link_ref();

        log_debug('logged ' . $log->id());
        return $result;
    }

    // set the log entry parameters to unlink a display component ($cmp) from a view ($dsp)
    function log_unlink($dsp): bool
    {
        log_debug($this->dsp_id() . ' from "' . $dsp->name . '" for user ' . $this->user()->id());
        $log = new change_log_link($this->user());
        $log->action = change_log_action::DELETE;
        $log->set_table(change_log_table::VIEW_LINK);
        $log->old_from = clone $this;
        $log->old_to = clone $dsp;
        $log->row_id = $this->id;
        $result = $log->add_link_ref();

        log_debug('logged ' . $log->id());
        return $result;
    }

// link a view component to a view
    function link($dsp, $order_nbr): string
    {
        log_debug($this->dsp_id() . ' to ' . $dsp->dsp_id() . ' at pos ' . $order_nbr);

        $dsp_lnk = new component_link($this->user());
        $dsp_lnk->fob = $dsp;
        $dsp_lnk->tob = $this;
        $dsp_lnk->order_nbr = $order_nbr;
        $dsp_lnk->pos_type_id = 1; // to be reviewed
        return $dsp_lnk->save();
    }

    // remove a view component from a view
    // TODO check if the view component is not linked anywhere else
    // and if yes, delete the view component after confirmation
    function unlink($dsp): string
    {
        $result = '';

        if (isset($dsp) and $this->user() != null) {
            log_debug($this->dsp_id() . ' from "' . $dsp->name . '" (' . $dsp->id . ')');
            $dsp_lnk = new component_link($this->user());
            $dsp_lnk->load_by_link($dsp, $this);
            $msg = $dsp_lnk->del();
            $result .= $msg->get_last_message();
        } else {
            $result .= log_err("Cannot unlink view component, because view is not set.", "component.php");
        }

        return $result;
    }

    // create a database record to save user specific settings for this component
    protected function add_usr_cfg(string $class = self::class): bool
    {
        global $db_con;
        $result = true;

        if (!$this->has_usr_cfg()) {
            log_debug('for "' . $this->dsp_id() . ' und user ' . $this->user()->name);

            // check again if there ist not yet a record
            $db_con->set_type(sql_db::TBL_COMPONENT, true);
            $qp = new sql_par(self::class);
            $qp->name = 'view_cmp_del_usr_cfg_if';
            $db_con->set_name($qp->name);
            $db_con->set_usr($this->user()->id());
            $db_con->set_fields(array(component::FLD_ID));
            $db_con->set_where_std($this->id);
            $qp->sql = $db_con->select_by_set_id();
            $qp->par = $db_con->get_par();
            $db_row = $db_con->get1($qp);
            if ($db_row != null) {
                $this->usr_cfg_id = $db_row[component::FLD_ID];
            }
            if (!$this->has_usr_cfg()) {
                // create an entry in the user sandbox
                $db_con->set_type(sql_db::TBL_USER_PREFIX . sql_db::TBL_COMPONENT);
                $log_id = $db_con->insert(array(component::FLD_ID, user::FLD_ID), array($this->id, $this->user()->id()));
                if ($log_id <= 0) {
                    log_err('Insert of user_component failed.');
                    $result = false;
                } else {
                    // TODO check if correct in all cases
                    $this->usr_cfg_id = $this->id;
                    $result = true;
                }
            }
        }
        return $result;
    }

    /**
     * create an SQL statement to retrieve the user changes of the current view component
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_user_changes(sql_creator $sc, string $class = self::class): sql_par
    {
        $sc->set_type(self::class, true);
        $sc->set_fields(array_merge(
            self::FLD_NAMES_USR,
            self::FLD_NAMES_NUM_USR
        ));
        return parent::load_sql_user_changes($sc, $class);
    }

    /**
     * set the update parameters for the component code id
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param component $db_rec the view component as saved in the database before the update
     * @returns string any message that should be shown to the user or a empty string if everything is fine
     */
    function save_field_code_id(sql_db $db_con, component $db_rec): string
    {
        $result = '';
        if ($this->code_id <> $db_rec->code_id) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->code_id;
            $log->new_value = $this->code_id;
            $log->row_id = $this->id;
            $log->set_field(sql_db::FLD_CODE_ID);
            $result = $this->save_field($db_con, $log);
        }
        return $result;
    }

    /**
     * set the update parameters for the component user interface message id
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param component $db_rec the view component as saved in the database before the update
     * @returns string any message that should be shown to the user or a empty string if everything is fine
     */
    function save_field_ui_msg_id(sql_db $db_con, component $db_rec): string
    {
        $result = '';
        if ($this->ui_msg_code_id <> $db_rec->ui_msg_code_id) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->ui_msg_code_id;
            $log->new_value = $this->ui_msg_code_id;
            $log->row_id = $this->id;
            $log->set_field(self::FLD_UI_MSG_ID);
            $result = $this->save_field($db_con, $log);
        }
        return $result;
    }

    /**
     * set the update parameters for the word row
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param component $db_rec the view component as saved in the database before the update
     * @param component $std_rec the default parameter used for this view component
     * @returns string any message that should be shown to the user or a empty string if everything is fine
     */
    function save_field_wrd_row(sql_db $db_con, component $db_rec, component $std_rec): string
    {
        $result = '';
        if ($db_rec->word_id_row <> $this->word_id_row) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->load_wrd_row();
            $log->old_id = $db_rec->word_id_row;
            $log->new_value = $this->load_wrd_row();
            $log->new_id = $this->word_id_row;
            $log->std_value = $std_rec->load_wrd_row();
            $log->std_id = $std_rec->word_id_row;
            $log->row_id = $this->id;
            $log->set_field(self::FLD_ROW_PHRASE);
            $result = $this->save_field_user($db_con, $log);
        }
        return $result;
    }

    /**
     * set the update parameters for the word col
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param component $db_rec the view component as saved in the database before the update
     * @param component $std_rec the default parameter used for this view component
     * @returns string any message that should be shown to the user or a empty string if everything is fine
     */
    function save_field_wrd_col(sql_db $db_con, component $db_rec, component $std_rec): string
    {
        $result = '';
        if ($db_rec->word_id_col <> $this->word_id_col) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->load_wrd_col();
            $log->old_id = $db_rec->word_id_col;
            $log->new_value = $this->load_wrd_col();
            $log->new_id = $this->word_id_col;
            $log->std_value = $std_rec->load_wrd_col();
            $log->std_id = $std_rec->word_id_col;
            $log->row_id = $this->id;
            $log->set_field(self::FLD_COL_PHRASE);
            $result = $this->save_field_user($db_con, $log);
        }
        return $result;
    }

    /**
     * set the update parameters for the word col2
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param component $db_rec the view component as saved in the database before the update
     * @param component $std_rec the default parameter used for this view component
     * @returns string any message that should be shown to the user or a empty string if everything is fine
     */
    function save_field_wrd_col2(sql_db $db_con, component $db_rec, component $std_rec): string
    {
        $result = '';
        if ($db_rec->word_id_col2 <> $this->word_id_col2) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->load_wrd_col2();
            $log->old_id = $db_rec->word_id_col2;
            $log->new_value = $this->load_wrd_col2();
            $log->new_id = $this->word_id_col2;
            $log->std_value = $std_rec->load_wrd_col2();
            $log->std_id = $std_rec->word_id_col2;
            $log->row_id = $this->id;
            $log->set_field(self::FLD_COL2_PHRASE);
            $result = $this->save_field_user($db_con, $log);
        }
        return $result;
    }

    /**
     * set the update parameters for the formula
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param component $db_rec the view component as saved in the database before the update
     * @param component $std_rec the default parameter used for this view component
     * @returns string any message that should be shown to the user or an empty string if everything is fine
     */
    function save_field_formula(sql_db $db_con, component $db_rec, component $std_rec): string
    {
        $result = '';
        if ($db_rec->formula_id <> $this->formula_id) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->load_formula();
            $log->old_id = $db_rec->formula_id;
            $log->new_value = $this->load_formula();
            $log->new_id = $this->formula_id;
            $log->std_value = $std_rec->load_formula();
            $log->std_id = $std_rec->formula_id;
            $log->row_id = $this->id;
            $log->set_field(formula::FLD_ID);
            $result = $this->save_field_user($db_con, $log);
        }
        return $result;
    }

    /**
     * save all updated component fields excluding the name, because already done when adding a component
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param component|sandbox $db_rec the view component as saved in the database before the update
     * @param component|sandbox $std_rec the default parameter used for this view component
     * @returns string any message that should be shown to the user or a empty string if everything is fine
     */
    function save_fields(sql_db $db_con, component|sandbox $db_rec, component|sandbox $std_rec): string
    {
        $result = parent::save_fields_typed($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_code_id($db_con, $db_rec);
        $result .= $this->save_field_ui_msg_id($db_con, $db_rec);
        $result .= $this->save_field_wrd_row($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_wrd_col($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_wrd_col2($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_formula($db_con, $db_rec, $std_rec);
        log_debug('all fields for ' . $this->dsp_id() . ' has been saved');
        return $result;
    }

    /**
     * delete the view component links of linked to this view component
     * @return user_message of the link removal and if needed the error messages that should be shown to the user
     */
    function del_links(): user_message
    {
        $result = new user_message();

        // collect all component links where this component is used
        $lnk_lst = new component_link_list($this->user());
        $lnk_lst->load_by_component($this);

        // if there are links, delete if not used by anybody else than the user who has requested the deletion
        // or exclude the links for the user if the link is used by someone else
        if (!$lnk_lst->is_empty()) {
            $result->add($lnk_lst->del());
        }

        return $result;
    }

}

