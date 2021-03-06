<?php

/*

  verb.php - predicate object to link two words
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

class verb
{

    public ?int $id = null;           // the database id of the word link type (verb)
    public ?user $usr = null;         // not used at the moment, because there should not be any user specific verbs
    //                                   otherwise if id is 0 (not NULL) the standard word link type, otherwise the user specific verb
    public ?string $code_id = '';     // the main id to detect verbs that have a special behavior
    public ?string $name = '';        // the verb name to build the "sentence" for the user, which cannot be empty
    public ?string $plural = '';      // name used if more than one word is shown
    //                                   e.g. instead of "ABB" "is a" "company"
    //                                        use "ABB", Nestlé" "are" "companies"
    public ?string $reverse = '';     // name used if displayed the other way round
    //                                   e.g. for "Country" "has a" "Human Development Index"
    //                                        the reverse would be "Human Development Index" "is used for" "Country"
    public ?string $rev_plural = '';  // the reverse name for many words
    public ?string $frm_name = '';    // short name of the verb for the use in formulas, because there both sides are combined
    public ?string $description = ''; // for the mouse over explain

    // set the class vars based on a database record
    // $db_row is an array with the database values
    function row_mapper($db_row): bool
    {
        $result = false;
        if ($db_row != null) {
            if ($db_row['verb_id'] > 0) {
                $this->id = $db_row['verb_id'];
                $this->code_id = $db_row['code_id'];
                $this->name = $db_row['verb_name'];
                $this->plural = $db_row['name_plural'];
                $this->reverse = $db_row['name_reverse'];
                $this->rev_plural = $db_row['name_plural_reverse'];
                $this->frm_name = $db_row['formula_name'];
                $this->description = $db_row['description'];
                $result = true;
            } else {
                $this->id = 0;
            }
        } else {
            $this->id = 0;
        }
        return $result;
    }

    // load the missing verb parameters from the database
    function load(): bool
    {

        global $db_con;
        $result = false;

        // set the where clause depending on the values given
        $sql_where = '';
        if ($this->code_id > 0) {
            $sql_where = "code_id = " . $this->code_id;
        } elseif ($this->id > 0) {
            $sql_where = "verb_id = " . $this->id;
        } elseif ($this->name <> '') {
            $sql_where = "( verb_name = " . sf($this->name, sql_db::FLD_FORMAT_TEXT) . " OR formula_name = " . sf($this->name, sql_db::FLD_FORMAT_TEXT) . ")";
        }

        if ($sql_where == '') {
            log_err("Either the database ID or the verb name must be set for loading.", "verb->load");
        } else {
            log_debug('verb->load by (' . $sql_where . ')');
            // similar statement used in word_link_list->load, check if changes should be repeated in word_link_list.php
            $db_con->set_type(DB_TYPE_VERB);
            $db_con->set_usr($this->usr->id);
            $db_con->set_fields(array('code_id', 'name_plural', 'name_reverse', 'name_plural_reverse', 'formula_name', 'description'));
            $db_con->set_where_text($sql_where);
            $sql = $db_con->select();
            if (!isset($this->usr)) {
                log_err("User is missing", "verb->load");
            } else {
                $db_con->usr_id = $this->usr->id;
            }
            $db_row = $db_con->get1($sql);
            $result = $this->row_mapper($db_row);
            log_debug('verb->load (' . $this->dsp_id() . ')');
        }
        return $result;
    }

    /*

    display functions

    */

    // display the unique id fields (used also for debugging)
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

    function name(): string
    {
        return $this->name;
    }

    // create the HTML code to display the formula name with the HTML link
    function display($back): string
    {
        return '<a href="/http/verb_edit.php?id=' . $this->id . '&back=' . $back . '">' . $this->name . '</a>';
    }

    // returns the html code to select a word link type
    // database link must be open
    function dsp_selector($side, $form, $class, $back)
    {
        log_debug('verb->dsp_selector -> for verb id ' . $this->id);
        $result = '';

        if ($side == 'forward') {
            // TODO: add the PostgreSQL version
            $sql = "SELECT * FROM (
              SELECT verb_id AS id, 
                    IF (name_reverse <> '' AND name_reverse <> verb_name, CONCAT(verb_name, ' (', name_reverse, ')'), verb_name) AS name,
                    words
                FROM verbs ) AS links
            ORDER BY words DESC, name;";
        } else {
            $sql = "SELECT * FROM (
            SELECT verb_id AS id, 
                   IF (name_reverse <> '' AND name_reverse <> verb_name, CONCAT(verb_name, ' (', name_reverse, ')'), verb_name) AS name,
                   words
              FROM verbs 
      UNION SELECT verb_id * -1 AS id, 
                   CONCAT(name_reverse, ' (', verb_name, ')') AS name,
                   words
              FROM verbs 
             WHERE name_reverse <> '' 
               AND name_reverse <> verb_name) AS links
          ORDER BY words DESC, name;";
        }
        $sel = new selector;
        $sel->usr = $this->usr;
        $sel->form = $form;
        $sel->name = 'verb';
        $sel->label = "Verb:";
        $sel->bs_class = $class;
        $sel->sql = $sql;
        $sel->selected = $this->id;
        $sel->dummy_text = '';
        $result .= $sel->display();
        log_debug('verb->dsp_selector -> select sql ' . $sql);

        log_debug('verb->dsp_selector -> admin id ' . $this->id);
        if (isset($this->usr)) {
            if ($this->usr->is_admin()) {
                // admin users should always have the possibility to create a new verb / link type
                $result .= btn_add('add new verb', '/http/verb_add.php?back=' . $back);
            }
        }

        log_debug('verb->dsp_selector -> done verb id ' . $this->id);
        return $result;
    }

    // show the html form to add or edit a new verb
    function dsp_edit($back)
    {
        log_debug('verb->dsp_edit ' . $this->dsp_id());
        $result = '';

        if ($this->id <= 0) {
            $script = "verb_add";
            $result .= dsp_text_h2('Add verb (word link type)');
            $result .= dsp_form_start($script);
        } else {
            $script = "verb_edit";
            $result .= dsp_text_h2('Change verb (word link type)');
            $result .= dsp_form_start($script);
        }
        $result .= dsp_tbl_start_half();
        $result .= '  <tr>';
        $result .= '    <td>';
        $result .= '      verb name:';
        $result .= '    </td>';
        $result .= '    <td>';
        $result .= '      <input type="text" name="name" value="' . $this->name . '">';
        $result .= '    </td>';
        $result .= '  </tr>';
        $result .= '  <tr>';
        $result .= '    <td>';
        $result .= '      verb plural:';
        $result .= '    </td>';
        $result .= '    <td>';
        $result .= '      <input type="text" name="plural" value="' . $this->plural . '">';
        $result .= '    </td>';
        $result .= '  </tr>';
        $result .= '  <tr>';
        $result .= '    <td>';
        $result .= '      reverse:';
        $result .= '    </td>';
        $result .= '    <td>';
        $result .= '      <input type="text" name="reverse" value="' . $this->reverse . '">';
        $result .= '    </td>';
        $result .= '  </tr>';
        $result .= '  <tr>';
        $result .= '    <td>';
        $result .= '      plural_reverse:';
        $result .= '    </td>';
        $result .= '    <td>';
        $result .= '      <input type="text" name="plural_reverse" value="' . $this->rev_plural . '">';
        $result .= '    </td>';
        $result .= '  </tr>';
        $result .= '  <input type="hidden" name="back" value="' . $back . '">';
        $result .= '  <input type="hidden" name="confirm" value="1">';
        $result .= dsp_tbl_end();
        $result .= dsp_form_end();

        log_debug('verb->dsp_edit ... done');
        return $result;
    }

    // get the term corresponding to this verb name
    // so in this case, if a word or formula with the same name already exists, get it
    private function term()
    {
        $trm = new term;
        $trm->name = $this->name;
        $trm->usr = $this->usr;
        $trm->load();
        return $trm;
    }

    /*

    save functions

    */

    // true if no one has used this verb
    private function not_used(): bool
    {
        log_debug('verb->not_used (' . $this->id . ')');

        global $db_con;
        $result = true;

        // to review: additional check the database foreign keys
        $sql = "SELECT words 
              FROM verbs 
             WHERE verb_id = " . $this->id . ";";
        //$db_con = new mysql;
        $db_con->usr_id = $this->usr->id;
        $db_row = $db_con->get1($sql);
        $used_by_words = $db_row['words'];
        if ($used_by_words > 0) {
            $result = false;
        }

        return $result;
    }

    // true if no other user has modified the verb
    private function not_changed(): bool
    {
        log_debug('verb->not_changed (' . $this->id . ') by someone else than the owner (' . $this->owner_id . ')');

        global $db_con;
        $result = true;

        /*
        $change_user_id = 0;
        $sql = "SELECT user_id
                  FROM user_verbs
                 WHERE verb_id = ".$this->id."
                   AND user_id <> ".$this->owner_id."
                   AND (excluded <> 1 OR excluded is NULL)";
        //$db_con = new mysql;
        $db_con->usr_id = $this->usr->id;
        $change_user_id = $db_con->get1($sql);
        if ($change_user_id > 0) {
          $result = false;
        }
        */

        log_debug('verb->not_changed for ' . $this->id . ' is ' . zu_dsp_bool($result));
        return $result;
    }

    // true if no one else has used the verb
    function can_change(): bool
    {
        log_debug('verb->can_change ' . $this->id);
        $can_change = false;
        if ($this->not_used and $this->not_changed) {
            $can_change = true;
        }

        log_debug('verb->can_change -> (' . zu_dsp_bool($can_change) . ')');
        return $can_change;
    }

    // set the log entry parameter for a new verb
    private function log_add(): user_log
    {
        log_debug('verb->log_add ' . $this->dsp_id());
        $log = new user_log;
        $log->usr = $this->usr;
        $log->action = 'add';
        $log->table = 'verbs';
        $log->field = 'verb_name';
        $log->old_value = '';
        $log->new_value = $this->name;
        $log->row_id = 0;
        $log->add();

        return $log;
    }

    // set the main log entry parameters for updating one verb field
    private function log_upd(): user_log
    {
        log_debug('verb->log_upd ' . $this->dsp_id() . ' for user ' . $this->usr->name);
        $log = new user_log;
        $log->usr = $this->usr;
        $log->action = 'update';
        $log->table = 'verbs';

        return $log;
    }

    // set the log entry parameter to delete a verb
    private function log_del(): user_log
    {
        log_debug('verb->log_del ' . $this->dsp_id() . ' for user ' . $this->usr->name);
        $log = new user_log;
        $log->usr = $this->usr;
        $log->action = 'del';
        $log->table = 'verbs';
        $log->field = 'verb_name';
        $log->old_value = $this->name;
        $log->new_value = '';
        $log->row_id = $this->id;
        $log->add();

        return $log;
    }

    // actually update a formula field in the main database record or the user sandbox
    private function save_field_do($db_con, $log): bool
    {
        $result = true;
        if ($log->new_id > 0) {
            $new_value = $log->new_id;
            $std_value = $log->std_id;
        } else {
            $new_value = $log->new_value;
            $std_value = $log->std_value;
        }
        if ($log->add()) {
            if ($this->can_change()) {
                $db_con->set_type(DB_TYPE_VERB);
                $result .= $db_con->update($this->id, $log->field, $new_value);
            } else {
                // todo: create a new verb and request to delete the old
            }
        }
        return $result;
    }

    // set the update parameters for the verb name
    private function save_field_name($db_con, $db_rec): bool
    {
        $result = true;
        if ($db_rec->name <> $this->name) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->name;
            $log->new_value = $this->name;
            $log->std_value = $db_rec->name;
            $log->row_id = $this->id;
            $log->field = 'verb_name';
            $result = $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    // set the update parameters for the verb plural
    private function save_field_plural($db_con, $db_rec): bool
    {
        $result = true;
        if ($db_rec->plural <> $this->plural) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->plural;
            $log->new_value = $this->plural;
            $log->std_value = $db_rec->plural;
            $log->row_id = $this->id;
            $log->field = 'name_plural';
            $result = $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    // set the update parameters for the verb reverse
    private function save_field_reverse($db_con, $db_rec): bool
    {
        $result = true;
        if ($db_rec->reverse <> $this->reverse) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->reverse;
            $log->new_value = $this->reverse;
            $log->std_value = $db_rec->reverse;
            $log->row_id = $this->id;
            $log->field = 'name_reverse';
            $result = $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    // set the update parameters for the verb rev_plural
    private function save_field_rev_plural($db_con, $db_rec): bool
    {
        $result = true;
        if ($db_rec->rev_plural <> $this->rev_plural) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->rev_plural;
            $log->new_value = $this->rev_plural;
            $log->std_value = $db_rec->rev_plural;
            $log->row_id = $this->id;
            $log->field = 'name_plural_reverse';
            $result = $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    // set the update parameters for the verb description
    private function save_field_description($db_con, $db_rec): bool
    {
        $result = true;
        if ($db_rec->description <> $this->description) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->description;
            $log->new_value = $this->description;
            $log->std_value = $db_rec->description;
            $log->row_id = $this->id;
            $log->field = 'description';
            $result = $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    // save all updated verb fields excluding the name, because already done when adding a verb
    private function save_fields($db_con, $db_rec): bool
    {
        $result = $this->save_field_plural($db_con, $db_rec);
        if ($result) {
            $result = $this->save_field_reverse($db_con, $db_rec);
        }
        if ($result) {
            $result = $this->save_field_rev_plural($db_con, $db_rec);
        }
        if ($result) {
            $result = $this->save_field_description($db_con, $db_rec);
        }
        log_debug('verb->save_fields all fields for ' . $this->dsp_id() . ' has been saved');
        return $result;
    }

    // check if the id parameters are supposed to be changed
    private function save_id_if_updated($db_con, $db_rec, $std_rec): string
    {
        $result = '';
        /*
            todo:
            if ($db_rec->name <> $this->name) {
              // check if target link already exists
              zu_debug('verb->save_id_if_updated check if target link already exists '.$this->dsp_id().' (has been "'.$db_rec->dsp_id().'")');
              $db_chk = clone $this;
              $db_chk->id = 0; // to force the load by the id fields
              $db_chk->load_standard();
              if ($db_chk->id > 0) {
                if (UI_CAN_CHANGE_VIEW_COMPONENT_NAME) {
                  // ... if yes request to delete or exclude the record with the id parameters before the change
                  $to_del = clone $db_rec;
                  $result .= $to_del->del();
                  // .. and use it for the update
                  $this->id = $db_chk->id;
                  $this->owner_id = $db_chk->owner_id;
                  // force the include again
                  $this->excluded = null;
                  $db_rec->excluded = '1';
                  $this->save_field_excluded ($db_con, $db_rec, $std_rec);
                  zu_debug('verb->save_id_if_updated found a display component link with target ids "'.$db_chk->dsp_id().'", so del "'.$db_rec->dsp_id().'" and add '.$this->dsp_id());
                } else {
                  $result .= 'A view component with the name "'.$this->name.'" already exists. Please use another name.';
                }
              } else {
                if ($this->can_change() AND $this->not_used()) {
                  // in this case change is allowed and done
                  zu_debug('verb->save_id_if_updated change the existing display component link '.$this->dsp_id().' (db "'.$db_rec->dsp_id().'", standard "'.$std_rec->dsp_id().'")');
                  //$this->load_objects();
                  $result .= $this->save_id_fields($db_con, $db_rec, $std_rec);
                } else {
                  // if the target link has not yet been created
                  // ... request to delete the old
                  $to_del = clone $db_rec;
                  $result .= $to_del->del();
                  // .. and create a deletion request for all users ???

                  // ... and create a new display component link
                  $this->id = 0;
                  $this->owner_id = $this->usr->id;
                  $result .= $this->add($db_con);
                  zu_debug('verb->save_id_if_updated recreate the display component link del "'.$db_rec->dsp_id().'" add '.$this->dsp_id().' (standard "'.$std_rec->dsp_id().'")');
                }
              }
            }
        */
        log_debug('verb->save_id_if_updated for ' . $this->dsp_id() . ' has been done');
        return $result;
    }

    // create a new verb
    private function add($db_con): int
    {
        log_debug('verb->add the verb ' . $this->dsp_id());
        $result = 0;

        // log the insert attempt first
        $log = $this->log_add();
        if ($log->id > 0) {
            // insert the new verb
            $db_con->set_type(DB_TYPE_VERB);
            $this->id = $db_con->insert("verb_name", $this->name);
            if ($this->id > 0) {
                // update the id in the log
                if (!$log->add_ref($this->id)) {
                    log_err('Updating the reference in the log failed');
                    // TODO do rollback or retry?
                } else {

                    // create an empty db_rec element to force saving of all set fields
                    $db_rec = new verb;
                    $db_rec->name = $this->name;
                    $db_rec->usr = $this->usr;
                    // save the verb fields
                    if ($this->save_fields($db_con, $db_rec)) {
                        $result = $this->id;
                    }
                }

            } else {
                log_err("Adding verb " . $this->name . " failed.", "verb->save");
            }
        }

        return $result;
    }

    // add or update a verb in the database (or create a user verb if the program settings allow this)
    function save(): string
    {
        log_debug('verb->save ' . $this->dsp_id() . ' for user ' . $this->usr->name);

        global $db_con;
        $result = '';

        // build the database object because the is anyway needed
        $db_con->set_usr($this->usr->id);
        $db_con->set_type(DB_TYPE_VERB);

        // check if a new word is supposed to be added
        if ($this->id <= 0) {
            // check if a word, formula or verb with the same name is already in the database
            $trm = $this->term();
            if ($trm->id > 0 and $trm->type <> 'verb') {
                $result .= $trm->id_used_msg();
            } else {
                $this->id = $trm->id;
                log_debug('verb->save adding verb name ' . $this->dsp_id() . ' is OK');
            }
        }

        // create a new verb or update an existing
        if ($this->id <= 0) {
            $result .= $this->add($db_con);
        } else {
            log_debug('verb->save update "' . $this->id . '"');
            // read the database values to be able to check if something has been changed; done first,
            // because it needs to be done for user and general formulas
            $db_rec = new verb;
            $db_rec->id = $this->id;
            $db_rec->usr = $this->usr;
            $db_rec->load();
            log_debug("verb->save -> database verb loaded (" . $db_rec->name . ")");

            // if the name has changed, check if verb, verb or formula with the same name already exists; this should have been checked by the calling function, so display the error message directly if it happens
            if ($db_rec->name <> $this->name) {
                // check if a verb, formula or verb with the same name is already in the database
                $trm = $this->term();
                if ($trm->id > 0 and $trm->type <> 'verb') {
                    $result .= $trm->id_used_msg();
                } else {
                    if ($this->can_change()) {
                        $result .= $this->save_field_name($db_con, $db_rec);
                    } else {
                        // todo: create a new verb and request to delete the old
                    }
                }
            }

            // if a problem has appeared up to here, don't try to save the values
            // the problem is shown to the user by the calling interactive script
            if ($result == '') {
                if (!$this->save_fields($db_con, $db_rec)) {
                    $result = 'Saving of fields for a verb failed';
                    log_err($result);
                }
            }
        }

        return $result;
    }

    // exclude or delete a verb
    function del(): bool
    {
        log_debug('verb->del');

        global $db_con;
        $result = false;

        $result .= $this->load();
        if ($this->id > 0 and $result == '') {
            log_debug('verb->del ' . $this->dsp_id());
            if ($this->can_change()) {
                $log = $this->log_del();
                if ($log->id > 0) {
                    //$db_con = new mysql;
                    $db_con->usr_id = $this->usr->id;
                    $db_con->set_type(DB_TYPE_VERB);
                    $result = $db_con->delete('verb_id', $this->id);
                }
            } else {
                // todo: create a new verb and request to delete the old
            }
        }
        return $result;
    }

}

?>
