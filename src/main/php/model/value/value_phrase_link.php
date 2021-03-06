<?php

/*

  value_phrase_link.php - only for fast selection of the values assigned to one word or a list of words
  ---------------------
  
  replication of the group saved in the value
  no sure if this object is still needed
  
  a user specific value word link is not allowed
  If a user changes the word links of a value where he is not owner a new value is created
  or if a value with the word conbination already exists, the changes are applied to this value
  
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

class val_lnk
{

    // database fields
    public ?int $id = null;     // the primary database id of the numeric value, which is the same for the standard and the user specific value
    public ?user $usr = null;   // the person for whom the value word is loaded, so to say the viewer
    public ?int $val_id = null; // the id of the linked value
    public ?int $wrd_id = null; // the id of the linked word

    // field for modifications
    public ?value $val = null;  // the value object to which the words are linked
    public ?word $wrd = null;   // the word (not the triple) object to be linked to the value

    // maybe not used at the moment
    //public $type         = null;  //
    //public $weight       = null;  //
    //public $frm_id       = null;  //

    // load the word to value link from the database
    function load()
    {

        global $db_con;

        $sql = '';
        // the id and the user must be set
        if ($this->id > 0) {
            $sql = "SELECT value_phrase_link_id,
                     value_id,
                     phrase_id
                FROM value_phrase_links 
               WHERE value_phrase_link_id = " . $this->id . ";";
        }
        if ($this->val->id > 0 and $this->wrd->id > 0) {
            $sql = "SELECT value_phrase_link_id,
                     value_id,
                     phrase_id
                FROM value_phrase_links 
               WHERE value_id = " . $this->val->id . "
                 AND phrase_id = " . $this->wrd->id . ";";
        }
        if ($sql <> '') {
            //$db_con = new mysql;
            $db_con->usr_id = $this->usr->id;
            $db_val = $db_con->get1($sql);
            $this->id = $db_val['value_phrase_link_id'];
            $this->val_id = $db_val['value_id'];
            $this->wrd_id = $db_val['phrase_id'];
        } else {
            log_err("Cannot find value word link, because neither the id nor the value and word are set", "val_lnk->load");
        }
    }


    // true if no other user has ever used the related value
    private function used()
    {
        $result = true;

        if (isset($this->val)) {
            log_debug('val_lnk->used check if value with id ' . $this->val->id . ' has never been used');
            $result = $this->val->used();
            log_debug('val_lnk->used for id ' . $this->val->id . ' is ' . zu_dsp_bool($result));
        }
        return $result;
    }

    // set the log entry parameter for a new value word link
    private function log_add(): user_log_link
    {
        log_debug('val_lnk->log_add for "' . $this->wrd->id . ' to ' . $this->val->id);
        $log = new user_log_link;
        $log->usr = $this->usr;
        $log->action = 'add';
        $log->table = 'value_phrase_links';
        $log->new_from = $this->val;
        $log->new_to = $this->wrd;
        $log->row_id = 0;
        $log->add();

        return $log;
    }

    // set the main log entry parameters for updating one value word link
    // e.g. if the entered the number for "interest income", but see that he has used the word "interest cost" and changes it to "interest income"
    private function log_upd($db_rec): user_log_link
    {
        log_debug('val_lnk->log_upd for "' . $this->wrd->id . ' to ' . $this->val->id);
        $log = new user_log_link;
        $log->usr = $this->usr;
        $log->action = 'update';
        $log->table = 'value_phrase_links'; // no user sandbox for links, only the values itself can differ from user to user
        //$log->field = 'phrase_id';
        $log->old_from = $db_rec->val;
        $log->old_to = $db_rec->wrd;
        $log->new_from = $this->val;
        $log->new_to = $this->wrd;
        $log->row_id = $this->id;

        return $log;
    }

    // set the log entry parameter to remove a value word link
    private function log_del(): user_log_link
    {
        log_debug('val_lnk->log_del for "' . $this->wrd->id . ' to ' . $this->val->id);
        $log = new user_log_link;
        $log->usr = $this->usr;
        $log->action = 'del';
        $log->table = 'value_phrase_links';
        $log->old_from = $this->val;
        $log->new_to = $this->wrd;
        $log->row_id = $this->id;
        $log->add();

        return $log;
    }

    // save the new word link
    private function save_field_wrd($db_con, $db_rec)
    {
        $result = '';
        if ($db_rec->wrd->id <> $this->wrd->id) {
            $log = $this->log_upd();
            if ($log->add()) {
                $db_con->set_type(DB_TYPE_VALUE_PHRASE_LINK);
                $result .= $db_con->update($this->id, 'phrase_id', $this->wrd->id);
            }
        }
        return $result;
    }

    private function cleanup($db_con): bool
    {
        $result = true;

        // check duplicates
        $sql = "SELECT value_phrase_link_id 
              FROM value_phrase_links 
             WHERE value_id = " . $this->val->id . " 
               AND phrase_id  = " . $this->wrd->id . " 
               AND value_phrase_link_id <> " . $this->id . ";";
        $db_row = $db_con->get1($sql);
        $this->id = $db_row['value_phrase_link_id'];
        if ($this->id > 0) {
            //$result = $db_con->delete(array('value_id','phrase_id','value_phrase_link_id'), array($this->val->id,$this->wrd->id,$this->id));
            $sql_del = "DELETE FROM value_phrase_links 
                    WHERE value_id = " . $this->val->id . " 
                      AND phrase_id  = " . $this->wrd->id . " 
                      AND value_phrase_link_id <> " . $this->id . ";";
            $sql_result = $db_con->exe($sql_del, $this->usr->id, DBL_SYSLOG_ERROR, "val_lnk->update", (new Exception)->getTraceAsString());
            $db_row = $db_con->get1($sql);
            $this->id = $db_row['value_phrase_link_id'];
            if ($this->id > 0) {
                log_err("Duplicate words (" . $this->wrd->id . ") for value " . $this->val_id . " found and the automatic removal failed.", "val_lnk->update");
            } else {
                log_warning("Duplicate words (" . $this->wrd->id . ") for value " . $this->val_id . " found, but they have been removed automatically.", "val_lnk->update");
            }
        }
        return $result;
    }

    // to be dismissed
    function update()
    {
        $this->save();
    }

    // change a link of a word to a value
    // only allowed if the value has not yet been used
    function save()
    {
        log_debug("val_lnk->save link word id " . $this->wrd->name . " to " . $this->val->id . " (link id " . $this->id . " for user " . $this->usr->id . ").");

        global $db_con;
        $db_con->set_usr($this->usr->id);
        $db_con->set_type(DB_TYPE_VALUE_PHRASE_LINK);

        if (!$this->used()) {
            // check if a new value is supposed to be added
            if ($this->id <= 0) {
                log_debug("val_lnk->save check if word " . $this->wrd->name . " is already linked to " . $this->val->id . ".");
                // check if a value_phrase_link with the same word is already in the database
                $db_chk = new val_lnk;
                $db_chk->val = $this->val;
                $db_chk->wrd = $this->wrd;
                $db_chk->usr = $this->usr;
                $db_chk->load();
                if ($db_chk->id > 0) {
                    $this->id = $db_chk->id;
                }
            }

            if ($this->id <= 0) {
                log_debug('val_lnk->save add new value_phrase_link of "' . $this->wrd->name . '" to "' . $this->val->id . '"');
                // log the insert attempt first
                $log = $this->log_add();
                if ($log->id > 0) {
                    // insert the new value_phrase_link
                    $db_con->set_type(DB_TYPE_VALUE_PHRASE_LINK);
                    $this->id = $db_con->insert(array("value_id", "word_id"), array($this->val->id, $this->wrd->id));
                    if ($this->id > 0) {
                        // update the id in the log
                        $result = $log->add_ref($this->id);
                    } else {
                        log_err("Adding value_phrase_link " . $this->val->id . " failed.", "val_lnk->save");
                    }
                }
            } else {
                log_debug('val_lnk->save update "' . $this->id . '"');
                // read the database values to be able to check if something has been changed; done first,
                // because it needs to be done for user and general formulas
                $db_rec = new val_lnk;
                $db_rec->id = $this->id;
                $db_rec->usr = $this->usr;
                $db_rec->load();
                log_debug("val_lnk->save -> database value_phrase_link loaded (" . $db_rec->id . ")");

                // update the linked word
                $result = $this->save_field_wrd($db_con, $db_rec);

                // check for duplicates and remove them
                $result .= $this->cleanup($db_con);

            }
        } else {
            // try to create a new value and link all words
            // if the value already exist, create a user entry
        }
        log_debug("val_lnk->save ... done");
    }

    // remove a link
    // the user id is the user who has requested the change,
    // but it is a parameter and not part of the object, because there are not user specific value word links
    function del($user_id)
    {
        log_debug("val_lnk->del (v" . $this->val_id . ",t" . $this->wrd->id . ",u" . $user_id . ")");

        global $db_con;
        $result = '';

        if (!$this->used()) {
            $log = $this->log_add();
            if ($log->id > 0) {
                //$db_con = new mysql;
                $db_con->usr_id = $this->usr->id;
                $db_con->set_type(DB_TYPE_VALUE_PHRASE_LINK);
                $result .= $db_con->delete(array('value_id', 'phrase_id'), array($this->val->id, $this->wrd->id));
            }
        } else {
            // check if removing a word link is matching another value
            // if yes merge value with this value
            // if no create a new value
        }

        log_debug("val_lnk->del -> (" . $result . ")");
        return $result;
    }

}

?>
