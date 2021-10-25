<?php

/*

  view_component_link.php - link a single display component/element to a view
  -----------------------

  TODO
  if a link is owned by someone, who has deleted it, it can be changed by anyone else
  or another way to formulate this: if the owner deletes a link, the ownership should be move to the remaining users
  
  force to remove all user settings to be able to delete a link as an admin
  
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

class view_cmp_link extends user_sandbox
{

    const POS_BELOW = 1;  // the view component is placed below the previous component
    const POS_SIDE = 2;   // the view component is placed on the right (or left for right to left writing) side of the previous component

    public ?int $view_id = null;            // the id of the view to which the display item should be linked
    public ?int $view_component_id = null;  // the id of the linked display item
    public ?int $order_nbr = null;          // to sort the display item
    public ?int $pos_type_id = null;        // to to position the display item relative the the previous item (1 = below, 2= side, )
    public ?string $pos_code = null;        // side or below or ....


    function __construct()
    {
        parent::__construct();
        $this->obj_type = user_sandbox::TYPE_LINK;
        $this->obj_name = DB_TYPE_VIEW_COMPONENT_LINK;
        $this->from_name = DB_TYPE_VIEW;
        $this->to_name = DB_TYPE_VIEW_COMPONENT;

        $this->rename_can_switch = UI_CAN_CHANGE_VIEW_COMPONENT_LINK;
    }

    // reset the in memory fields used e.g. if some ids are updated
    private function reset_objects()
    {
        $this->fob = null; // the display (view) object (used to save the correct name in the log)
        $this->tob = null; // the display component (view entry) object (used to save the correct name in the log)
    }

    function reset()
    {
        $this->id = null;
        $this->usr_cfg_id = null;
        $this->usr = null;
        $this->owner_id = null;
        $this->excluded = null;

        $this->view_id = null;
        $this->view_component_id = null;
        $this->order_nbr = null;
        $this->pos_type_id = null;
        $this->pos_code = null;

        $this->reset_objects();
    }

    // build the sql where string
    /*
    private function sql_where() {
      $sql_where = '';
      if ($this->id > 0) {
        $sql_where = "l.view_component_link_id = ".$this->id;
      } elseif ($this->view_id > 0 AND $this->view_component_id > 0) {
        $sql_where = "l.view_id = ".$this->view_id." AND l.view_component_id = ".$this->view_component_id;
      }
      return $sql_where;
    }
    */

    private function row_mapper($db_row, $map_usr_fields = false)
    {
        if ($db_row != null) {
            if ($db_row['view_component_link_id'] > 0) {
                $this->id = $db_row['view_component_link_id'];
                $this->owner_id = $db_row['user_id'];
                $this->view_id = $db_row['view_id'];
                $this->view_component_id = $db_row['view_component_id'];
                $this->order_nbr = $db_row['order_nbr'];
                $this->pos_type_id = $db_row['position_type'];
                $this->excluded = $db_row['excluded'];
                if ($map_usr_fields) {
                    $this->usr_cfg_id = $db_row['user_view_component_link_id'];
                }
            } else {
                $this->id = 0;
            }
        } else {
            $this->id = 0;
        }
    }

    // load the view component link parameters for all users
    function load_standard(): bool
    {

        global $db_con;
        $result = false;

        // try to get the search values from the objects
        if ($this->id <= 0) {
            if (isset($this->fob) and $this->view_id <= 0) {
                $this->view_id = $this->fob->id;
            }
            if (isset($this->tob) and $this->view_component_id <= 0) {
                $this->view_component_id = $this->tob->id;
            }
        }

        $db_con->set_type(DB_TYPE_VIEW_COMPONENT_LINK);
        $db_con->set_fields(array(sql_db::FLD_USER_ID));
        $db_con->set_link_fields('view_id', 'view_component_id');
        $db_con->set_fields(array('order_nbr', 'position_type', 'excluded', 'user_id'));
        $db_con->set_where_link($this->id, $this->view_id, $this->view_component_id);
        $sql = $db_con->select();

        if ($db_con->get_where() <> '') {
            $db_dsl = $db_con->get1($sql);
            $this->row_mapper($db_dsl);
            // TODO check if correct
            if ($this->usr != null) {
                $result = $this->load_owner();
            }
        }
        return $result;
    }

    function load_sql(sql_db $db_con, bool $get_name = false): string
    {
        $sql_name = 'dsp_cmp_lst_by_';
        if ($this->id > 0) {
            $sql_name .= 'id';
        } elseif ($this->view_id > 0 and $this->view_component_id > 0) {
            $sql_name .= 'view_and_cmp_id';
        } else {
            log_err("At lease on phrase ID must be set to load a value list.", "value_list->load_by_phr_lst_sql");
        }

        $db_con->set_type(DB_TYPE_VIEW_COMPONENT_LINK);
        $db_con->set_usr($this->usr->id);
        $db_con->set_link_fields('view_id', 'view_component_id');
        $db_con->set_usr_num_fields(array('order_nbr', 'position_type', 'excluded'));
        $db_con->set_where_link($this->id, $this->view_id, $this->view_component_id);
        $sql = $db_con->select();

        if ($get_name) {
            $result = $sql_name;
        } else {
            $result = $sql;
        }
        return $result;
    }

    // load the missing view component parameters from the database for the requesting user
    function load(): bool
    {
        global $db_con;
        $result = false;

        // check the all minimal input parameters are set
        if (!isset($this->usr)) {
            log_err("The user id must be set to load a view component link.", "view_component_link->load");
        } else {

            // try to get the search values from the objects
            if ($this->id <= 0 and ($this->view_id <= 0 or $this->view_component_id <= 0)) {
                if (isset($this->fob) and $this->view_id <= 0) {
                    $this->view_id = $this->fob->id;
                }
                if (isset($this->tob) and $this->view_component_id <= 0) {
                    $this->view_component_id = $this->tob->id;
                }
            }

            $sql = $this->load_sql($db_con);

            if ($db_con->get_where() <> '') {
                $db_dsl = $db_con->get1($sql);
                $this->row_mapper($db_dsl);
                if ($this->id > 0) {
                    //if (is_null($db_item['excluded']) OR $db_item['excluded'] == 0) {
                    //}
                    log_debug('view_component_link->load of ' . $this->id . ' done');
                    $result = true;
                }
            }
        }
        log_debug('view_component_link->load of ' . $this->id . ' done and quit');
        return $result;
    }

    // to load the related objects if the link object is loaded by an external query like in user_display to show the sandbox
    function load_objects(): bool
    {
        $result = true;
        if (!isset($this->fob) and $this->view_id > 0) {
            $dsp = new view_dsp;
            $dsp->id = $this->view_id;
            $dsp->usr = $this->usr;
            if ($dsp->load()) {
                $this->fob = $dsp;
            } else {
                $result = false;
            }
        }
        if (!isset($this->tob) and $this->view_component_id > 0) {
            $cmp = new view_dsp;
            $cmp->id = $this->view_component_id;
            $cmp->usr = $this->usr;
            if ($cmp->load()) {
                $this->tob = $cmp;
            } else {
                $result = false;
            }
        }
        return $result;
    }

    // return the html code to display the link name
    function name_linked($back): string
    {
        $result = '';

        $this->load_objects();
        if (isset($this->fob)
            and isset($this->tob)) {
            $result = $this->fob->name_linked(NULL, $back) . ' to ' . $this->tob->name_linked($back);
        } else {
            $result .= log_err("The view name or the component name cannot be loaded.", "view_component_link->name");
        }

        return $result;
    }

    /*

    display functions

    */

    // display the unique id fields
    // NEVER call any methods from this function because this function is used for debugging and a call can cause an endless loop
    function dsp_id(): string
    {
        $result = '';

        if (isset($this->fob) and isset($this->tob)) {
            if ($this->fob->name <> '' and $this->tob->name <> '') {
                $result .= '"' . $this->tob->name . '" in "'; // e.g. Company details
                $result .= $this->fob->name . '"';     // e.g. cash flow statement
            }
            if ($this->fob->id <> 0 and $this->tob->id <> 0) {
                $result .= ' (' . $this->fob->id . ',' . $this->tob->id;
            }
            // fallback
            if ($result == '') {
                $result .= $this->fob->dsp_id() . ' to ' . $this->tob->dsp_id();
            }
        } else {
            $result .= 'view component objects not set';
        }
        if ($this->id > 0) {
            $result .= ' -> ' . $this->id . ')';
        } else {
            $result .= ', but no link id)';
        }
        if (isset($this->usr)) {
            $result .= ' for user ' . $this->usr->id . ' (' . $this->usr->name . ')';
        }
        return $result;
    }

    function name(): string
    {
        $result = '';

        if (isset($this->fob) and isset($this->tob)) {
            if ($this->fob->name <> '' and $this->tob->name <> '') {
                $result .= '"' . $this->tob->name . '" in "'; // e.g. Company details
                $result .= $this->fob->name . '"';     // e.g. cash flow statement
            }
        } else {
            $result .= 'view component objects not set';
        }
        return $result;
    }

    //
    private function pos_type_name()
    {
        log_debug('view_component_link->pos_type_name do');

        global $db_con;

        if ($this->type_id > 0) {
            $sql = "SELECT type_name, description
                FROM view_component_position_types
               WHERE view_component_position_type_id = " . $this->type_id . ";";
            //$db_con = new mysql;
            $db_con->usr_id = $this->usr->id;
            $db_type = $db_con->get1($sql);
            $this->type_name = $db_type[sql_db::FLD_TYPE_NAME];
        }
        log_debug('view_component_link->pos_type_name done');
        return $this->type_name;
    }

    // remember the move of a display component
    // up only the component that has been move by the user
    // and not all other component changed, because this would be more confusing
    private function log_move($direction)
    {

    }

    // move one view component
    private function move($direction): bool
    {
        $result = false;

        // load any missing parameters
        if (!isset($this->id) or !isset($this->view_id)) {
            $this->load();
        }
        $this->load_objects();

        // check the all minimal input parameters
        if ($this->id <= 0) {
            log_err("Cannot load the view component link.", "view_component_link->move");
        } elseif ($this->view_id <= 0 or $this->view_component_id <= 0) {
            log_err("The view component id and the view component id must be given to move it.", "view_component_link->move");
        } else {
            log_debug('view_component_link->move ' . $direction . ' ' . $this->dsp_id());

            // new reorder code that can create a separate order for each user
            if (!isset($this->fob) or !isset($this->tob)) {
                log_err("The view component and the view component cannot be loaded to move them.", "view_component_link->move");
            } else {
                $this->fob->load_components();

                // correct any wrong order numbers e.g. a missing number
                $order_number_corrected = false;
                log_debug('view_component_link->move check order numbers for ' . $this->fob->dsp_id());
                $order_nbr = 0;
                foreach ($this->fob->cmp_lst as $entry) {
                    // get the component link (TODO add the order number to the entry lst, so that this loading is not needed)
                    $cmp_lnk = new view_cmp_link;
                    $cmp_lnk->fob = $this->fob;
                    $cmp_lnk->tob = $entry;
                    $cmp_lnk->usr = $this->usr;
                    $cmp_lnk->load();
                    // fix any wrong order numbers
                    if ($cmp_lnk->order_nbr != $order_nbr) {
                        log_debug('view_component_link->move check order number of the view component ' . $entry->dsp_id() . ' corrected from ' . $cmp_lnk->order_nbr . ' to ' . $order_nbr . ' in ' . $this->fob->dsp_id());
                        //zu_err('Order number of the view component "'.$entry->name.'" corrected from '.$cmp_lnk->order_nbr.' to '.$order_nbr.'.', "view_component_link->move");
                        $cmp_lnk->order_nbr = $order_nbr;
                        $cmp_lnk->save();
                        $order_number_corrected = true;
                    }
                    log_debug('view_component_link->move check order numbers checked for ' . $this->fob->dsp_id() . ' and ' . $entry->dsp_id() . ' at position ' . $order_nbr);
                    $order_nbr++;
                }
                if ($order_number_corrected) {
                    log_debug('view_component_link->move reload after correction');
                    $this->fob->load_components();
                    // check if correction was successful
                    $order_nbr = 0;
                    foreach ($this->fob->cmp_lst as $entry) {
                        $cmp_lnk = new view_cmp_link;
                        $cmp_lnk->fob = $this->fob;
                        $cmp_lnk->tob = $entry;
                        $cmp_lnk->usr = $this->usr;
                        $cmp_lnk->load();
                        if ($cmp_lnk->order_nbr != $order_nbr) {
                            log_err('Component link ' . $cmp_lnk->dsp_id() . ' should have position ' . $order_nbr . ', but is ' . $cmp_lnk->order_nbr, "view_component_link->move");
                        }
                    }
                }
                log_debug('view_component_link->move order numbers checked for ' . $this->fob->dsp_id());

                // actually move the selected component
                // TODO what happens if the another user has deleted some components?
                $order_nbr = 0;
                $prev_entry = null;
                $prev_entry_down = false;
                foreach ($this->fob->cmp_lst as $entry) {
                    // get the component link (TODO add the order number to the entry lst, so that this loading is not needed)
                    $cmp_lnk = new view_cmp_link;
                    $cmp_lnk->fob = $this->fob;
                    $cmp_lnk->tob = $entry;
                    $cmp_lnk->usr = $this->usr;
                    $cmp_lnk->load();
                    if ($prev_entry_down) {
                        if (isset($prev_entry)) {
                            log_debug('view_component_link->move order number of the view component ' . $prev_entry->tob->dsp_id() . ' changed from ' . $prev_entry->order_nbr . ' to ' . $order_nbr . ' in ' . $this->fob->dsp_id());
                            $prev_entry->order_nbr = $order_nbr;
                            $prev_entry->save();
                            $prev_entry = null;
                        }
                        log_debug('view_component_link->move order number of the view component "' . $cmp_lnk->tob->name . '" changed from ' . $cmp_lnk->order_nbr . ' to ' . $order_nbr . ' - 1 in "' . $this->fob->name . '"');
                        $cmp_lnk->order_nbr = $order_nbr - 1;
                        $cmp_lnk->save();
                        $result = true;
                        $prev_entry_down = false;
                    }
                    if ($entry->id == $this->view_component_id) {
                        if ($direction == 'up') {
                            if ($cmp_lnk->order_nbr > 0) {
                                log_debug('view_component_link->move order number of the view component ' . $cmp_lnk->tob->dsp_id() . ' changed from ' . $cmp_lnk->order_nbr . ' to ' . $order_nbr . ' - 1 in ' . $this->fob->dsp_id());
                                $cmp_lnk->order_nbr = $order_nbr - 1;
                                $cmp_lnk->save();
                                $result = true;
                                if (isset($prev_entry)) {
                                    log_debug('view_component_link->move order number of the view component ' . $prev_entry->tob->dsp_id() . ' changed from ' . $prev_entry->order_nbr . ' to ' . $order_nbr . ' in ' . $this->fob->dsp_id());
                                    $prev_entry->order_nbr = $order_nbr;
                                    $prev_entry->save();
                                }
                            }
                        } else {
                            if ($cmp_lnk->order_nbr > 0) {
                                $prev_entry = $cmp_lnk;
                                $prev_entry_down = true;
                            }
                        }
                    }
                    $prev_entry = $cmp_lnk;
                    $order_nbr++;
                }
            }

            // force to reload view components
            log_debug('view_component_link->move reload');
            $this->fob->load_components();
        }

        log_debug('view_component_link->move done');
        return $result;
    }

    // move on view component up
    function move_up(): bool
    {
        return $this->move('up');
    }

    // move on view component down
    function move_down(): bool
    {
        return $this->move('down');
    }

    // create a database record to save user specific settings for this view_component_link
    function add_usr_cfg(): bool
    {
        global $db_con;
        $result = true;

        if (!$this->has_usr_cfg()) {
            if (isset($this->fob) and isset($this->tob)) {
                log_debug('view_component_link->add_usr_cfg for "' . $this->fob->name . '"/"' . $this->tob->name . '" by user "' . $this->usr->name . '"');
            } else {
                log_debug('view_component_link->add_usr_cfg for "' . $this->id . '" and user "' . $this->usr->name . '"');
            }

            // check again if there is not yet a record
            $db_con->set_type(DB_TYPE_VIEW_COMPONENT_LINK, true);
            $db_con->set_usr($this->usr->id);
            $db_con->set_where($this->id);
            $sql = $db_con->select();
            $db_row = $db_con->get1($sql);
            if ($db_row != null) {
                $this->usr_cfg_id = $db_row['view_component_link_id'];
            }
            if (!$this->has_usr_cfg()) {
                // create an entry in the user sandbox
                $db_con->set_type(DB_TYPE_USER_PREFIX . DB_TYPE_VIEW_COMPONENT_LINK);
                $log_id = $db_con->insert(array('view_component_link_id', 'user_id'), array($this->id, $this->usr->id));
                if ($log_id <= 0) {
                    log_err('Insert of user_view_component_link failed.');
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
        log_debug('view_component_link->del_usr_cfg_if_not_needed pre check for ' . $this->dsp_id());

        global $db_con;
        $result = false;

        //if ($this->has_usr_cfg) {

        // check again if there ist not yet a record
        $sql = 'SELECT view_component_link_id,
                     order_nbr,
                     position_type,
                     excluded
                FROM user_view_component_links
               WHERE view_component_link_id = ' . $this->id . ' 
                 AND user_id = ' . $this->usr->id . ';';
        //$db_con = New mysql;
        $db_con->usr_id = $this->usr->id;
        $usr_cfg = $db_con->get1($sql);
        log_debug('view_component_link->del_usr_cfg_if_not_needed check for "' . $this->dsp_id() . ' with (' . $sql . ')');
        if ($usr_cfg != false) {
            if ($usr_cfg['view_component_link_id'] > 0) {
                if ($usr_cfg['order_nbr'] == Null
                    and $usr_cfg['position_type'] == Null
                    and $usr_cfg['excluded'] == Null) {
                    // delete the entry in the user sandbox
                    log_debug('view_component_link->del_usr_cfg_if_not_needed any more for "' . $this->dsp_id());
                    $result = $this->del_usr_cfg_exe($db_con);
                }
            }
        }
        //}
        return $result;
    }

    // set the update parameters for the view component order_nbr
    private
    function save_field_order_nbr($db_con, $db_rec, $std_rec): string
    {
        $result = '';
        if ($db_rec->order_nbr <> $this->order_nbr) {
            $log = $this->log_upd_field();
            $log->old_value = $db_rec->order_nbr;
            $log->new_value = $this->order_nbr;
            $log->std_value = $std_rec->order_nbr;
            $log->row_id = $this->id;
            $log->field = 'order_nbr';
            $result .= $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    // set the update parameters for the word type
    function save_field_type($db_con, $db_rec, $std_rec): string
    {
        $result = '';
        if ($db_rec->pos_type_id <> $this->pos_type_id) {
            $log = $this->log_upd_field();
            $log->old_value = $db_rec->pos_type_name();
            $log->old_id = $db_rec->pos_type_id;
            $log->new_value = $this->pos_type_name();
            $log->new_id = $this->pos_type_id;
            $log->std_value = $std_rec->pos_type_name();
            $log->std_id = $std_rec->pos_type_id;
            $log->row_id = $this->id;
            $log->field = 'position_type';
            $result .= $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    // save all updated view_component_link fields excluding the name, because already done when adding a view_component_link
    function save_fields($db_con, $db_rec, $std_rec): string
    {
        $result = $this->save_field_order_nbr($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_type($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_excluded($db_con, $db_rec, $std_rec);
        log_debug('view_component_link->save_fields all fields for ' . $this->dsp_id() . ' has been saved');
        return $result;
    }

}
