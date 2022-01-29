<?php

/*

  user_log_link.php - object to save updates of references (links) by the user in the database in a format, so that it can fast be displayed to the user
  -----------------

  A requirement for the expected behaviour of this setup is the strict adherence of these rules in all classes:

  1. never change a database ID
  2. never delete a word


  Other assumptions are:

  Every user has its sandbox, means a list of all his changes

  The normal word table contain the value, word, formula, verb or links that is used by most users
  for each normal table there is an overwrite table with the user changes/overwrites
  maybe for each huge table is also a log table with the hist of the user changes


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


class user_log_link extends user_log
{

    // object set by the calling function
    public ?object $old_from = null;       // the from reference before the user change; should be the object, but is sometimes still the id
    public ?object $old_link = null;       // the reference type before the user change
    public ?object $old_to = null;         // the to reference before the user change
    public ?object $new_from = null;       // the from reference after the user change
    public ?object $new_link = null;       // the reference type after the user change
    public ?object $new_to = null;         // the to reference after the user change
    public ?object $std_from = null;       // the standard from reference for all users that does not have changed it
    public ?object $std_link = null;       // the standard reference type for all users that does not have changed it
    public ?object $std_to = null;         // the standard to reference for all users that does not have changed it
    public ?int $row_id = null;            // the reference id of the row in the database table
    // fields to save the database row that are filled here based on the object
    public ?int $old_from_id = null;       // old id ref to the from record
    public ?int $old_link_id = null;       // old id ref to the link record
    public ?int $old_to_id = null;         // old id ref to the to record
    public ?string $old_text_from = null;     // fixed description for old_from
    public ?string $old_text_link = null;  // fixed description for old_link
    public ?string $old_text_to = null;    // fixed description for old_to
    public ?int $new_from_id = null;       // new id ref to the from record
    public ?int $new_link_id = null;       // new id ref to the link record
    public ?int $new_to_id = null;         // new id ref to the to record
    public ?string $new_text_from = null;  // fixed description for new_from
    public ?string $new_text_link = null;  // fixed description for new_link
    public ?string $new_text_to = null;    // fixed description for new_to
    // to be replaced with new_text_link
    public ?string $link_text = null;      // is used for fixed links such as the source for values

    private function dsp_id(): string
    {
        $result = '';

        if (isset($this->usr)) {
            $result .= 'user_log_link for user ' . $this->usr->dsp_id();
        }
        $result .= ' action ' . $this->action . ' (' . $this->action_id . ')';
        $result .= ' table ' . $this->table . ' (' . $this->table_id . ')';
        if (isset($this->old_from)) {
            $result .= ' from old ' . $this->old_from->dsp_id();
        }
        if (isset($this->old_link)) {
            $result .= ' link old ' . $this->old_link->dsp_id();
        }
        if (isset($this->old_to)) {
            $result .= ' to old ' . $this->old_to->dsp_id();
        }
        if (isset($this->new_from)) {
            $result .= ' from new ' . $this->new_from->dsp_id();
        }
        if (isset($this->new_link)) {
            $result .= ' link new ' . $this->new_link->dsp_id();
        }
        if (isset($this->new_to)) {
            $result .= ' to new ' . $this->new_to->dsp_id();
        }

        return $result;
    }

    // identical to the functions in user_log (maybe move to a common object??)
    protected function set_table()
    {
        log_debug('user_log_link->set_table "' . $this->table . '" for ' . $this->usr->dsp_id());

        global $db_con;

        // check parameter
        if ($this->table == "") {
            log_err("missing table name", "user_log_link->set_table");
        }
        if ($this->usr->id <= 0) {
            log_err("missing user", "user_log_link->set_table");
        }

        // if e.g. a "value" is changed $this->table is "values" and the reference 1 is saved in the log to save space
        //$db_con = new mysql;
        $db_type = $db_con->get_type();
        $db_con->set_type(DB_TYPE_CHANGE_TABLE);
        $db_con->usr_id = $this->usr->id;
        $table_id = $db_con->get_id($this->table);

        // add new table name if needed
        if ($table_id <= 0) {
            $table_id = $db_con->add_id($this->table);
        }
        if ($table_id > 0) {
            $this->table_id = $table_id;
        } else {
            log_fatal("Insert to change log failed due to table id failure.", "user_log->add");
        }
        // restore the type before saving the log
        $db_con->set_type($db_type);
    }

    protected function set_action()
    {
        log_debug('user_log_link->set_action "' . $this->action . '" for ' . $this->usr->dsp_id());

        global $db_con;

        // check parameter
        if ($this->action == "") {
            log_err("missing action name", "user_log_link->set_action");
        }
        if ($this->usr->id <= 0) {
            log_err("missing user", "user_log_link->set_action");
        }

        // if e.g. the action is "add" the reference 1 is saved in the log table to save space
        //$db_con = new mysql;
        $db_type = $db_con->get_type();
        $db_con->set_type(DB_TYPE_CHANGE_ACTION);
        $db_con->usr_id = $this->usr->id;
        $action_id = $db_con->get_id($this->action);

        // add new action name if needed
        if ($action_id <= 0) {
            $action_id = $db_con->add_id($this->action);
        }
        if ($action_id > 0) {
            $this->action_id = $action_id;
        } else {
            log_fatal("Insert to change log failed due to action id failure.", "user_log_link->set_action");
        }
        // restore the type before saving the log
        $db_con->set_type($db_type);
    }

    // functions used utils each call is done with the object instead of the id
    private function set_usr()
    {
        log_debug('user_log_link->set_usr for ' . $this->usr->dsp_id());
        if (!isset($this->usr)) {
            $usr = new user;
            $usr->id = $this->usr->id;
            $usr->load_test_user();
            $this->usr = $usr;
            log_debug('user_log_link->set_usr got ' . $this->usr->name);
        }
    }

    private function word_name($id)
    {
        log_debug('user_log_link->word_name for ' . $id);
        $result = '';
        if ($id > 0) {
            $this->set_usr();
            $wrd = new word_dsp($this->usr);
            $wrd->id = $id;
            $wrd->load();
            $result = $wrd->name;
            log_debug('user_log_link->word_name got ' . $result);
        }
        return $result;
    }

    private function source_name($id)
    {
        global $db_con;
        $result = '';
        //$db_con = new mysql;
        $db_con->set_type(DB_TYPE_SOURCE);
        $result .= $db_con->get_name($id);
        return $result;
    }


    // this should be dismissed
    function add_link_ref(): bool
    {
        return $this->add();
    }

    // log a user change of a link / verb
    // this should be dismissed, instead use add, which also save the text reference for fast and reliable displaying
    function add_link()
    {
        log_debug("user_log_link->add_link (u" . $this->usr->id . " " . $this->action . " " . $this->table .
            ",of" . $this->old_from . ",ol" . $this->old_link . ",ot" . $this->old_to .
            ",nf" . $this->new_from . ",nl" . $this->new_link . ",nt" . $this->new_to . ",r" . $this->row_id . ")");

        global $db_con;

        $this->set_table();
        $this->set_action();

        $sql_fields = array();
        $sql_values = array();
        $sql_fields[] = "user_id";
        $sql_values[] = $this->usr->id;
        $sql_fields[] = "change_action_id";
        $sql_values[] = $this->action_id;
        $sql_fields[] = "change_table_id";
        $sql_values[] = $this->table_id;

        $sql_fields[] = "old_from_id";
        $sql_values[] = $this->old_from;
        $sql_fields[] = "old_link_id";
        $sql_values[] = $this->old_link;
        $sql_fields[] = "old_to_id";
        $sql_values[] = $this->old_to;

        $sql_fields[] = "new_from_id";
        $sql_values[] = $this->new_from;
        $sql_fields[] = "new_link_id";
        $sql_values[] = $this->new_link;
        $sql_fields[] = "new_to_id";
        $sql_values[] = $this->new_to;

        $sql_fields[] = "row_id";
        $sql_values[] = $this->row_id;

        //$db_con = new mysql;
        $db_type = $db_con->get_type();
        $db_con->set_type(DB_TYPE_CHANGE_LINK);
        $db_con->set_usr($this->usr->id);
        $log_id = $db_con->insert($sql_fields, $sql_values);

        if ($log_id <= 0) {
            // write the error message in steps to get at least some message if the parameters has caused the error
            $func_name = 'user_log_link->add_link';
            $msg_text = 'Insert to link log failed';
            $traceback = (new Exception)->getTraceAsString();
            log_fatal($msg_text, $func_name, '', $traceback, $this->usr);
            $msg_description = $msg_text . ' with ' . $this->dsp_id();
            log_fatal($msg_text, $func_name, $msg_description, $traceback, $this->usr);
            $result = False;
        } else {
            $this->id = $log_id;
            $result = True;
        }
        // restore the type before saving the log
        $db_con->set_type($db_type);

        log_debug('user_log_link->add_link -> (' . zu_dsp_bool($result) . ')');
        return $result;
    }

    // display the last change related to one object (word, formula, value, verb, ...)
    // mainly used for testing
    function dsp_last($ex_time): string
    {

        global $db_con;
        $result = '';

        $this->set_table();

        $sql_where = '';
        if ($this->old_from_id > 0) {
            $sql_where .= ' AND c.old_from_id = ' . $this->old_from_id;
        }
        if ($this->old_from_id > 0) {
            $sql_where .= ' AND c.old_to_id = ' . $this->old_to_id;
        }
        if ($this->new_from_id > 0) {
            $sql_where .= ' AND c.new_from_id = ' . $this->new_from_id;
        }
        if ($this->new_from_id > 0) {
            $sql_where .= ' AND c.new_to_id = ' . $this->new_to_id;
        }

        $sql = "SELECT c.change_time,
                   u.user_name,
                   c.old_text_from,
                   c.old_from_id,
                   c.old_text_link,
                   c.old_link_id,
                   c.old_text_to,
                   c.old_to_id,
                   c.new_text_from,
                   c.new_from_id,
                   c.new_text_link,
                   c.new_link_id,
                   c.new_text_to,
                   c.new_to_id
              FROM change_links c, users u
             WHERE c.change_table_id = " . $this->table_id . "
               AND c.user_id = u.user_id
               AND u.user_id = " . $this->usr->id . "
                   " . $sql_where . "
          ORDER BY c.change_link_id DESC;";
        log_debug("user_log->dsp_last get sql (" . $sql . ")");
        //$db_con = new mysql;
        $db_type = $db_con->get_type();
        $db_con->set_type(DB_TYPE_CHANGE_LINK);
        $db_con->usr_id = $this->usr->id;
        $db_row = $db_con->get1_old($sql);
        if ($db_row != null) {
            if (!$ex_time) {
                $result .= $db_row['change_time'] . ' ';
            }
            if ($db_row['user_name'] <> '') {
                $result .= $db_row['user_name'] . ' ';
            }
            if ($db_row['new_text_from'] <> '' and $db_row['new_text_to'] <> '') {
                $result .= 'linked ' . $db_row['new_text_from'] . ' to ' . $db_row['new_text_to'];
            } elseif ($db_row['old_text_from'] <> '' and $db_row['old_text_to'] <> '') {
                $result .= 'unlinked ' . $db_row['old_text_from'] . ' from ' . $db_row['old_text_to'];
            }
        }
        // restore the type before saving the log
        $db_con->set_type($db_type);
        return $result;
    }

    // similar to add_link, but additional fix the references as a text for fast displaying
    // $link_text is used for fixed links such as the source for values
    function add(): bool
    {
        log_debug('user_log_link->add do "' . $this->action . '" of "' . $this->table . '" for user ' . $this->usr->dsp_id());

        global $db_con;

        $this->set_table();
        $this->set_action();

        // set the table specific references
        log_debug('user_log_link->add -> set fields');
        if ($this->table == "words"
            or $this->table == "word_links") {
            if ($this->action == "add" or $this->action == "update") {
                if ($this->new_from != null and $this->new_link != null and $this->new_to != null) {
                    $this->new_text_from = $this->new_from->name;
                    $this->new_text_link = $this->new_link->name;
                    $this->new_text_to = $this->new_to->name;
                    $this->new_from_id = $this->new_from->id;
                    $this->new_link_id = $this->new_link->id;
                    $this->new_to_id = $this->new_to->id;
                } else {
                    log_err('Object(s) missing when trying to log a triple add action');
                }
            }
            if ($this->action == "del" or $this->action == "update") {
                if ($this->old_from != null and $this->old_link != null and $this->old_to != null) {
                    $this->old_text_from = $this->old_from->name;
                    $this->old_text_link = $this->old_link->name;
                    $this->old_text_to = $this->old_to->name;
                    $this->old_from_id = $this->old_from->id;
                    $this->old_link_id = $this->old_link->id;
                    $this->old_to_id = $this->old_to->id;
                } else {
                    log_err('Object(s) missing when trying to log a triple del action');
                }
            }
        }
        if ($this->table == "refs") {
            if ($this->action == "add" or $this->action == "update") {
                if ($this->new_from != null and $this->new_link != null and $this->new_to != null) {
                    $this->new_text_from = $this->new_from->name;
                    $this->new_text_link = $this->new_link->name;
                    $this->new_text_to = $this->new_to->external_key;
                    $this->new_from_id = $this->new_from->id;
                    $this->new_link_id = $this->new_link->id;
                    $this->new_to_id = $this->new_to->id;
                } else {
                    log_err('Object(s) missing when trying to log a ref add action');
                }
            }
            if ($this->action == "del" or $this->action == "update") {
                if ($this->old_from != null and $this->old_link != null and $this->old_to != null) {
                    $this->old_text_from = $this->old_from->name;
                    $this->old_text_link = $this->old_link->name;
                    $this->old_text_to = $this->old_to->external_key;
                    $this->old_from_id = $this->old_from->id;
                    $this->old_link_id = $this->old_link->id;
                    $this->old_to_id = $this->old_to->id;
                } else {
                    log_err('Object(s) missing when trying to log a ref del action');
                }
            }
        }
        if ($this->table == "view_component_links"
            or $this->table == "value_phrase_links"
            or $this->table == "formula_links") {
            if ($this->action == "add" or $this->action == "update") {
                if ($this->new_from != null and $this->new_to != null) {
                    $this->new_text_from = $this->new_from->name;
                    $this->new_text_to = $this->new_to->name;
                    $this->new_from_id = $this->new_from->id;
                    $this->new_to_id = $this->new_to->id;
                } else {
                    log_err('Object(s) missing when trying to log an add action');
                }
            }
            if ($this->action == "del" or $this->action == "update") {
                if ($this->old_from != null and $this->old_to != null) {
                    $this->old_text_from = $this->old_from->name;
                    $this->old_text_to = $this->old_to->name;
                    $this->old_from_id = $this->old_from->id;
                    $this->old_to_id = $this->old_to->id;
                } else {
                    log_err('Object(s) missing when trying to log an del action');
                }
            }
        }
        if ($this->table == "values" and $this->link_text == "source") {
            if ($this->old_to > 0) {
                $this->old_text_to = $this->source_name($this->old_to);
            }
            if ($this->new_to > 0) {
                $this->new_text_to = $this->source_name($this->new_to);
            }
        }
        log_debug('user_log_link->add -> set fields done');

        $sql_fields = array();
        $sql_values = array();
        $sql_fields[] = "user_id";
        $sql_values[] = $this->usr->id;
        $sql_fields[] = "change_action_id";
        $sql_values[] = $this->action_id;
        $sql_fields[] = "change_table_id";
        $sql_values[] = $this->table_id;

        $sql_fields[] = "old_from_id";
        $sql_values[] = $this->old_from_id;
        $sql_fields[] = "old_link_id";
        $sql_values[] = $this->old_link_id;
        $sql_fields[] = "old_to_id";
        $sql_values[] = $this->old_to_id;

        $sql_fields[] = "new_from_id";
        $sql_values[] = $this->new_from_id;
        $sql_fields[] = "new_link_id";
        $sql_values[] = $this->new_link_id;
        $sql_fields[] = "new_to_id";
        $sql_values[] = $this->new_to_id;

        $sql_fields[] = "old_text_from";
        $sql_values[] = $this->old_text_from;
        $sql_fields[] = "old_text_link";
        $sql_values[] = $this->old_text_link;
        $sql_fields[] = "old_text_to";
        $sql_values[] = $this->old_text_to;

        $sql_fields[] = "new_text_from";
        $sql_values[] = $this->new_text_from;
        $sql_fields[] = "new_text_link";
        $sql_values[] = $this->new_text_link;
        $sql_fields[] = "new_text_to";
        $sql_values[] = $this->new_text_to;

        $sql_fields[] = "row_id";
        $sql_values[] = $this->row_id;

        //$db_con = new mysql;
        $db_type = $db_con->get_type();
        $db_con->set_type(DB_TYPE_CHANGE_LINK);
        $db_con->set_usr($this->usr->id);
        $log_id = $db_con->insert($sql_fields, $sql_values);

        if ($log_id <= 0) {
            // write the error message in steps to get at least some message if the parameters causes an additional the error
            $func_name = 'user_log_link->add';
            $msg_text = 'Insert to change log failed';
            $traceback = (new Exception)->getTraceAsString();
            log_fatal($msg_text, $func_name, '', $traceback, $this->usr);
            $msg_description = $msg_text . ' with ' . $this->dsp_id();
            log_fatal($msg_text, $func_name, $msg_description, $traceback, $this->usr);
            $result = False;
        } else {
            $this->id = $log_id;
            $result = True;
        }
        // restore the type before saving the log
        $db_con->set_type($db_type);

        log_debug('user_log_link->add -> (' . zu_dsp_bool($result) . ')');
        return $result;
    }

    // add the row id to an existing log entry
    // e.g. because the row id is known after the adding of the real record,
    // but the log entry has been created upfront to make sure that logging is complete
    function add_ref($row_id): bool
    {
        log_debug("user_log_link->add_ref (" . $row_id . " to " . $this->id . " for user " . $this->usr->dsp_id() . ")");

        global $db_con;

        $result = true;
        $db_type = $db_con->get_type();
        $db_con->set_type(DB_TYPE_CHANGE_LINK);
        $db_con->set_usr($this->usr->id);
        if (!$db_con->update($this->id, "row_id", $row_id)) {
            // write the error message in steps to get at least some message if the parameters causes an additional the error
            $func_name = 'user_log_link->add_ref';
            $msg_text = 'Insert to change ref log failed';
            $traceback = (new Exception)->getTraceAsString();
            $msg_description = $msg_text . ' with ' . $this->dsp_id();
            log_fatal($msg_text, $func_name, $msg_description, $traceback, $this->usr);
            $result = False;
        }
        // restore the type before saving the log
        $db_con->set_type($db_type);
        return $result;
    }


}