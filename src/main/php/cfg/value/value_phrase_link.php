<?php

/*

    model/value/value_phrase_link.php - only for fast selection of the values assigned to one word, a triple or a list of words or triples
    ---------------------------------

    replication of the phrases linked by the phrase group saved in the value
    the phrase group of the value is the master and these value phrase links are the slave, means they are actually replicated information
    so these value phrase links are a kind of helder table for an OLAP Cube creation

    a user specific value word link is not allowed
    If a user changes the word links of a value where he is not owner a new value is created
    or if a value with the word combination already exists, the changes are applied to this value

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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace cfg\value;

use cfg\db\sql_db;
use cfg\db\sql_par;
use cfg\db_object_seq_id_user;
use cfg\log\change_action;
use cfg\log\change_action_list;
use cfg\log\change_link;
use cfg\log\change_table_list;
use cfg\phrase;
use cfg\sys_log_level;
use cfg\user;
use Exception;

include_once MODEL_HELPER_PATH . 'db_object_seq_id_user.php';

class value_phrase_link extends db_object_seq_id_user
{
    // object specific database and JSON object field names
    const FLD_ID = 'value_phrase_link_id';
    const FLD_WEIGHT = 'weight';
    const FLD_TYPE = 'link_type_id';
    const FLD_FORMULA = 'condition_formula_id';

    // all database field names excluding the id
    const FLD_NAMES = array(
        user::FLD_ID,
        value::FLD_ID,
        phrase::FLD_ID,
        self::FLD_WEIGHT,
        self::FLD_TYPE,
        self::FLD_FORMULA
    );

    // database fields
    public value $val;     // the value object to which the words are linked
    public phrase $phr;    // the word (not the triple) object to be linked to the value
    // maybe not used at the moment
    public ?float $weight; //
    public ?int $type;     //
    public ?int $frm_id;   //

    /*
     * construct and map
     */

    /**
     * always create the linked related objects to be able to use the value and phrase id
     */
    function __construct(user $usr)
    {
        parent::__construct($usr);
        $this->val = new value($usr);
        $this->phr = new phrase($usr);

        $this->type = null;
        $this->weight = null;
        $this->frm_id = null;
    }

    function row_mapper_val_phr_lnk(array $db_row): bool
    {
        $result = false;
        if ($db_row != null) {
            if ($db_row[user::FLD_ID] != $this->user()->id() AND $db_row[user::FLD_ID] > 0) {
                log_err('Value user (' .  $this->user()->dsp_id() . ') and phrase link user (' .  $db_row[user::FLD_ID] . ') does not match for link ' . $db_row[self::FLD_ID]);
                $this->id = 0;
            } else {
                $this->id = $db_row[self::FLD_ID];
                $this->val->set_id($db_row[value::FLD_ID]);
                $this->phr->set_id($db_row[phrase::FLD_ID]);
                $this->weight = $db_row[self::FLD_WEIGHT];
                $this->type = $db_row[self::FLD_TYPE];
                $this->frm_id = $db_row[self::FLD_FORMULA];
                $result = true;
            }
        } else {
            $this->id = 0;
        }
        return $result;
    }


    /*
     * set and get
     */

    function set(int $id, value $val, phrase $phr): void
    {
        $this->set_id($id);
        $this->set_value($val);
        $this->set_phrase($phr);
    }

    function set_value(value $val): void
    {
        $this->val = $val;
    }

    function value(): value
    {
        return $this->val;
    }

    function set_phrase(phrase $phr): void
    {
        $this->phr = $phr;
    }

    function phrase(): phrase
    {
        return $this->phr;
    }


    /*
     * loading
     */

    /**
     * create an SQL statement to retrieve a single phrase link either by id of by value id, phrase id and user
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_obj_vars(sql_db $db_con): sql_par
    {
        $db_con->set_class(sql_db::TBL_VALUE_PHRASE_LINK);
        $qp = new sql_par(self::class);
        $sql_where = '';


        if ($this->id > 0) {
            $qp->name .= sql_db::FLD_ID;
            $sql_where .= $db_con->where_par(
                array(self::FLD_ID),
                array($this->id)
            );
        } elseif ($this->val->is_id_set() and $this->phr->id() > 0 and $this->user()->id() > 0) {
            $qp->name .= 'val_phr_usr_id';
            $sql_where .= $db_con->where_par(
                array(value::FLD_ID, phrase::FLD_ID, user::FLD_ID),
                array($this->val->id(), $this->phr->id(), $this->user()->id())
            );
        } else {
            log_err("The id or the value id, phrase id and user id must be set to load the value phrase links", self::class . '->load_sql');
        }

        if ($sql_where != '') {
            $db_con->set_name($qp->name);
            $db_con->set_usr($this->user()->id());
            $db_con->set_fields(self::FLD_NAMES);
            $db_con->set_where_text($sql_where);
            $qp->sql = $db_con->select_by_set_id();
            $qp->par = $db_con->get_par();

        }
        return $qp;
    }

    /**
     * load the word to value link from the database
     */
    function load_lnk(): bool
    {

        global $db_con;

        $qp = $this->load_sql_obj_vars($db_con);

        return $this->row_mapper_val_phr_lnk($db_con->get1($qp));
    }


    /**
     * true if no other user has ever used the related value
     */
    private function used(): bool
    {
        $result = true;

        if (isset($this->val)) {
            if ($this->val->is_id_set()) {
                $result = $this->val->used();
                log_debug('val_lnk->used for id ' . $this->val->id() . ' is ' . zu_dsp_bool($result));
            }
        }
        return $result;
    }


    /*
     *  save functions
     */

    /**
     * set the log entry parameter for a new value word link
     * TODO check if it is not better to log the deletion of a value and creation of a new value?
     */
    private function log_add(): change_link
    {
        log_debug('val_lnk->log_add for "' . $this->phr->id() . ' to ' . $this->val->id());
        $log = new change_link($this->user());
        $log->action = change_action::ADD;
        $log->set_table(change_table_list::VALUE_PHRASE_LINK);
        $log->new_from = $this->val;
        $log->new_to = $this->phr;
        $log->row_id = 0;
        $log->add();

        return $log;
    }

    // set the main log entry parameters for updating one value word link
    // e.g. if the entered the number for "interest income", but see that he has used the word "interest cost" and changes it to "interest income"
    private function log_upd($db_rec): change_link
    {
        log_debug('val_lnk->log_upd for "' . $this->phr->id() . ' to ' . $this->val->id());
        $log = new change_link($this->user());
        $log->action = change_action::UPDATE;
        $log->set_table(change_table_list::VALUE_PHRASE_LINK); // no user sandbox for links, only the values itself can differ from user to user
        //$log->set_field(phrase::FLD_ID);
        $log->old_from = $db_rec->val;
        $log->old_to = $db_rec->wrd;
        $log->new_from = $this->val;
        $log->new_to = $this->phr;
        $log->row_id = $this->id;

        return $log;
    }

    // save the new word link
    private function save_field_wrd(sql_db $db_con, $db_rec): string
    {
        $result = '';
        if ($db_rec->wrd->id <> $this->phr->id()) {
            $log = $this->log_upd($db_con);
            if ($log->add()) {
                $db_con->set_class(sql_db::TBL_VALUE_PHRASE_LINK);
                $result .= $db_con->update_old($this->id, phrase::FLD_ID, $this->phr->id());
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
             WHERE group_id = " . $this->val->id() . " 
               AND phrase_id  = " . $this->phr->id() . " 
               AND value_phrase_link_id <> " . $this->id . ";";
        $db_row = $db_con->get1($sql);
        $this->id = $db_row['value_phrase_link_id'];
        if ($this->id > 0) {
            //$result = $db_con->delete(array(value::FLD_ID,phrase::FLD_ID,'value_phrase_link_id'), array($this->val->id,$this->phr->id,$this->id));
            $sql_del = "DELETE FROM value_phrase_links 
                    WHERE group_id = " . $this->val->id() . " 
                      AND phrase_id  = " . $this->phr->id() . " 
                      AND value_phrase_link_id <> " . $this->id . ";";
            $sql_result = $db_con->exe($sql_del, $this->user()->id(), sys_log_level::ERROR, "val_lnk->update", (new Exception)->getTraceAsString());
            $db_row = $db_con->get1($sql);
            $this->id = $db_row['value_phrase_link_id'];
            if ($this->id > 0) {
                log_err("Duplicate words (" . $this->phr->id() . ") for value " . $this->val->dsp_id() . " found and the automatic removal failed.", "val_lnk->update");
            } else {
                log_warning("Duplicate words (" . $this->phr->id() . ") for value " . $this->dsp_id() . " found, but they have been removed automatically.", "val_lnk->update");
            }
        }
        return $result;
    }

    // change a link of a word to a value
    // only allowed if the value has not yet been used
    function save()
    {
        log_debug("val_lnk->save link word id " . $this->phr->name() . " to " . $this->val->id() . " (link id " . $this->id . " for user " . $this->user()->id() . ").");

        global $db_con;
        $db_con->set_usr($this->user()->id());
        $db_con->set_class(sql_db::TBL_VALUE_PHRASE_LINK);

        if (!$this->used()) {
            // check if a new value is supposed to be added
            if ($this->id <= 0) {
                log_debug("val_lnk->save check if word " . $this->phr->name() . " is already linked to " . $this->val->id() . ".");
                // check if a value_phrase_link with the same word is already in the database
                $db_chk = new value_phrase_link($this->user());
                $db_chk->val = $this->val;
                $db_chk->phr = $this->phr;
                $db_chk->load_lnk();
                if ($db_chk->id > 0) {
                    $this->id = $db_chk->id;
                }
            }

            if ($this->id <= 0) {
                log_debug('val_lnk->save add new value_phrase_link of "' . $this->phr->name() . '" to "' . $this->val->id() . '"');
                // log the insert attempt first
                $log = $this->log_add();
                if ($log->id() > 0) {
                    // insert the new value_phrase_link
                    $db_con->set_class(sql_db::TBL_VALUE_PHRASE_LINK);
                    $this->id = $db_con->insert_old(array("group_id", "word_id"), array($this->val->id(), $this->phr->id()));
                    if ($this->id > 0) {
                        // update the id in the log
                        $result = $log->add_ref($this->id);
                    } else {
                        log_err("Adding value_phrase_link " . $this->val->id() . " failed.", "val_lnk->save");
                    }
                }
            } else {
                log_debug('update "' . $this->id . '"');
                // read the database values to be able to check if something has been changed; done first,
                // because it needs to be done for user and general formulas
                $db_rec = new value_phrase_link($this->user());
                $db_rec->id = $this->id;
                $db_rec->load_lnk();
                log_debug("database value_phrase_link loaded (" . $db_rec->id . ")");

                // update the linked word
                $result = $this->save_field_wrd($db_con, $db_rec);

                // check for duplicates and remove them
                $result .= $this->cleanup($db_con);

            }
        } else {
            // try to create a new value and link all words
            // if the value already exist, create a user entry
            log_warning('creating of a new value for "' . $this->id . '" not yet coded');
        }
        log_debug("done");
    }

    /**
     * remove a link
     * the user id is the user who has requested the change,
     * but it is a parameter and not part of the object, because there are no user specific value word links
     */
    function del($user_id): string
    {
        log_debug("(v" . $this->val->dsp_id() . ",t" . $this->phr->id() . ",u" . $user_id . ")");

        global $db_con;
        $result = '';

        if (!$this->used()) {
            $log = $this->log_add();
            if ($log->id() > 0) {
                //$db_con = new mysql;
                $db_con->usr_id = $this->user()->id();
                $db_con->set_class(sql_db::TBL_VALUE_PHRASE_LINK);
                $result .= $db_con->delete_old(array(value::FLD_ID, phrase::FLD_ID), array($this->val->id(), $this->phr->id()));
            }
        } else {
            // check if removing a word link is matching another value
            // if yes merge value with this value
            // if no create a new value
            log_warning('check if removing a word link is matching another value for "' . $this->id . '" not yet coded');
        }

        log_debug($result);
        return $result;
    }


    /*
     * debug
     */

    /**
     * @return string with the description for this value phrase link for debugging
     */
    function dsp_id(): string
    {
        return 'link ' . $this->val->dsp_id_short()
            . ' to ' . $this->phr->dsp_id(false) . ' for ' . $this->user()->dsp_id();
    }

}
