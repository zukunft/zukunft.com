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
  
  Copyright (c) 1995-2022 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

class view_cmp_link extends user_sandbox_link
{

    /*
     * database link
     */

    // the database and JSON object field names used only for formula links
    const FLD_ID = 'view_component_link_id';
    const FLD_ORDER_NBR = 'order_nbr';
    const FLD_POS_TYPE = 'position_type';

    // all database field names excluding the id
    const FLD_NAMES = array(
        view::FLD_ID,
        view_cmp::FLD_ID
    );
    // list of the user specific database field names
    const FLD_NAMES_NUM_USR = array(
        self::FLD_ORDER_NBR,
        self::FLD_POS_TYPE,
        self::FLD_EXCLUDED,
        user_sandbox::FLD_SHARE,
        user_sandbox::FLD_PROTECT
    );

    /*
     * code links
     */

    const POS_BELOW = 1;  // the view component is placed below the previous component
    const POS_SIDE = 2;   // the view component is placed on the right (or left for right to left writing) side of the previous component

    /*
     * object vars
     */

    public view $dsp;
    public view_cmp $cmp;

    public ?int $view_id = null;            // the id of the view to which the display item should be linked
    public ?int $view_component_id = null;  // the id of the linked display item
    public ?int $order_nbr = null;          // to sort the display item
    public ?int $pos_type_id = null;        // to to position the display item relative the the previous item (1 = below, 2= side, )
    public ?string $pos_code = null;        // side or below or ....

    /*
     * construct and map
     */

    function __construct(user $usr)
    {
        parent::__construct($usr);
        $this->obj_type = user_sandbox::TYPE_LINK;
        $this->obj_name = DB_TYPE_VIEW_COMPONENT_LINK;
        $this->from_name = DB_TYPE_VIEW;
        $this->to_name = DB_TYPE_VIEW_COMPONENT;

        $this->rename_can_switch = UI_CAN_CHANGE_VIEW_COMPONENT_LINK;
    }

    function reset()
    {
        parent::reset();

        $this->reset_objects($this->usr);

        $this->view_id = null;
        $this->view_component_id = null;
        $this->order_nbr = null;
        $this->pos_type_id = null;
        $this->pos_code = null;
    }

    /**
     * reset the in memory fields used e.g. if some ids are updated
     */
    private function reset_objects(user $usr)
    {
        $this->fob = new view($usr); // the display (view) object (used to save the correct name in the log)
        $this->tob = new view_cmp($usr); // the display component (view entry) object (used to save the correct name in the log)
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

    /**
     * map the database fields to the object fields
     *
     * @param array $db_row with the data directly from the database
     * @param bool $map_usr_fields false for using the standard protection settings for the default view component link used for all users
     * @param string $id_fld the name of the id field as defined in this child and given to the parent
     * @return bool true if the view component link is loaded and valid
     */
    function row_mapper(array $db_row, bool $map_usr_fields = true, string $id_fld = self::FLD_ID): bool
    {
        $result = parent::row_mapper($db_row, $map_usr_fields, self::FLD_ID);
        if ($result) {
            $this->view_id = $db_row[view::FLD_ID];
            $this->view_component_id = $db_row[view_cmp::FLD_ID];
            $this->order_nbr = $db_row[self::FLD_ORDER_NBR];
            $this->pos_type_id = $db_row[self::FLD_POS_TYPE];
        }
        return $result;
    }

    /*
     * loading
     */

    /**
     * create an SQL statement to retrieve the parameters of the standard view component link from the database
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_standard_sql(sql_db $db_con, string $class = ''): sql_par
    {
        $qp = new sql_par(self::class);
        $qp->name = 'id';
        $db_con->set_type(DB_TYPE_VIEW_COMPONENT_LINK);
        $db_con->set_fields(array(sql_db::FLD_USER_ID));
        $db_con->set_link_fields(view::FLD_ID, view_cmp::FLD_ID);
        $db_con->set_fields(array(self::FLD_ORDER_NBR, self::FLD_POS_TYPE, self::FLD_EXCLUDED, user_sandbox::FLD_USER));
        $db_con->set_where_link($this->id, $this->view_id, $this->view_component_id);
        $qp->sql = $db_con->select_by_id();
        $qp->par = $db_con->get_par();

        return $qp;
    }

    /**
     * load the view component link parameters for all users
     * @param sql_par|null $qp placeholder to align the function parameters with the parent
     * @param string $class the name of this class to be delivered to the parent function
     * @return bool true if the standard view component link has been loaded
     */
    function load_standard(?sql_par $qp = null, string $class = self::class): bool
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
        $db_con->set_link_fields(view::FLD_ID, view_cmp::FLD_ID);
        $db_con->set_fields(array_merge(
            self::FLD_NAMES_NUM_USR,
            array(sql_db::FLD_USER_ID)));
        $db_con->set_where_link($this->id, $this->view_id, $this->view_component_id);
        $sql = $db_con->select_by_id();

        if ($db_con->get_where() <> '') {
            $db_dsl = $db_con->get1_old($sql);
            $result = $this->row_mapper($db_dsl, false);
            if ($result) {
                $result = $this->load_owner();
            }
        }
        return $result;
    }

    /**
     * create an SQL statement to retrieve the parameters of a view component link from the database
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_db $db_con, string $class = ''): sql_par
    {
        $qp = parent::load_sql($db_con, self::class);
        if ($this->id > 0) {
            $qp->name .= 'id';
        } elseif ($this->view_id > 0 and $this->view_component_id > 0) {
            $qp->name .= 'view_and_cmp_id';
        } else {
            log_err('Either the view component link id or view id and a component id (and the user= must be set ' .
                'to load a ' . self::class, self::class . '->load_sql');
        }

        $db_con->set_type(DB_TYPE_VIEW_COMPONENT_LINK);
        $db_con->set_name($qp->name);
        $db_con->set_usr($this->usr->id);
        $db_con->set_link_fields(view::FLD_ID, view_cmp::FLD_ID);
        $db_con->set_usr_num_fields(self::FLD_NAMES_NUM_USR);
        if ($this->id > 0) {
            $db_con->add_par(sql_db::PAR_INT, $this->id);
            $qp->sql = $db_con->select_by_field_list(array(view_cmp_link::FLD_ID));
        } elseif ($this->view_id > 0 and $this->view_component_id > 0) {
            $db_con->add_par(sql_db::PAR_INT, $this->view_id);
            $db_con->add_par(sql_db::PAR_INT, $this->view_component_id);
            $qp->sql = $db_con->select_by_field_list(array(view::FLD_ID, view_cmp::FLD_ID));
        }
        $qp->par = $db_con->get_par();

        return $qp;
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

            $qp = $this->load_sql($db_con);

            if ($db_con->get_where() <> '') {
                $db_dsl = $db_con->get1($qp);
                $this->row_mapper($db_dsl);
                if ($this->id > 0) {
                    //if (is_null($db_item[self::FLD_EXCLUDED]) OR $db_item[self::FLD_EXCLUDED] == 0) {
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
            $dsp = new view_dsp($this->usr);
            $dsp->id = $this->view_id;
            if ($dsp->load()) {
                $this->fob = $dsp;
            } else {
                $result = false;
            }
        }
        if (!isset($this->tob) and $this->view_component_id > 0) {
            $cmp = new view_dsp($this->usr);
            $cmp->id = $this->view_component_id;
            if ($cmp->load()) {
                $this->tob = $cmp;
            } else {
                $result = false;
            }
        }
        return $result;
    }

    // return the html code to display the link name
    function name_linked(string $back = ''): string
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
            $db_type = $db_con->get1_old($sql);
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
    // TODO load to list once, resort and write all positions with one SQL statement
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
                // TODO define the common sorting start number, which is 1 and not 0
                $order_nbr = 1;
                foreach ($this->fob->cmp_lst as $entry) {
                    // get the component link (TODO add the order number to the entry lst, so that this loading is not needed)
                    $cmp_lnk = new view_cmp_link($this->usr);
                    $cmp_lnk->fob = $this->fob;
                    $cmp_lnk->tob = $entry;
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
                        $cmp_lnk = new view_cmp_link($this->usr);
                        $cmp_lnk->fob = $this->fob;
                        $cmp_lnk->tob = $entry;
                        $cmp_lnk->load();
                        if ($cmp_lnk->order_nbr != $order_nbr) {
                            log_err('Component link ' . $cmp_lnk->dsp_id() . ' should have position ' . $order_nbr . ', but is ' . $cmp_lnk->order_nbr, "view_component_link->move");
                        }
                    }
                }
                log_debug('view_component_link->move order numbers checked for ' . $this->fob->dsp_id());

                // actually move the selected component
                // TODO what happens if the another user has deleted some components?
                $order_nbr = 1;
                $prev_entry = null;
                $prev_entry_down = false;
                foreach ($this->fob->cmp_lst as $entry) {
                    // get the component link (TODO add the order number to the entry lst, so that this loading is not needed)
                    $cmp_lnk = new view_cmp_link($this->usr);
                    $cmp_lnk->fob = $this->fob;
                    $cmp_lnk->tob = $entry;
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

            // force reloading view components
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
            $db_con->set_where_std($this->id);
            $sql = $db_con->select_by_id();
            $db_row = $db_con->get1_old($sql);
            if ($db_row != null) {
                $this->usr_cfg_id = $db_row[self::FLD_ID];
            }
            if (!$this->has_usr_cfg()) {
                // create an entry in the user sandbox
                $db_con->set_type(DB_TYPE_USER_PREFIX . DB_TYPE_VIEW_COMPONENT_LINK);
                $log_id = $db_con->insert(array(self::FLD_ID, user_sandbox::FLD_USER), array($this->id, $this->usr->id));
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

    /**
     * check if the database record for the user specific settings can be removed
     * @return bool true if the checking and the potential removing has been successful, which does not mean, that the user sandbox database row has actually been removed
     */
    function del_usr_cfg_if_not_needed(): bool
    {
        log_debug('view_component_link->del_usr_cfg_if_not_needed pre check for ' . $this->dsp_id());

        global $db_con;
        $result = true;

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
        $usr_cfg = $db_con->get1_old($sql);
        if ($usr_cfg != false) {
            if ($usr_cfg[self::FLD_ID] > 0) {
                if ($usr_cfg[self::FLD_ORDER_NBR] == Null
                    and $usr_cfg[self::FLD_POS_TYPE] == Null
                    and $usr_cfg[self::FLD_EXCLUDED] == Null) {
                    // delete the entry in the user sandbox
                    $result = $this->del_usr_cfg_exe($db_con);
                }
            }
        }
        //}
        return $result;
    }

    // set the update parameters for the view component order_nbr
    private
    function save_field_order_nbr(sql_db $db_con, user_sandbox $db_rec, user_sandbox $std_rec): string
    {
        $result = '';
        if ($db_rec->order_nbr <> $this->order_nbr) {
            $log = $this->log_upd_field();
            $log->old_value = $db_rec->order_nbr;
            $log->new_value = $this->order_nbr;
            $log->std_value = $std_rec->order_nbr;
            $log->row_id = $this->id;
            $log->field = self::FLD_ORDER_NBR;
            $result .= $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    // set the update parameters for the word type
    function save_field_type(sql_db $db_con, user_sandbox $db_rec, user_sandbox $std_rec): string
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
            $log->field = self::FLD_POS_TYPE;
            $result .= $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    // save all updated view_component_link fields excluding the name, because already done when adding a view_component_link
    function save_fields(sql_db $db_con, user_sandbox $db_rec, user_sandbox $std_rec): string
    {
        $result = $this->save_field_order_nbr($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_type($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_excluded($db_con, $db_rec, $std_rec);
        log_debug('view_component_link->save_fields all fields for ' . $this->dsp_id() . ' has been saved');
        return $result;
    }

    /**
     * create a new link object including the order number
     * @returns int the id of the creates object
     */
    function add_insert(): int
    {
        global $db_con;
        return $db_con->insert(
            array($this->from_name . '_id', $this->to_name . '_id', "user_id", 'order_nbr'),
            array($this->fob->id, $this->tob->id, $this->usr->id, $this->order_nbr));
    }

}
