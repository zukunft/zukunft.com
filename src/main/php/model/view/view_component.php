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
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.
  
  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>
  
  Copyright (c) 1995-2021 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

class view_component extends user_sandbox
{

    // database fields additional to the user sandbox fields for the view component
    public ?string $comment = null;         // the view component description that is shown as a mouseover explain to the user
    public ?int $order_nbr = null;          // the position in the linked view
    public ?int $word_id_row = null;        // if the view component uses a related word tree this is the start node
    //                                         e.g. for "company" the start node could be "cash flow statement" to show the cash flow for any company
    public ?int $link_type_id = null;       // the word link type used to build the word tree started with the $start_word_id
    public ?int $formula_id = null;         // to select a formula (no used case at the moment)
    public ?int $word_id_col = null;        // for a table to defined which columns should be used (if not defined by the calling word)
    public ?int $word_id_col2 = null;       // for a table to defined second columns layer or the second axis in case of a chart
    //                                         e.g. for a "company cash flow statement" the "col word" could be "Year"
    //                                              "col2 word" could be "Quarter" to show the Quarters between the year upon request

    // linked fields
    public ?word $wrd_row = null;           // the word object for $word_id_row
    public ?word $wrd_col = null;           // the word object for $word_id_col
    public ?word $wrd_col2 = null;          // the word object for $word_id_col2
    public ?formula $frm = null;            // the formula object for $formula_id
    public ?string $link_type_name = null;  //
    public ?string $code_id = null;         // the entry type code id
    public ?string $back = null;            // the calling stack

    function __construct()
    {
        $this->obj_type = user_sandbox::TYPE_NAMED;
        $this->obj_name = 'view_component';

        $this->rename_can_switch = UI_CAN_CHANGE_VIEW_COMPONENT_NAME;
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
        $this->type_name = '';
        $this->code_id = '';
        $this->back = null;
    }

    private function row_mapper($db_row, $map_usr_fields = false)
    {
        if ($db_row != null) {
            if ($db_row['view_component_id'] > 0) {
                $this->id = $db_row['view_component_id'];
                $this->name = $db_row['view_component_name'];
                $this->comment = $db_row['comment'];
                $this->type_id = $db_row['view_component_type_id'];
                $this->word_id_row = $db_row['word_id_row'];
                $this->link_type_id = $db_row['link_type_id'];
                $this->formula_id = $db_row['formula_id'];
                $this->word_id_col = $db_row['word_id_col'];
                $this->word_id_col2 = $db_row['word_id_col2'];
                $this->excluded = $db_row['excluded'];
                if ($map_usr_fields) {
                    $this->usr_cfg_id = $db_row['user_view_component_id'];
                    $this->owner_id = $db_row['user_id'];
                    //$this->share_id = $db_row['share_type_id'];
                    //$this->protection_id = $db_row['protection_type_id'];
                } else {
                    //$this->share_id = cl(DBL_SHARE_PUBLIC);
                    //$this->protection_id = cl(DBL_PROTECT_NO);
                }
            } else {
                $this->id = 0;
            }
        } else {
            $this->id = 0;
        }
    }

    // load the view component parameters for all users
    function load_standard(): bool
    {
        global $db_con;
        $result = false;

        $db_con->set_type(DB_TYPE_VIEW_COMPONENT);
        $db_con->set_usr($this->usr->id);
        $db_con->set_fields(array('comment', 'view_component_type_id', 'word_id_row', 'link_type_id', 'formula_id', 'word_id_col', 'word_id_col2', 'excluded'));
        $db_con->set_where($this->id, $this->name);
        $sql = $db_con->select();

        if ($db_con->get_where() <> '') {
            $db_cmp = $db_con->get1($sql);
            $this->row_mapper($db_cmp);
            $result = $this->load_owner();
            if ($result) {
                $result = $this->load_phrases();
            }
        }
        return $result;
    }

    // load the missing view component parameters from the database
    function load(): bool
    {
        log_debug('view_component->load');

        global $db_con;
        $result = false;

        // check the minimal input parameters
        if (!isset($this->usr)) {
            log_err("The user id must be set to load a view component.", "view_component->load");
        } elseif ($this->id <= 0 and $this->name == '') {
            log_err("Either the database ID (" . $this->id . ") or the display item name (" . $this->name . ") and the user (" . $this->usr->id . ") must be set to find a display item.", "view_component->load");
        } else {

            $db_con->set_type(DB_TYPE_VIEW_COMPONENT);
            $db_con->set_usr($this->usr->id);
            $db_con->set_join_usr_fields(array('code_id'), 'view_component_type');
            $db_con->set_fields(array('comment'));
            $db_con->set_usr_num_fields(array('view_component_type_id', 'word_id_row', 'link_type_id', 'formula_id', 'word_id_col', 'word_id_col2', 'excluded'));
            $db_con->set_where($this->id, $this->name);
            $sql = $db_con->select();

            if ($db_con->get_where() <> '') {
                $db_item = $db_con->get1($sql);
                //zu_debug('view_component->level-22 '.$debug.' done.', 10);
                log_debug('view_component->load with ' . $sql);
                //zu_debug('view_component->level-2 '.$debug.' done.', 10);
                $this->row_mapper($db_item, true);
                if ($this->id > 0) {
                    $this->load_phrases();
                    log_debug('view_component->load of ' . $this->dsp_id() . ' done');
                    $result = true;
                }
            }
        }
        log_debug('view_component->load of ' . $this->dsp_id() . ' quit');
        return $result;
    }

    // load the related word and formula objects
    function load_phrases()
    {
        $this->load_wrd_row();
        $this->load_wrd_col();
        $this->load_wrd_col2();
        $this->load_formula();
        log_debug('view_component->load_phrases done for ' . $this->dsp_id());
    }

    //
    function load_wrd_row()
    {
        $result = '';
        if ($this->word_id_row > 0) {
            $wrd_row = new word_dsp;
            $wrd_row->id = $this->word_id_row;
            $wrd_row->usr = $this->usr;
            $wrd_row->load();
            $this->wrd_row = $wrd_row;
            $result = $wrd_row->name;
        }
        return $result;
    }

    //
    function load_wrd_col()
    {
        $result = '';
        if ($this->word_id_col > 0) {
            $wrd_col = new word_dsp;
            $wrd_col->id = $this->word_id_col;
            $wrd_col->usr = $this->usr;
            $wrd_col->load();
            $this->wrd_col = $wrd_col;
            $result = $wrd_col->name;
        }
        return $result;
    }

    //
    function load_wrd_col2()
    {
        $result = '';
        if ($this->word_id_col2 > 0) {
            $wrd_col2 = new word_dsp;
            $wrd_col2->id = $this->word_id_col2;
            $wrd_col2->usr = $this->usr;
            $wrd_col2->load();
            $this->wrd_col2 = $wrd_col2;
            $result = $wrd_col2->name;
        }
        return $result;
    }

    // load the related formula and returns the name of the formula
    function load_formula(): string
    {
        $result = '';
        if ($this->formula_id > 0) {
            $frm = new formula;
            $frm->id = $this->formula_id;
            $frm->usr = $this->usr;
            $frm->load();
            $this->frm = $frm;
            $result = $frm->name;
        }
        return $result;
    }

    // list of all view ids that are directly assigned to this view component
    function assign_dsp_ids(): array
    {

        global $db_con;
        $result = array();

        if ($this->id > 0 and isset($this->usr)) {
            log_debug('view_component->assign_dsp_ids for view_component "' . $this->id . '" and user "' . $this->usr->name . '"');
            // this sql is similar to the load statement in view_links.php, maybe combine
            $db_con->set_type(DB_TYPE_VIEW_COMPONENT_LINK);
            //$db_con->set_join_fields(array('position_type'), 'position_type');
            $db_con->set_fields(array('view_id','view_component_id'));
            $db_con->set_usr_num_fields(array('order_nbr','position_type','excluded'));
            $db_con->set_where($this->id);
            $sql = $db_con->select();
            $db_con->usr_id = $this->usr->id;
            $db_lst = $db_con->get($sql);
            foreach ($db_lst as $db_row) {
                log_debug('view_component->assign_dsp_ids -> check exclusion ');
                if (is_null($db_row['excluded']) or $db_row['excluded'] == 0) {
                    $result[] = $db_row['view_id'];
                }
            }
            log_debug('view_component->assign_dsp_ids -> number of views ' . count($result));
        } else {
            log_err("The user id must be set to list the view_component links.", "view_component->assign_dsp_ids");
        }

        return $result;
    }

    // return the html code to display a view name with the link
    function name_linked($back): string
    {

        return '<a href="/http/view_component_edit.php?id=' . $this->id . '&back=' . $back . '">' . $this->name . '</a>';
    }

    //
    function type_name()
    {
        log_debug('view_component->type_name do');

        global $db_con;

        if ($this->type_id > 0) {
            $sql = "SELECT view_component_type_name, description
                FROM view_component_types
               WHERE view_component_type_id = " . $this->type_id . ";";
            //$db_con = new mysql;
            $db_con->usr_id = $this->usr->id;
            $db_type = $db_con->get1($sql);
            $this->type_name = $db_type['type_name'];
        }
        log_debug('view_component->type_name done');
        return $this->type_name;
    }

    // create an object for the export
    function export_obj()
    {
        log_debug('view_component->export_obj ' . $this->dsp_id());
        $result = new view_component();

        // add the component parameters
        $this->load_phrases();
        if ($this->order_nbr >= 0) {
            $result->pos = $this->order_nbr;
        }
        $result->name = $this->name;
        if ($this->type_name() <> '') {
            $result->obj_type = $this->type_name();
        }
        if ($this->code_id <> '') {
            $result->code_id = $this->code_id;
        }
        if (isset($this->wrd_row)) {
            $result->row = $this->wrd_row->name;
        }
        if (isset($this->wrd_col)) {
            $result->column = $this->wrd_col->name;
        }
        if (isset($this->wrd_col2)) {
            $result->column2 = $this->wrd_col2->name;
        }
        if ($this->comment <> '') {
            $result->comment = $this->comment;
        }

        log_debug('view_component->export_obj -> ' . json_encode($result));
        return $result;
    }

    // import a view from an object
    function import_obj()
    {
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

    // not used at the moment
    /*  private function link_type_name() {
        if ($this->type_id > 0) {
          $sql = "SELECT view_component_type_name
                    FROM view_component_types
                   WHERE view_component_type_id = ".$this->type_id.";";
          $db_con = new mysql;
          $db_con->usr_id = $this->usr->id;
          $db_type = $db_con->get1($sql);
          $this->type_name = $db_type['type_name'];
        }
        return $this->type_name;
      } */

    /*

      to link and unlink a view_component

    */

    // returns the next free order number for a new view component
    function next_nbr($view_id)
    {
        log_debug('view_component->next_nbr for view "' . $view_id . '"');

        global $db_con;

        if ($view_id == '' or $view_id == Null or $view_id == 0) {
            log_err('Cannot get the next position, because the view_id is not set', 'view_component->next_nbr');
        } else {
            $sql_avoid_code_check_prefix = "SELECT";
            $sql = $sql_avoid_code_check_prefix . " max(m.order_nbr) AS max_order_nbr
                FROM ( SELECT 
                              " . $db_con->get_usr_field("order_nbr", "l", "u", sql_db::FLD_FORMAT_NUM) . " 
                          FROM view_component_links l 
                    LEFT JOIN user_view_component_links u ON u.view_component_link_id = l.view_component_link_id 
                                                      AND u.user_id = " . $this->usr->id . " 
                        WHERE l.view_id = " . $view_id . " ) AS m;";
            //$db_con = new mysql;
            $db_con->usr_id = $this->usr->id;
            $db_row = $db_con->get1($sql);
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
    function log_link($dsp)
    {
        log_debug('view_component->log_link ' . $this->dsp_id() . ' to "' . $dsp->name . '"  for user ' . $this->usr->id);
        $log = new user_log_link;
        $log->usr = $this->usr;
        $log->action = 'add';
        $log->table = 'view_component_links';
        $log->new_from = clone $this;
        $log->new_to = clone $dsp;
        $log->row_id = $this->id;
        $result = $log->add_link_ref();

        log_debug('view_component -> link logged ' . $log->id . '');
        return $result;
    }

    // set the log entry parameters to unlink a display component ($cmp) from a view ($dsp)
    function log_unlink($dsp)
    {
        log_debug('view_component->log_unlink ' . $this->dsp_id() . ' from "' . $dsp->name . '" for user ' . $this->usr->id);
        $log = new user_log_link;
        $log->usr = $this->usr;
        $log->action = 'del';
        $log->table = 'view_component_links';
        $log->old_from = clone $this;
        $log->old_to = clone $dsp;
        $log->row_id = $this->id;
        $result = $log->add_link_ref();

        log_debug('view_component -> unlink logged ' . $log->id);
        return $result;
    }

    // link a view component to a view
    function link($dsp, $order_nbr)
    {
        log_debug('view_component->link ' . $this->dsp_id() . ' to ' . $dsp->dsp_id() . ' at pos ' . $order_nbr);
        $result = '';

        $dsp_lnk = new view_component_link;
        $dsp_lnk->fob = $dsp;
        $dsp_lnk->tob = $this;
        $dsp_lnk->usr = $this->usr;
        $dsp_lnk->order_nbr = $order_nbr;
        $dsp_lnk->pos_type_id = 1; // to be reviewed
        $result = '';
        $result .= $dsp_lnk->save();

        return $result;
    }

    // remove a view component from a view
    // to do: check if the view component is not linked anywhere else
    // and if yes, delete the view component after confirmation
    function unlink($dsp)
    {
        $result = '';

        if (isset($dsp) and isset($this->usr)) {
            log_debug('view_component->unlink ' . $this->dsp_id() . ' from "' . $dsp->name . '" (' . $dsp->id . ')');
            $dsp_lnk = new view_component_link;
            $dsp_lnk->fob = $dsp;
            $dsp_lnk->tob = $this;
            $dsp_lnk->usr = $this->usr;
            $result .= $dsp_lnk->del();
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
            log_debug('view_component->add_usr_cfg for "' . $this->dsp_id() . ' und user ' . $this->usr->name);

            // check again if there ist not yet a record
            $db_con->set_type(DB_TYPE_WORD, true);
            $db_con->set_usr($this->usr->id);
            $db_con->set_where($this->id);
            $sql = $db_con->select();
            $db_row = $db_con->get1($sql);
            if ($db_row != null) {
                $this->usr_cfg_id = $db_row['view_component_id'];
            }
            if (!$this->has_usr_cfg()) {
                // create an entry in the user sandbox
                $db_con->set_type(DB_TYPE_USER_PREFIX . DB_TYPE_VIEW_COMPONENT);
                $log_id = $db_con->insert(array('view_component_id', 'user_id'), array($this->id, $this->usr->id));
                if ($log_id <= 0) {
                    log_err('Insert of user_view_component failed.');
                    $result = false;
                } else {
                    $result = true;
                }
            }
        }
        return $result;
    }

    // check if the database record for the user specific settings can be removed
    function del_usr_cfg_if_not_needed(): bool
    {
        log_debug('view_component->del_usr_cfg_if_not_needed pre check for "' . $this->dsp_id() . ' und user ' . $this->usr->name);

        global $db_con;
        $result = false;

        //if ($this->has_usr_cfg) {

        // check again if there is not yet a record
        $sql = "SELECT view_component_id,
                     view_component_name,
                     comment,
                     view_component_type_id,
                     word_id_row,
                     link_type_id,
                     formula_id,
                     word_id_col,
                     word_id_col2,
                     excluded
                FROM user_view_components
               WHERE view_component_id = " . $this->id . " 
                 AND user_id = " . $this->usr->id . ";";
        //$db_con = New mysql;
        $db_con->usr_id = $this->usr->id;
        $usr_cfg = $db_con->get1($sql);
        log_debug('view_component->del_usr_cfg_if_not_needed check for "' . $this->dsp_id() . ' und user ' . $this->usr->name . ' with (' . $sql . ')');
        if ($usr_cfg['view_component_id'] > 0) {
            if ($usr_cfg['comment'] == ''
                and $usr_cfg['view_component_type_id'] == Null
                and $usr_cfg['word_id_row'] == Null
                and $usr_cfg['link_type_id'] == Null
                and $usr_cfg['formula_id'] == Null
                and $usr_cfg['word_id_col'] == Null
                and $usr_cfg['word_id_col2'] == Null
                and $usr_cfg['excluded'] == Null) {
                // delete the entry in the user sandbox
                log_debug('view_component->del_usr_cfg_if_not_needed any more for "' . $this->dsp_id() . ' und user ' . $this->usr->name);
                $result = $this->del_usr_cfg_exe($db_con);
            }
        }
        //}
        return $result;
    }

    // set the update parameters for the view component comment
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
            $log->field = 'view_component_type_id';
            $result = $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    // set the update parameters for the word row
    function save_field_wrd_row($db_con, $db_rec, $std_rec): bool
    {
        $result = true;
        if ($db_rec->word_id_row <> $this->word_id_row) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->load_wrd_row();
            $log->old_id = $db_rec->word_id_row;
            $log->new_value = $this->load_wrd_row();
            $log->new_id = $this->word_id_row;
            $log->std_value = $std_rec->load_wrd_row();
            $log->std_id = $std_rec->word_id_row;
            $log->row_id = $this->id;
            $log->field = 'word_id_row';
            $result = $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    // set the update parameters for the word col
    function save_field_wrd_col($db_con, $db_rec, $std_rec): bool
    {
        $result = true;
        if ($db_rec->word_id_col <> $this->word_id_col) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->load_wrd_col();
            $log->old_id = $db_rec->word_id_col;
            $log->new_value = $this->load_wrd_col();
            $log->new_id = $this->word_id_col;
            $log->std_value = $std_rec->load_wrd_col();
            $log->std_id = $std_rec->word_id_col;
            $log->row_id = $this->id;
            $log->field = 'word_id_col';
            $result = $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    // set the update parameters for the word col2
    function save_field_wrd_col2($db_con, $db_rec, $std_rec): bool
    {
        $result = true;
        if ($db_rec->word_id_col2 <> $this->word_id_col2) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->load_wrd_col2();
            $log->old_id = $db_rec->word_id_col2;
            $log->new_value = $this->load_wrd_col2();
            $log->new_id = $this->word_id_col2;
            $log->std_value = $std_rec->load_wrd_col2();
            $log->std_id = $std_rec->word_id_col2;
            $log->row_id = $this->id;
            $log->field = 'word_id_col2';
            $result = $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    // set the update parameters for the formula
    function save_field_formula($db_con, $db_rec, $std_rec): bool
    {
        $result = true;
        if ($db_rec->formula_id <> $this->formula_id) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->load_formula();
            $log->old_id = $db_rec->formula_id;
            $log->new_value = $this->load_formula();
            $log->new_id = $this->formula_id;
            $log->std_value = $std_rec->load_formula();
            $log->std_id = $std_rec->formula_id;
            $log->row_id = $this->id;
            $log->field = 'formula_id';
            $result = $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    // save all updated view_component fields excluding the name, because already done when adding a view_component
    function save_fields($db_con, $db_rec, $std_rec): bool
    {
        $result = $this->save_field_comment($db_con, $db_rec, $std_rec);
        if ($result) {
            $result = $this->save_field_type($db_con, $db_rec, $std_rec);
        }
        if ($result) {
            $result = $this->save_field_wrd_row($db_con, $db_rec, $std_rec);
        }
        if ($result) {
            $result = $this->save_field_wrd_col($db_con, $db_rec, $std_rec);
        }
        if ($result) {
            $result = $this->save_field_wrd_col2($db_con, $db_rec, $std_rec);
        }
        if ($result) {
            $result = $this->save_field_formula($db_con, $db_rec, $std_rec);
        }
        if ($result) {
            $result = $this->save_field_excluded($db_con, $db_rec, $std_rec);
        }
        log_debug('view_component->save_fields all fields for ' . $this->dsp_id() . ' has been saved');
        return $result;
    }

}

