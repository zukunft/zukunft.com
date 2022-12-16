<?php

/*

  view_component.php - a single display object like a headline or a table
  ------------------
  
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

use api\view_cmp_api;
use export\view_cmp_exp;
use export\exp_obj;
use html\view_cmp_dsp;

class view_cmp extends user_sandbox_named_with_type
{

    /*
     * database link
     */

    // the database and JSON object field names used only for view components links
    const FLD_ID = 'view_component_id';
    const FLD_NAME = 'view_component_name';
    const FLD_TYPE = 'view_component_type_id';
    const FLD_ROW_PHRASE = 'word_id_row';
    const FLD_COL_PHRASE = 'word_id_col';
    const FLD_COL2_PHRASE = 'word_id_col2';
    const FLD_LINK_TYPE = 'link_type_id';
    const FLD_COMMENT = 'comment';
    // the JSON object field names
    const FLD_POSITION = 'position';
    const FLD_POSITION_OLD = 'pos';

    // all database field names excluding the id
    const FLD_NAMES = array();
    // list of the user specific database field names
    const FLD_NAMES_USR = array(
        self::FLD_COMMENT
    );
    // list of the user specific database field names
    const FLD_NAMES_NUM_USR = array(
        self::FLD_TYPE,
        self::FLD_ROW_PHRASE,
        self::FLD_LINK_TYPE,
        formula::FLD_ID,
        self::FLD_COL_PHRASE,
        self::FLD_COL2_PHRASE,
        self::FLD_EXCLUDED,
        user_sandbox::FLD_SHARE,
        user_sandbox::FLD_PROTECT
    );


    /*
     * object vars
     */

    // database fields additional to the user sandbox fields for the view component
    public ?int $word_id_row = null;        // if the view component uses a related word tree this is the start node
    //                                         e.g. for "company" the start node could be "cash flow statement" to show the cash flow for any company
    public ?int $link_type_id = null;       // the word link type used to build the word tree started with the $start_word_id
    public ?int $formula_id = null;         // to select a formula (no used case at the moment)
    public ?int $word_id_col = null;        // for a table to defined which columns should be used (if not defined by the calling word)
    public ?int $word_id_col2 = null;       // for a table to defined second columns layer or the second axis in case of a chart
    //                                         e.g. for a "company cash flow statement" the "col word" could be "Year"
    //                                              "col2 word" could be "Quarter" to show the Quarters between the year upon request

    // database fields repeated from the component link for a easy to use in memory view object
    public ?int $order_nbr = null;          // the position in the linked view
    public ?int $pos_type = null;           // the position in the linked view

    // linked fields
    public ?object $obj = null;             // the object that should be shown to the user
    public ?word $wrd_row = null;           // the word object for $word_id_row
    public ?word $wrd_col = null;           // the word object for $word_id_col
    public ?word $wrd_col2 = null;          // the word object for $word_id_col2
    public ?formula $frm = null;            // the formula object for $formula_id
    public ?string $link_type_name = null;  //
    public ?string $code_id = null;         // the entry type code id
    public ?string $back = null;            // the calling stack

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
        $this->obj_name = sql_db::TBL_VIEW_COMPONENT;

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
        $this->link_type_name = '';
        $this->code_id = '';
        $this->back = null;
    }

    /*
     * api and display object mapper
     */

    /**
     * @return view_cmp_api the view component frontend api object
     */
    function api_obj(): object
    {
        $api_obj = new view_cmp_api();
        $this->fill_api_obj($api_obj);
        return $api_obj;
    }

    /**
     * @return view_cmp_dsp the view component object with the html creation functions
     */
    function dsp_obj(): object
    {
        global $view_component_types;

        $dsp_obj = new view_cmp_dsp();

        parent::fill_dsp_obj($dsp_obj);

        $dsp_obj->set_type_id($this->type_id);
        //$dsp_obj->set_type($view_component_types->get_by_id($this->type_id)->code_id());

        return $dsp_obj;
    }

    /*
     * database mapper
     */

    /**
     * map the database fields to the object fields
     *
     * @param array $db_row with the data directly from the database
     * @param bool $map_usr_fields false for using the standard protection settings for the default view component used for all users
     * @param string $id_fld the name of the id field as defined in this child and given to the parent
     * @return bool true if the view component is loaded and valid
     */
    function row_mapper(array $db_row, bool $map_usr_fields = true, string $id_fld = self::FLD_ID): bool
    {
        $result = parent::row_mapper($db_row, $map_usr_fields, self::FLD_ID);
        if ($result) {
            $this->name = $db_row[self::FLD_NAME];
            $this->description = $db_row[self::FLD_COMMENT];
            $this->type_id = $db_row[self::FLD_TYPE];
            $this->word_id_row = $db_row[self::FLD_ROW_PHRASE];
            $this->link_type_id = $db_row[self::FLD_LINK_TYPE];
            $this->formula_id = $db_row[formula::FLD_ID];
            $this->word_id_col = $db_row[self::FLD_COL_PHRASE];
            $this->word_id_col2 = $db_row[self::FLD_COL2_PHRASE];
        }
        return $result;
    }

    /*
     * get and set functions
     */

    /**
     * set the most used view component vars with one set statement
     * @param int $id mainly for test creation the database id of the view component
     * @param string $name mainly for test creation the name of the view component
     * @param string $type_code_id the code id of the predefined view component type
     */
    public function set(int $id = 0, string $name = '', string $type_code_id = ''): void
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
        $this->type_id = cl(db_cl::VIEW_COMPONENT_TYPE, $type_code_id);
    }


    /*
     * get preloaded information
     */

    /**
     * @return string the name of the view type
     */
    public function type_name(): string
    {
        global $view_component_types;
        return $view_component_types->name($this->type_id);
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
        $db_con->set_type(sql_db::TBL_VIEW_COMPONENT);
        $db_con->set_fields(array_merge(
            self::FLD_NAMES,
            self::FLD_NAMES_USR,
            self::FLD_NAMES_NUM_USR,
            array(sql_db::FLD_USER_ID)
        ));

        return parent::load_standard_sql($db_con, $class);
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
        $qp = $this->load_standard_sql($db_con);
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
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    protected function load_sql(sql_db $db_con, string $query_name, string $class = self::class): sql_par
    {
        $qp = parent::load_sql_obj_vars($db_con, $class);
        $qp->name .= $query_name;

        $db_con->set_type(sql_db::TBL_VIEW_COMPONENT);
        $db_con->set_name($qp->name);
        $db_con->set_usr($this->user()->id);
        $db_con->set_usr_fields(self::FLD_NAMES_USR);
        $db_con->set_usr_num_fields(self::FLD_NAMES_NUM_USR);

        return $qp;
    }

    /**
     * create an SQL statement to retrieve the parameters of a view component from the database
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_obj_vars(sql_db $db_con, string $class = self::class): sql_par
    {
        $qp = parent::load_sql_obj_vars($db_con, $class);
        if ($this->id != 0) {
            $qp->name .= 'id';
        } elseif ($this->name != '') {
            $qp->name .= 'name';
        } else {
            log_err('Either the id, code_id or name must be set to get a view');
        }

        $db_con->set_type(sql_db::TBL_VIEW_COMPONENT);
        $db_con->set_name($qp->name);
        $db_con->set_usr($this->user()->id);
        $db_con->set_usr_fields(self::FLD_NAMES_USR);
        $db_con->set_usr_num_fields(self::FLD_NAMES_NUM_USR);
        if ($this->id != 0) {
            $db_con->add_par(sql_db::PAR_INT, $this->id);
            $qp->sql = $db_con->select_by_set_id();
        } elseif ($this->name != '') {
            $db_con->add_par(sql_db::PAR_TEXT, $this->name);
            $qp->sql = $db_con->select_by_set_name();
        } else {
            log_err('Either the id or name must be set to get a named user sandbox object');
        }
        $qp->par = $db_con->get_par();

        return $qp;
    }

    // load the missing view component parameters from the database
    function load_obj_vars(): bool
    {
        log_debug('view_component->load');

        global $db_con;
        $result = false;

        // check the minimal input parameters
        if (!$this->user()->is_set()) {
            log_err("The user id must be set to load a view component.", "view_component->load");
        } elseif ($this->id <= 0 and $this->name == '') {
            log_err("Either the database ID (" . $this->id . ") or the display item name (" . $this->name . ") and the user (" . $this->user()->id . ") must be set to find a display item.", "view_component->load");
        } else {

            $qp = $this->load_sql_obj_vars($db_con);
            $db_cmp = $db_con->get1($qp);

            $this->row_mapper($db_cmp);
            if ($this->id > 0) {
                $this->load_phrases();
                log_debug('view_component->load of ' . $this->dsp_id() . ' done');
                $result = true;
            }
        }
        log_debug('view_component->load of ' . $this->dsp_id() . ' quit');
        return $result;
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

    function id_field(): string
    {
        return self::FLD_ID;
    }

    function name_field(): string
    {
        return self::FLD_NAME;
    }

    /**
     * get the view component type database id based on the code id
     * @param string $code_id
     * @return int
     */
    private function type_id_by_code_id(string $code_id): int
    {
        global $view_component_types;
        return $view_component_types->id($code_id);
    }

    /**
     * list of all view ids that are directly assigned to this view component
     */
    function assign_dsp_ids(): array
    {
        $result = array();

        if ($this->id > 0 and $this->user()->is_set()) {
            $lst = new view_cmp_link_list($this->user());
            $lst->load_by_component($this);
            $result = $lst->view_ids();
        } else {
            log_err("The user id must be set to list the view_component links.", "view_component->assign_dsp_ids");
        }

        return $result;
    }

    /**
     * return the html code to display a view name with the link
     */
    function name_linked(string $back = ''): string
    {

        return '<a href="/http/view_component_edit.php?id=' . $this->id . '&back=' . $back . '">' . $this->name . '</a>';
    }

    /*
     * import & export functions
     */

    /**
     *  */
    /**
     * import a view component from a JSON object
     * @param array $json_obj an array with the data of the json object
     * @param bool $do_save can be set to false for unit testing
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_obj(array $json_obj, bool $do_save = true): user_message
    {
        $result = new user_message();

        foreach ($json_obj as $key => $value) {

            if ($key == exp_obj::FLD_NAME) {
                $this->name = $value;
            }
            if ($key == self::FLD_POSITION or $key == self::FLD_POSITION_OLD) {
                $this->order_nbr = $value;
            }
            if ($key == exp_obj::FLD_CODE_ID) {
                if ($value != '') {
                    $this->type_id = $this->type_id_by_code_id($value);
                }
            }
            if ($key == exp_obj::FLD_DESCRIPTION) {
                $this->description = $value;
            }
        }

        if ($result->is_ok() and $do_save) {
            $result->add_message($this->save());
        } else {
            log_debug('not saved');
        }

        return $result;
    }

    /**
     * create an object for the export
     */
    function export_obj(bool $do_load = true): exp_obj
    {
        log_debug('view_component->export_obj ' . $this->dsp_id());
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
            $result->comment = $this->description;
        }

        log_debug('view_component->export_obj -> ' . json_encode($result));
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
                    FROM view_component_types
                   WHERE view_component_type_id = ".$this->type_id.";";
          $db_con = new mysql;
          $db_con->usr_id = $this->user()->id;
          $db_type = $db_con->get1($sql);
          $this->type_name = $db_type[sql_db::FLD_TYPE_NAME];
        }
        return $this->type_name;
      } */

    /*
      to link and unlink a view_component
    */

    /**
     * returns the next free order number for a new view component
     */
    function next_nbr($view_id)
    {
        log_debug('view_component->next_nbr for view "' . $view_id . '"');

        global $db_con;

        $result = 1;
        if ($view_id == '' or $view_id == Null or $view_id == 0) {
            log_err('Cannot get the next position, because the view_id is not set', 'view_component->next_nbr');
        } else {
            $sql_avoid_code_check_prefix = "SELECT";
            $sql = $sql_avoid_code_check_prefix . " max(m.order_nbr) AS max_order_nbr
                FROM ( SELECT 
                              " . $db_con->get_usr_field("order_nbr", "l", "u", sql_db::FLD_FORMAT_VAL) . " 
                          FROM view_component_links l 
                    LEFT JOIN user_view_component_links u ON u.view_component_link_id = l.view_component_link_id 
                                                      AND u.user_id = " . $this->user()->id . " 
                        WHERE l.view_id = " . $view_id . " ) AS m;";
            //$db_con = new mysql;
            $db_con->usr_id = $this->user()->id;
            $db_row = $db_con->get1_old($sql);
            $result = $db_row["max_order_nbr"];

            // if nothing is found, assume one as the next free number
            if ($result <= 0) {
                $result = 1;
            } else {
                $result++;
            }
        }

        log_debug("view_component->next_nbr -> (" . $result . ")");
        return $result;
    }

    // set the log entry parameters for a value update
    function log_link($dsp): bool
    {
        log_debug('view_component->log_link ' . $this->dsp_id() . ' to "' . $dsp->name . '"  for user ' . $this->user()->id);
        $log = new user_log_link;
        $log->usr = $this->user();
        $log->action = user_log::ACTION_ADD;
        $log->table = 'view_component_links';
        $log->new_from = clone $this;
        $log->new_to = clone $dsp;
        $log->row_id = $this->id;
        $result = $log->add_link_ref();

        log_debug('view_component -> link logged ' . $log->id);
        return $result;
    }

    // set the log entry parameters to unlink a display component ($cmp) from a view ($dsp)
    function log_unlink($dsp): bool
    {
        log_debug('view_component->log_unlink ' . $this->dsp_id() . ' from "' . $dsp->name . '" for user ' . $this->user()->id);
        $log = new user_log_link;
        $log->usr = $this->user();
        $log->action = user_log::ACTION_DELETE;
        $log->table = 'view_component_links';
        $log->old_from = clone $this;
        $log->old_to = clone $dsp;
        $log->row_id = $this->id;
        $result = $log->add_link_ref();

        log_debug('view_component -> unlink logged ' . $log->id);
        return $result;
    }

// link a view component to a view
    function link($dsp, $order_nbr): string
    {
        log_debug('view_component->link ' . $this->dsp_id() . ' to ' . $dsp->dsp_id() . ' at pos ' . $order_nbr);

        $dsp_lnk = new view_cmp_link($this->user());
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

        if (isset($dsp) and $this->user()->is_set()) {
            log_debug('view_component->unlink ' . $this->dsp_id() . ' from "' . $dsp->name . '" (' . $dsp->id . ')');
            $dsp_lnk = new view_cmp_link($this->user());
            $dsp_lnk->fob = $dsp;
            $dsp_lnk->tob = $this;
            $msg = $dsp_lnk->del();
            $result .= $msg->get_last_message();
        } else {
            $result .= log_err("Cannot unlink view component, because view is not set.", "view_component.php");
        }

        return $result;
    }

    // create a database record to save user specific settings for this view_component
    function add_usr_cfg(): bool
    {
        global $db_con;
        $result = true;

        if (!$this->has_usr_cfg()) {
            log_debug('view_component->add_usr_cfg for "' . $this->dsp_id() . ' und user ' . $this->user()->name);

            // check again if there ist not yet a record
            $db_con->set_type(sql_db::TBL_VIEW_COMPONENT, true);
            $qp = new sql_par(self::class);
            $qp->name = 'view_cmp_del_usr_cfg_if';
            $db_con->set_name($qp->name);
            $db_con->set_usr($this->user()->id);
            $db_con->set_fields(array('view_component_id'));
            $db_con->set_where_std($this->id);
            $qp->sql = $db_con->select_by_set_id();
            $qp->par = $db_con->get_par();
            $db_row = $db_con->get1($qp);
            if ($db_row != null) {
                $this->usr_cfg_id = $db_row['view_component_id'];
            }
            if (!$this->has_usr_cfg()) {
                // create an entry in the user sandbox
                $db_con->set_type(sql_db::TBL_USER_PREFIX . sql_db::TBL_VIEW_COMPONENT);
                $log_id = $db_con->insert(array('view_component_id', user_sandbox::FLD_USER), array($this->id, $this->user()->id));
                if ($log_id <= 0) {
                    log_err('Insert of user_view_component failed.');
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
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function usr_cfg_sql(sql_db $db_con, string $class = self::class): sql_par
    {
        $db_con->set_type(sql_db::TBL_VIEW_COMPONENT);
        $db_con->set_fields(array_merge(
            self::FLD_NAMES_USR,
            self::FLD_NAMES_NUM_USR
        ));
        return parent::usr_cfg_sql($db_con, $class);
    }

    /**
     * check if the database record for the user specific settings can be removed
     * @returns bool true if a user specific view component has been removed
     */
    function del_usr_cfg_if_not_needed(): bool
    {
        log_debug('pre check for "' . $this->dsp_id() . ' und user ' . $this->user()->name);

        global $db_con;
        $result = true;

        //if ($this->has_usr_cfg) {

        // check again if there is not yet a record
        $qp = $this->usr_cfg_sql($db_con);
        $db_con->usr_id = $this->user()->id;
        $usr_cfg = $db_con->get1($qp);

        log_debug('check for "' . $this->dsp_id() . ' und user ' . $this->user()->name . ' with (' . $qp->sql . ')');
        if ($usr_cfg != null) {
            if ($usr_cfg['view_component_id'] > 0) {
                if ($usr_cfg[self::FLD_NAME] == ''
                    and $usr_cfg[self::FLD_COMMENT] == ''
                    and $usr_cfg[self::FLD_TYPE] == Null
                    and $usr_cfg[self::FLD_ROW_PHRASE] == Null
                    and $usr_cfg[self::FLD_LINK_TYPE] == Null
                    and $usr_cfg[formula::FLD_ID] == Null
                    and $usr_cfg[self::FLD_COL_PHRASE] == Null
                    and $usr_cfg[self::FLD_COL2_PHRASE] == Null
                    and $usr_cfg[self::FLD_EXCLUDED] == Null) {
                    // delete the entry in the user sandbox
                    log_debug('any more for "' . $this->dsp_id() . ' und user ' . $this->user()->name);
                    $result = $this->del_usr_cfg_exe($db_con);
                }
            }
        }
        return $result;
    }

    /**
     * save the comment field for the view component comment
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param user_sandbox $db_rec the view component as saved in the database before the update
     * @param user_sandbox $std_rec the default parameter used for this view component
     * @returns string any message that should be shown to the user or a empty string if everything is fine
     */
    function save_field_comment(sql_db $db_con, user_sandbox $db_rec, user_sandbox $std_rec): string
    {
        $result = '';
        // TODO check if everywhere null value on import does not lead to overwrite of existing values
        if ($db_rec->description <> $this->description and $this->description != null) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->description;
            $log->new_value = $this->description;
            $log->std_value = $std_rec->description;
            $log->row_id = $this->id;
            $log->field = self::FLD_COMMENT;
            $result = $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    /**
     * set the update parameters for the word type
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param user_sandbox $db_rec the view component as saved in the database before the update
     * @param user_sandbox $std_rec the default parameter used for this view component
     * @returns string any message that should be shown to the user or a empty string if everything is fine
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
     * set the update parameters for the word row
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param user_sandbox $db_rec the view component as saved in the database before the update
     * @param user_sandbox $std_rec the default parameter used for this view component
     * @returns string any message that should be shown to the user or a empty string if everything is fine
     */
    function save_field_wrd_row(sql_db $db_con, user_sandbox $db_rec, user_sandbox $std_rec): string
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
            $log->field = self::FLD_ROW_PHRASE;
            $result = $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    /**
     * set the update parameters for the word col
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param user_sandbox $db_rec the view component as saved in the database before the update
     * @param user_sandbox $std_rec the default parameter used for this view component
     * @returns string any message that should be shown to the user or a empty string if everything is fine
     */
    function save_field_wrd_col(sql_db $db_con, user_sandbox $db_rec, user_sandbox $std_rec): string
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
            $log->field = self::FLD_COL_PHRASE;
            $result = $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    /**
     * set the update parameters for the word col2
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param user_sandbox $db_rec the view component as saved in the database before the update
     * @param user_sandbox $std_rec the default parameter used for this view component
     * @returns string any message that should be shown to the user or a empty string if everything is fine
     */
    function save_field_wrd_col2(sql_db $db_con, user_sandbox $db_rec, user_sandbox $std_rec): string
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
            $log->field = self::FLD_COL2_PHRASE;
            $result = $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    /**
     * set the update parameters for the formula
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param user_sandbox $db_rec the view component as saved in the database before the update
     * @param user_sandbox $std_rec the default parameter used for this view component
     * @returns string any message that should be shown to the user or a empty string if everything is fine
     */
    function save_field_formula(sql_db $db_con, user_sandbox $db_rec, user_sandbox $std_rec): string
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
            $log->field = formula::FLD_ID;
            $result = $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    /**
     * save all updated view_component fields excluding the name, because already done when adding a view_component
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param user_sandbox $db_rec the view component as saved in the database before the update
     * @param user_sandbox $std_rec the default parameter used for this view component
     * @returns string any message that should be shown to the user or a empty string if everything is fine
     */
    function save_fields(sql_db $db_con, user_sandbox $db_rec, user_sandbox $std_rec): string
    {
        $result = $this->save_field_comment($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_type($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_wrd_row($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_wrd_col($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_wrd_col2($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_formula($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_excluded($db_con, $db_rec, $std_rec);
        log_debug('view_component->save_fields all fields for ' . $this->dsp_id() . ' has been saved');
        return $result;
    }

    /**
     * delete the view component links of linked to this view component
     */
    function del_links(): user_message
    {
        $result = new user_message();

        // collect all component links where this component is used
        $lnk_lst = new view_cmp_link_list($this->user());
        $lnk_lst->load_by_component($this);

        // if there are links, delete if not used by anybody else than the user who has requested the deletion
        // or exclude the links for the user if the link is used by someone else
        if (!$lnk_lst->is_empty()) {
            $result->add($lnk_lst->del());
        }

        return $result;
    }

}

