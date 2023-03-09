<?php

/*

  formula_value.php - the calculated numeric result of a formula
  -----------------
  
  TODO: add these function
  
  set_dirty_on_value_update  - set all formula result value to dirty that are depending on an updated values via Apache Kafka messages not via database
  set_dirty_on_result_update - set all formula result value to dirty that are depending on an updated formula result
  set_cleanup_prios          - define which formula results needs to be updated first
  cleanup                    - update/calculated all dirty formula results
                               do the cleanup calculations always "in memory" 
                               drop the results in blocks to the database
  

  
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

use api\formula_value_api;
use export\exp_obj;
use export\formula_value_exp;

class formula_value extends db_object
{

    /*
     * database link
     */

    // database fields only used for formula values
    const FLD_ID = 'formula_value_id';
    const FLD_SOURCE_GRP = 'source_phrase_group_id';
    const FLD_SOURCE_TIME = 'source_time_id';
    const FLD_GRP = 'phrase_group_id';
    const FLD_TIME = 'time_word_id';
    const FLD_VALUE = 'formula_value';
    const FLD_LAST_UPDATE = 'last_update';
    const FLD_DIRTY = 'dirty';

    // all database field names used
    const FLD_NAMES = array(
        formula::FLD_ID,
        user::FLD_ID,
        self::FLD_SOURCE_GRP,
        self::FLD_SOURCE_TIME,
        self::FLD_GRP,
        self::FLD_VALUE,
        self::FLD_LAST_UPDATE,
        self::FLD_DIRTY
    );


    /*
     * object vars
     */

    // database fields
    // $id                                           (the second unique key is frm_id, src_phr_grp_id, src_time_id, phr_grp_id, time_id, usr_id)
    public user $usr;                          // the user who wants to see the result because the formula and values can differ for each user; this is
    public ?int $owner_id = null;              // the user for whom the result has been calculated; if Null the result is the standard result
    public ?bool $is_std = True;               // true as long as no user specific value, formula or assignment is used for this result
    public ?int $src_phr_grp_id = null;        // the word group used for calculating the result
    public ?int $src_time_id = null;           // the time word id for calculating the result
    public ?int $phr_grp_id = null;            // the result word group as saved in the database
    public ?int $time_id = null;               // the result time word id as saved in the database, which can differ from the source time
    public ?float $value = null;               // ... and finally the numeric value
    public ?DateTime $last_update = null;      // ... and the time of the last update; all updates up to this time are included in this result
    public ?bool $dirty = null;                // true as long as an update is pending

    // objects directly linked to database fields
    public formula $frm;                       // the formula object used to calculate this result

    // in memory only fields (all methods except load and save should use the wrd_lst object not the ids and not the group id)
    public ?phrase_list $src_phr_lst = null;   // the source word list obj (not a list of word objects) based on which the result has been calculated
    public ?phrase $src_time_phr = null;       // the time word object created while loading
    public ?phrase_list $phr_lst = null;       // the phrase list obj (not a list of phrase objects) filled while loading
    public ?phrase $time_phr = null;           // the time word object created while loading
    public ?bool $val_missing = False;         // true if at least one of the formula values is not set which means is NULL (but zero is a value)
    public ?bool $is_updated = False;          // true if the formula value has been calculated, but not yet saved
    public ?string $ref_text = null;           // the formula text in the database reference format on which the result is based
    public ?string $num_text = null;           // the formula text filled with numbers used for the result calculation
    public ?DateTime $last_val_update = null;  // the time of the last update of an underlying value, formula result or formula
    //                                            if this is later than the last update the result needs to be updated

    // to be dismissed
    public ?phrase $phr = null;  // to get the most interesting result for this word


    /*
     * construct and map
     */

    function __construct(user $usr)
    {
        parent::__construct();
        $this->reset($usr);
    }

    function reset(user $usr): void
    {
        $this->set_id(0);
        $this->set_user($usr);
        $this->frm = new formula($usr);
    }

    /**
     * map the database fields to the object fields
     *
     * @param array $db_row with the data directly from the database
     * @return bool true if a formula value has been loaded and is valid
     */
    function row_mapper(array $db_row): bool
    {
        $lib = new library();
        $this->id = 0;
        $result = false;
        if ($db_row != null) {
            if ($db_row[self::FLD_ID] > 0) {
                $this->id = $db_row[self::FLD_ID];
                $this->frm->set_id($db_row[formula::FLD_ID]);
                $this->owner_id = $db_row[user_sandbox::FLD_USER];
                $this->src_phr_grp_id = $db_row[self::FLD_SOURCE_GRP];
                $this->src_time_id = $db_row[self::FLD_SOURCE_TIME];
                $this->phr_grp_id = $db_row[self::FLD_GRP];
                $this->value = $db_row[self::FLD_VALUE];
                $this->last_update = $lib->get_datetime($db_row[self::FLD_LAST_UPDATE]);
                $this->last_val_update = $lib->get_datetime($db_row[self::FLD_LAST_UPDATE]);
                $this->dirty = $db_row[self::FLD_DIRTY];
                $this->load_phrases(true);
                $result = true;
            }
        }

        return $result;
    }


    /*
     * get and set
     */

    /**
     * set the user of the formula result list
     *
     * @param user $usr the person who wants to access the formula result
     * @return void
     */
    function set_user(user $usr): void
    {
        $this->usr = $usr;
    }

    /**
     * @return user the person who wants to see the formula result
     */
    function user(): user
    {
        return $this->usr;
    }

    function is_std(): bool
    {
        return $this->is_std;
    }


    /*
     * cast
     */

    /**
     * @return formula_value_api the formula result frontend api object
     */
    function api_obj(): object
    {
        $api_obj = new formula_value_api($this->id);
        $api_obj->set_number($this->value);
        return $api_obj;
    }


    /*
     * loading
     */

    /**
     * create the SQL to load a formula values
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $query_name the unique name of the query e.g. id or name
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_db $db_con, string $query_name, string $class = self::class): sql_par
    {
        $qp = new sql_par($class);
        $qp->name .= $query_name;

        $db_con->set_type(sql_db::TBL_FORMULA_VALUE);
        $db_con->set_name($qp->name);
        $db_con->set_usr($this->user()->id);
        $db_con->set_fields(self::FLD_NAMES);

        return $qp;
    }

    /**
     * create the SQL to load a formula values by the id
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param int $id the id of the formula value
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_id(sql_db $db_con, int $id, string $class = self::class): sql_par
    {
        $qp = $this->load_sql($db_con, 'id', $class);
        $db_con->add_par_int($id);
        $qp->sql = $db_con->select_by_field($this->id_field());
        $qp->par = $db_con->get_par();

        return $qp;
    }

    /**
     * prepare the query parameter to load a formula values by phrase group id
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    private function load_by_grp_sql_prepare(sql_db $db_con): sql_par
    {
        $qp = $this->load_sql($db_con, 'grp');
        $db_con->set_name($qp->name);
        // select the result based on the phrase group e.g. a more complex word list
        $db_con->add_par(sql_db::PAR_INT, $this->phr_grp_id);

        return $qp;
    }

    /**
     * create the query parameter to load a formula values
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param sql_par $qp the query parameters as previous defined
     * @param array $fld_lst the list of selection fields as previous defined
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    private function load_by_grp_sql_select(sql_db $db_con, sql_par $qp, array $fld_lst): sql_par
    {
        // and include the result words in the search, because one source word list can result to two result word
        // e.g. one time specific and one general
        $qp->sql = $db_con->select_by_field_list($fld_lst);
        $qp->par = $db_con->get_par();

        return $qp;
    }

    /**
     * create the SQL to load a formula values by phrase group id and time phrase
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_by_grp_time_sql(sql_db $db_con): sql_par
    {
        $qp = $this->load_by_grp_sql_prepare($db_con);
        $fld_lst = [];
        $fld_lst[] = self::FLD_GRP;
        $qp->name .= '_time';
        $db_con->set_name($qp->name);
        return $this->load_by_grp_sql_select($db_con, $qp, $fld_lst);
    }

    /**
     * create the SQL to load a formula values by phrase group id
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_by_grp_sql(sql_db $db_con): sql_par
    {
        $qp = $this->load_by_grp_sql_prepare($db_con);
        $fld_lst = [];
        $fld_lst[] = self::FLD_GRP;
        return $this->load_by_grp_sql_select($db_con, $qp, $fld_lst);
    }

    /**
     * load (or force reload from database of) a formula value by the id
     *
     * @return bool true if formula value has been loaded
     */
    function load_by_id(int $id = 0, string $class = self::class): int
    {
        global $db_con;
        $result = 0;

        if ($id > 0) {
            // if the id is given load the formula value from the database
            $this->reset($this->user());
            $this->id = $id;
        } else {
            // if the id is not given, refresh the object based pn the database
            if ($this->id > 0) {
                $id = $this->id;
                $this->reset($this->user());
                $this->id = $id;
            } else {
                log_err('The formula value id and the user must be set ' .
                    'to load a ' . self::class, self::class . '->load_by_id');
            }
        }
        $qp = $this->load_sql_by_id($db_con, $id);
        if ($qp->name != '') {
            $db_row = $db_con->get1($qp);
            $this->row_mapper($db_row);
            $result = $this->id();
        }

        return $result;
    }

    /**
     * load all a formula value by the phrase group id and time phrase
     *
     * @return bool true if formula value has been loaded
     */
    function load_by_grp(int $grp_id, ?int $time_phr_id = null): bool
    {
        global $db_con;
        $result = false;

        if ($grp_id <= 0) {
            log_err('The formula value phrase group id and the user must be set ' .
                'to load a ' . self::class, self::class . '->load_by_grp');
        } else {
            $this->reset($this->user());
            $this->phr_grp_id = $grp_id;
            if ($time_phr_id != null) {
                $this->time_id = $time_phr_id;
                $qp = $this->load_by_grp_time_sql($db_con);
            } else {
                $qp = $this->load_by_grp_sql($db_con);
            }
            if ($qp->name != '') {
                $db_row = $db_con->get1($qp);
                $this->row_mapper($db_row);
                $result = true;
            }
        }

        return $result;
    }

    /**
     * load all a formula value by a give phrase list and if set the time phrase
     *
     * @return bool true if formula value has been loaded
     */
    function load_by_phr_lst(phrase_list $phr_lst, ?int $time_phr_id = null): bool
    {
        $result = false;

        if ($phr_lst->is_valid()) {
            $this->reset($this->user());
            $grp = $phr_lst->get_grp();
            $result = $this->load_by_grp($grp->id(), $time_phr_id);
        } else {
            log_err('The formula value phrase list and the user must be set ' .
                'to load a ' . self::class, self::class . '->load_by_phr_lst');
        }

        return $result;
    }

    /**
     * create the SQL to load a formula values by a given where statement
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $sql_where the ready to use SQL where statement
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_where(sql_db $db_con, sql_par $qp, string $sql_where = ''): sql_par
    {
        $db_con->set_name($qp->name);
        $db_con->set_usr($this->user()->id);
        $db_con->set_fields(self::FLD_NAMES);
        $db_con->set_where_text($sql_where);
        $qp->sql = $db_con->select_by_set_id();
        $qp->par = $db_con->get_par();

        return $qp;
    }

    /**
     * load the record from the database
     * in a separate function, because this can be called twice from the load function
     *
     * @param sql_par $qp the ready to use SQL where statement with the name and the parameters
     * @return bool true if one database record has been loaded
     */
    private function load_rec(sql_par $qp): bool
    {
        global $db_con;
        $result = false;

        $val_rows = $db_con->get($qp);
        if ($val_rows != null) {
            if (count($val_rows) > 0) {
                $val_row = $val_rows[0];
                $result = $this->row_mapper($val_row);
            }
        }
        return $result;
    }

    /**
     * set the SQL query parameters to load a formula value
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_obj_vars_sql(sql_db $db_con): sql_par
    {
        $sql_where = "";
        if ($this->id > 0) {
            $qp = $this->load_sql_by_id($db_con, $this->id);
        } else {
            // prepare the search by getting the word group based on the word list
            log_debug('not by id');

            // set the result group id if the result list is set, but not the group id
            $phr_grp = null;
            if ($this->phr_grp_id <= 0) {
                $phr_lst = null;
                if ($this->phr_lst != null) {
                    if (!$this->phr_lst->is_empty()) {
                        $phr_lst = clone $this->phr_lst;
                        //$phr_lst->ex_time();
                        log_debug('get group by ' . $phr_lst->dsp_name());
                        // ... or based on the phrase ids
                    }
                } elseif (!empty($this->phr_ids())) {
                    $phr_lst = new phrase_list($this->user());
                    $phr_lst->load_names_by_ids(new phr_ids($this->phr_ids()));
                    // ... or to get the most interesting result for this word
                } elseif ($this->phr != null and isset($this->frm)) {
                    if ($this->phr->id() > 0 and $this->frm->id() > 0 and isset($this->frm->name_wrd)) {
                        // get the best matching word group
                        $phr_lst = new phrase_list($this->user());
                        $phr_lst->add($this->phr);
                        $phr_lst->add($this->frm->name_wrd->phrase());
                        log_debug('get group by words ' . $phr_lst->dsp_name());
                    }
                }
                if (isset($phr_lst)) {
                    $this->phr_lst = $phr_lst;
                    log_debug('get group for ' . $phr_lst->dsp_name() . ' (including formula name)');
                    $phr_grp = $phr_lst->get_grp();
                    if (isset($phr_grp)) {
                        if ($phr_grp->id() > 0) {
                            $this->phr_grp_id = $phr_grp->id();
                        }
                    }
                }
            }
            if ($this->phr_grp_id <= 0) {
                log_debug('group not found!');
            }

            $db_con->set_type(sql_db::TBL_FORMULA_VALUE);
            $qp = new sql_par(self::class);
            $qp->name = 'fv_by_';

            // create the source phrase list if just the word is given
            if ($this->phr_lst == null and $this->phr != null) {
                if ($this->phr->id() > 0 or $this->phr->name() != '') {
                    $new_phr_lst = new phrase_list($this->user());
                    $new_phr_lst->add($this->phr);
                    $this->phr_lst = $new_phr_lst;
                }
            }

            // set the source group id if the source list is set, but not the group id
            if ($this->src_phr_grp_id <= 0 and $this->src_phr_lst != null) {

                if (!$this->src_phr_lst->is_empty()) {
                    $phr_grp = $this->src_phr_lst->get_grp();
                    if (isset($phr_grp)) {
                        if ($phr_grp->id() > 0) {
                            $this->src_phr_grp_id = $phr_grp->id();
                        }
                    }
                    log_debug('source group ' . $this->src_phr_grp_id . ' found for ' . $this->src_phr_lst->dsp_name());
                }
            }

            $sql_order = '';
            // include the source words in the search if requested
            if ($this->src_phr_grp_id > 0 and $this->user()->id() > 0) {
                $qp->name .= '_usr_src_phr_grp';
                $db_con->add_par(sql_db::PAR_INT, $this->src_phr_grp_id);
                $db_con->add_par(sql_db::PAR_INT, $this->user()->id);
                if ($sql_where != '') {
                    $sql_where .= ' AND ';
                }
                $sql_where .= " source_phrase_group_id = " . $db_con->par_name() . "
                           AND (user_id = " . $db_con->par_name() . " OR user_id = 0 OR user_id IS NULL) ";
                $sql_order = " ORDER BY user_id DESC";
            } else {
                $qp->name .= '_src_phr_grp';
                if ($this->src_phr_grp_id > 0) {
                    $db_con->add_par(sql_db::PAR_INT, $this->src_phr_grp_id);
                    if ($sql_where != '') {
                        $sql_where .= ' AND ';
                    }
                    $sql_where .= " source_phrase_group_id = " . $db_con->par_name() . " AND ";
                    $sql_order = " ORDER BY user_id";
                }
            }
            // and include the result words in the search, because one source word list can result to two result word
            // e.g. one time specific and one general
            // select the result based on words
            $sql_wrd = "";
            if ($this->phr_grp_id > 0 and $this->user()->id() > 0) {
                $qp->name .= '_usr_phr_grp';
                $db_con->add_par(sql_db::PAR_INT, $this->phr_grp_id);
                $db_con->add_par(sql_db::PAR_INT, $this->user()->id);
                if ($sql_where != '') {
                    $sql_where .= ' AND ';
                }
                $sql_where .= " phrase_group_id = " . $db_con->par_name() . "
                          AND (user_id = " . $db_con->par_name() . " OR user_id = 0 OR user_id IS NULL)";
                $sql_order = " ORDER BY user_id DESC";
            } else {
                if ($this->phr_grp_id > 0) {
                    $qp->name .= '_phr_grp';
                    $db_con->add_par(sql_db::PAR_INT, $this->phr_grp_id);
                    if ($sql_where != '') {
                        $sql_where .= ' AND ';
                    }
                    $sql_where .= " phrase_group_id = " . $db_con->par_name() . " ";
                    $sql_order = "ORDER BY user_id";
                }
            }
            // include the formula in the search
            if ($this->frm->id() > 0) {
                $qp->name .= '_frm_id';
                $db_con->add_par(sql_db::PAR_INT, $this->frm->id());
                if ($sql_where != '') {
                    $sql_where .= ' AND ';
                }
                $sql_where .= " formula_id = " . $db_con->par_name() . " ";
            }
            if ($sql_order <> '') {
                // if only the target value list is set, get the "best" result
                // TODO define what is the best result
                $sql_where .= $sql_order;
            }
        }

        if ($sql_where != '') {
            $qp = $this->load_sql_where($db_con, $qp, $sql_where);
        }
        return $qp;
    }

    // load the missing formula parameters from the database
    // TODO load user specific values
    // TODO create load_sql and name the query
    function load_obj_vars(): bool
    {

        global $db_con;
        $result = false;

        // check the all minimal input parameters
        if (!$this->user()->is_set()) {
            log_err("The user id must be set to load a result.", "formula_value->load");
        } else {

            // prepare the selection of the result
            $qp = $this->load_obj_vars_sql($db_con);

            // check if a valid identification is given and load the result
            if (!$qp->has_par()) {
                log_err("Either the database ID (" . $this->id() . ") or the source or result words or word group and the user (" . $this->user()->id() . ") must be set to load a result.", "formula_value->load");
            } else {
                $result = $this->load_rec($qp);

                // if no general value can be found, test if a more specific value can be found in the database
                // e.g. if ABB,Sales,2014 is requested, but there is only a value for ABB,Sales,2014,CHF,million get it
                // similar to the selection in value->load: maybe combine?
                log_debug('check best guess');
                if ($this->id() <= 0) {
                    if (!isset($phr_lst)) {
                        log_debug('no formula value found for ' . $qp->sql . ', but phrase list is also not set');
                    } else {
                        log_debug('try best guess');
                        if (count($phr_lst->lst) > 0) {
                            // the phrase groups with the least number of additional words that have at least one formula value
                            $sql_grp_from = '';
                            $sql_grp_where = '';
                            $pos = 1;
                            foreach ($phr_lst->lst as $phr) {
                                if ($sql_grp_from <> '') {
                                    $sql_grp_from .= ',';
                                }
                                $sql_grp_from .= 'phrase_group_word_links l' . $pos;
                                $pos_prior = $pos - 1;
                                if ($sql_grp_where <> '') {
                                    $sql_grp_where .= ' AND l' . $pos_prior . '.phrase_group_id = l' . $pos . '.phrase_group_id AND ';
                                }
                                $qp->name .= '_word_id';
                                $db_con->add_par(sql_db::PAR_INT, $phr->id());
                                $sql_grp_where .= ' l' . $pos . '.word_id = ' . $db_con->par_name();
                                $pos++;
                            }
                            $sql_grp = 'SELECT' . ' l1.phrase_group_id 
                            FROM ' . $sql_grp_from . ' 
                          WHERE ' . $sql_grp_where;
                            // TODO:
                            // count the number of phrases per group
                            // and add the user specific phrase links
                            // select also the time
                            $sql_val = "SELECT formula_value_id 
                            FROM formula_values
                          WHERE phrase_group_id IN (" . $sql_grp . ");";
                            log_debug('sql val "' . $sql_val . '"');
                            //$db_con = new mysql;
                            $db_con->usr_id = $this->user()->id();
                            $val_ids_rows = $db_con->get_old($sql_val);
                            if ($val_ids_rows != null) {
                                if (count($val_ids_rows) > 0) {
                                    $val_id_row = $val_ids_rows[0];
                                    $this->set_id($val_id_row[self::FLD_ID]);
                                    if ($this->id() > 0) {
                                        $qp->name .= '_guess_fv_id';
                                        $db_con->add_par(sql_db::PAR_INT, $this->id());
                                        $sql_where = "formula_value_id = " . $db_con->par_name();
                                        $this->load_rec($qp, $sql_where);
                                        log_debug('best guess id (' . $this->id() . ')');
                                    }
                                }
                            }
                        }
                    }
                }

                log_debug(phrase_list::class);
                $this->load_phrases();
            }
            log_debug('got id ' . $this->id() . ': ' . $this->value);
        }
        return $result;
    }

    function id_field(): string
    {
        return self::FLD_ID;
    }

    /*
     * phrase loading methods
     */

    /**
     * update the source phrase list based on the source phrase group id
     * @param bool $force_reload set to true if a loaded phrase list should refresh with database values
     */
    private function load_phr_lst_src(bool $force_reload = false): void
    {
        if ($this->src_phr_grp_id > 0) {
            if ($this->src_phr_lst == null or $force_reload) {
                log_debug('for source group "' . $this->src_phr_grp_id . '"');
                $phr_grp = new phrase_group($this->user());
                $phr_grp->set_id($this->src_phr_grp_id);
                $phr_grp->load();
                if (!$phr_grp->phr_lst->empty()) {
                    $this->src_phr_lst = $phr_grp->phr_lst;
                    log_debug('source phrases ' . $this->src_phr_lst->dsp_name() . ' loaded');
                } else {
                    log_debug('no source words found for ' . $this->dsp_id());
                }
            }
        }
        if ($this->src_phr_lst->empty()) {
            log_warning("Missing source words for the calculated value " . $this->id() . ' (group id ' . $this->src_phr_grp_id . ').', "formula_value->load_phr_lst_src");
        }
    }

    /**
     * update the phrase list based on the word group id
     * @param bool $force_reload set to true if a loaded phrase list should refresh with database values
     */
    private function load_phr_lst(bool $force_reload = false): void
    {
        if ($this->phr_grp_id > 0) {
            if ($this->phr_lst == null or $force_reload) {
                log_debug('for group "' . $this->phr_grp_id . '"');
                $phr_grp = new phrase_group($this->user());
                $phr_grp->set_id($this->phr_grp_id);
                $phr_grp->load();
                if (!$phr_grp->phr_lst->empty()) {
                    $this->phr_lst = $phr_grp->phr_lst;
                    log_debug('phrases ' . $this->phr_lst->dsp_name() . ' loaded');
                } else {
                    log_debug('no result phrases found for ' . $this->dsp_id());
                }
            }
        }
        if ($this->phr_lst->empty()) {
            log_warning("Missing result phrases for the calculated value " . $this->id(), "formula_value->load_phr_lst");
        }
    }

    /**
     * update the source time word object based on the source time word id
     * @param bool $force_reload set to true if a loaded source time phrase should be reloaded from database
     */
    private function load_time_wrd_src(bool $force_reload = false): void
    {
        if ($this->src_time_id <> 0) {
            if ($this->src_time_phr == null or $force_reload) {
                log_debug('formula_value->load_time_wrd_src for source time "' . $this->src_time_id . '"');
                $time_phr = new phrase($this->user());
                $time_phr->set_id($this->src_time_id);
                $time_phr->load_by_obj_par();
                if ($time_phr->isset()) {
                    $this->src_time_phr = $time_phr;
                    if ($this->src_phr_lst != null) {
                        $this->src_phr_lst->add($time_phr);
                        log_debug('formula_value->load_time_wrd_src source time word "' . $time_phr->name() . '" added');
                    }
                }
            }
        }
    }

    /**
     * update the time word object based on the time word id
     * @param bool $force_reload set to true if a loaded time phrase should be reloaded from database
     */
    private function load_time_wrd(bool $force_reload = false): void
    {
        if ($this->time_id <> 0) {
            if ($this->time_phr == null or $force_reload) {
                log_debug('formula_value->load_phr_lst for time "' . $this->time_id . '"');
                $time_phr = new phrase($this->user());
                $time_phr->set_id($this->time_id);
                $time_phr->load_by_obj_par();
                if ($time_phr->isset()) {
                    $this->time_phr = $time_phr;
                    if ($this->phr_lst != null) {
                        $this->phr_lst->add($time_phr);
                        log_debug('formula_value->load_time_wrd time word "' . $time_phr->name() . '" added');
                    }
                }
            }
        }
    }

    /**
     * update the phrase objects based on the phrase group ids
     * (usually done after loading the formula result from the database)
     */
    function load_phrases(bool $force_reload = false): void
    {
        if ($this->id > 0) {
            log_debug('for user ' . $this->user()->name);
            $this->load_phr_lst_src($force_reload);
            $this->load_phr_lst($force_reload);
            $this->load_time_wrd_src($force_reload);
            $this->load_time_wrd($force_reload);
        }
    }

    /**
     * update the formulas objects based on the id
     */
    private function load_formula(): void
    {
        if ($this->frm->id() > 0) {
            log_debug('for user ' . $this->user()->name);
            $frm = new formula($this->user());
            $frm->load_by_id($this->frm->id(), formula::class);
            $this->frm = $frm;
        }
    }


    /*
     * im- and export
     */

    /**
     * validate a formulas value by comparing the external object result with the calculated result
     *
     * @param array $json_obj an array with the data of the json object
     * @param bool $do_save can be set to false for unit testing
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_obj(array $json_obj, bool $do_save = true): user_message
    {
        log_debug();
        $result = new user_message;

        foreach ($json_obj as $key => $fv) {

            if ($key == export::WORDS) {
                $phr_lst = new phrase_list($this->user());
                $result->add($phr_lst->import_lst($fv, $do_save));
                if ($result->is_ok() and $do_save) {
                    $phr_grp = $phr_lst->get_grp();
                    log_debug('got word group ' . $phr_grp->dsp_id());
                    $this->grp = $phr_grp;
                    log_debug('set grp id to ' . $this->grp->id());
                }
                $this->phr_lst = $phr_lst;
            }

            /*
            if ($key == exp_obj::FLD_TIMESTAMP) {
                if (strtotime($fv)) {
                    $this->time_stamp = get_datetime($fv, $this->dsp_id(), 'JSON import');
                } else {
                    log_err('Cannot add timestamp "' . $fv . '" when importing ' . $this->dsp_id(), 'value->import_obj');
                }
            }
            */

            if ($key == exp_obj::FLD_NUMBER) {
                $this->value = $fv;
            }

        }

        if ($result->is_ok() and $do_save) {
            $this->save();
            log_debug($this->dsp_id());
        } else {
            log_debug($result->all_message_text());
        }

        return $result;
    }

    /**
     * create an JSON formula value object for the export
     * to enable the validation of the results during import
     *
     * @param bool $do_load true if the formula value should be validated again before export
     *                      use false for a faster export
     * @return formula_value_exp the filled formula validation object used for JSON creation
     */
    function export_obj(bool $do_load = true): formula_value_exp
    {
        log_debug();
        $result = new formula_value_exp();

        // reload the value parameters
        if ($do_load) {
            $this->load_by_id();
            log_debug(formula_value::class . '->export_obj load phrases');
            $this->load_phrases();
        }

        // add the phrases
        log_debug(formula_value::class . '->export_obj get phrases');
        $phr_lst = array();
        // TODO use either word and triple export_obj function or phrase
        if ($this->phr_lst != null) {
            if (!$this->phr_lst->is_empty()) {
                foreach ($this->phr_lst->lst() as $phr) {
                    $phr_lst[] = $phr->name();
                }
                if (count($phr_lst) > 0) {
                    $result->words = $phr_lst;
                }
            }
        }

        // add the value itself
        $result->number = $this->value;

        log_debug(json_encode($result));
        return $result;
    }

    /*
       methods to prepare the words for saving into the database
       ---------------------------------------------------------
    */

    // update the source word group id based on the word list ($this->phr_lst)
    private function save_prepare_phr_lst_src(): void
    {
        if (isset($this->src_phr_lst)) {
            // TODO check if the phrases are already loaded
            // $this->src_phr_lst->load();
            // get the word group id (and create the group if needed)
            // TODO include triples
            if (count($this->src_phr_lst->id_lst()) > 0) {
                log_debug("source group for " . $this->src_phr_lst->dsp_id() . ".");
                $grp = new phrase_group($this->user());
                $grp->load_by_lst($this->src_phr_lst);
                $this->src_phr_grp_id = $grp->get_id();
            }
            log_debug("source group id " . $this->src_phr_grp_id . " for " . $this->src_phr_lst->dsp_name() . ".");
        }
    }

    // update the word group id based on the word list ($this->phr_lst)
    private function save_prepare_phr_lst(): void
    {
        if (isset($this->phr_lst)) {
            // get the word group id (and create the group if needed)
            // TODO include triples
            $grp = new phrase_group($this->user());
            $grp->load_by_lst($this->phr_lst);
            $this->phr_grp_id = $grp->get_id();
            log_debug("group id " . $this->phr_grp_id . " for " . $this->phr_lst->dsp_name() . ".");
        }
    }

    // update the word ids based on the word objects (usually done before saving the formula result to the database)
    private function save_prepare_wrds()
    {
        log_debug();
        $this->save_prepare_phr_lst_src();
        $this->save_prepare_phr_lst();
        log_debug("done.");
    }

    /**
     * depending on the phrases format the numeric value
     * e.g. if the result phrases contains a word of type percent format the value per default as percent
     * similar to the corresponding function in the "value" class
     *
     * @returns string with the value in the most useful format for humans
     */
    function val_formatted(): string
    {
        $result = '';

        if (!is_null($this->value)) {
            log_debug('formula_value->val_formatted');
            if ($this->phr_lst == null) {
                $this->load_phrases();
                log_debug('formula_value->val_formatted loaded');
            }
            log_debug('formula_value->val_formatted check ' . $this->dsp_id());
            if ($this->phr_lst->has_percent()) {
                $result = round($this->value * 100, $this->user()->percent_decimals) . ' %';
                log_debug('formula_value->val_formatted percent of ' . $this->value);
            } else {
                if ($this->value >= 1000 or $this->value <= -1000) {
                    log_debug('formula_value->val_formatted format');
                    $result .= number_format($this->value, 0, $this->user()->dec_point, $this->user()->thousand_sep);
                } else {
                    log_debug('formula_value->val_formatted round');
                    $result = round($this->value, 2);
                }
            }
        }
        log_debug('formula_value->val_formatted done');
        return $result;
    }

    /**
     * create and return the figure object for the value
     */
    function figure(): figure
    {
        $fig = new figure($this->user());
        $fig->id = $this->id();
        $fig->set_type_result();
        $fig->number = $this->value;
        $fig->last_update = $this->last_update;
        $fig->obj = $this;

        return $fig;
    }

    /**
     * @returns array with the ids of the phrases related to this formula result
     */
    function phr_ids(): array
    {
        $id_lst = [];
        if ($this->phr_lst != null) {
            $id_lst = $this->phr_lst->id_lst();
        }
        if ($this->time_phr != null) {
            if ($this->time_phr->isset()) {
                $id_lst[] = $this->time_phr->id();
            }
        }
        return $id_lst;
    }

    /*
     *  display functions
     */

    /**
     * display the value with the unique id fields
     */
    function dsp_id(): string
    {
        $result = '';

        if ($this->value != null) {
            $result .= $this->value . ' ';
        }
        if (isset($this->phr_lst)) {
            $result .= $this->phr_lst->dsp_id();
        }
        if (isset($this->time_phr)) {
            $result .= '@' . $this->time_phr->dsp_id();
        }
        if ($result <> '') {
            $result .= ' (' . $this->id() . ')';
        } else {
            $result .= $this->id();
        }
        if ($this->user()->is_set()) {
            $result .= ' for user ' . $this->user()->id() . ' (' . $this->user()->name . ')';
        }
        return $result;
    }

    /**
     * this function is called from dsp_id, so no other call is allowed
     * @return string the best possible name for the object
     */
    function name(): string
    {
        $result = '';

        if (isset($this->phr_lst)) {
            $result .= $this->phr_lst->dsp_name();
        }
        if (isset($this->time_phr)) {
            $result .= '@' . $this->time_phr->dsp_name();
        }

        return $result;
    }

    function name_linked(): string
    {
        log_debug('formula_value->name_linked ');
        $result = '';

        if (isset($this->phr_lst)) {
            $result .= $this->phr_lst->name_linked();
        }
        if (isset($this->time_phr)) {
            $result .= '@' . $this->time_phr->name_linked();
        }

        log_debug('formula_value->name done');
        return $result;
    }

    /**
     * html code to show the value with the indication if the value is influence by the user input
     */
    function display(): string
    {
        $result = '';
        if (!is_null($this->value)) {
            $num_text = $this->val_formatted();
            if ($this->owner_id > 0) {
                $result .= '<span class="user_specific">' . $num_text . '</span>' . "\n";
            } else {
                $result .= $num_text . "\n";
            }
        }
        return $result;
    }

    /**
     * html code to show the value with the possibility to click for the result explanation
     */
    function display_linked(string $back = ''): string
    {
        $result = '';
        if (!is_null($this->value)) {
            $num_text = $this->val_formatted();
            $link_format = '';
            if ($this->owner_id > 0) {
                $link_format = ' class="user_specific"';
            }
            // TODO review
            $wrd_ids = $this->phr_ids();
            if (!empty($wrd_ids)) {
                $lead_phr_id = $wrd_ids[0];
                $result .= '<a href="/http/formula_result.php?id=' . $this->id() . '&phrase=' . $lead_phr_id . '&group=' . $this->phr_grp_id . '&back=' . $back . '"' . $link_format . '>' . $num_text . '</a>';
            }
        }
        return $result;
    }

    // explain a formula result to the user
    // create an HTML page that shows different levels of detail information for one formula result to explain to the user how the value is calculated
    function explain($lead_phr_id, $back): string
    {
        $lib = new library();
        log_debug('formula_value->explain ' . $this->dsp_id() . ' for user ' . $this->user()->name);
        $result = '';

        // display the leading word
        // $lead_wrd =
        // $lead_wrd->id()  = $lead_phr_id;
        // $lead_wrd->usr = $this->user();
        // $lead_wrd->load();
        //$result .= $lead_phr_id->name();

        // build the title
        $title = '';
        // add the words that specify the calculated value to the title
        $val_phr_lst = clone $this->phr_lst;
        $val_phr_lst->add($this->time_phr);
        $val_wrd_lst = $val_phr_lst->wrd_lst_all();
        $title .= $lib->dsp_array($val_wrd_lst->api_obj()->ex_measure_and_time_lst()->dsp_obj()->names_linked());
        $time_phr = $lib->dsp_array($val_wrd_lst->dsp_obj()->time_lst()->dsp_obj()->names_linked());
        if ($time_phr <> '') {
            $title .= ' (' . $time_phr . ')';
        }
        $title .= ': ';
        // add the value  to the title
        $title .= $this->display($back);
        $result .= dsp_text_h1($title);
        log_debug('explain the value for ' . $val_phr_lst->dsp_name() . ' based on ' . $this->src_phr_lst->dsp_name());

        // display the measure and scaling of the value
        if ($val_wrd_lst->has_percent()) {
            $result .= 'from ' . $val_wrd_lst->api_obj()->measure_scale_lst()->dsp_obj()->dsp();
        } else {
            $result .= 'in ' . $val_wrd_lst->api_obj()->measure_scale_lst()->dsp_obj()->dsp();
        }
        $result .= '</br></br>' . "\n";

        // display the formula with links
        $frm = new formula($this->user());
        $frm->load_by_id($this->frm->id(), formula::class);
        $result .= ' based on</br>' . $frm->name_linked($back);
        $result .= ' ' . $frm->dsp_text($back) . "\n";
        $result .= ' ' . $frm->btn_edit($back) . "\n";
        $result .= '</br></br>' . "\n";

        // load the formula element groups
        // each element group can contain several elements
        // e.g. for <journey time premium offset = "journey time average" / "journey time max premium" "percent">
        // <"journey time max premium" "percent"> is one element group with two elements
        // and these two elements together are use to select the value
        $exp = $frm->expression();
        //$elm_lst = $exp->element_lst ($back);
        $elm_grp_lst = $exp->element_grp_lst($back);
        log_debug("elements loaded (" . $lib->dsp_count($elm_grp_lst->lst()) . " for " . $frm->ref_text . ")");

        $result .= ' where</br>';

        // check the element consistency and if it fails, create a warning
        if (!isset($this->src_phr_lst)) {
            log_warning("Missing source words for the calculated value " . $this->dsp_id(), "formula_value->explain");
        } else {

            $elm_nbr = 0;
            foreach ($elm_grp_lst->lst() as $elm_grp) {

                // display the formula element names and create the element group object
                $result .= $elm_grp->dsp_names($back) . ' ';
                log_debug('elm grp name "' . $elm_grp->dsp_names($back) . '" with back "' . $back . '"');


                // exclude the formula word from the words used to select the formula element values
                // so reverse what has been done when saving the result
                $src_phr_lst = clone $this->src_phr_lst;
                $frm_wrd_id = $frm->name_wrd->id();
                $src_phr_lst->diff_by_ids(array($frm_wrd_id));
                log_debug('formula word "' . $frm->name_wrd->name() . '" excluded from ' . $src_phr_lst->dsp_name());

                // select or guess the element time word if needed
                log_debug('guess the time ... ');
                if ($this->src_time_id <= 0) {
                    if ($this->time_id > 0) {
                        $elm_time_phr = $this->time_phr;
                        log_debug('time ' . $this->time_phr->name() . ' taken from the result');
                    } else {
                        $elm_time_phr = $src_phr_lst->assume_time();
                        log_debug('time ' . $elm_time_phr->name() . ' assumed');
                    }
                } else {
                    $elm_time_phr = $this->src_time_phr;
                    log_debug('time ' . $elm_time_phr->name() . ' taken from the source');
                }

                $elm_grp->phr_lst = $src_phr_lst;
                $elm_grp->time_phr = $elm_time_phr;
                $elm_grp->usr = $this->user();
                log_debug('words set ' . $elm_grp->phr_lst->dsp_name() . ' taken from the source and user "' . $elm_grp->usr->name . '"');

                // finally, display the value used in the formula
                $result .= ' = ' . $elm_grp->dsp_values($this->time_phr, $back);
                $result .= '</br>';
                log_debug('next element');
                $elm_nbr++;
            }
        }

        return $result;
    }

    // update (calculate) all formula values that are depending
    // e.g. if the PE ratio for ABB, 2018 has been updated,
    //      the target price for ABB, 2018 needs to be updated if it is based on the PE ratio
    // so:  get a list of all formulas, where the formula value is used
    //      based on the frm id and the word group
    function update_depending(): array
    {
        $lib = new library();
        log_debug("(f" . $this->frm->id() . ",t" . $lib->dsp_array($this->phr_ids()) . ",tt" . $this->time_id . ",v" . $this->value . " and user " . $this->user()->name . ")");

        global $db_con;
        $result = array();

        // get depending formulas
        $frm_elm_lst = new formula_element_list($this->user());
        $frm_elm_lst->load_by_frm_and_type_id($this->frm->id(), formula_element_type::FORMULA);
        $frm_ids = array();
        foreach ($frm_elm_lst as $frm_elm) {
            if ($frm_elm->obj != null) {
                $frm_ids[] = $frm_elm->obj->id;
            }
        }
        // get formula results that may need an update (maybe include also word groups that have any word of the updated word group)
        if (!empty($frm_ids)) {
            $sql_in = $lib->sql_array($frm_ids, ' formula_id IN (', ') ');
            $sql = "SELECT formula_value_id, formula_id
                FROM formula_values 
               WHERE " . $sql_in . "
                 AND phrase_group_id = " . $this->phr_grp_id . "
                 AND user_id         = " . $this->user()->id() . ";";
            //$db_con = New mysql;
            $db_con->usr_id = $this->user()->id();
            $val_rows = $db_con->get_old($sql);
            foreach ($val_rows as $val_row) {
                $frm_ids[] = $val_row[formula::FLD_ID];
                $fv_upd = new formula_value($this->user());
                $fv_upd->load_by_id($val_row[self::FLD_ID]);
                $fv_upd->update();
                // if the value is really updated, remember the value is to check if this triggers more updates
                $result[] = $fv_upd->save();
            }
        }

        return $result;
    }

    // update the result of this formula value (without loading or saving)
    function update()
    {
        log_debug('formula_value->update ' . $this->dsp_id());
        // check parameters
        if (!isset($this->phr_lst)) {
            log_err("Phrase list is missing.", "formula_value->update");
        } elseif ($this->frm->id() <= 0) {
            log_err("Formula ID is missing.", "formula_value->update");
        } else {
            // prepare update
            $this->load_phrases();
            $this->load_formula();

            $frm = $this->frm;
            $phr_lst = $this->src_phr_lst;
            $frm->calc($phr_lst, '');

            //$this->save_if_updated ();
            log_debug('formula_value->update ' . $this->dsp_id() . ' to ' . $this->value . ' done');
        }
    }

    private function save_without_time(): string
    {
        $fv_no_time = clone $this;
        $fv_no_time->src_time_phr = null;
        $fv_no_time->time_phr = null;
        return $fv_no_time->save();
    }


    /**
     * @return bool true if a value without time is already saved
     *
     * e.g. if the user asks for the inhabitants of the city of zurich and does not specify the time
     *      the guess is that the user wants the latest reported number
     *      so this function will return false if the latest reported number is not yet saved in the database
     */
    // TODO add check
    private function has_no_time_value(): bool
    {
        $fv_check = clone $this;
        $phr_lst_ex_time = $fv_check->phr_lst;
        $phr_lst_ex_time->ex_time();
        return !$fv_check->load_by_phr_lst($phr_lst_ex_time);
    }

    // check if a single formula result needs to be saved to the database
    function save_if_updated(bool $has_result_phrases = false): bool
    {
        global $debug;
        $result = true;

        // don't save the result if some needed numbers are missing
        if ($this->val_missing) {
            log_debug('Some values are missing for ' . $this->dsp_id());
        } else {
            // save only if any parameter has been updated since last calculation
            if ($this->last_val_update <= $this->last_update) {
                if (isset($this->last_val_update) and isset($this->last_update)) {
                    log_debug($this->dsp_id() . ' not saved because the result has been calculated at ' . $this->last_update->format('Y-m-d H:i:s') . ' and after the last parameter update ' . $this->last_val_update->format('Y-m-d H:i:s'));
                } else {
                    log_debug($this->dsp_id() . ' not saved because the result has been calculated after the last parameter update ');
                }
                //zu_debug('formula_value->save_if_updated -> save '.$this->dsp_id().' not saved because the result has been calculated at '.$this->last_update.' which is after the last parameter update at '.$this->last_update);
            } else {
                if (isset($this->last_val_update) and isset($this->last_update)) {
                    log_debug('save ' . $this->dsp_id() . ' because parameters have been updated at ' . $this->last_val_update->format('Y-m-d H:i:s') . ' and the formula result update is from ' . $this->last_update->format('Y-m-d H:i:s'));
                } else {
                    if (isset($this->last_val_update)) {
                        log_debug('save ' . $this->dsp_id() . ' and result update time is set to ' . $this->last_val_update->format('Y-m-d H:i:s'));
                        $this->last_update = $this->last_val_update;
                    } else {
                        log_debug('save ' . $this->dsp_id() . ' but times are missing');
                    }
                }
                // check the formula result consistency
                if (!isset($this->phr_lst)) {
                    log_warning('The result phrases for ' . $this->dsp_id() . ' are missing.', 'formula_value->save_if_updated');
                }
                if (!isset($this->src_phr_lst)) {
                    log_warning('The source phrases for ' . $this->dsp_id() . ' are missing.', 'formula_value->save_if_updated');
                }

                // add the formula name word, but not is the result words are defined in the formula
                // e.g. if the formula "country weight" is calculated the word "country weight" should be added to the result values
                if (!$has_result_phrases) {
                    log_debug('add the formula name ' . $this->frm->dsp_id() . ' to the result phrases ' . $this->phr_lst->dsp_id());
                    if ($this->frm != null) {
                        if ($this->frm->name_wrd != null) {
                            $this->phr_lst->add($this->frm->name_wrd->phrase());
                        }
                    }
                }

                // e.g. if the formula is a division and the values used have a measure word like meter or CHF, the result is only in percent, but not in meter or CHF
                // simplified version, that needs to be review to handle more complex formulas
                if (strpos($this->frm->ref_text_r, expression::DIV) !== false) {
                    log_debug('check measure ' . $this->phr_lst->dsp_id());
                    if ($this->phr_lst->has_measure()) {
                        $this->phr_lst->ex_measure();
                        log_debug('measure removed from words ' . $this->phr_lst->dsp_id());
                    }
                }

                // build the formula result object
                //$this->frm_id = $this->frm->id();
                //$this->user()->id() = $frm_result->result_user;
                log_debug('save "' . $this->value . '" for ' . $this->phr_lst->dsp_id());

                // get the default time for the words e.g. if the increase for ABB sales is calculated the last reported sales increase is assumed
                $lst_ex_time = $this->phr_lst->wrd_lst_all();
                $lst_ex_time->ex_time();
                $fv_default_time = $lst_ex_time->assume_time(); // must be the same function called used in 2num
                if (isset($fv_default_time)) {
                    log_debug('save "' . $this->value . '" for ' . $this->phr_lst->dsp_id() . ' and default time ' . $fv_default_time->dsp_id());
                } else {
                    log_debug('save "' . $this->value . '" for ' . $this->phr_lst->dsp_id());
                }

                if (!isset($this->value)) {
                    log_info('No result calculated for "' . $this->frm->name() . '" based on ' . $this->src_phr_lst->dsp_id() . ' for user ' . $this->user()->id() . '.', "formula_value->save_if_updated");
                } else {
                    // save the default value if the result time is the "newest"
                    if (isset($fv_default_time)) {
                        log_debug('check if result time ' . $this->time_phr->dsp_id() . ' is the default time ' . $fv_default_time->dsp_id());
                        if ($this->time_phr->id() == $fv_default_time->id()) {
                            // if there is not yet a general value for all user, save it now
                            $result .= $this->save_without_time();
                        }
                    }

                    // save the value without time if no value without time is yet saved for the phrase group
                    if ($this->has_no_time_value()) {
                        $result .= $this->save_without_time();
                    }

                    // save the result
                    $fv_id = $this->save();

                    if ($debug > 0) {
                        $debug_txt = 'result = ' . $this->value . ' saved for ' . $this->phr_lst->name_linked();
                        if ($debug > 3) {
                            $debug_txt .= ' (group id "' . $this->phr_grp_id . '" and the result time is ' . $this->time_phr->name_linked() . ') as id "' . $fv_id . '" based on ' . $this->src_phr_lst->name_linked() . ' (group id "' . $this->src_phr_grp_id . '" and the result time is ' . $this->src_time_phr->name_linked() . ')';
                        }
                        if (!$this->is_std) {
                            $debug_txt .= ' for user "' . $this->user()->name . '"';
                        }
                        log_debug($debug_txt . '');
                    }
                }
            }
        }
        return $result;
    }

    /**
     * save the formula result to the database
     * for the word selection the id list is the lead, not the object list and not the group
     * @return int the id of the saved record
     */
    function save(): int
    {

        global $db_con;
        global $debug;
        $result = 0;

        // check the parameters e.g. a result must always be linked to a formula
        if ($this->frm->id() <= 0) {
            log_err("Formula id missing.", "formula_value->save");
        } elseif (empty($this->phr_lst)) {
            log_err("No words for the result.", "formula_value->save");
        } elseif (empty($this->src_phr_lst)) {
            log_err("No words for the calculation.", "formula_value->save");
        } elseif (!$this->user()->is_set()) {
            log_err("User missing.", "formula_value->save");
        } else {
            if ($debug > 0) {
                $debug_txt = 'formula_value->save (' . $this->value . ' for formula ' . $this->frm->id() . ' with ' . $this->phr_lst->dsp_name() . ' based on ' . $this->src_phr_lst->dsp_name();
                if (!$this->is_std) {
                    $debug_txt .= ' and user ' . $this->user()->id();
                }
                $debug_txt .= ')';
                log_debug($debug_txt);
            }

            // build the database object because the is anyway needed
            //$db_con = new mysql;
            $db_con->set_usr($this->user()->id);
            $db_con->set_type(sql_db::TBL_FORMULA_VALUE);

            // build the word list if needed to separate the time word from the word list
            $this->save_prepare_wrds();
            log_debug("group id " . $this->phr_grp_id . " and source group id " . $this->src_phr_grp_id);

            // to check if a database update is needed to create a second fv object with the database values
            $fv_db = clone $this;
            $fv_db->load_obj_vars();
            $row_id = $fv_db->id;
            $db_val = $fv_db->value;

            // if value exists, check it an update is needed
            if ($row_id > 0) {
                if ($db_con->sf($db_val) <> $db_con->sf($this->value)) {
                    $db_con->set_type(sql_db::TBL_FORMULA_VALUE);
                    if ($db_con->update($row_id,
                        array(formula_value::FLD_VALUE, formula_value::FLD_LAST_UPDATE),
                        array($this->value, 'Now()'))) {
                        $this->id = $row_id;
                        $result = $row_id;
                    }
                    log_debug("update (" . $db_val . " to " . $this->value . " for " . $row_id . ")");
                } else {
                    $this->id = $row_id;
                    $result = $row_id;
                    log_debug("not update (" . $db_val . " to " . $this->value . " for " . $row_id . ")");
                }
            } else {
                $field_names = array();
                $field_values = array();
                $field_names[] = formula::FLD_ID;
                $field_values[] = $this->frm->id();
                $field_names[] = formula_value::FLD_VALUE;
                $field_values[] = $this->value;
                $field_names[] = formula_value::FLD_GRP;
                $field_values[] = $this->phr_grp_id;
                $field_names[] = formula_value::FLD_SOURCE_GRP;
                $field_values[] = $this->src_phr_grp_id;
                $field_names[] = formula_value::FLD_SOURCE_TIME;
                $field_values[] = $this->src_time_id;
                if (!$this->is_std) {
                    $field_names[] = user_sandbox::FLD_USER;
                    $field_values[] = $this->user()->id();
                }
                $field_names[] = formula_value::FLD_LAST_UPDATE;
                //$field_values[] = 'Now()'; // replaced with time of last change that has been included in the calculation
                $field_values[] = $this->last_val_update->format('Y-m-d H:i:s');
                $db_con->set_type(sql_db::TBL_FORMULA_VALUE);
                $id = $db_con->insert($field_names, $field_values);
                $this->id = $id;
                $result = $id;
            }
        }

        log_debug("id (" . $result . ")");
        return $result;

    }
}