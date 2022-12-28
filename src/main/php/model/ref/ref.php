<?php

/*

    model/ref/ref.php - a link between a phrase and another system such as wikidata
    -----------------

    a reference is potentially a bidirectional interface to another system
    that includes specific coding for the external system
    a source is always unidirectional and based on standard data format

    ref types are

    TODO add to UI; add unit tests


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

use export\exp_obj;
use export\ref_exp;

class ref
{

    /*
     * database link
     */

    // object specific database and JSON object field names
    const FLD_ID = 'ref_id';
    const FLD_TYPE = 'ref_type_id';
    const FLD_EX_KEY = 'external_key';

    // all database field names excluding the id used to identify if there are some user specific changes
    const FLD_NAMES = array(
        self::FLD_EX_KEY
    );

    // persevered reference names for unit and integration tests
    const TEST_REF_NAME = 'System Test Reference Name';

    // database fields
    public ?int $id = null;               // the database id of the reference
    public ?string $external_key = null;  // the unique key in the external system

    // in memory only fields
    private user $usr;                    // just needed for logging the changes
    public ?phrase $phr = null;           // the phrase object incl. the database id of the word, verb or formula
    public ?ref_type $ref_type = null;    // the ref type object incl. the database id of the ref type

    /*
     * im- and export link
     */

    // the field names used for the im- and export in the json or yaml format
    const FLD_EX_NAME = 'name';
    const FLD_EX_TYPE = 'type';


    /*
     * construct and map
     */

    function __construct(user $usr)
    {
        $this->set_user($usr);
        $this->create_objects($usr);
    }

    function reset(): void
    {
        $this->id = null;
        $this->external_key = '';

        $this->create_objects($this->user());
    }

    private function create_objects(user $usr):void
    {
        $this->phr = new phrase($usr);
        // TODO set a proper default value
        $this->ref_type = new ref_type(ref_type::WIKIDATA, ref_type::WIKIDATA);
    }

    /**
     * set the class vars based on a database record
     *
     * @param array $db_row is an array with the database values
     * @return bool true if the verb is loaded and valid
     */
    function row_mapper(array $db_row): bool
    {
        $result = false;
        if ($db_row != null) {
            if ($db_row[self::FLD_ID] > 0) {
                $this->id = $db_row[self::FLD_ID];
                $this->phr->set_id($db_row[phrase::FLD_ID]);
                $this->external_key = $db_row[self::FLD_EX_KEY];
                $this->ref_type = get_ref_type_by_id($db_row[self::FLD_TYPE]);
                if ($this->load_objects()) {
                    $result = true;
                    log_debug('done ' . $this->dsp_id());
                }
            } else {
                $this->id = 0;
            }
        } else {
            $this->id = 0;
        }
        return $result;
    }


    /*
     * set and get
     */

    /**
     * set the most often used reference vars with one set statement
     * @param int $id mainly for test creation the database id of the reference
     */
    public function set(int $id = 0): void
    {
        $this->set_id($id);
    }

    /**
     * @param int|null $id the database id of the verb
     */
    public function set_id(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * set the user of the reference
     *
     * @param user $usr the person who wants to access the reference
     * @return void
     */
    function set_user(user $usr): void
    {
        $this->usr = $usr;
    }

    /**
     * @return int the database id which is not 0 if the object has been saved
     */
    public function id(): int
    {
        return $this->id;
    }

    /**
     * @return user the person who wants to see the reference
     */
    function user(): user
    {
        return $this->usr;
    }


    /*
     * get preloaded information
     */

    /**
     * get the name of the reference type
     * @return string the name of the reference type
     */
    public function type_name(): string
    {
        //global $reference_types;
        //return $reference_types->name($this->ref_type->name());
        return $this->ref_type->name();
    }


    /*
     * loading
     */

    /**
     * create the common part of an SQL statement to retrieve the parameters of a source from the database
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    protected function load_sql(sql_db $db_con, string $query_name, string $class = self::class): sql_par
    {
        $qp = new sql_par($class);
        $qp->name .= $query_name;

        $db_con->set_type(sql_db::TBL_REF);
        $db_con->set_name($qp->name);
        $db_con->set_usr($this->user()->id);
        $db_con->set_link_fields(phrase::FLD_ID, self::FLD_TYPE);
        $db_con->set_fields(self::FLD_NAMES);

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a ref by id from the database
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param int $id the id of the user sandbox object
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_id(sql_db $db_con, int $id): sql_par
    {
        $qp = $this->load_sql($db_con, 'id');
        $qp->sql = $db_con->select_by_id($id);
        $qp->par = $db_con->get_par();

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a ref by name from the database
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $name the name of the term and the related word, triple, formula or verb
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_name(sql_db $db_con, string $name): sql_par
    {
        $qp = $this->load_sql($db_con, 'ex_key');
        $qp->sql = $db_con->select_by_name($name, self::FLD_EX_KEY);
        $qp->par = $db_con->get_par();

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a ref by id from the database
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param int $id the id of the user sandbox object
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_link_ids(sql_db $db_con, int $id): sql_par
    {
        $qp = $this->load_sql($db_con, 'link_ids');
        $db_con->set_where_link_no_fld($this->id, $this->phr->id(), $this->ref_type->id);
        $qp->sql = $db_con->select_by_id($id);
        $qp->par = $db_con->get_par();

        return $qp;
    }

    /**
     * test if the name is used already
     */
    function load_obj_vars(): bool
    {
        global $db_con;
        $result = false;

        // check if the minimal input parameters are set
        if ($this->id <= 0 and ($this->phr->id() <= 0 or $this->ref_type->id <= 0)) {
            log_err('Either the database ID (' . $this->id . ') or the phrase id (' . $this->phr->id() . ') AND the reference type id (' . $this->ref_type->id . ') must be set to load a reference.', 'ref->load');
        } else {

            $db_con->set_type(sql_db::TBL_REF);
            $qp = new sql_par(self::class);
            if ($this->id != 0) {
                $qp->name = 'ref_by_id';
            } else {
                $qp->name = 'ref_by_link_ids';
            }
            $db_con->set_name($qp->name);
            $db_con->set_usr($this->user()->id);
            $db_con->set_link_fields(phrase::FLD_ID, self::FLD_TYPE);
            $db_con->set_fields(self::FLD_NAMES);
            $db_con->set_where_link_no_fld($this->id, $this->phr->id(), $this->ref_type->id);
            $qp->sql = $db_con->select_by_set_id();
            $qp->par = $db_con->get_par();

            if ($db_con->get_where() <> '') {
                $db_ref = $db_con->get1($qp);
                $result = $this->row_mapper($db_ref);
            }
        }
        return $result;
    }

    /**
     * load a verb from the database
     * @param sql_par $qp the query parameters created by the calling function
     * @return int the id of the object found and zero if nothing is found
     */
    protected function load(sql_par $qp): int
    {
        global $db_con;

        $db_row = $db_con->get1($qp);
        $this->row_mapper($db_row);
        return $this->id();
    }

    /**
     * load a verb by database id
     * @param int $id the id of the word, triple, formula, verb, view or view component
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_id(int $id): int
    {
        global $db_con;

        log_debug($id);
        $qp = $this->load_sql_by_id($db_con, $id);
        return $this->load($qp);
    }

    /**
     * load a verb by the verb name
     * @param string $external_key_name the name of the external key for the reference
     * @return int the id of the verb found and zero if nothing is found
     */
    function load_by_ex_key(string $external_key_name): int
    {
        global $db_con;

        log_debug($external_key_name);
        $qp = $this->load_sql_by_name($db_con, $external_key_name);
        return $this->load($qp);
    }

    // to load the related objects if the reference object is loaded
    private function load_objects(): bool
    {
        $result = true;

        if ($this->phr->name() == null or $this->phr->name() == '') {
            if ($this->phr->id() <> 0) {
                $phr = new phrase($this->user());
                if ($phr->load_by_id($this->phr->id())) {
                    $this->phr = $phr;
                    log_debug('phrase ' . $this->phr->dsp_id() . ' loaded');
                } else {
                    $result = false;
                }
            }
        }

        log_debug('done');
        return $result;
    }

    function id_field(): string
    {
        return self::FLD_ID;
    }


    /*
     * im- and export
     */

    /**
     * import a link to external database from an imported json object
     *
     * @param array $json_obj an array with the data of the json object
     * @param bool $do_save can be set to false for unit testing
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_obj(array $json_obj, bool $do_save = true): user_message
    {
        $result = new user_message();

        // reset of object not needed, because the calling function has just created the object
        foreach ($json_obj as $key => $value) {
            if ($key == exp_obj::FLD_NAME) {
                $this->external_key = $value;
            }
            if ($key == exp_obj::FLD_TYPE) {
                $this->ref_type = get_ref_type($value);

                if ($this->ref_type == null) {
                    $result->add_message('Reference type for ' . $value . ' not found');
                } else {
                    $this->ref_type = get_ref_type($value);
                    log_debug('ref_type set based on ' . $value . ' (' . $this->ref_type->name . ')');
                }
            }
        }
        // to be able to log the object names
        if ($this->load_objects()) {
            if ($result == '' and $do_save) {
                $result->add_message($this->save());
            }
        }

        return $result;
    }

    /**
     * create a reference object for export (so excluding e.g. the database id)
     * @return ref_exp a reduced reference object for the JSON message creation
     */
    function export_obj(): ref_exp
    {
        $result = new ref_exp();

        if ($this->external_key <> '') {
            $result->name = $this->external_key;
        }
        if ($this->ref_type <> '') {
            $result->type = $this->ref_type->code_id;
        }

        return $result;
    }

    /*
    display functions
    */

    /**
     * display the unique id fields
     */
    function dsp_id(): string
    {
        $result = $this->name();
        if ($result <> '') {
            if ($this->id > 0) {
                $result .= ' (' . $this->id . ')';
            }
        } else {
            $result .= $this->id;
        }
        return $result;
    }

    // create the unique name
    function name(): string
    {
        $result = '';

        if (isset($this->phr)) {
            $result .= 'ref of "' . $this->phr->name() . '"';
        } else {
            if ($this->phr->id() != null) {
                if ($this->phr->id() != 0) {
                    $result .= 'ref of phrase id ' . $this->phr->id() . ' ';
                }
            }
        }
        if (isset($this->ref_type)) {
            $result .= 'to "' . $this->ref_type->name . '"';
        } else {
            if (isset($this->ref_type)) {
                if ($this->ref_type->id > 0) {
                    $result .= 'to type id ' . $this->ref_type->id . ' ';
                }
            }
        }
        return $result;
    }

    // set the log entry parameter for a new reference
    function log_add(): change_log_link
    {
        log_debug('ref->log_add ' . $this->dsp_id());

        // check that the minimal parameters are set
        if (!isset($this->phr)) {
            log_err('The phrase object must be set to log adding an external reference.', 'ref->log_add');
        }
        if (!isset($this->ref_type)) {
            log_err('The reference type object must be set to log adding an external reference.', 'ref->log_add');
        }

        $log = new change_log_link;
        $log->usr = $this->user();
        $log->action = change_log_action::ADD;
        $log->set_table(change_log_table::REF);
        // TODO review in log_link
        // TODO object must be loaded before it can be logged
        $log->new_from = $this->phr;
        $log->new_link = $this->ref_type;
        $log->new_to = $this;
        $log->row_id = 0;
        $log->add();

        return $log;
    }

    // set the main log entry parameters for updating one reference field
    function log_upd($db_rec): change_log_link
    {
        log_debug('ref->log_upd ' . $this->dsp_id());
        $log = new change_log_link;
        $log->usr = $this->user();
        $log->action = change_log_action::UPDATE;
        $log->set_table(change_log_table::REF);
        $log->old_from = $db_rec->phr;
        $log->old_link = $db_rec->ref_type;
        $log->old_to = $db_rec;
        $log->new_from = $this->phr;
        $log->new_link = $this->ref_type;
        $log->new_to = $this;
        $log->row_id = $this->id;
        $log->add();

        return $log;
    }

    // set the log entry parameter to delete a reference
    function log_del(): change_log_link
    {
        log_debug('ref->log_del ' . $this->dsp_id());

        // check that the minimal parameters are set
        if (!isset($this->phr)) {
            log_err('The phrase object must be set to log deletion of an external reference.', 'ref->log_del');
        }
        if (!isset($this->ref_type)) {
            log_err('The reference type object must be set to log deletion of an external reference.', 'ref->log_del');
        }

        $log = new change_log_link;
        $log->usr = $this->user();
        $log->action = change_log_action::DELETE;
        $log->set_table(change_log_table::REF);
        $log->old_from = $this->phr;
        $log->old_link = $this->ref_type;
        $log->old_to = $this;
        $log->row_id = $this->id;
        $log->add();

        return $log;
    }

    // update a ref in the database or update the existing
    // returns the database id of the created reference or 0 if not successful
    private function add(): string
    {
        log_debug('ref->add ' . $this->dsp_id());

        global $db_con;
        $result = '';

        // log the insert attempt first
        $log = $this->log_add();
        if ($log->id() > 0) {
            // insert the new reference
            $db_con->set_type(sql_db::TBL_REF);
            $db_con->set_usr($this->user()->id);

            $this->id = $db_con->insert(
                array(phrase::FLD_ID, self::FLD_EX_KEY, self::FLD_TYPE),
                array($this->phr->id(), $this->external_key, $this->ref_type->id));
            if ($this->id > 0) {
                // update the id in the log for the correct reference
                if (!$log->add_ref($this->id)) {
                    $result .= 'Adding reference ' . $this->dsp_id() . ' in the log failed.';
                    log_err($result, 'ref->add');
                }
            } else {
                $result .= 'Adding reference ' . $this->dsp_id() . ' failed.';
                log_err($result, 'ref->add');
            }
        }

        return $result;
    }

    // get a similar reference
    function get_similar(): ?ref
    {
        $result = null;
        log_debug('ref->get_similar ' . $this->dsp_id());

        $db_chk = clone $this;
        $db_chk->reset();
        $db_chk->phr = $this->phr;
        $db_chk->ref_type = $this->ref_type;
        $db_chk->load_obj_vars();
        if ($db_chk->id > 0) {
            log_debug('ref->get_similar an external reference for ' . $this->dsp_id() . ' already exists');
            $result = $db_chk;
        }

        return $result;
    }

    // update a ref in the database or update the existing
    // returns the id of the updated or created reference
    function save(): string
    {
        log_debug('ref->save ' . $this->dsp_id());

        global $db_con;
        $result = '';

        // build the database object because the is anyway needed
        if ($this->user()->is_set()) {
            $db_con->set_usr($this->user()->id);
        }
        $db_con->set_type(sql_db::TBL_REF);

        // check if the external reference is supposed to be added
        if ($this->id <= 0) {
            // check possible duplicates before adding
            log_debug('ref->save check possible duplicates before adding ' . $this->dsp_id());
            $similar = $this->get_similar();
            if (isset($similar)) {
                if ($similar->id <> 0) {
                    $this->id = $similar->id;
                }
            }
        }

        // create a new object or update an existing
        if ($this->id <= 0) {
            log_debug('ref->save add');
            $result .= $this->add();
        } else {
            log_debug('ref->save update');

            // read the database values to be able to check if something has been changed;
            // done first, because it needs to be done for user and general object values
            $db_rec = clone $this;
            $db_rec->reset();
            $db_rec->load_by_id($this->id);
            log_debug('ref->save reloaded from db');

            // if needed log the change and update the database
            if ($this->external_key <> $db_rec->external_key) {
                $log = $this->log_upd($db_rec);
                if ($log->id() > 0) {
                    $db_con->set_type(sql_db::TBL_REF);
                    if ($db_con->update($this->id, self::FLD_EX_KEY, $this->external_key)) {
                        log_debug('ref->save update ... done.');
                    }
                }
            }
        }
        return $result;
    }

    // delete a reference of return false if it fails
    function del(): user_message
    {
        global $db_con;
        $result = new user_message();

        if (!$this->load_obj_vars()) {
            log_warning('Reload of ref ' . $this->dsp_id() . ' for deletion failed', 'ref->del');
        } else {
            if ($this->id <= 0) {
                log_warning('Delete failed, because it seems that the ref ' . $this->dsp_id() . ' has been deleted in the meantime.', 'ref->del');
            } else {
                $log = $this->log_del();
                if ($log->id() > 0) {
                    $db_con->set_type(sql_db::TBL_REF);
                    $del_result = $db_con->delete(self::FLD_ID, $this->id);
                    if ($del_result == '') {
                        log_debug('done.');
                    } else {
                        $result->add_message($del_result);
                    }
                }
            }
        }
        return $result;
    }

}