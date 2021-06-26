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

    // database fields additional to the user sandbox fields for the view component
    public $comment = '';   // the view description that is shown as a mouseover explain to the user
    public $type_id = NULL; // the id of the view type
    public $code_id = '';   // to select internal predefined views

    // in memory only fields
    public $type_name = '';   //
    public $cmp_lst = NULL;   // array of the view component objects
    public $back = NULL;   // the calling stack

    function __construct()
    {
        $this->obj_type = user_sandbox::TYPE_NAMED;
        $this->obj_name = DB_TYPE_VIEW;

        $this->rename_can_switch = UI_CAN_CHANGE_VIEW_NAME;
    }

    function reset()
    {
        $this->id = NULL;
        $this->usr_cfg_id = NULL;
        $this->usr = NULL;
        $this->owner_id = NULL;
        $this->excluded = NULL;

        $this->name = '';

        $this->comment = '';
        $this->type_id = NULL;
        $this->code_id = '';

        $this->type_name = '';
        $this->cmp_lst = NULL;
        $this->back = NULL;
    }

    private function row_mapper($db_row, $map_usr_fields = false)
    {
        if ($db_row != null) {
            if ($db_row['view_id'] > 0) {
                $this->id = $db_row['view_id'];
                $this->name = $db_row['view_name'];
                $this->comment = $db_row['comment'];
                $this->type_id = $db_row['view_type_id'];
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

    // load the view parameters for all users
    function load_standard(): bool
    {

        global $db_con;
        $result = false;

        $db_con->set_type(DB_TYPE_VIEW);
        $db_con->set_usr($this->usr->id);
        $db_con->set_fields(array('comment', 'view_type_id', 'excluded'));
        $db_con->set_where($this->id, $this->name, $this->code_id);
        $sql = $db_con->select();

        if ($db_con->get_where() <> '') {
            $db_dsp = $db_con->get1($sql);
            $this->row_mapper($db_dsp);
            $result = $this->load_owner();
        }
        return $result;
    }

    // load the missing view parameters from the database
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

            $db_con->set_type(DB_TYPE_VIEW);
            $db_con->set_usr($this->usr->id);
            $db_con->set_usr_fields(array('comment'));
            $db_con->set_usr_num_fields(array('view_type_id', 'excluded'));
            $db_con->set_where($this->id, $this->name, $this->code_id);
            $sql = $db_con->select();

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

    // load all parts of this view for this user
    function load_components()
    {
        log_debug('view->load_components for ' . $this->dsp_id());

        global $db_con;

        // TODO make the order user specific
        $sql = " SELECT e.view_component_id, 
                    u.view_component_id AS user_entry_id,
                    e.user_id, 
                    IF(y.order_nbr IS NULL, l.order_nbr, y.order_nbr) AS order_nbr,
                    IF(u.view_component_name IS NULL,    e.view_component_name,    u.view_component_name)    AS view_component_name,
                    IF(u.view_component_type_id IS NULL, e.view_component_type_id, u.view_component_type_id) AS view_component_type_id,
                    IF(c.code_id IS NULL,            t.code_id,            c.code_id)            AS code_id,
                    IF(u.word_id_row IS NULL,        e.word_id_row,        u.word_id_row)        AS word_id_row,
                    IF(u.link_type_id IS NULL,       e.link_type_id,       u.link_type_id)       AS link_type_id,
                    IF(u.formula_id IS NULL,         e.formula_id,         u.formula_id)         AS formula_id,
                    IF(u.word_id_col IS NULL,        e.word_id_col,        u.word_id_col)        AS word_id_col,
                    IF(u.word_id_col2 IS NULL,       e.word_id_col2,       u.word_id_col2)       AS word_id_col2,
                    IF(y.excluded IS NULL,           l.excluded,           y.excluded)           AS link_excluded,
                    IF(u.excluded IS NULL,           e.excluded,           u.excluded)           AS excluded
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
           ORDER BY IF(y.order_nbr IS NULL, l.order_nbr, y.order_nbr);";
        log_debug("view->load_components ... " . $sql);
        //$db_con = New mysql;
        $db_con->usr_id = $this->usr->id;
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
                $new_entry->code_id = $db_entry['code_id'];
                $new_entry->load_phrases();
                $this->cmp_lst[] = $new_entry;
            }
        }
        log_debug('view->load_components ' . count($this->cmp_lst) . ' loaded for ' . $this->dsp_id());

        return $this->cmp_lst;
    }

    // return the beginning html code for the view_type;
    // the view type defines something like the basic setup of a view
    // e.g. the catch view does not have the header, whereas all other views have
    function dsp_type_open()
    {
        log_debug('view->dsp_type_open (' . $this->type_id . ')');
        $result = '';
        // move to database !!
        // but avoid security leaks
        // maybe use a view component for that
        if ($this->type_id == 1) {
            $result .= '<h1>';
        }
        return $result;
    }

    function dsp_type_close()
    {
        log_debug('view->dsp_type_close (' . $this->type_id . ')');
        $result = '';
        // move to a view component function
        // for the word array build an object
        if ($this->type_id == 1) {
            $result = $result . '<br><br>';
            //$result = $result . '<a href="/http/view.php?words='.implode (",", $word_array).'&type=3">Really?</a>';
            $result = $result . '</h1>';
        }
        return $result;
    }

    // TODO review (get the object instead)
    function type_name()
    {

        global $db_con;

        if ($this->type_id > 0) {
            $sql = "SELECT type_name, description
                FROM view_types
               WHERE view_type_id = " . $this->type_id . ";";
            //$db_con = new mysql;
            $db_con->usr_id = $this->usr->id;
            $db_type = $db_con->get1($sql);
            $this->type_name = $db_type['type_name'];
        }
        return $this->type_name;
    }

    // return the html code of all view components
    function dsp_entries($wrd, $back)
    {
        log_debug('view->dsp_entries "' . $wrd->name . '" with the view ' . $this->dsp_id() . ' for user "' . $this->usr->name . '"');

        $result = '';
        $word_array = array();
        $this->load_components();
        foreach ($this->cmp_lst as $cmp) {
            log_debug('view->dsp_entries ... "' . $cmp->name . '" type "' . $cmp->type_id . '"');

            // list of all possible view components
            $result .= $cmp->text();        // just to display a simple text
            $result .= $cmp->word_name($wrd); // show the word name and give the user the possibility to change the word name
            $result .= $cmp->table($wrd); // display a table (e.g. ABB as first word, Cash Flow Statement as second word)
            $result .= $cmp->num_list($wrd, $back); // a word list with some key numbers e.g. all companies with the PE ratio
            $result .= $cmp->formulas($wrd); // display all formulas related to the given word
            $result .= $cmp->formula_values($wrd); // show a list of formula results related to a word
            $result .= $cmp->word_children($wrd); // show all words that are based on the given start word
            $result .= $cmp->word_parents($wrd); // show all word that this words is based on
            $result .= $cmp->json_export($wrd, $back); // offer to configure and create an JSON file
            $result .= $cmp->xml_export($wrd, $back); // offer to configure and create an XML file
            $result .= $cmp->csv_export($wrd, $back); // offer to configure and create an CSV file
            $result .= $cmp->all($wrd, $back); // shows all: all words that link to the given word and all values related to the given word
        }

        log_debug('view->dsp_entries ... done');
        return $result;
    }

    // return the html code to display a view name with the link
    function name_linked($wrd, $back)
    {
        $result = '';

        $result .= '<a href="/http/view_edit.php?id=' . $this->id;
        if (isset($wrd)) {
            $result .= '&word=' . $wrd->id;
        }
        $result .= '&back=' . $back . '">' . $this->name . '</a>';

        return $result;
    }

    // returns the html code for a view: this is the main function of this lib
    // view_id is used to force the display to a set form; e.g. display the sectors of a company instead of the balance sheet
    // view_type_id is used to .... remove???
    // word_id - id of the starting word to display; can be a single word, a comma separated list of word ids, a word group or a word triple
    function display($wrd, $back)
    {
        log_debug('view->display "' . $wrd->name . '" with the view ' . $this->dsp_id() . ' (type ' . $this->type_id . ')  for user "' . $this->usr->name . '"');
        $result = '';

        // check and correct the parameters
        if ($back == '') {
            $back = $wrd->id;
        }

        if ($this->id <= 0) {
            log_err("The view id must be loaded to display it.", "view->display");
        } else {
            // display always the view name in the top right corner and allow the user to edit the view
            $result .= $this->dsp_type_open();
            $result .= $this->dsp_navbar($back);
            $result .= $this->dsp_entries($wrd, $back);
            $result .= $this->dsp_type_close();
        }
        log_debug('view->display ... done');

        return $result;
    }

    // create an object for the export
    function export_obj()
    {
        log_debug('view->export_obj ' . $this->dsp_id());
        $result = new view();

        // add the view parameters
        $result->name = $this->name;
        $result->comment = $this->comment;
        $result->obj_type = $this->type_name();
        if ($this->code_id <> '') {
            $result->code_id = $this->code_id;
        }

        // add the view components used
        $this->load_components();
        $exp_cmp_lst = array();
        foreach ($this->cmp_lst as $cmp) {
            $exp_cmp_lst[] = $cmp->export_obj();
        }
        $result->view_components = $exp_cmp_lst;

        log_debug('view->export_obj -> ' . json_encode($result));
        return $result;
    }

    // import a view from an object
    function import_obj($json_obj)
    {
        log_debug('view->import_obj');
        $result = '';

        foreach ($json_obj as $key => $value) {

            if ($key == 'name') {
                $this->name = $value;
            }
            if ($key == 'comment') {
                $this->comment = $value;
            }
            /* TODO
            if ($key == 'type')    { $this->type_id = cl($value); }
            if ($key == 'code_id') {
            }
            if ($key == 'view_components') {
            }
            */
        }

        if ($result == '') {
            $this->save();
            log_debug('view->import_obj -> ' . $this->dsp_id());
        } else {
            log_debug('view->import_obj -> ' . $result);
        }

        return $result;
    }

    /*

    display functions

    */

    // display the unique id fields
    function dsp_id(): string
    {
        $result = '';

        if ($this->name <> '') {
            $result .= '"' . $this->name . '"';
            if ($this->id > 0) {
                $result .= ' (' . $this->id . ')';
            }
        } else {
            $result .= $this->id;
        }
        if (isset($this->usr)) {
            $result .= ' for user ' . $this->usr->id . ' (' . $this->usr->name . ')';
        }
        return $result;
    }

    function name()
    {
        $result = '"' . $this->name . '"';
        return $result;
    }

    // move one view component one place up
    // in case of an error the error message is returned
    // if everything is fine an empty string is returned
    function entry_up($view_component_id)
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

    // move one view component one place down
    function entry_down($view_component_id)
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

    // create a selection page where the user can select a view that should be used for a word
    function selector_page($wrd_id, $back)
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

    // true if the view is part of the view element list
    function is_in_list($dsp_lst)
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

    // create a database record to save user specific settings for this view
    function add_usr_cfg()
    {
        $result = '';
        log_debug('view->add_usr_cfg ' . $this->dsp_id());

        global $db_con;

        if (!$this->has_usr_cfg) {

            // check again if there ist not yet a record
            $sql = 'SELECT user_id 
                FROM user_views 
               WHERE view_id = ' . $this->id . ' 
                 AND user_id = ' . $this->usr->id . ';';
            //$db_con = New mysql;
            $db_con->usr_id = $this->usr->id;
            $db_row = $db_con->get1($sql);
            $usr_db_id = $db_row['user_id'];
            if ($usr_db_id <= 0) {
                // create an entry in the user sandbox
                $db_con->set_type(DB_TYPE_USER_PREFIX . DB_TYPE_VIEW);
                $log_id = $db_con->insert(array('view_id', 'user_id'), array($this->id, $this->usr->id));
                if ($log_id <= 0) {
                    log_err('Insert of user_view failed.');
                }
            }
        }
        return $result;
    }

    // check if the database record for the user specific settings can be removed
    function del_usr_cfg_if_not_needed()
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

    // set the update parameters for the view comment
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

    // set the update parameters for the word type
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

    // save all updated view fields excluding the name, because already done when adding a view
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
