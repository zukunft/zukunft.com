<?php

/*

    model/formula/formula_link.php - link a formula to a word
    ------------------------------

    The main sections of this object are
    - db const:          const for the database link
    - object vars:       the variables of this word object
    - construct and map: including the mapping of the db row to this word object
    - set and get:       to capsule the vars from unexpected changes
    - preloaded:         select e.g. types from cache
    - load:              database access object (DAO) functions
    - save:              manage to update the database
    - sql write fields:  field list for writing to the database
    - debug:             internal support functions for debugging


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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace cfg\formula;

include_once DB_PATH . 'sql.php';
include_once DB_PATH . 'sql_creator.php';
include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_field_default.php';
include_once DB_PATH . 'sql_field_type.php';
include_once DB_PATH . 'sql_par.php';
include_once DB_PATH . 'sql_par_field_list.php';
include_once DB_PATH . 'sql_par_type.php';
include_once DB_PATH . 'sql_type.php';
include_once DB_PATH . 'sql_type_list.php';
include_once MODEL_HELPER_PATH . 'combine_named.php';
include_once MODEL_HELPER_PATH . 'type_object.php';
include_once MODEL_LOG_PATH . 'change.php';
include_once MODEL_LOG_PATH . 'change_action.php';
include_once MODEL_LOG_PATH . 'change_table_list.php';
include_once MODEL_PHRASE_PATH . 'phrase.php';
include_once MODEL_SANDBOX_PATH . 'sandbox.php';
include_once MODEL_SANDBOX_PATH . 'sandbox_link.php';
include_once MODEL_SANDBOX_PATH . 'sandbox_named.php';
include_once MODEL_USER_PATH . 'user.php';
include_once MODEL_USER_PATH . 'user_message.php';
include_once SHARED_ENUM_PATH . 'change_actions.php';
include_once SHARED_ENUM_PATH . 'change_tables.php';
include_once SHARED_PATH . 'library.php';

use cfg\db\sql;
use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\db\sql_par;
use cfg\db\sql_par_field_list;
use cfg\db\sql_par_type;
use cfg\db\sql_type;
use cfg\db\sql_type_list;
use cfg\helper\combine_named;
use cfg\helper\type_object;
use cfg\log\change;
use cfg\phrase\phrase;
use cfg\sandbox\sandbox;
use cfg\sandbox\sandbox_link;
use cfg\sandbox\sandbox_named;
use cfg\user\user;
use cfg\user\user_message;
use shared\enum\change_actions;
use shared\enum\change_tables;
use shared\library;

class formula_link extends sandbox_link
{

    /*
     * db const
     */

    // object specific database and JSON object field names
    const TBL_COMMENT = 'for the link of a formula to phrases e.g. if the term pattern of a value matches this term pattern';
    // the database and JSON object field names used only for formula links
    const FLD_ID = 'formula_link_id';
    const FLD_TYPE = 'formula_link_type_id';
    const FLD_ORDER = 'order_nbr';
    const FLD_ORDER_SQL_TYP = sql_par_type::INT;

    // all database field names excluding the id
    const FLD_NAMES = array(
        formula_db::FLD_ID,
        phrase::FLD_ID,
        user::FLD_ID,
        formula_link_type::FLD_ID,
        self::FLD_ORDER,
        sql_db::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );
    // list of the link database field names
    const FLD_NAMES_LINK = array(
        formula_db::FLD_ID,
        phrase::FLD_ID
    );
    // all database field names excluding the id
    const FLD_NAMES_NUM_USR = array(
        formula_link_type::FLD_ID,
        self::FLD_ORDER,
        sql_db::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );
    // all database field names excluding the id used to identify if there are some user specific changes
    const ALL_SANDBOX_FLD_NAMES = array(
        formula_link_type::FLD_ID,
        self::FLD_ORDER,
        sql_db::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );
    // list of fields that CAN be changed by the user
    const FLD_LST_USER_CAN_CHANGE = array(
        [formula_link_type::FLD_ID, type_object::FLD_ID_SQL_TYP, sql_field_default::NULL, sql::INDEX, formula_link_type::class, '', formula_link_type::FLD_ID],
        [self::FLD_ORDER, sql_field_type::INT, sql_field_default::NULL, '', '', ''],
    );
    // list of fields that CANNOT be changed by the user
    const FLD_LST_NON_CHANGEABLE = array(
        [formula_db::FLD_ID, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, formula::class, ''],
        [phrase::FLD_ID, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, '', ''],
    );


    /*
     * object vars
     */

    // database fields additional to the user sandbox_link fields
    public ?int $order_nbr = null;    // to set the priority of the formula links


    /*
     * construct and map
     */

    /**
     * formula_link constructor that set the parameters for the _sandbox object
     */
    function __construct(user $usr)
    {
        parent::__construct($usr);
        $lib = new library();
        $this->from_name = $lib->class_to_name(formula::class);
        $this->to_name = $lib->class_to_name(phrase::class);

        $this->reset();
    }

    function reset(): void
    {
        parent::reset();

        $this->reset_objects($this->user());

        $this->order_nbr = null;
        global $frm_lnk_typ_cac;
        $this->set_predicate_id($frm_lnk_typ_cac->id(formula_link_type::DEFAULT));
    }

    /**
     * reset the in memory fields used e.g. if some ids are updated
     */
    private function reset_objects(user $usr): void
    {
        $this->set_formula(new formula($usr));
        $this->set_phrase(new phrase($usr));
    }

    /**
     * map the database fields to the object fields
     *
     * @param array|null $db_row with the data directly from the database
     * @param bool $load_std true if only the standard user sandbox object is loaded
     * @param bool $allow_usr_protect false for using the standard protection settings for the default object used for all users
     * @param string $id_fld the name of the id field as defined in this child and given to the parent
     * @return bool true if the formula link is loaded and valid
     */
    function row_mapper_sandbox(
        ?array $db_row,
        bool   $load_std = false,
        bool   $allow_usr_protect = true,
        string $id_fld = self::FLD_ID
    ): bool
    {
        $result = parent::row_mapper_sandbox($db_row, $load_std, $allow_usr_protect, self::FLD_ID);
        if ($result) {
            // TODO load by if from cache?
            $this->formula()->set_id($db_row[formula_db::FLD_ID]);
            $this->phrase()->set_id($db_row[phrase::FLD_ID]);
            $this->predicate_id = $db_row[formula_link_type::FLD_ID];
            $this->order_nbr = $db_row[formula_link::FLD_ORDER];
        }
        return $result;
    }


    /*
     * set and get
     */

    // TODO add function "formula()" that returns the "From_OBject (fob)"
    // TODO check that all link objects have a self speaking interface function for the "From_OBject (fob)" and "To_OBject (tob)"

    /**
     * set the main vars with one function
     * @param int $id the database id of the link
     * @param formula $frm the formula that should be linked
     * @param phrase $phr the phrase to which the formula should be linked
     * @return void
     */
    function set(int $id, formula $frm, phrase $phr): void
    {
        $this->set_id($id);
        $this->set_formula($frm);
        $this->set_phrase($phr);
    }

    /**
     * rename and cast the parent from object function
     * @param formula $frm the formula that should be linked
     * @return void
     */
    function set_formula(formula $frm): void
    {
        $this->set_fob($frm);
    }

    /**
     * rename and cast the parent from object function
     * @param phrase $phr the phrase to which the formula should be linked
     * @return void
     */
    function set_phrase(phrase $phr): void
    {
        $this->set_tob($phr);
    }

    function formula(): combine_named|sandbox_named|formula
    {
        return $this->fob();
    }

    function phrase(): combine_named|sandbox_named|phrase
    {
        return $this->tob();
    }

    /**
     * @return int the formula id and null if the formula is not set
     */
    function formula_id(): int
    {
        $result = 0;
        if ($this->fob() != null) {
            if ($this->fob()->id() > 0) {
                $result = $this->fob()->id();
            }
        }
        return $result;
    }

    /**
     * @return int the phrase id and null if the phrase is not set
     */
    function phrase_id(): int
    {
        $result = 0;
        if ($this->tob() != null) {
            if ($this->tob()->id() > 0) {
                $result = $this->tob()->id();
            }
        }
        return $result;
    }

    /**
     * expose the order number as pos
     * @return int|null
     */
    function pos(): ?int
    {
        return $this->order_nbr;
    }


    /*
     * preloaded
     */

    /**
     * get the name of the formula link type
     * @return string the name of the formula link type
     */
    function predicate_name(): string
    {
        global $frm_lnk_typ_cac;
        return $frm_lnk_typ_cac->name($this->predicate_id);
    }


    /*
     * load
     */

    /**
     * create an SQL statement to retrieve the user specific formula link from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation e.g. standard for values and results
     * @return sql_par the SQL statement base on the parameters set in $this
     */
    function load_sql_user_changes(
        sql_creator   $sc,
        sql_type_list $sc_par_lst = new sql_type_list()
    ): sql_par
    {
        $sc->set_class($this::class, new sql_type_list([sql_type::USER]));
        return parent::load_sql_user_changes($sc, $sc_par_lst);
    }

    /**
     * create the common part of an SQL statement to retrieve the parameters of a formula link from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name, string $class = self::class): sql_par
    {
        $qp = new sql_par($class);
        $qp->name .= $query_name;

        $sc->set_class($class);
        $sc->set_name($qp->name);
        $sc->set_usr($this->user()->id());
        $sc->set_fields(self::FLD_NAMES_LINK);
        $sc->set_usr_num_fields(self::FLD_NAMES_NUM_USR);

        return $qp;
    }

    /**
     * @return string the query name extension to make the query name unique and parameter specific
     */
    private function load_sql_name_extension(): string
    {
        $result = '';
        if ($this->id() != 0) {
            $result .= sql_db::FLD_ID;
        } elseif ($this->is_unique()) {
            $result .= 'link_ids';
        } else {
            log_err("Either the database ID (" . $this->id() . ") or the link ids must be set to load a word.", "formula_link->load");
        }
        return $result;
    }

    /**
     * create an SQL statement to retrieve the parameters of the standard formula link from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_standard_sql(sql_creator $sc): sql_par
    {
        $sc->set_class($this::class);
        $qp = new sql_par($this::class, new sql_type_list([sql_type::NORM]));
        $qp->name .= $this->load_sql_name_extension();
        $sc->set_name($qp->name);
        $sc->set_usr($this->user()->id());
        $sc->set_fields(self::FLD_NAMES);
        if ($this->id() != 0) {
            $sc->add_where($this->id_field(), $this->id());
        } elseif ($this->formula_id() != 0 and $this->phrase_id() != 0) {
            $sc->add_where(formula_db::FLD_ID, $this->formula_id());
            $sc->add_where(phrase::FLD_ID, $this->phrase_id());
        } else {
            log_err('Cannot load default formula link because no unique field is set');
        }
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * load the standard formula link to check if the user has done some personal changes
     * e.g. switched off a formula assignment
     * @param sql_par|null $qp placeholder to align the function parameters with the parent
     * @return bool true if the loading of the standard formula link been successful
     */
    function load_standard(?sql_par $qp = null): bool
    {

        global $db_con;
        $result = false;

        if ($this->is_unique()) {
            $qp = $this->load_standard_sql($db_con->sql_creator());

            if ($qp->name <> '') {
                $db_frm = $db_con->get1($qp);
                $this->row_mapper_sandbox($db_frm, true, false);
                $result = $this->load_owner();
            }
        }
        return $result;
    }

    /**
     * load a named user sandbox object by name
     * @param formula $frm the formula that is supposed to be linked
     * @param phrase $phr the phrase that is linked to the formula
     * @param string $class the name of the child class from where the call has been triggered
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_link(formula $frm, phrase $phr, string $class = self::class): int
    {
        global $frm_lnk_typ_cac;
        $id = parent::load_by_link_id($frm->id(), $frm_lnk_typ_cac->default_id(), $phr->id(), $class);
        // no need to reload the linked objects, just assign it
        if ($id != 0) {
            $this->set_formula($frm);
            $this->set_phrase($phr);
        }
        return $id;
    }

    /**
     * to load the formula and the phase object
     * if the link object is loaded by an external query like in user_display to show the sandbox
     * @return bool true if the loading of the linked objects has been successful
     */
    function load_objects(): bool
    {
        $result = true;
        if ($this->formula_id() > 0) {
            $frm = new formula($this->user());
            $frm->load_by_id($this->formula_id());
            if ($frm->id() > 0) {
                $this->set_formula($frm);
            } else {
                $result = false;
            }
        }
        if ($result) {
            if ($this->phrase_id() <> 0) {
                $phr = new phrase($this->user());
                $phr->load_by_id($this->phrase_id());
                if ($phr->id() != 0) {
                    $this->set_phrase($phr);
                } else {
                    $result = false;
                }
            }
        }
        return $result;
    }

    function from_field(): string
    {
        return formula_db::FLD_ID;
    }

    function to_field(): string
    {
        return phrase::FLD_ID;
    }

    function type_field(): string
    {
        return formula_link_type::FLD_ID;
    }


    /*
     * save
     */

    /**
     * @return bool true if no one has used this formula
     */
    function not_used(): bool
    {
        log_debug('formula_link->not_used (' . $this->id() . ')');

        // to review: maybe replace by a database foreign key check
        return $this->not_changed();
    }

    /**
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     *                 to check if no one else has changed the formula link
     */
    function not_changed_sql(sql_creator $sc): sql_par
    {
        $sc->set_class(formula_link::class);
        return $sc->load_sql_not_changed($this->id(), $this->owner_id());
    }

    /**
     * @return bool true if no other user has modified the formula link
     */
    function not_changed(): bool
    {
        log_debug($this->id() . ' by someone else than the owner (' . $this->owner_id() . ')');

        global $db_con;
        $result = true;
        $qp = $this->not_changed_sql($db_con->sql_creator());
        $db_con->usr_id = $this->user()->id();
        $db_row = $db_con->get1($qp);
        if ($db_row != null) {
            if ($db_row[user::FLD_ID] > 0) {
                $result = false;
            }
        }
        log_debug('for ' . $this->dsp_id() . ' is ' . zu_dsp_bool($result));
        return $result;
    }

    /**
     * set the main log entry parameters for updating one display word link field
     * e.g. that the user can see "moved formula list to position 3 in word view"
     * @return change the change log object with the presets for formula links
     */
    function log_upd_field(): change
    {
        $log = new change($this->user());
        $log->set_action(change_actions::UPDATE);
        if ($this->can_change()) {
            $log->set_class(formula_link::class);
        } else {
            $log->set_table(change_tables::FORMULA_LINK_USR);
        }

        return $log;
    }

    /**
     * save all updated formula_link fields excluding the name, because already done when adding a formula_link
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param formula_link|sandbox $db_obj the database record before the saving
     * @param formula_link|sandbox $norm_obj the database record defined as standard because it is used by most users
     * @return user_message the message that should be shown to the user in case something went wrong
     */
    function save_all_fields(sql_db $db_con, formula_link|sandbox $db_obj, formula_link|sandbox $norm_obj): user_message
    {
        // link type not used at the moment
        $usr_msg = $this->save_field_type($db_con, $db_obj, $norm_obj);
        $usr_msg->add($this->save_field_excluded($db_con, $db_obj, $norm_obj));
        log_debug('all fields for "' . $this->formula()->name() . '" to "' . $this->phrase()->name() . '" has been saved');
        return $usr_msg;
    }

    /**
     * create a new link object including the order number
     * @returns int the id of the creates object
     */
    function add_insert(): int
    {
        global $db_con;
        $db_con->set_class(self::class);
        return $db_con->insert_old(
            array($this->from_name . sql_db::FLD_EXT_ID, $this->to_name . sql_db::FLD_EXT_ID, user::FLD_ID, 'order_nbr'),
            array($this->formula_id(), $this->phrase_id(), $this->user()->id(), $this->order_nbr));
    }

    /**
     * update a formula_link in the database or create a user formula_link
     * @param bool $use_func if true a predefined function is used that also creates the log entries
     * @return user_message the message that should be shown to the user in case something went wrong
     */
    function save(?bool $use_func = null): user_message
    {

        global $db_con;
        $usr_msg = new user_message();

        // check if the required parameters are set
        if ($this->formula_id() != 0 and $this->phrase_id() != 0) {
            log_debug('"' . $this->formula()->name() . '" to "' . $this->phrase()->name() . '" (id ' . $this->id() . ') for user ' . $this->user()->name);
        } elseif ($this->id() > 0) {
            log_debug('id ' . $this->id() . ' for user ' . $this->user()->name);
        } else {
            log_err("Either the formula and the word or the id must be set to link a formula to a word.", "formula_link->save");
        }

        // decide which db write method should be used
        if ($use_func === null) {
            $use_func = $this->sql_default_script_usage();
        }

        // load the objects if needed
        $this->load_objects();

        // build the database object because the is anyway needed
        $db_con->set_usr($this->user()->id());
        $db_con->set_class(formula_link::class);

        // check if a new value is supposed to be added
        if ($this->id() <= 0) {
            log_debug('check if a new formula_link for "' . $this->formula()->name() . '" and "' . $this->phrase()->name() . '" needs to be created');
            // check if a formula_link with the same formula and word is already in the database
            $db_chk = new formula_link($this->user());
            $db_chk->set_formula($this->formula());
            $db_chk->set_phrase($this->phrase());
            $db_chk->load_standard();
            if ($db_chk->id() > 0) {
                $this->set_id($db_chk->id());
            }
        }

        if ($this->id() <= 0) {
            if ($this->db_ready()) {
                log_debug('new formula link from "' . $this->formula()->name() . '" to "' . $this->phrase()->name() . '"');
                $usr_msg->add_message_text($this->add($use_func)->get_last_message());
            }
        } else {
            log_debug('update "' . $this->id() . '"');
            // read the database values to be able to check if something has been changed; done first,
            // because it needs to be done for user and general formulas
            $db_rec = new formula_link($this->user());
            $db_rec->load_by_id($this->id());
            $db_rec->load_objects();
            $db_con->set_class(formula_link::class);
            log_debug("database formula loaded (" . $db_rec->id() . ")");
            $std_rec = new formula_link($this->user()); // must also be set to allow to take the ownership
            $std_rec->set_id($this->id());
            $std_rec->load_standard();
            log_debug("standard formula settings loaded (" . $std_rec->id() . ")");

            // for a correct user formula link detection (function can_change) set the owner even if the formula link has not been loaded before the save
            if ($this->owner_id() <= 0) {
                $this->set_owner_id($std_rec->owner_id());
            }

            // it should not be possible to change the formula or the word, but nevertheless check
            // instead of changing the formula or the word, a new link should be created and the old deleted
            if ($db_rec->formula() != null) {
                if ($db_rec->formula()->id() <> $this->formula()->id()
                    or $db_rec->phrase()->id() <> $this->phrase()->id()) {
                    log_debug("update link settings for id " . $this->id() . ": change formula " . $db_rec->formula_id() . " to " . $this->formula_id() . " and " . $db_rec->phrase_id() . " to " . $this->phrase_id());
                    $usr_msg->add_message_text(log_info('The formula link "' . $db_rec->formula()->name() . '" with "' . $db_rec->phrase()->name() . '" (id ' . $db_rec->formula_id() . ',' . $db_rec->phrase_id() . ') " cannot be changed to "' . $this->formula()->name() . '" with "' . $this->phrase()->name() . '" (id ' . $this->formula()->id() . ',' . $this->phrase()->id() . '). Instead the program should have created a new link.', "formula_link->save"));
                }
            }

            // check if the id parameters are supposed to be changed
            $this->load_objects();
            if ($usr_msg->is_ok()) {
                $usr_msg->add($this->save_id_if_updated($db_con, $db_rec, $std_rec, $use_func));
            }

            // if a problem has appeared up to here, don't try to save the values
            // the problem is shown to the user by the calling interactive script
            if ($usr_msg->is_ok()) {
                if ($use_func) {
                    $usr_msg->add($this->save_fields_func($db_con, $db_rec, $std_rec));
                } else {
                    $usr_msg->add($this->save_all_fields($db_con, $db_rec, $std_rec));
                }
            }
        }

        if (!$usr_msg->is_ok()) {
            log_err($usr_msg->get_last_message());
        }

        return $usr_msg;
    }


    protected function all_sandbox_fields(): array
    {
        return self::ALL_SANDBOX_FLD_NAMES;
    }


    /*
     * sql write fields
     */

    /**
     * get a list of all database fields that might be changed
     * excluding the internal fields e.g. the database id
     * field list must be corresponding to the db_fields_changed fields
     *
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return array list of all database field names that have been updated
     */
    function db_fields_all(sql_type_list $sc_par_lst = new sql_type_list()): array
    {
        return array_merge(
            parent::db_all_fields_link($sc_par_lst),
            [
                self::FLD_ORDER,
            ],
            parent::db_fields_all_sandbox()
        );
    }

    /**
     * get a list of database field names, values and types that have been updated
     *
     * @param sandbox|formula_link $sbx the compare value to detect the changed fields
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list list 3 entry arrays with the database field name, the value and the sql type that have been updated
     */
    function db_fields_changed(
        sandbox|formula_link $sbx,
        sql_type_list        $sc_par_lst = new sql_type_list()
    ): sql_par_field_list
    {
        global $cng_fld_cac;

        $sc = new sql_creator();
        $do_log = $sc_par_lst->incl_log();
        $usr_tbl = $sc_par_lst->is_usr_tbl();
        $table_id = $sc->table_id($this::class);

        $lst = parent::db_fields_changed($sbx, $sc_par_lst);
        // for the standard table the type field should always be included because it is part of the prime index
        if ($sbx->predicate_id() <> $this->predicate_id() or (!$usr_tbl and $sc_par_lst->is_insert())) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . formula_link_type::FLD_ID,
                    $cng_fld_cac->id($table_id . formula_link_type::FLD_ID),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            global $frm_lnk_typ_cac;
            $lst->add_type_field(
                formula_link_type::FLD_ID,
                type_object::FLD_NAME,
                $this->predicate_id(),
                $sbx->predicate_id(),
                $frm_lnk_typ_cac
            );
        }
        if ($sbx->pos() <> $this->pos()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . self::FLD_ORDER,
                    $cng_fld_cac->id($table_id . self::FLD_ORDER),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                self::FLD_ORDER,
                $this->pos(),
                self::FLD_ORDER_SQL_TYP,
                $sbx->pos()
            );
        }
        return $lst->merge($this->db_changed_sandbox_list($sbx, $sc_par_lst));
    }


    /*
     * debug
     */

    /**
     * @return string the html code to display the link name
     */
    function name(): string
    {
        $result = '';

        if ($this->formula() != null) {
            $result = $this->formula()->name();
        }
        if ($this->phrase_id() != 0) {
            $result = ' to ' . $this->phrase()->name();
        }

        return $result;
    }

    /**
     * @return string return the html code to display the link name
     */
    function name_linked(string $back = ''): string
    {
        $result = '';

        $this->load_objects();
        if ($this->formula_id() != 0 and $this->phrase_id() != 0) {
            $result = $this->formula()->name_linked($back) . ' to ' . $this->phrase()->display_linked();
        } else {
            $result .= log_err("The formula or the linked word cannot be loaded.", "formula_link->name");
        }

        return $result;
    }

}