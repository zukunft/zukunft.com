<?php

/*

    cfg/log/change_link.php - object to save updates of references (links) by the user in the database in a format, so that it can fast be displayed to the user
    -----------------------

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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace cfg\log;

use cfg\db\sql;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\db\sql_par;
use cfg\db\sql_par_field_list;
use cfg\db\sql_par_type;
use cfg\db\sql_type;
use cfg\db\sql_type_list;
use cfg\sandbox_link;
use cfg\source;
use cfg\db\sql_db;
use cfg\type_object;
use cfg\user;
use cfg\word;
use Exception;
use shared\library;

include_once MODEL_LOG_PATH . 'change_log.php';
include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_par.php';
include_once MODEL_USER_PATH . 'user.php';

class change_link extends change_log
{

    /*
     * database link
     */

    // user log database and JSON object field names for link user sandbox objects
    const TBL_COMMENT = 'to log the link changes done by the users';
    const FLD_ID = 'change_link_id';
    const FLD_TABLE_ID = 'change_table_id';
    const FLD_OLD_FROM_TEXT = 'old_text_from';
    const FLD_OLD_FROM_ID = 'old_from_id';
    const FLD_OLD_LINK_TEXT = 'old_text_link';
    const FLD_OLD_LINK_ID = 'old_link_id';
    const FLD_OLD_TO_TEXT = 'old_text_to';
    const FLD_OLD_TO_ID = 'old_to_id';
    const FLD_NEW_FROM_TEXT = 'new_text_from';
    const FLD_NEW_FROM_ID = 'new_from_id';
    const FLD_NEW_LINK_TEXT = 'new_text_link';
    const FLD_NEW_LINK_ID = 'new_link_id';
    const FLD_NEW_TO_TEXT_COM = 'the fixed text to display to the user or the external reference id e.g. Q1 (for universe) in case of wikidata';
    const FLD_NEW_TO_TEXT = 'new_text_to';
    const FLD_NEW_TO_ID_COM = 'either internal row id or the ref type id of the external system e.g. 2 for wikidata';
    const FLD_NEW_TO_ID = 'new_to_id';

    // all database field names
    const FLD_NAMES = array(
        user::FLD_ID,
        self::FLD_TABLE_ID,
        self::FLD_TIME,
        self::FLD_OLD_FROM_TEXT,
        self::FLD_OLD_FROM_ID,
        self::FLD_OLD_LINK_TEXT,
        self::FLD_OLD_LINK_ID,
        self::FLD_OLD_TO_TEXT,
        self::FLD_OLD_TO_ID,
        self::FLD_NEW_FROM_TEXT,
        self::FLD_NEW_FROM_ID,
        self::FLD_NEW_LINK_TEXT,
        self::FLD_NEW_LINK_ID,
        self::FLD_NEW_TO_TEXT,
        self::FLD_NEW_TO_ID
    );

    // field lists for the sql table creation that are used for all change logs (incl. value and link changes)
    const FLD_LST_KEY = array(
        [self::FLD_ID, sql_field_type::KEY_INT, sql_field_default::NOT_NULL, sql::INDEX, '', self::FLD_ID_COM],
        [self::FLD_TIME, sql_field_type::TIME, sql_field_default::TIME_NOT_NULL, sql::INDEX, '', self::FLD_TIME_COM],
        [user::FLD_ID, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, user::class, self::FLD_USER_COM],
        [self::FLD_ACTION, sql_field_type::INT_SMALL, sql_field_default::NOT_NULL, '', change_action::class, self::FLD_ACTION_COM],
    );
    // field list to log the actual change of the value with a standard group id
    const FLD_LST_CHANGE = array(
        [self::FLD_TABLE_ID, sql_field_type::INT, sql_field_default::NOT_NULL, '', change_table::class, ''],
        [self::FLD_OLD_FROM_ID, sql_field_type::INT, sql_field_default::NULL, '', '', ''],
        [self::FLD_OLD_LINK_ID, sql_field_type::INT, sql_field_default::NULL, '', '', ''],
        [self::FLD_OLD_TO_ID, sql_field_type::INT, sql_field_default::NULL, '', '', ''],
        [self::FLD_OLD_FROM_TEXT, sql_field_type::TEXT, sql_field_default::NULL, '', '', ''],
        [self::FLD_OLD_LINK_TEXT, sql_field_type::TEXT, sql_field_default::NULL, '', '', ''],
        [self::FLD_OLD_TO_TEXT, sql_field_type::TEXT, sql_field_default::NULL, '', '', ''],
        [self::FLD_NEW_FROM_ID, sql_field_type::INT, sql_field_default::NULL, '', '', ''],
        [self::FLD_NEW_LINK_ID, sql_field_type::INT, sql_field_default::NULL, '', '', ''],
        [self::FLD_NEW_TO_ID, sql_field_type::INT, sql_field_default::NULL, '', '', self::FLD_NEW_TO_ID_COM],
        [self::FLD_NEW_FROM_TEXT, sql_field_type::TEXT, sql_field_default::NULL, '', '', ''],
        [self::FLD_NEW_LINK_TEXT, sql_field_type::TEXT, sql_field_default::NULL, '', '', ''],
        [self::FLD_NEW_TO_TEXT, sql_field_type::TEXT, sql_field_default::NULL, '', '', self::FLD_NEW_TO_TEXT_COM],
    );


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
    public int|string|null $row_id = null; // the reference id of the row in the database table
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

    /**
     * map the database fields to one change log entry to this log object
     *
     * @param array|null $db_row with the data directly from the database
     * @param string $id_fld the name of the id field as set in the child class
     * @return bool true if a change log entry is found
     */
    function row_mapper(?array $db_row, string $id_fld = ''): bool
    {
        $result = parent::row_mapper($db_row, self::FLD_ID);
        if ($result) {
            $this->table_id = $db_row[self::FLD_TABLE_ID];
            $this->set_time_str($db_row[self::FLD_TIME]);
            $this->old_text_from = $db_row[self::FLD_OLD_FROM_TEXT];
            $this->old_from_id = $db_row[self::FLD_OLD_FROM_ID];
            $this->old_text_link = $db_row[self::FLD_OLD_LINK_TEXT];
            $this->old_link_id = $db_row[self::FLD_OLD_LINK_ID];
            $this->old_text_to = $db_row[self::FLD_OLD_TO_TEXT];
            $this->old_to_id = $db_row[self::FLD_OLD_TO_ID];
            $this->new_text_from = $db_row[self::FLD_NEW_FROM_TEXT];
            $this->new_from_id = $db_row[self::FLD_NEW_FROM_ID];
            $this->new_text_link = $db_row[self::FLD_NEW_LINK_TEXT];
            $this->new_link_id = $db_row[self::FLD_NEW_LINK_ID];
            $this->new_text_to = $db_row[self::FLD_NEW_TO_TEXT];
            $this->new_to_id = $db_row[self::FLD_NEW_TO_ID];
            // TODO check if not the complete user should be loaded
            $usr = new user();
            $usr->set_id($db_row[user::FLD_ID]);
            $usr->name = $db_row[user::FLD_NAME];
            $this->set_user($usr);
        }
        return $result;
    }

    /**
     * TODO make sure that always a order is defined to allow page views
     * TODO use always limit queries to avoid long runners
     * create an SQL statement to retrieve a change long entry for links by the changing user
     *
     * @param sql $sc with the target db_type set
     * @param user $usr the id of the user sandbox object
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_user(sql $sc, user $usr): sql_par
    {
        $qp = new sql_par(self::class);
        $qp->name .= 'user_last';
        $sc->set_class(change_link::class);

        $sc->set_name($qp->name);
        $sc->set_usr($usr->id);
        $sc->set_fields(self::FLD_NAMES);
        $sc->set_join_fields(array(user::FLD_NAME), user::class);

        $sc->add_where(user::FLD_ID, $usr->id);
        $sc->set_order(self::FLD_ID, sql::ORDER_DESC);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();
        return $qp;
    }

    function load_sql_by_vars(sql_db $db_con, int $table_id): sql_par
    {
        $qp = new sql_par(self::class);
        $qp->name .= 'table';
        $db_con->set_class(change_link::class);

        $fields = [];
        $values = [];
        $fields[] = self::FLD_TABLE_ID;
        $values[] = $table_id;

        if ($this->old_from_id > 0) {
            $qp->name .= '_old_from';
            $fields[] = self::FLD_OLD_FROM_ID;
            $values[] = $this->old_from_id;
        }
        if ($this->old_to_id > 0) {
            $qp->name .= '_old_to';
            $fields[] = self::FLD_OLD_TO_ID;
            $values[] = $this->old_to_id;
        }
        if ($this->new_from_id > 0) {
            $qp->name .= '_old_to';
            $fields[] = self::FLD_NEW_FROM_ID;
            $values[] = $this->new_from_id;
        }
        if ($this->new_to_id > 0) {
            $qp->name .= '_new_to';
            $fields[] = self::FLD_NEW_TO_ID;
            $values[] = $this->new_to_id;
        }

        $db_con->set_name($qp->name);
        $db_con->set_usr($this->user()->id());
        $db_con->set_fields(self::FLD_NAMES);
        $db_con->set_join_fields(array(user::FLD_NAME), user::class);

        $db_con->set_where_text($db_con->where_par($fields, $values));
        $db_con->set_order(self::FLD_ID, sql::ORDER_DESC);
        $qp->sql = $db_con->select_by_set_id();
        $qp->par = $db_con->get_par();

        return $qp;
    }

    /**
     * get the last link changed by a user
     * TODO check the permission of the view user
     * @param user $usr the user
     * @return int
     */
    function load_last_by_user(user $usr): int
    {
        global $db_con;
        $qp = $this->load_sql_by_user($db_con, $usr);
        return $this->load($qp);
    }

    // identical to the functions in user_log (maybe move to a common object??)
    protected function add_table(sql_db $db_con, string $table_name = ''): int
    {
        // check parameter
        if ($table_name == "") {
            log_err("missing table name", "user_log_link->set_table");
        }
        if ($this->user()->id() <= 0) {
            log_err("missing user", "user_log_link->set_table");
        }

        // if e.g. a "value" is changed $table_name is "values" and the reference 1 is saved in the log to save space
        $db_type = $db_con->get_class();
        $db_con->set_class(change_table::class);
        $table_id = $db_con->get_id($table_name);

        // add new table name if needed
        if ($table_id <= 0) {
            $table_id = $db_con->add_id($table_name);
        }
        if ($table_id > 0) {
            $this->table_id = $table_id;
        } else {
            log_fatal("Insert to change log failed due to table id failure.", "user_log->add");
        }
        // restore the type before saving the log
        $db_con->set_class($db_type);
        return $table_id;
    }

    // functions used utils each call is done with the object instead of the id
    private function set_usr(): void
    {
        log_debug('user_log_link->set_usr for ' . $this->user()->dsp_id());
        if ($this->user() != null) {
            $usr = new user;
            $usr->load_by_id($this->user()->id());
            $this->set_user($usr);
            log_debug('user_log_link->set_usr got ' . $this->user()->name);
        }
    }

    private function word_name($id): string
    {
        log_debug('user_log_link->word_name for ' . $id);
        $result = '';
        if ($id > 0) {
            $this->set_usr();
            $wrd = new word($this->user());
            $wrd->load_by_id($id);
            $result = $wrd->name();
            log_debug('user_log_link->word_name got ' . $result);
        }
        return $result;
    }

    private function source_name($id): string
    {
        global $db_con;
        $result = '';
        //$db_con = new mysql;
        $db_con->set_class(source::class);
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
    function add_link(): bool
    {
        global $db_con;
        log_debug("user_log_link->add_link (u" . $this->user()->id() . " " . $this->action() . " " . $this->table() .
            ",of" . $this->old_from . ",ol" . $this->old_link . ",ot" . $this->old_to .
            ",nf" . $this->new_from . ",nl" . $this->new_link . ",nt" . $this->new_to . ",r" . $this->row_id . ")");

        global $db_con;

        $this->add_table($db_con);

        $sql_fields = array();
        $sql_values = array();
        $sql_fields[] = user::FLD_ID;
        $sql_values[] = $this->user()->id();
        $sql_fields[] = change_action::FLD_ID;
        $sql_values[] = $this->action_id;
        $sql_fields[] = change_table::FLD_ID;
        $sql_values[] = $this->table_id;

        $sql_fields[] = change_link::FLD_OLD_FROM_ID;
        $sql_values[] = $this->old_from;
        $sql_fields[] = change_link::FLD_OLD_LINK_ID;
        $sql_values[] = $this->old_link;
        $sql_fields[] = change_link::FLD_OLD_TO_ID;
        $sql_values[] = $this->old_to;

        $sql_fields[] = change_link::FLD_NEW_FROM_ID;
        $sql_values[] = $this->new_from;
        $sql_fields[] = change_link::FLD_NEW_LINK_ID;
        $sql_values[] = $this->new_link;
        $sql_fields[] = change_link::FLD_NEW_TO_ID;
        $sql_values[] = $this->new_to;

        $sql_fields[] = change_log::FLD_LST_ROW_ID;
        $sql_values[] = $this->row_id;

        //$db_con = new mysql;
        $db_type = $db_con->get_class();
        $db_con->set_class(change_link::class);
        $db_con->set_usr($this->user()->id());
        $log_id = $db_con->insert_old($sql_fields, $sql_values);

        if ($log_id <= 0) {
            // write the error message in steps to get at least some message if the parameters has caused the error
            $func_name = 'user_log_link->add_link';
            $msg_text = 'Insert to link log failed';
            $traceback = (new Exception)->getTraceAsString();
            log_fatal($msg_text, $func_name, '', $traceback, $this->user());
            $msg_description = $msg_text . ' with ' . $this->dsp_id();
            log_fatal($msg_text, $func_name, $msg_description, $traceback, $this->user());
            $result = False;
        } else {
            $this->set_id($log_id);
            $result = True;
        }
        // restore the type before saving the log
        $db_con->set_class($db_type);

        log_debug(zu_dsp_bool($result));
        return $result;
    }

    /**
     * display the last change related to one object (word, formula, value, verb, ...)
     * mainly used for testing
     */
    function dsp_last($ex_time = false): string
    {

        global $db_con;
        $result = '';

        //$this->add_table();

        $db_type = $db_con->get_class();
        $qp = $this->load_sql_by_vars($db_con, $this->table_id);
        $db_row = $db_con->get1($qp);
        $this->row_mapper($db_row);
        if ($db_row != null) {
            if (!$ex_time) {
                $result .= $db_row['change_time'] . ' ';
            }
            if ($db_row[user::FLD_NAME] <> '') {
                $result .= $db_row[user::FLD_NAME] . ' ';
            }
            if ($db_row['new_text_from'] <> '' and $db_row['new_text_to'] <> '') {
                $result .= 'linked ' . $db_row['new_text_from'] . ' to ' . $db_row['new_text_to'];
            } elseif ($db_row['old_text_from'] <> '' and $db_row['old_text_to'] <> '') {
                $result .= 'unlinked ' . $db_row['old_text_from'] . ' from ' . $db_row['old_text_to'];
            }
        }
        // restore the type before saving the log
        $db_con->set_class($db_type);
        return $result;
    }

    /**
     * similar to add_link, but additional fix the references as a text for fast displaying
     * $link_text is used for fixed links such as the source for values
     */
    function add(): bool
    {
        log_debug('do "' . $this->action() . '" of "' . $this->table() . '" for user ' . $this->user()->dsp_id());

        global $db_con;

        // set the table specific references
        log_debug('set fields');
        if ($this->table() == change_table_list::WORD
            or $this->table() == change_table_list::TRIPLE) {
            if ($this->action() == change_action::ADD or $this->action() == change_action::UPDATE) {
                if ($this->new_from != null and $this->new_link != null and $this->new_to != null) {
                    $this->new_text_from = $this->new_from->name();
                    $this->new_text_link = $this->new_link->name();
                    $this->new_text_to = $this->new_to->name();
                    $this->new_from_id = $this->new_from->id();
                    $this->new_link_id = $this->new_link->id();
                    $this->new_to_id = $this->new_to->id();
                } else {
                    log_err('Object(s) missing when trying to log a triple add action');
                }
            }
            if ($this->action() == change_action::DELETE or $this->action() == change_action::UPDATE) {
                if ($this->old_from != null and $this->old_link != null and $this->old_to != null) {
                    $this->old_text_from = $this->old_from->name();
                    $this->old_text_link = $this->old_link->name();
                    $this->old_text_to = $this->old_to->name();
                    $this->old_from_id = $this->old_from->id();
                    $this->old_link_id = $this->old_link->id();
                    $this->old_to_id = $this->old_to->id();
                } else {
                    log_err('Object(s) missing when trying to log a triple del action');
                }
            }
        }
        if ($this->table() == change_table_list::REF) {
            if ($this->action() == change_action::ADD or $this->action() == change_action::UPDATE) {
                if ($this->new_from != null and $this->new_link != null and $this->new_to != null) {
                    $this->new_text_from = $this->new_from->name();
                    $this->new_text_link = $this->new_link->name();
                    $this->new_text_to = $this->new_to->external_key;
                    $this->new_from_id = $this->new_from->id();
                    $this->new_link_id = $this->new_link->id();
                    $this->new_to_id = $this->new_to->id();
                } else {
                    log_err('Object(s) missing when trying to log a ref add action');
                }
            }
            if ($this->action() == change_action::DELETE or $this->action() == change_action::UPDATE) {
                if ($this->old_from != null and $this->old_link != null and $this->old_to != null) {
                    $this->old_text_from = $this->old_from->name();
                    $this->old_text_link = $this->old_link->name();
                    $this->old_text_to = $this->old_to->external_key;
                    $this->old_from_id = $this->old_from->id();
                    $this->old_link_id = $this->old_link->id();
                    $this->old_to_id = $this->old_to->id();
                } else {
                    log_err('Object(s) missing when trying to log a ref del action');
                }
            }
        }
        if ($this->table() == change_table_list::VIEW_LINK
            or $this->table() == change_table_list::VALUE_PHRASE_LINK
            or $this->table() == change_table_list::FORMULA_LINK) {
            if ($this->action() == change_action::ADD or $this->action() == change_action::UPDATE) {
                if ($this->new_from != null and $this->new_to != null) {
                    $this->new_text_from = $this->new_from->name();
                    $this->new_text_to = $this->new_to->name();
                    $this->new_from_id = $this->new_from->id();
                    $this->new_to_id = $this->new_to->id();
                } else {
                    log_err('Object(s) missing when trying to log an add action');
                }
            }
            if ($this->action() == change_action::DELETE or $this->action() == change_action::UPDATE) {
                if ($this->old_from != null and $this->old_to != null) {
                    $this->old_text_from = $this->old_from->name();
                    $this->old_text_to = $this->old_to->name();
                    $this->old_from_id = $this->old_from->id();
                    $this->old_to_id = $this->old_to->id();
                } else {
                    log_err('Object(s) missing when trying to log an del action');
                }
            }
        }
        if ($this->table() == change_table_list::VALUE and $this->link_text == 'source') {
            if ($this->old_to > 0) {
                $this->old_text_to = $this->source_name($this->old_to);
            }
            if ($this->new_to > 0) {
                $this->new_text_to = $this->source_name($this->new_to);
            }
        }
        log_debug('set fields done');

        $sql_fields = array();
        $sql_values = array();
        $sql_fields[] = user::FLD_ID;
        $sql_values[] = $this->user()->id();
        $sql_fields[] = change_action::FLD_ID;
        $sql_values[] = $this->action_id;
        $sql_fields[] = change_table::FLD_ID;
        $sql_values[] = $this->table_id;

        $sql_fields[] = change_link::FLD_OLD_FROM_ID;
        $sql_values[] = $this->old_from_id;
        $sql_fields[] = change_link::FLD_OLD_LINK_ID;
        $sql_values[] = $this->old_link_id;
        $sql_fields[] = change_link::FLD_OLD_TO_ID;
        $sql_values[] = $this->old_to_id;

        $sql_fields[] = change_link::FLD_NEW_FROM_ID;
        $sql_values[] = $this->new_from_id;
        $sql_fields[] = change_link::FLD_NEW_LINK_ID;
        $sql_values[] = $this->new_link_id;
        $sql_fields[] = change_link::FLD_NEW_TO_ID;
        $sql_values[] = $this->new_to_id;

        $sql_fields[] = change_link::FLD_OLD_FROM_TEXT;
        $sql_values[] = $this->old_text_from;
        $sql_fields[] = change_link::FLD_OLD_LINK_TEXT;
        $sql_values[] = $this->old_text_link;
        $sql_fields[] = change_link::FLD_OLD_TO_TEXT;
        $sql_values[] = $this->old_text_to;

        $sql_fields[] = change_link::FLD_NEW_FROM_TEXT;
        $sql_values[] = $this->new_text_from;
        $sql_fields[] = change_link::FLD_NEW_LINK_TEXT;
        $sql_values[] = $this->new_text_link;
        $sql_fields[] = change_link::FLD_NEW_TO_TEXT;
        $sql_values[] = $this->new_text_to;

        $sql_fields[] = change_log::FLD_ROW_ID;
        $sql_values[] = $this->row_id;

        //$db_con = new mysql;
        $db_type = $db_con->get_class();
        $db_con->set_class(change_link::class);
        $db_con->set_usr($this->user()->id());
        $log_id = $db_con->insert_old($sql_fields, $sql_values);

        if ($log_id <= 0) {
            // write the error message in steps to get at least some message if the parameters causes an additional the error
            $func_name = 'user_log_link->add';
            $msg_text = 'Insert to change log failed';
            $traceback = (new Exception)->getTraceAsString();
            log_fatal($msg_text, $func_name, '', $traceback, $this->user());
            $msg_description = $msg_text . ' with ' . $this->dsp_id();
            log_fatal($msg_text, $func_name, $msg_description, $traceback, $this->user());
            $result = False;
        } else {
            $this->set_id($log_id);
            $result = True;
        }
        // restore the type before saving the log
        $db_con->set_class($db_type);

        log_debug(zu_dsp_bool($result));
        return $result;
    }

    // add the row id to an existing log entry
    // e.g. because the row id is known after the adding of the real record,
    // but the log entry has been created upfront to make sure that logging is complete
    function add_ref($row_id): bool
    {
        log_debug($row_id . " to " . $this->id() . " for user " . $this->user()->dsp_id());

        global $db_con;

        $result = true;
        $db_type = $db_con->get_class();
        $db_con->set_class(change_link::class);
        $db_con->set_usr($this->user()->id());
        if (!$db_con->update_old($this->id(), 'row_id', $row_id)) {
            // write the error message in steps to get at least some message if the parameters causes an additional the error
            $func_name = 'user_log_link->add_ref';
            $msg_text = 'Insert to change ref log failed';
            $traceback = (new Exception)->getTraceAsString();
            $msg_description = $msg_text . ' with ' . $this->dsp_id();
            log_fatal($msg_text, $func_name, $msg_description, $traceback, $this->user());
            $result = False;
        }
        // restore the type before saving the log
        $db_con->set_class($db_type);
        return $result;
    }

    /*
     * debug
     */

    /**
     * @return string with the unique log entry description for debugging
     */
    function dsp_id(): string
    {
        $result = '';

        if ($this->user() != null) {
            $result .= 'user_log_link for user ' . $this->user()->dsp_id();
        }
        $result .= ' action ' . $this->action() . ' (' . $this->action_id . ')';
        $result .= ' table ' . $this->table() . ' (' . $this->table_id . ')';
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


    /*
     * sql write
     */

    /**
     * create the sql statement to add a log link entry to the database
     *
     * @param sql $sc with the target db_type set
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @param sandbox_link|null $sbx the sandbox link object used to get the sql parameter names e.g. "_from_phrase_id" instead of "_new_from_id"
     * @return sql_par the SQL insert statement, the name of the SQL statement and the parameter list
     */
    function sql_insert_link(
        sql           $sc,
        sql_type_list $sc_par_lst,
        ?sandbox_link $sbx = null
    ): sql_par
    {
        $sc_par_lst->add(sql_type::INSERT);
        // do not use the user extension for the change table name
        $sc_par_lst_chg = $sc_par_lst->remove(sql_type::USER);
        $qp = $sc->sql_par($this::class, $sc_par_lst_chg);
        $sc->set_class($this::class, $sc_par_lst_chg);
        if ($sc_par_lst->is_list_tbl()) {
            $lib = new library();
            $qp->name = $lib->class_to_name($this::class);
        }
        $sc->set_name($qp->name);
        $qp->sql = $sc->create_sql_insert($this->db_field_values_link_types($sc, $sc_par_lst, $sbx), $sc_par_lst);
        $qp->par = $this->db_values();

        return $qp;
    }


    /*
     * sql write fields
     */

    /**
     * get a list of all database fields to log am link
     * list must be corresponding to the db_values fields
     *
     * @param sql $sc with the target db_type set
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @param sandbox_link|null $sbx the sandbox link object used to get the sql parameter names e.g. "_from_phrase_id" instead of "_new_from_id"
     * @return sql_par_field_list list of the database field names
     */
    function db_field_values_link_types(
        sql           $sc,
        sql_type_list $sc_par_lst,
        ?sandbox_link $sbx
    ): sql_par_field_list
    {
        $fvt_lst = new sql_par_field_list();
        $fvt_lst->add_field(user::FLD_ID, $this->user()->id(), user::FLD_ID_SQLTYP);
        $fvt_lst->add_field(change_action::FLD_ID, $this->action_id, type_object::FLD_ID_SQLTYP);
        $fvt_lst->add_field(change_table::FLD_ID, $this->table_id, type_object::FLD_ID_SQLTYP);

        if ($this->old_text_from !== null) {
            $fvt_lst->add_field(self::FLD_OLD_FROM_TEXT, $this->old_text_from, $sc->get_sql_par_type($this->old_text_from));
        }
        if ($this->old_text_link !== null) {
            $fvt_lst->add_field(self::FLD_OLD_LINK_TEXT, $this->old_text_link, $sc->get_sql_par_type($this->old_text_link));
        }
        if ($this->old_text_to !== null) {
            $fvt_lst->add_field(self::FLD_OLD_TO_TEXT, $this->old_text_to, $sc->get_sql_par_type($this->old_text_to));
        }
        if ($this->new_text_from !== null) {
            $fvt_lst->add_field(self::FLD_NEW_FROM_TEXT, $this->new_text_from, $sc->get_sql_par_type($this->new_text_from));
        }
        if ($this->new_text_link !== null) {
            $fvt_lst->add_field(self::FLD_NEW_LINK_TEXT, $this->new_text_link, $sc->get_sql_par_type($this->new_text_link));
        }
        if ($this->new_text_to !== null) {
            $fvt_lst->add_field(self::FLD_NEW_TO_TEXT, $this->new_text_to, $sc->get_sql_par_type($this->new_text_to));
        }

        if ($this->old_from_id > 0) {
            $fvt_lst->add_field(self::FLD_OLD_FROM_ID, $this->old_from_id, sql_par_type::INT);
        }
        if ($this->old_link_id > 0) {
            $fvt_lst->add_field(self::FLD_OLD_LINK_ID, $this->old_link_id, sql_par_type::INT);
        }
        if ($this->old_to_id > 0) {
            $fvt_lst->add_field(self::FLD_OLD_TO_ID, $this->old_to_id, sql_par_type::INT);
        }
        if ($this->new_from_id > 0) {
            $par_name = '';
            if ($sbx != null) {
                $par_name = sql::PAR_PREFIX . $sbx->from_field();
            }
            $fvt_lst->add_field(self::FLD_NEW_FROM_ID, $this->new_from_id, sql_par_type::INT, null, $par_name);
        } else {
            if ($sbx->is_excluded() and !$sc_par_lst->is_delete()) {
                $par_name = '_' . $sbx->from_field();
                $fvt_lst->add_field(self::FLD_NEW_FROM_ID, $this->new_from_id, sql_par_type::INT, null, $par_name);
            }
        }
        if ($this->new_link_id > 0) {
            $par_name = '';
            if ($sbx != null) {
                $par_name = sql::PAR_PREFIX . $sbx->type_field();
            }
            $fvt_lst->add_field(self::FLD_NEW_LINK_ID, $this->new_link_id, sql_par_type::INT, null, $par_name);
        } else {
            if ($sbx->is_excluded() and !$sc_par_lst->is_delete()) {
                $par_name = '_' . $sbx->type_field();
                $fvt_lst->add_field(self::FLD_NEW_LINK_ID, $this->new_link_id, sql_par_type::INT, null, $par_name);
            }
        }
        if ($this->new_to_id > 0) {
            $par_name = '';
            if ($sbx != null) {
                $par_name = sql::PAR_PREFIX . $sbx->to_field();
            }
            $fvt_lst->add_field(self::FLD_NEW_TO_ID, $this->new_to_id, sql_par_type::INT, null, $par_name);
        } else {
            if ($sbx->is_excluded() and !$sc_par_lst->is_delete()) {
                $par_name = '_' . $sbx->to_field();
                $fvt_lst->add_field(self::FLD_NEW_TO_ID, $this->new_to_id, sql_par_type::INT, null, $par_name);
            }
        }

        $par_name = '';
        if ($sbx != null) {
            if ($sc_par_lst->is_insert_part() and !$sc_par_lst->is_usr_tbl()) {
                $par_name = sql::PAR_NEW_ID_PREFIX . $sbx->id_field();
            } else {
                $par_name = '_' . $sbx->id_field();
            }
        }
        $fvt_lst->add_field(self::FLD_ROW_ID, $this->row_id, sql_par_type::INT, null, $par_name);
        return $fvt_lst;
    }

    /**
     * get a list of all database fields
     * list must be corresponding to the db_values fields
     * TODO deprecate
     *
     * @return array list of the database field names
     */
    function db_fields(): array
    {
        $sql_fields = array();
        $sql_fields[] = user::FLD_ID;
        $sql_fields[] = change_action::FLD_ID;
        $sql_fields[] = change_field::FLD_ID;

        if ($this->old_text_from !== null) {
            $sql_fields[] = self::FLD_OLD_FROM_TEXT;
        }
        if ($this->old_text_link !== null) {
            $sql_fields[] = self::FLD_OLD_LINK_TEXT;
        }
        if ($this->old_text_to !== null) {
            $sql_fields[] = self::FLD_OLD_TO_TEXT;
        }
        if ($this->new_text_from !== null) {
            $sql_fields[] = self::FLD_NEW_FROM_TEXT;
        }
        if ($this->new_text_link !== null) {
            $sql_fields[] = self::FLD_NEW_LINK_TEXT;
        }
        if ($this->new_text_to !== null) {
            $sql_fields[] = self::FLD_NEW_TO_TEXT;
        }

        if ($this->old_from_id > 0) {
            $sql_fields[] = self::FLD_OLD_FROM_ID;
        }
        if ($this->old_link_id > 0) {
            $sql_fields[] = self::FLD_OLD_LINK_ID;
        }
        if ($this->old_to_id > 0) {
            $sql_fields[] = self::FLD_OLD_TO_ID;
        }
        if ($this->new_from_id > 0) {
            $sql_fields[] = self::FLD_NEW_FROM_ID;
        }
        if ($this->new_link_id > 0) {
            $sql_fields[] = self::FLD_NEW_LINK_ID;
        }
        if ($this->new_to_id > 0) {
            $sql_fields[] = self::FLD_NEW_TO_ID;
        }

        $sql_fields[] = self::FLD_ROW_ID;
        return $sql_fields;
    }

    /**
     * get a list of database field values that have been updated
     *
     * @return array list of the database field values
     */
    function db_values(): array
    {
        $sql_values = array();
        $sql_values[] = $this->user()->id();
        $sql_values[] = $this->action_id;
        $sql_values[] = $this->field_id;

        if ($this->old_text_from !== null) {
            $sql_values[] = $this->old_text_from;
        }
        if ($this->old_text_link !== null) {
            $sql_values[] = $this->old_text_link;
        }
        if ($this->old_text_to !== null) {
            $sql_values[] = $this->old_text_to;
        }
        if ($this->new_text_from !== null) {
            $sql_values[] = $this->new_text_from;
        }
        if ($this->new_text_link !== null) {
            $sql_values[] = $this->new_text_link;
        }
        if ($this->new_text_to !== null) {
            $sql_values[] = $this->new_text_to;
        }

        if ($this->old_from_id > 0) {
            $sql_values[] = $this->old_from_id;
        }
        if ($this->old_link_id > 0) {
            $sql_values[] = $this->old_link_id;
        }
        if ($this->old_to_id > 0) {
            $sql_values[] = $this->old_to_id;
        }
        if ($this->new_from_id > 0) {
            $sql_values[] = $this->new_from_id;
        }
        if ($this->new_link_id > 0) {
            $sql_values[] = $this->new_link_id;
        }
        if ($this->new_to_id > 0) {
            $sql_values[] = $this->new_to_id;
        }

        $sql_values[] = $this->row_id;
        return $sql_values;
    }


}