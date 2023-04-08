<?php

/*

    model/formula/formula_link.php - link a formula to a word
    ------------------------------

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

namespace model;

include_once MODEL_SANDBOX_PATH . 'sandbox_link_with_type.php';

class formula_link extends sandbox_link_with_type
{

    // list of the formula link types that have a coded functionality
    const DEFAULT = "default";               // a simple link between a formula and a phrase
    const TIME_PERIOD = "time_period_based"; // for time based links

    // the database and JSON object field names used only for formula links
    const FLD_ID = 'formula_link_id';
    const FLD_TYPE = 'link_type_id';

    // all database field names excluding the id
    const FLD_NAMES_NUM_USR = array(
        self::FLD_TYPE,
        sandbox::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );
    // all database field names excluding the id used to identify if there are some user specific changes
    const ALL_FLD_NAMES = array(
        self::FLD_TYPE,
        sandbox::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );

    // database fields additional to the user sandbox fields
    public ?int $order_nbr = null;    // to set the priority of the formula links
    public ?int $link_type_id = null; // define a special behavior for this link (maybe not needed at the moment)

    /**
     * formula_link constructor that set the parameters for the _sandbox object
     */
    function __construct(user $usr)
    {
        parent::__construct($usr);
        $this->obj_type = sandbox::TYPE_LINK;
        $this->obj_name = sql_db::TBL_FORMULA_LINK;
        $this->from_name = sql_db::TBL_FORMULA;
        $this->to_name = sql_db::TBL_PHRASE;

        $this->reset();
    }

    function reset(): void
    {
        parent::reset();

        $this->reset_objects($this->user());

        $this->order_nbr = null;
        $this->link_type_id = null;
    }

    /**
     * reset the in memory fields used e.g. if some ids are updated
     */
    private function reset_objects(user $usr)
    {
        $this->fob = new formula($usr);
        $this->tob = new phrase($usr);
    }

    /**
     * map the database fields to the object fields
     *
     * @param array $db_row with the data directly from the database
     * @param bool $load_std true if only the standard user sandbox object ist loaded
     * @param bool $allow_usr_protect false for using the standard protection settings for the default object used for all users
     * @param string $id_fld the name of the id field as defined in this child and given to the parent
     * @return bool true if the formula link is loaded and valid
     */
    function row_mapper(
        ?array $db_row,
        bool   $load_std = false,
        bool   $allow_usr_protect = true,
        string $id_fld = self::FLD_ID
    ): bool
    {
        $result = parent::row_mapper($db_row, $load_std, $allow_usr_protect, self::FLD_ID);
        if ($result) {
            $this->fob->set_id($db_row[formula::FLD_ID]);
            $this->tob->set_id($db_row[phrase::FLD_ID]);
            $this->link_type_id = $db_row[self::FLD_TYPE];
        }
        return $result;
    }

    /*
     * load functions
     */

    /**
     * create an SQL statement to retrieve the user specific formula link from the database
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param bool $get_name to create the SQL statement name for the predefined SQL within the same function to avoid duplicating if in case of more than on where type
     * @return sql_par the SQL statement base on the parameters set in $this
     */
    function usr_cfg_sql(sql_db $db_con, string $class = self::class): sql_par
    {
        $db_con->set_type(sql_db::TBL_FORMULA_LINK, true);
        $qp = new sql_par($class);
        $sql_name = self::class . '_add_usr_cfg';
        $db_con->set_name($qp->name);
        $db_con->set_usr($this->user()->id());
        $db_con->set_fields(formula_link::FLD_NAMES_NUM_USR);
        $db_con->set_where_std($this->id);
        $qp->sql = $db_con->select_by_set_id();
        $qp->par = $db_con->get_par();

        return $qp;
    }

    /*
     * internal check function
     */

    /**
     * @return bool true if the user is valid
     */
    private function is_usr_set(): bool
    {
        $result = false;
        if ($this->user()->id() > 0) {
            $result = true;
        }
        return $result;
    }

    /*
     * get functions
     */

    /**
     * @return int the formula id and null if the formula is not set
     */
    function formula_id(): int
    {
        $result = 0;
        if ($this->fob != null) {
            if ($this->fob->id > 0) {
                $result = $this->fob->id;
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
        if ($this->tob != null) {
            if ($this->tob->id() > 0) {
                $result = $this->tob->id();
            }
        }
        return $result;
    }

    /*
     * get preloaded information
     */

    /**
     * get the name of the formula link type
     * @return string the name of the formula link type
     */
    function type_name(): string
    {
        global $formula_link_types;
        return $formula_link_types->name($this->type_id);
    }


    /*
     * load functions
     */

    /**
     * create the common part of an SQL statement to retrieve the parameters of a formula link from the database
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    protected function load_sql(sql_db $db_con, string $query_name, string $class = self::class): sql_par
    {
        $qp = parent::load_sql_obj_vars($db_con, $class);
        $qp->name .= $query_name;

        $db_con->set_type(sql_db::TBL_FORMULA_LINK);
        $db_con->set_name($qp->name);
        $db_con->set_usr($this->user()->id());
        $db_con->set_link_fields(formula::FLD_ID, phrase::FLD_ID);
        $db_con->set_usr_num_fields(self::FLD_NAMES_NUM_USR);

        return $qp;
    }

    /**
     * @return string the query name extension to make the query name unique and parameter specific
     */
    private function load_sql_name_extension(): string
    {
        $result = '';
        if ($this->id != 0) {
            $result .= 'id';
        } elseif ($this->is_unique()) {
            $result .= 'link_ids';
        } else {
            log_err("Either the database ID (" . $this->id . ") or the link ids must be set to load a word.", "formula_link->load");
        }
        return $result;
    }

    /**
     * create an SQL statement to retrieve the parameters of the standard formula link from the database
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_standard_sql(sql_db $db_con, string $class = self::class): sql_par
    {
        $db_con->set_type(sql_db::TBL_FORMULA_LINK);
        $qp = new sql_par($class, true);
        $qp->name .= $this->load_sql_name_extension();
        $db_con->set_name($qp->name);
        $db_con->set_usr($this->user()->id());
        $db_con->set_link_fields(formula::FLD_ID, phrase::FLD_ID);
        $db_con->set_fields(array(sql_db::FLD_USER_ID, formula_link::FLD_TYPE, self::FLD_EXCLUDED));
        $db_con->set_where_link_no_fld($this->id, $this->formula_id(), $this->phrase_id());
        $qp->sql = $db_con->select_by_set_id();
        $qp->par = $db_con->get_par();

        return $qp;
    }

    /**
     * load the standard formula link to check if the user has done some personal changes
     * e.g. switched off a formula assignment
     * @param sql_par|null $qp placeholder to align the function parameters with the parent
     * @param string $class the name of this class to be delivered to the parent function
     * @return bool true if the loading of the standard formula link been successful
     */
    function load_standard(?sql_par $qp = null, string $class = self::class): bool
    {

        global $db_con;
        $result = false;

        if ($this->is_unique()) {
            $qp = $this->load_standard_sql($db_con);

            if ($db_con->get_where() <> '') {
                $db_frm = $db_con->get1($qp);
                $this->row_mapper($db_frm, true, false);
                $result = $this->load_owner();
            }
        }
        return $result;
    }

    /**
     * create an SQL statement to retrieve the parameters of a formula link from the database
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_obj_vars(sql_db $db_con, string $class = self::class): sql_par
    {
        $qp = parent::load_sql_obj_vars($db_con, $class);
        $qp->name .= $this->load_sql_name_extension();
        $db_con->set_type(sql_db::TBL_FORMULA_LINK);
        $db_con->set_name($qp->name);
        $db_con->set_usr($this->user()->id());
        $db_con->set_link_fields(formula::FLD_ID, phrase::FLD_ID);
        $db_con->set_usr_num_fields(self::FLD_NAMES_NUM_USR);
        $db_con->set_where_link_no_fld($this->id, $this->formula_id(), $this->phrase_id());
        $qp->sql = $db_con->select_by_set_id();
        $qp->par = $db_con->get_par();

        return $qp;
    }

    /**
     * load the missing formula link parameters from the database
     * the formula link can be either identified by the id
     * or by the IDs of the formula and the assigned phrase
     * @return bool true if the loading of the formula link been successful
     */
    function load_obj_vars(): bool
    {
        global $db_con;
        $result = false;

        // check the all minimal input parameters are set
        if (!$this->is_usr_set()) {
            log_err("The user id must be set to load a formula link.", "formula_link->load");
        } else {

            if (!$this->is_unique()) {
                log_warning("The formula link " . $this->dsp_id() . " is not unique", "formula_link->load");
            } else {
                $qp = $this->load_sql_obj_vars($db_con);

                if ($qp->has_par()) {
                    $db_row = $db_con->get1($qp);
                    $this->row_mapper($db_row);
                    if ($this->id > 0) {
                        log_debug('formula_link->load (' . $this->id . ')');
                        $result = true;
                    }
                }
            }
        }
        return $result;
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
            $frm->load_by_id($this->formula_id(), formula::class);
            if ($frm->id > 0) {
                $this->fob = $frm;
            } else {
                $result = false;
            }
        }
        if ($result) {
            if ($this->phrase_id() <> 0) {
                $phr = new phrase($this->user());
                $phr->id = $this->phrase_id();
                if ($phr->load_by_obj_par()) {
                    $this->tob = $phr;
                } else {
                    $result = false;
                }
            }
        }
        return $result;
    }

    function id_field(): string
    {
        return self::FLD_ID;
    }

    function from_field(): string
    {
        return formula::FLD_ID;
    }

    function to_field(): string
    {
        return phrase::FLD_ID;
    }

    /*
     * display functions
     */

    /**
     * @return string the unique id of the formula link
     */
    function dsp_id(): string
    {
        $result = '';

        if ($this->fob != null) {
            if ($this->fob->name() <> '') {
                $result .= $this->fob->name() . ' '; // e.g. Company details
                $result .= ' (' . $this->fob->id . ')';
            }
        }
        if ($this->tob != null) {
            if ($this->tob->name() <> '') {
                if ($result != '') {
                    $result .= ' ';
                }
                $result .= $this->tob->name();     // e.g. cash flow statement
                $result .= ' (' . $this->tob->id . ')';
            }
        }

        if ($this->id > 0) {
            $result .= ' -> ' . $this->id;
        }
        if ($this->user()->is_set()) {
            $result .= ' for user ' . $this->user()->id() . ' (' . $this->user()->name . ')';
        }
        return $result;
    }

    /**
     * @return string the html code to display the link name
     */
    function name(): string
    {
        $result = '';

        if ($this->fob != null) {
            $result = $this->fob->name();
        }
        if ($this->tob != null) {
            $result = ' to ' . $this->tob->name();
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
        if (isset($this->fob) and isset($this->tob)) {
            $result = $this->fob->name_linked($back) . ' to ' . $this->tob->dsp_link();
        } else {
            $result .= log_err("The formula or the linked word cannot be loaded.", "formula_link->name");
        }

        return $result;
    }

    /*
     * save functions
     */

    /**
     * @return bool true if no one has used this formula
     */
    function not_used(): bool
    {
        log_debug('formula_link->not_used (' . $this->id . ')');

        // to review: maybe replace by a database foreign key check
        return $this->not_changed();
    }

    /**
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     *                 to check if no one else has changed the formula link
     */
    function not_changed_sql(sql_db $db_con): sql_par
    {
        $db_con->set_type(sql_db::TBL_FORMULA_LINK);
        return $db_con->not_changed_sql($this->id, $this->owner_id);
    }

    /**
     * @return bool true if no other user has modified the formula link
     */
    function not_changed(): bool
    {
        log_debug($this->id . ' by someone else than the owner (' . $this->owner_id . ')');

        global $db_con;
        $result = true;
        $qp = $this->not_changed_sql($db_con);
        $db_con->usr_id = $this->user()->id();
        $db_row = $db_con->get1($qp);
        if ($db_row != null) {
            if ($db_row[self::FLD_USER] > 0) {
                $result = false;
            }
        }
        log_debug('for ' . $this->id . ' is ' . zu_dsp_bool($result));
        return $result;
    }

    /**
     * @return bool true if a record for a user specific configuration already exists in the database
     */
    function has_usr_cfg(): bool
    {
        $has_cfg = false;
        if ($this->usr_cfg_id > 0) {
            $has_cfg = true;
        }
        return $has_cfg;
    }

    /**
     * create a database record to save user specific settings for this formula_link
     * @return bool true if adding the new formula link has been successful
     */
    protected function add_usr_cfg(string $class = self::class): bool
    {
        global $db_con;
        $result = true;

        if (!$this->has_usr_cfg()) {
            // check again if there ist not yet a record
            $qp = $this->usr_cfg_sql($db_con);
            $db_row = $db_con->get1($qp);
            if ($db_row != null) {
                $this->usr_cfg_id = $db_row[formula_link::FLD_ID];
            }
            // create an entry in the user sandbox
            $db_con->set_type(sql_db::TBL_USER_PREFIX . sql_db::TBL_FORMULA_LINK);
            $log_id = $db_con->insert(array(formula_link::FLD_ID, sandbox::FLD_USER), array($this->id, $this->user()->id()));
            if ($log_id <= 0) {
                log_err('Insert of user_formula_link failed.');
                $result = false;
            }
        }
        return $result;
    }


    /**
     * set the main log entry parameters for updating one display word link field
     * e.g. that the user can see "moved formula list to position 3 in word view"
     * @return change_log_named the change log object with the presets for formula links
     */
    function log_upd_field(): change_log_named
    {
        $log = new change_log_named;
        $log->usr = $this->user();
        $log->action = change_log_action::UPDATE;
        if ($this->can_change()) {
            $log->set_table(change_log_table::FORMULA_LINK);
        } else {
            $log->set_table(change_log_table::FORMULA_LINK_USR);
        }

        return $log;
    }

    /**
     * save all updated formula_link fields excluding the name, because already done when adding a formula_link
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param formula_link|sandbox $db_rec the database record before the saving
     * @param formula_link|sandbox $std_rec the database record defined as standard because it is used by most users
     * @return string the message shown to the user why the action has failed or an empty string if everything is fine
     */
    function save_fields(sql_db $db_con, formula_link|sandbox $db_rec, formula_link|sandbox $std_rec): string
    {
        // link type not used at the moment
        $result = $this->save_field_type($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_excluded($db_con, $db_rec, $std_rec);
        log_debug('all fields for "' . $this->fob->name() . '" to "' . $this->tob->name() . '" has been saved');
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
            array($this->from_name . sql_db::FLD_EXT_ID, $this->to_name . sql_db::FLD_EXT_ID, "user_id", 'order_nbr'),
            array($this->fob->id, $this->tob->id, $this->user()->id, $this->order_nbr));
    }

    /**
     * update a formula_link in the database or create a user formula_link
     * @return string the message shown to the user why the action has failed or an empty string if everything is fine
     */
    function save(): string
    {

        global $db_con;
        $result = '';

        // check if the required parameters are set
        if (isset($this->fob) and isset($this->tob)) {
            log_debug('"' . $this->fob->name() . '" to "' . $this->tob->name() . '" (id ' . $this->id . ') for user ' . $this->user()->name);
        } elseif ($this->id > 0) {
            log_debug('id ' . $this->id . ' for user ' . $this->user()->name);
        } else {
            log_err("Either the formula and the word or the id must be set to link a formula to a word.", "formula_link->save");
        }

        // load the objects if needed
        $this->load_objects();

        // build the database object because the is anyway needed
        $db_con->set_usr($this->user()->id());
        $db_con->set_type(sql_db::TBL_FORMULA_LINK);

        // check if a new value is supposed to be added
        if ($this->id <= 0) {
            log_debug('check if a new formula_link for "' . $this->fob->name() . '" and "' . $this->tob->name() . '" needs to be created');
            // check if a formula_link with the same formula and word is already in the database
            $db_chk = new formula_link($this->user());
            $db_chk->fob = $this->fob;
            $db_chk->tob = $this->tob;
            $db_chk->load_standard();
            if ($db_chk->id > 0) {
                $this->id = $db_chk->id;
            }
        }

        if ($this->id <= 0) {
            log_debug('new link from "' . $this->fob->name() . '" to "' . $this->tob->name() . '"');
            $result .= $this->add()->get_last_message();
        } else {
            log_debug('update "' . $this->id . '"');
            // read the database values to be able to check if something has been changed; done first,
            // because it needs to be done for user and general formulas
            $db_rec = new formula_link($this->user());
            $db_rec->id = $this->id;
            $db_rec->load_obj_vars();
            $db_rec->load_objects();
            $db_con->set_type(sql_db::TBL_FORMULA_LINK);
            log_debug("database formula loaded (" . $db_rec->id . ")");
            $std_rec = new formula_link($this->user()); // must also be set to allow to take the ownership
            $std_rec->id = $this->id;
            $std_rec->load_standard();
            log_debug("standard formula settings loaded (" . $std_rec->id . ")");

            // for a correct user formula link detection (function can_change) set the owner even if the formula link has not been loaded before the save
            if ($this->owner_id <= 0) {
                $this->owner_id = $std_rec->owner_id;
            }

            // it should not be possible to change the formula or the word, but nevertheless check
            // instead of changing the formula or the word, a new link should be created and the old deleted
            if ($db_rec->fob != null) {
                if ($db_rec->fob->id <> $this->fob->id
                    or $db_rec->tob->id <> $this->tob->id) {
                    log_debug("update link settings for id " . $this->id . ": change formula " . $db_rec->formula_id() . " to " . $this->formula_id() . " and " . $db_rec->phrase_id() . " to " . $this->phrase_id());
                    $result .= log_info('The formula link "' . $db_rec->fob->name . '" with "' . $db_rec->tob->name . '" (id ' . $db_rec->formula_id() . ',' . $db_rec->phrase_id() . ') " cannot be changed to "' . $this->fob->name . '" with "' . $this->tob->name . '" (id ' . $this->fob->id . ',' . $this->tob->id . '). Instead the program should have created a new link.', "formula_link->save");
                }
            }

            // check if the id parameters are supposed to be changed
            $this->load_objects();
            if ($result == '') {
                $result = $this->save_id_if_updated($db_con, $db_rec, $std_rec);
            }

            // if a problem has appeared up to here, don't try to save the values
            // the problem is shown to the user by the calling interactive script
            if ($result == '') {
                $result = $this->save_fields($db_con, $db_rec, $std_rec);
            }
        }

        if ($result != '') {
            log_err($result);
        }

        return $result;
    }

}