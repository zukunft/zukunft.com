<?php

/*

  formula_link.php - link a formula to a word
  ----------------
  
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

class formula_link extends user_sandbox
{

    // database fields additional to the user sandbox fields
    public $formula_id = NULL; // the id of the formula to which the word or triple should be linked
    public $phrase_id = NULL; // the id of the linked word or triple

    public $link_type_id = NULL; // define a special behavior for this link (maybe not needed at the moment)
    public $link_name = '';   // ???

    /*
    // in memory only fields for searching and reference
    public $frm           = NULL; // the formula object (used to save the correct name in the log)
    public $phr           = NULL; // the word object (used to save the correct name in the log)
    */

    function __construct()
    {
        $this->obj_type = user_sandbox::TYPE_LINK;
        $this->obj_name = DB_TYPE_FORMULA_LINK;
        $this->from_name = DB_TYPE_FORMULA;
        $this->to_name = DB_TYPE_PHRASE;

    }

    function reset()
    {
        $this->id = NULL;
        $this->usr_cfg_id = NULL;
        $this->usr = NULL;
        $this->owner_id = NULL;
        $this->excluded = NULL;

        $this->formula_id = NULL;
        $this->phrase_id = NULL;
        $this->link_type_id = NULL;
        $this->link_name = '';

        $this->reset_objects();
    }

    // reset the in memory fields used e.g. if some ids are updated
    private function reset_objects()
    {
        $this->fob = NULL;
        $this->tob = NULL;
    }

    private function row_mapper($db_row, $map_usr_fields = false)
    {
        if ($db_row != null) {
            if ($db_row['formula_link_id'] > 0) {
                $this->id = $db_row['formula_link_id'];
                $this->owner_id = $db_row['user_id'];
                $this->formula_id = $db_row['formula_id'];
                $this->phrase_id = $db_row['phrase_id'];
                $this->link_type_id = $db_row['link_type_id'];
                $this->excluded = $db_row['excluded'];
                if ($map_usr_fields) {
                    $this->usr_cfg_id = $db_row['user_formula_link_id'];
                }
            } else {
                $this->id = 0;
            }
        } else {
            $this->id = 0;
        }
    }

    // load the formula parameters for all users
    function load_standard(): bool
    {

        global $db_con;
        $result = false;

        // try to get the search values from the objects
        if ($this->id <= 0) {
            if (isset($this->fob) and $this->formula_id <= 0) {
                $this->formula_id = $this->fob->id;
            }
            if (isset($this->tob) and $this->phrase_id <= 0) {
                $this->phrase_id = $this->tob->id;
            }
        }

        $db_con->set_type(DB_TYPE_FORMULA_LINK);
        $db_con->set_usr($this->usr->id);
        $db_con->set_link_fields('formula_id', 'phrase_id');
        $db_con->set_fields(array('link_type_id', 'excluded'));
        $db_con->set_where_link($this->id, $this->formula_id, $this->phrase_id);
        $sql = $db_con->select();

        if ($db_con->get_where() <> '') {
            $db_frm = $db_con->get1($sql);
            $this->row_mapper($db_frm);
            $result = $this->load_owner();
        }
        return $result;
    }

    // load the missing formula parameters from the database
    function load(): bool
    {
        global $db_con;
        $result = false;

        // check the all minimal input parameters are set
        if (!isset($this->usr)) {
            log_err("The user id must be set to load a formula link.", "formula_link->load");
        } else {

            // try to get the search values from the objects
            if ($this->id <= 0 and ($this->formula_id <= 0 or $this->phrase_id <= 0)) {
                if (isset($this->fob) and $this->formula_id <= 0) {
                    $this->formula_id = $this->fob->id;
                }
                if (isset($this->tob) and $this->phrase_id <= 0) {
                    $this->phrase_id = $this->tob->id;
                }
            }

            $db_con->set_type(DB_TYPE_FORMULA_LINK);
            $db_con->set_usr($this->usr->id);
            $db_con->set_link_fields('formula_id', 'phrase_id');
            $db_con->set_usr_num_fields(array('link_type_id', 'excluded'));
            $db_con->set_where_link($this->id, $this->formula_id, $this->phrase_id);
            $sql = $db_con->select();

            if ($db_con->get_where() <> '') {
                $db_row = $db_con->get1($sql);
                $this->row_mapper($db_row, true);
                if ($this->id > 0) {
                    log_debug('formula_link->load (' . $this->id . ')');
                    $result = true;
                }
            }
        }
        return $result;
    }

    // to load the related objects if the link object is loaded by an external query like in user_display to show the sandbox
    function load_objects(): bool
    {
        $result = true;
        if (!isset($this->fob)) {
            if ($this->formula_id > 0) {
                $frm = new formula;
                $frm->id = $this->formula_id;
                $frm->usr = $this->usr;
                if ($frm->load()) {
                    $this->fob = $frm;
                } else {
                    $result = false;
                }
            }
        }
        if ($result) {
            if (!isset($this->tob)) {
                if ($this->phrase_id <> 0) {
                    $phr = new phrase;
                    $phr->id = $this->phrase_id;
                    $phr->usr = $this->usr;
                    if ($phr->load()) {
                        $this->tob = $phr;
                    } else {
                        $result = false;
                    }
                }
            }
        }
        $this->link_type_name();
        return $result;
    }

    //
    function link_type_name()
    {
        log_debug('formula_link->link_type_name do');

        global $db_con;

        if ($this->link_type_id > 0 and $this->link_name == '') {
            $sql = "SELECT type_name, description
                FROM formula_link_types
               WHERE formula_link_type_id = " . $this->link_type_id . ";";
            //$db_con = new mysql;
            $db_con->usr_id = $this->usr->id;
            $db_type = $db_con->get1($sql);
            $this->link_name = $db_type['type_name'];
        }
        log_debug('formula_link->link_type_name done');
        return $this->link_name;
    }

    /*

    display functions

    */

    // return the html code to display the link name
    function name_linked($back)
    {
        $result = '';

        $this->load_objects();
        if (isset($this->fob)
            and isset($this->tob)) {
            $result = $this->fob->name_linked($back) . ' to ' . $this->tob->dsp_link();
        } else {
            $result .= log_err("The formula or the linked word cannot be loaded.", "formula_link->name");
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

        if ($this->fob->name <> '' and $this->tob->name <> '') {
            $result .= $this->fob->name . ' '; // e.g. Company details
            $result .= $this->tob->name;     // e.g. cash flow statement
        }
        $result .= ' (' . $this->fob->id . ',' . $this->tob->id;
        if ($this->id > 0) {
            $result .= ' -> ' . $this->id . ')';
        }
        if (isset($this->usr)) {
            $result .= ' for user ' . $this->usr->id . ' (' . $this->usr->name . ')';
        }
        return $result;
    }

    // return the html code to display the link name
    function name()
    {
        $result = '';

        if (isset($this->fob)) {
            $result = $this->fob->name();
        }
        if (isset($this->tob)) {
            $result = ' to ' . $this->tob->name();
        }

        return $result;
    }

    /*

    save functions

    */

    // true if no one has used this formula
    function not_used(): bool
    {
        log_debug('formula_link->not_used (' . $this->id . ')');

        // to review: maybe replace by a database foreign key check
        return $this->not_changed();
    }

    // true if no other user has modified the formula
    function not_changed(): bool
    {
        log_debug('formula_link->not_changed (' . $this->id . ') by someone else than the owner (' . $this->owner_id . ')');

        global $db_con;
        $result = true;

        if ($this->owner_id > 0) {
            $sql = "SELECT user_id 
                FROM user_formula_links 
               WHERE formula_link_id = " . $this->id . "
                 AND user_id <> " . $this->owner_id . "
                 AND (excluded <> 1 OR excluded is NULL)";
        } else {
            $sql = "SELECT user_id 
                FROM user_formula_links 
               WHERE formula_link_id = " . $this->id . "
                 AND (excluded <> 1 OR excluded is NULL)";
        }
        //$db_con = new mysql;
        $db_con->usr_id = $this->usr->id;
        $db_row = $db_con->get1($sql);
        if ($db_row['user_id'] > 0) {
            $result = false;
        }
        log_debug('formula_link->not_changed for ' . $this->id . ' is ' . zu_dsp_bool($result));
        return $result;
    }

    // true if the user is the owner and no one else has changed the formula_link
    // because if another user has changed the formula_link and the original value is changed, maybe the user formula_link also needs to be updated
    function can_change(): bool
    {
        if (isset($this->fob) and isset($this->tob)) {
            log_debug('formula_link->can_change "' . $this->fob->name . '"/"' . $this->tob->name . '" by user "' . $this->usr->name . '" (id ' . $this->usr->id . ', owner id ' . $this->owner_id . ')');
        } else {
            log_debug('formula_link->can_change "' . $this->id . '" by user "' . $this->usr->name . '" (id ' . $this->usr->id . ', owner id ' . $this->owner_id . ')');
        }
        $can_change = false;
        if ($this->owner_id == $this->usr->id or $this->owner_id <= 0) {
            $can_change = true;
        }
        log_debug('formula_link->can_change -> (' . zu_dsp_bool($can_change) . ')');
        return $can_change;
    }

    // true if a record for a user specific configuration already exists in the database
    function has_usr_cfg(): bool
    {
        $has_cfg = false;
        if ($this->usr_cfg_id > 0) {
            $has_cfg = true;
        }
        return $has_cfg;
    }

    // create a database record to save user specific settings for this formula_link
    function add_usr_cfg(): bool
    {
        global $db_con;
        $result = true;

        if (!$this->has_usr_cfg()) {
            if (isset($this->fob) and isset($this->tob)) {
                log_debug('formula_link->add_usr_cfg for "' . $this->fob->name . '"/"' . $this->tob->name . '" by user "' . $this->usr->name . '"');
            } else {
                log_debug('formula_link->add_usr_cfg for "' . $this->id . '" and user "' . $this->usr->name . '"');
            }

            // check again if there ist not yet a record
            $db_con->set_type(DB_TYPE_FORMULA_LINK, true);
            $db_con->set_usr($this->usr->id);
            $db_con->set_where($this->id);
            $sql = $db_con->select();
            $db_row = $db_con->get1($sql);
            if ($db_row != null) {
                $this->usr_cfg_id = $db_row['formula_link_id'];
            }
            if (!$this->has_usr_cfg()) {
                // create an entry in the user sandbox
                $db_con->set_type(DB_TYPE_USER_PREFIX . DB_TYPE_FORMULA_LINK);
                $log_id = $db_con->insert(array('formula_link_id', 'user_id'), array($this->id, $this->usr->id));
                if ($log_id <= 0) {
                    log_err('Insert of user_formula_link failed.');
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
        log_debug('formula_link->del_usr_cfg_if_not_needed pre check for "' . $this->dsp_id() . ' und user ' . $this->usr->name);

        global $db_con;
        $result = false;

        //if ($this->has_usr_cfg) {

        // check again if there ist not yet a record
        $sql = "SELECT formula_link_id,
                     link_type_id,
                     excluded
                FROM user_formula_links
               WHERE formula_link_id = " . $this->id . " 
                 AND user_id = " . $this->usr->id . ";";
        //$db_con = New mysql;
        $db_con->usr_id = $this->usr->id;
        $db_row = $db_con->get1($sql);
        log_debug('formula_link->del_usr_cfg_if_not_needed check for ' . $this->dsp_id() . ' und user ' . $this->usr->name . ' with (' . $sql . ')');
        if ($db_row['formula_link_id'] > 0) {
            if ($db_row['link_type_id'] == Null
                and $db_row['excluded'] == Null) {
                // delete the entry in the user sandbox
                log_debug('formula_link->del_usr_cfg_if_not_needed any more for ' . $this->dsp_id() . ' und user ' . $this->usr->name);
                $result = $this->del_usr_cfg_exe($db_con);
            }
        }
        //}
        return $result;
    }


    // set the main log entry parameters for updating one display word link field
    // e.g. that the user can see "moved formula list to position 3 in word view"
    function log_upd_field(): user_log
    {
        // zu_debug('formula_link->log_upd_field '.$this->dsp_id().' for user '.$this->usr->name);
        $log = new user_log;
        $log->usr = $this->usr;
        $log->action = 'update';
        if ($this->can_change()) {
            $log->table = 'formula_links';
        } else {
            $log->table = 'user_formula_links';
        }

        return $log;
    }

    // set the update parameters for the formula type
    function save_field_type($db_con, $db_rec, $std_rec): bool
    {
        $result = true;
        if ($db_rec->link_type_id <> $this->link_type_id) {
            $log = $this->log_upd_field();
            $log->old_value = $db_rec->link_type_name();
            $log->old_id = $db_rec->link_type_id;
            $log->new_value = $this->link_type_name();
            $log->new_id = $this->link_type_id;
            $log->std_value = $std_rec->link_type_name();
            $log->std_id = $std_rec->link_type_id;
            $log->row_id = $this->id;
            $log->field = 'link_type_id';
            $result = $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    // save all updated formula_link fields excluding the name, because already done when adding a formula_link
    function save_fields($db_con, $db_rec, $std_rec): bool
    {
        // link type not used at the moment
        //$result .= $this->save_field_type     ($db_con, $db_rec, $std_rec);
        $result = $this->save_field_excluded($db_con, $db_rec, $std_rec);
        log_debug('formula_link->save_fields all fields for "' . $this->fob->name . '" to "' . $this->tob->name . '" has been saved');
        return $result;
    }

    // update a formula_link in the database or create a user formula_link
    function save(): string
    {

        global $db_con;
        $result = '';

        // check if the required parameters are set
        if (isset($this->fob) and isset($this->tob)) {
            log_debug('formula_link->save "' . $this->fob->name . '" to "' . $this->tob->name . '" (id ' . $this->id . ') for user ' . $this->usr->name);
        } elseif ($this->id > 0) {
            log_debug('formula_link->save id ' . $this->id . ' for user ' . $this->usr->name);
        } else {
            log_err("Either the formula and the word or the id must be set to link a formula to a word.", "formula_link->save");
        }

        // load the objects if needed
        $this->load_objects();

        // build the database object because the is anyway needed
        $db_con->set_usr($this->usr->id);
        $db_con->set_type(DB_TYPE_FORMULA_LINK);

        // check if a new value is supposed to be added
        if ($this->id <= 0) {
            log_debug('formula_link->save check if a new formula_link for "' . $this->fob->name . '" and "' . $this->tob->name . '" needs to be created');
            // check if a formula_link with the same formula and word is already in the database
            $db_chk = new formula_link;
            $db_chk->fob = $this->fob;
            $db_chk->tob = $this->tob;
            $db_chk->usr = $this->usr;
            $db_chk->load_standard();
            if ($db_chk->id > 0) {
                $this->id = $db_chk->id;
            }
        }

        if ($this->id <= 0) {
            log_debug('formula_link->save new link from "' . $this->fob->name . '" to "' . $this->tob->name . '"');
            $result = strval($this->add());
        } else {
            log_debug('formula_link->save update "' . $this->id . '"');
            // read the database values to be able to check if something has been changed; done first,
            // because it needs to be done for user and general formulas
            $db_rec = new formula_link;
            $db_rec->id = $this->id;
            $db_rec->usr = $this->usr;
            $db_rec->load();
            $db_rec->load_objects();
            $db_con->set_type(DB_TYPE_FORMULA_LINK);
            log_debug("formula_link->save -> database formula loaded (" . $db_rec->id . ")");
            $std_rec = new formula_link;
            $std_rec->id = $this->id;
            $std_rec->usr = $this->usr; // must also be set to allow to take the ownership
            $std_rec->load_standard();
            log_debug("formula_link->save -> standard formula settings loaded (" . $std_rec->id . ")");

            // for a correct user formula link detection (function can_change) set the owner even if the formula link has not been loaded before the save
            if ($this->owner_id <= 0) {
                $this->owner_id = $std_rec->owner_id;
            }

            // it should not be possible to change the formula or the word, but nevertheless check
            // instead of changing the formula or the word, a new link should be created and the old deleted
            if ($db_rec->fob->id <> $this->fob->id
                or $db_rec->tob->id <> $this->tob->id) {
                log_debug("formula_link->save -> update link settings for id " . $this->id . ": change formula " . $db_rec->formula_id . " to " . $this->fob->id . " and " . $db_rec->phrase_id . " to " . $this->tob->id);
                $result .= log_info('The formula link "' . $db_rec->fob->name . '" with "' . $db_rec->tob->name . '" (id ' . $db_rec->fob->id . ',' . $db_rec->tob->id . ') " cannot be changed to "' . $this->fob->name . '" with "' . $this->tob->name . '" (id ' . $this->fob->id . ',' . $this->tob->id . '). Instead the program should have created a new link.', "formula_link->save");
            }

            // check if the id parameters are supposed to be changed
            $this->load_objects();
            if ($result == '') {
                $result = $this->save_id_if_updated($db_con, $db_rec, $std_rec);
            }

            // if a problem has appeared up to here, don't try to save the values
            // the problem is shown to the user by the calling interactive script
            if ($result == '') {
                if (!$this->save_fields($db_con, $db_rec, $std_rec)) {
                    $result = 'Saving of fields for ' . $this->obj_name . ' failed';
                    log_err($result);
                }
            }
        }

        return $result;
    }

}