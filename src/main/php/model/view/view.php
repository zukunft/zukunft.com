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
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.
  
  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>
  
  Copyright (c) 1995-2021 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

class view extends user_sandbox
{
    // list of the view used by the program that are never supposed to be changed
    const START = "start";
    const WORD = "word_dsp";
    const WORD_ADD = "word_add";
    const WORD_EDIT = "word_edit";
    const WORD_FIND = "word_find";
    const WORD_DEL = "word_del";
    const VALUE_ADD = "value_add";
    const VALUE_EDIT = "value_edit";
    const VALUE_DEL = "value_del";
    const VALUE_DISPLAY = "value";
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

    // persevered view name for unit and integration tests
    const TEST_NAME = 'System Test View';

    // database fields additional to the user sandbox fields for the view component
    public ?string $comment = null; // the view description that is shown as a mouseover explain to the user
    public ?string $code_id = null;   // to select internal predefined views

    // in memory only fields
    public ?array $cmp_lst = null;  // array of the view component objects in correct order
    public ?string $back = null;    // the calling stack

    function __construct()
    {
        parent::__construct();
        $this->obj_name = DB_TYPE_VIEW;

        $this->rename_can_switch = UI_CAN_CHANGE_VIEW_NAME;
    }

    function reset()
    {
        $this->id = null;
        $this->usr_cfg_id = null;
        $this->usr = null;
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

    function row_mapper($db_row, $map_usr_fields = false)
    {
        if ($db_row != null) {
            if ($db_row['view_id'] > 0) {
                $this->id = $db_row['view_id'];
                $this->name = $db_row['view_name'];
                $this->comment = $db_row['comment'];
                $this->type_id = $db_row['view_type_id'];
                $this->code_id = $db_row['code_id'];
                $this->excluded = $db_row['excluded'];
                if ($map_usr_fields) {
                    $this->usr_cfg_id = $db_row['user_view_id'];
                    $this->owner_id = $db_row['user_id'];
                }
            } else {
                $this->id = 0;
            }
        } else {
            $this->id = 0;
        }
    }

    /**
     * load the view parameters for all users
     */
    function load_standard(): bool
    {

        global $db_con;
        $result = false;

        $db_con->set_type(DB_TYPE_VIEW);
        $db_con->set_usr($this->usr->id);
        $db_con->set_fields(array('comment', 'view_type_id', 'code_id', 'excluded'));
        $db_con->set_where($this->id, $this->name, $this->code_id);
        $sql = $db_con->select();

        if ($db_con->get_where() <> '') {
            $db_dsp = $db_con->get1($sql);
            $this->row_mapper($db_dsp);
            $result = $this->load_owner();
        }
        return $result;
    }

    /**
     * create an SQL statement to retrieve the parameters of a view from the database
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param bool $get_name to create the SQL statement name for the predefined SQL within the same function to avoid duplicating if in case of more than on where type
     * @return string the SQL statement base on the parameters set in $this
     */
    function load_sql(sql_db $db_con, bool $get_name = false): string
    {
        $sql_name = 'view_by_';
        if ($this->id != 0) {
            $sql_name .= 'id';
        } elseif ($this->code_id != '') {
            $sql_name .= sql_db::FLD_CODE_ID;
        } elseif ($this->name != '') {
            $sql_name .= 'name';
        } else {
            log_err('Either the id, code_id or name must be set to get a view');
        }

        $db_con->set_type(DB_TYPE_VIEW);
        $db_con->set_usr($this->usr->id);
        $db_con->set_fields(array('code_id'));
        $db_con->set_usr_fields(array('comment'));
        $db_con->set_usr_num_fields(array('view_type_id', 'excluded'));
        $db_con->set_where($this->id, $this->name, $this->code_id);
        $sql = $db_con->select();

        if ($get_name) {
            $result = $sql_name;
        } else {
            $result = $sql;
        }
        return $result;
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

            $sql = $this->load_sql($db_con);

            if ($db_con->get_where() <> '') {
                $db_view = $db_con->get1($sql);
                $this->row_mapper($db_view, true);
                if ($this->id > 0) {
                    log_debug('view->load ' . $this->dsp_id());
                    $result = true;
                }
            }
        }
        return $result;
    }

    /**
     * create an SQL statement to retrieve all view components of a view
     *
     * @param sql_db $db_con as a function parameter for unit testing
     * @param bool $get_name to create the SQL statement name for the predefined SQL within the same function to avoid duplicating if in case of more than on where type
     * @return string the SQL statement base on the parameters set in $this
     */
    function load_components_sql(sql_db $db_con, bool $get_name = false): string
    {
        // TODO make the order user specific
        $sql_name = 'view_components_by_view_id';
        $sql = " SELECT e.view_component_id, 
                    u.view_component_id AS user_entry_id,
                    e.user_id, 
                    " . $db_con->get_usr_field('order_nbr', 'l', 'y', sql_db::FLD_FORMAT_VAL) . ",
                    " . $db_con->get_usr_field('view_component_name', 'e', 'u') . ",
                    " . $db_con->get_usr_field('view_component_type_id', 'e', 'u', sql_db::FLD_FORMAT_VAL) . ",
                    " . $db_con->get_usr_field(sql_db::FLD_CODE_ID, 't', 'c') . ",
                    " . $db_con->get_usr_field('word_id_row', 'e', 'u', sql_db::FLD_FORMAT_VAL) . ",
                    " . $db_con->get_usr_field('link_type_id', 'e', 'u', sql_db::FLD_FORMAT_VAL) . ",
                    " . $db_con->get_usr_field('formula_id', 'e', 'u', sql_db::FLD_FORMAT_VAL) . ",
                    " . $db_con->get_usr_field('word_id_col', 'e', 'u', sql_db::FLD_FORMAT_VAL) . ",
                    " . $db_con->get_usr_field('word_id_col2', 'e', 'u', sql_db::FLD_FORMAT_VAL) . ",
                    " . $db_con->get_usr_field('excluded', 'l', 'y', sql_db::FLD_FORMAT_VAL, 'link_excluded') . ",
                    " . $db_con->get_usr_field('excluded', 'e', 'u', sql_db::FLD_FORMAT_VAL) . "
               FROM view_component_links l            
          LEFT JOIN user_view_component_links y ON y.view_component_link_id = l.view_component_link_id 
                                               AND y.user_id = " . $this->usr->id . ", 
                    view_components e             
          LEFT JOIN user_view_components u ON u.view_component_id = e.view_component_id 
                                          AND u.user_id = " . $this->usr->id . " 
          LEFT JOIN view_component_types t ON e.view_component_type_id = t.view_component_type_id
          LEFT JOIN view_component_types c ON u.view_component_type_id = c.view_component_type_id
              WHERE l.view_id = " . $this->id . " 
                AND l.view_component_id = e.view_component_id 
           ORDER BY order_nbr;";
        log_debug("view->load_components_sql ... " . $sql);
        if ($get_name) {
            $result = $sql_name;
        } else {
            $result = $sql;
        }
        return $result;
    }

    /**
     * load all parts of this view for this user
     * @return bool false if a technical error on loading has occurred; an empty list if fine and returns true
     */
    function load_components(): bool
    {
        log_debug('view->load_components for ' . $this->dsp_id());

        global $db_con;
        $result = true;

        $db_con->usr_id = $this->usr->id;
        $sql = $this->load_components_sql($db_con);
        $db_lst = $db_con->get($sql);
        $this->cmp_lst = array();
        foreach ($db_lst as $db_entry) {
            // this is only for the view of the active user, so a direct exclude can be done
            if ((is_null($db_entry['excluded']) or $db_entry['excluded'] == 0)
                and (is_null($db_entry['link_excluded']) or $db_entry['link_excluded'] == 0)) {
                $new_entry = new view_component_dsp;
                $new_entry->id = $db_entry['view_component_id'];
                $new_entry->usr = $this->usr;
                $new_entry->owner_id = $db_entry['user_id'];
                $new_entry->order_nbr = $db_entry['order_nbr'];
                $new_entry->name = $db_entry['view_component_name'];
                $new_entry->word_id_row = $db_entry['word_id_row'];
                $new_entry->link_type_id = $db_entry['link_type_id'];
                $new_entry->type_id = $db_entry['view_component_type_id'];
                $new_entry->formula_id = $db_entry['formula_id'];
                $new_entry->word_id_col = $db_entry['word_id_col'];
                $new_entry->word_id_col2 = $db_entry['word_id_col2'];
                $new_entry->code_id = $db_entry[sql_db::FLD_CODE_ID];
                if (!$new_entry->load_phrases()) {
                    $result = false;
                }
                $this->cmp_lst[] = $new_entry;
            }
        }
        log_debug('view->load_components ' . count($this->cmp_lst) . ' loaded for ' . $this->dsp_id());

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
            $db_type = $db_con->get1($sql);
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
     * @param view_component $cmp the view component that should be added
     * @param int|null $pos is set the position, where the
     * @return bool true if the new component link has been saved to the database
     */
    function add_cmp(view_component $cmp, ?int $pos = null, bool $do_save = true): bool
    {
        $result = false;
        if ($pos == null) {
            $this->cmp_lst[] = $cmp;
            if (count($this->cmp_lst) != $cmp->order_nbr) {
                log_err('View component ' . $cmp->name . ' has been expected to be at position ' . $cmp->order_nbr . ' in ' . $this->name . 'but it is at position ' . count($this->cmp_lst));
            } else {
                if ($do_save) {
                    $cmp->save();
                    $cmp_lnk = new view_component_link();
                    $cmp_lnk->view_id = $this->id;
                    $cmp_lnk->view_component_id = $cmp->id;
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
            $cmp = new view_component_dsp;
            $cmp->id = $view_component_id;
            $cmp->usr = $this->usr;
            $cmp->load();
            $cmp_lnk = new view_component_link;
            $cmp_lnk->fob = $this;
            $cmp_lnk->tob = $cmp;
            $cmp_lnk->usr = $this->usr;
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
            $cmp = new view_component_dsp;
            $cmp->id = $view_component_id;
            $cmp->usr = $this->usr;
            $cmp->load();
            $cmp_lnk = new view_component_link;
            $cmp_lnk->fob = $this;
            $cmp_lnk->tob = $cmp;
            $cmp_lnk->usr = $this->usr;
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
        log_debug('view->selector_page (' . $this->id . ',' . $wrd_id . ')');

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
        $dsp_lst = $db_con->get($sql);
        foreach ($dsp_lst as $dsp) {
            $view_id = $dsp['id'];
            $view_name = $dsp['name'];
            if ($view_id == $this->id) {
                $result .= '<b><a href="' . $call . '&' . $field . '=' . $view_id . '">' . $view_name . '</a></b> ';
            } else {
                $result .= '<a href="' . $call . '&' . $field . '=' . $view_id . '">' . $view_name . '</a> ';
            }
            $call_edit = '/http/view_edit.php?id=' . $view_id . '&word=' . $wrd_id . '&back=' . $back;
            $result .= btn_edit('design the view', $call_edit) . ' ';
            $call_del = '/http/view_del.php?id=' . $view_id . '&word=' . $wrd_id . '&back=' . $back;
            $result .= btn_del('delete the view', $call_del) . ' ';
            $result .= '<br>';
        }

        log_debug('view->selector_page ... done');
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
     * @return bool true if the import has been successfully saved to the database
     */
    function import_obj(array $json_obj, bool $do_save = true): bool
    {
        log_debug('view->import_obj');
        $result = false;

        // reset the all parameters for the word object but keep the user
        $usr = $this->usr;
        $this->reset();
        $this->usr = $usr;
        foreach ($json_obj as $key => $value) {

            if ($key == 'name') {
                $this->name = $value;
            }
            if ($key == 'type') {
                if ($value != '') {
                    $this->type_id = $this->type_id_by_code_id($value);
                }
            }
            if ($key == 'comment') {
                $this->comment = $value;
            }
            if ($key == 'view_components') {
                $json_lst = $value;
                foreach ($json_lst as $json_cmp) {
                    $cmp = new view_component();
                    $cmp->import_obj($json_cmp, $do_save);
                    // on import first add all view components to the view object and save them all at once
                    $this->add_cmp($cmp, null, false);
                }
            }
        }

        if ($do_save) {
            if ($this->name == '') {
                log_err("Name in view missing");
            } else {
                if ($this->save()) {
                    $result = true;
                    // TODO save also the links
                    //$dsp_lnk = new view_component_link();
                    log_debug('view->import_obj -> ' . $this->dsp_id());
                }
            }
        } else {
            log_debug('view->import_obj -> ' . $result);
        }

        return $result;
    }

    /**
     * export mapper: create an object for the export
     */
    function export_obj(bool $do_load = true): view_exp
    {
        log_debug('view->export_obj ' . $this->dsp_id());
        $result = new view_exp();

        // add the view parameters
        $result->name = $this->name;
        $result->comment = $this->comment;
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

        log_debug('view->export_obj -> ' . json_encode($result));
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
            log_debug('view->is_in_list ' . $dsp_id . ' = ' . $this->id . '?');
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

        log_debug('view->add_usr_cfg ' . $this->dsp_id());

        if (!$this->has_usr_cfg()) {

            // check again if there ist not yet a record
            $db_con->set_type(DB_TYPE_VIEW, true);
            $db_con->set_usr($this->usr->id);
            $db_con->set_where($this->id);
            $sql = $db_con->select();
            $db_row = $db_con->get1($sql);
            if ($db_row != null) {
                $this->usr_cfg_id = $db_row['view_id'];
            }
            if (!$this->has_usr_cfg()) {
                // create an entry in the user sandbox
                $db_con->set_type(DB_TYPE_USER_PREFIX . DB_TYPE_VIEW);
                $log_id = $db_con->insert(array('view_id', 'user_id'), array($this->id, $this->usr->id));
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
        log_debug('view->del_usr_cfg_if_not_needed pre check for "' . $this->dsp_id() . ' und user ' . $this->usr->name);

        global $db_con;
        $result = false;

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
        $usr_cfg = $db_con->get1($sql);
        log_debug('view->del_usr_cfg_if_not_needed check for "' . $this->dsp_id() . ' und user ' . $this->usr->name . ' with (' . $sql . ')');
        if ($usr_cfg['view_id'] > 0) {
            if ($usr_cfg['comment'] == ''
                and $usr_cfg['view_type_id'] == Null
                and $usr_cfg['excluded'] == Null) {
                // delete the entry in the user sandbox
                log_debug('view->del_usr_cfg_if_not_needed any more for "' . $this->dsp_id() . ' und user ' . $this->usr->name);
                $result = $this->del_usr_cfg_exe($db_con);
            }
        }
        //}
        return $result;
    }

    /**
     * set the update parameters for the view comment
     */
    function save_field_comment($db_con, $db_rec, $std_rec): bool
    {
        $result = true;
        if ($db_rec->comment <> $this->comment) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->comment;
            $log->new_value = $this->comment;
            $log->std_value = $std_rec->comment;
            $log->row_id = $this->id;
            $log->field = 'comment';
            $result = $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    /**
     * set the update parameters for the word type
     */
    function save_field_type($db_con, $db_rec, $std_rec): bool
    {
        $result = true;
        if ($db_rec->type_id <> $this->type_id) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->type_name();
            $log->old_id = $db_rec->type_id;
            $log->new_value = $this->type_name();
            $log->new_id = $this->type_id;
            $log->std_value = $std_rec->type_name();
            $log->std_id = $std_rec->type_id;
            $log->row_id = $this->id;
            $log->field = 'view_type_id';
            $result = $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    /**
     * save all updated view fields excluding the name, because already done when adding a view
     */
    function save_fields($db_con, $db_rec, $std_rec): bool
    {
        $result = $this->save_field_comment($db_con, $db_rec, $std_rec);
        if ($result) {
            $result = $this->save_field_type($db_con, $db_rec, $std_rec);
        }
        if ($result) {
            $result = $this->save_field_excluded($db_con, $db_rec, $std_rec);
        }
        log_debug('view->save_fields all fields for ' . $this->dsp_id() . ' has been saved');
        return $result;
    }

}
