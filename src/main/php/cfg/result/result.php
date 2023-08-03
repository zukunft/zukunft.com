<?php

/*

    model/formula/result.php - the calculated numeric result of a formula
    -------------------------------

    TODO: add these function
    TODO rename to result

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

namespace cfg;

include_once DB_PATH . 'sql_par_type.php';
include_once SERVICE_EXPORT_PATH . 'result_exp.php';

use api\result_api;
use cfg\db\sql_creator;
use cfg\db\sql_par_type;
use model\export\exp_obj;
use model\export\result_exp;
use DateTime;
use html\html_base;
use html\formula\formula as formula_dsp;
use im_export\export;

class result extends sandbox_value
{

    /*
     * database link
     */

    // database fields only used for results
    const FLD_ID = 'result_id';
    const FLD_SOURCE_GRP = 'source_phrase_group_id';
    const FLD_GRP = 'phrase_group_id';
    const FLD_VALUE = 'result';
    const FLD_LAST_UPDATE = 'last_update';
    const FLD_DIRTY = 'dirty';

    // all database field names used
    const FLD_NAMES = array(
        formula::FLD_ID,
        user::FLD_ID,
        self::FLD_SOURCE_GRP,
        self::FLD_GRP,
        self::FLD_VALUE,
        self::FLD_LAST_UPDATE,
        self::FLD_DIRTY
    );


    /*
     * object vars
     */

    // database fields
    public ?float $value = null;               // ... and finally the numeric value
    public ?bool $is_std = True;               // true as long as no user specific value, formula or assignment is used for this result
    // objects directly linked to database fields
    public formula $frm;                       // the formula object used to calculate this result
    private phrase_group $grp;                 // the phrase group of the result
    public ?phrase_group $src_grp = null;      // the phrase group used for calculating the result

    // to deprecate
    public ?int $time_id = null;               // the result time word id as saved in the database, which can differ from the source time
    public ?DateTime $last_update = null;      // ... and the time of the last update; all updates up to this time are included in this result
    public ?bool $dirty = null;                // true as long as an update is pending


    // in memory only fields (all methods except load and save should use the wrd_lst object not the ids and not the group id)
    public ?phrase $src_time_phr = null;       // the time word object created while loading
    public ?phrase_list $phr_lst = null;       // the phrase list obj (not a list of phrase objects) filled while loading
    public ?phrase $time_phr = null;           // the time word object created while loading
    public ?bool $val_missing = False;         // true if at least one of the results is not set which means is NULL (but zero is a value)
    public ?bool $is_updated = False;          // true if the result has been calculated, but not yet saved
    public ?string $ref_text = null;           // the formula text in the database reference format on which the result is based
    public ?string $num_text = null;           // the formula text filled with numbers used for the result calculation
    public ?DateTime $last_val_update = null;  // the time of the last update of an underlying value, formula result or formula
    //                                            if this is later than the last update the result needs to be updated
    private string $symbol = '';               // the symbol of the related formula element


    /*
     * construct and map
     */

    function __construct(user $usr)
    {
        parent::__construct($usr);
        $this->last_update = new DateTime();
        $this->last_val_update = new DateTime();
        $this->reset();
    }

    function reset(): void
    {
        $this->set_id(0);
        $this->frm = new formula($this->user());
        $this->grp = new phrase_group($this->user());
        $this->src_grp = new phrase_group($this->user());
    }

    /**
     * map the database fields to the object fields
     *
     * @param array|null $db_row with the data directly from the database
     * @param string $id_fld the name of the id field as set in the child class
     * @return bool true if a result has been loaded and is valid
     */
    function row_mapper(?array $db_row, string $id_fld = ''): bool
    {
        $lib = new library();
        $result = parent::row_mapper($db_row, self::FLD_ID);
        if ($result) {
            $this->frm->set_id($db_row[formula::FLD_ID]);
            $this->grp->set_id($db_row[self::FLD_GRP]);
            $this->src_grp->set_id($db_row[self::FLD_SOURCE_GRP]);
            $this->value = $db_row[self::FLD_VALUE];
            $this->owner_id = $db_row[user::FLD_ID];
            $this->last_update = $lib->get_datetime($db_row[self::FLD_LAST_UPDATE]);
            $this->last_val_update = $lib->get_datetime($db_row[self::FLD_LAST_UPDATE]);
            $this->dirty = $db_row[self::FLD_DIRTY];

            $this->load_phrases(true);
        }

        return $result;
    }


    /*
     * set and get
     */

    function set_grp(phrase_group $grp): void
    {
        $this->grp = $grp;
    }

    function grp(): phrase_group
    {
        return $this->grp;
    }

    function set_symbol(string $symbol): void
    {
        $this->symbol = $symbol;
    }

    function symbol(): string
    {
        return $this->symbol;
    }

    function is_std(): bool
    {
        return $this->is_std;
    }

    function number(): float
    {
        return $this->value;
    }

    function last_update(): DateTime
    {
        return $this->last_update;
    }

    /*
     * reduce code line length
     */

    /**
     * @return phrase_list the phrase list of this value from the phrase group
     */
    function phr_lst(): phrase_list
    {
        return $this->grp->phr_lst;
    }

    /**
     * @return array with the phrase names of this value from the phrase group
     */
    function phr_names(): array
    {
        return $this->grp->phr_lst->names();
    }


    /*
     * cast
     */

    /**
     * @return result_api the formula result frontend api object
     */
    function api_obj(bool $do_save = true): object
    {
        $api_obj = new result_api($this->id);
        $api_obj->set_number($this->value);
        if ($this->phr_lst != null) {
            $grp = $this->phr_lst->get_grp($do_save);
            $api_obj->set_grp($grp->api_obj());
        }
        return $api_obj;
    }

    /**
     * @returns string the api json message for the object as a string
     */
    function api_json(bool $do_save = true): string
    {
        return $this->api_obj($do_save)->get_json();
    }


    /*
     * load
     */

    /**
     * create the SQL to load the single default result always by the id
     * @param sql_creator $sc with the target db_type set
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_standard_sql(sql_creator $sc, string $class = self::class): sql_par
    {
        $sc->set_type(sql_db::TBL_RESULT);
        $sc->set_fields(array_merge(self::FLD_NAMES, array(user::FLD_ID)));

        return parent::load_standard_sql($sc, $class);
    }

    /**
     * create the SQL to load a results
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the unique name of the query e.g. id or name
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name, string $class = self::class): sql_par
    {
        $qp = parent::load_sql($sc, $query_name, $class);

        $sc->set_type(sql_db::TBL_RESULT);
        $sc->set_name($qp->name);
        $sc->set_usr($this->user()->id);
        $sc->set_fields(self::FLD_NAMES);

        return $qp;
    }

    /**
     * create the SQL to load a results by the id
     *
     * @param sql_creator $sc with the target db_type set
     * @param int $id the id of the result
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_id(sql_creator $sc, int $id, string $class = self::class): sql_par
    {
        return parent::load_sql_by_id($sc, $id, $class);
    }

    /**
     * prepare the query parameter to load a results by phrase group id
     *
     * @param sql_creator $sc with the target db_type set
     * @param phrase_group $grp the group used for the selection
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    private function load_sql_by_grp_prepare(sql_creator $sc, phrase_group $grp): sql_par
    {
        $qp = $this->load_sql($sc, 'grp');
        $sc->set_name($qp->name);
        $sc->add_where(self::FLD_GRP, $grp->id());
        return $qp;
    }

    /**
     * create the SQL to load a results by phrase group id and time phrase
     *
     * @param sql_creator $sc with the target db_type set
     * @param phrase_group $grp the group used for the selection
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_grp_time(sql_creator $sc, phrase_group $grp, int $time_phr_id = null): sql_par
    {
        $qp = $this->load_sql_by_grp_prepare($sc, $grp);
        $qp->name .= '_time';
        $sc->set_name($qp->name);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * create the SQL to load a results by phrase group id
     *
     * @param sql_creator $sc with the target db_type set
     * @param phrase_group $grp the group used for the selection
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_grp(sql_creator $sc, phrase_group $grp): sql_par
    {
        $qp = $this->load_sql($sc, 'grp');
        $sc->set_name($qp->name);
        $sc->add_where(self::FLD_GRP, $grp->id());
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * create the SQL to load a results by formula id and phrase group id
     *
     * @param sql_creator $sc with the target db_type set
     * @param formula $frm the formula used for the selection
     * @param phrase_group $grp the group used for the selection
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_frm_grp(sql_creator $sc, formula $frm, phrase_group $grp): sql_par
    {
        $qp = $this->load_sql($sc, 'frm_grp');
        $sc->set_name($qp->name);
        $sc->add_where(formula::FLD_ID, $frm->id());
        $sc->add_where(self::FLD_GRP, $grp->id());
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * create the SQL to load a results by formula id and phrase group id
     *
     * @param sql_creator $sc with the target db_type set
     * @param formula $frm the formula used for the selection
     * @param phrase_group_list $lst the group used for the selection
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_frm_grp_lst(sql_creator $sc, formula $frm, phrase_group_list $lst): sql_par
    {
        $qp = $this->load_sql($sc, 'frm_grp_lst');
        $sc->set_name($qp->name);
        $sc->add_where(formula::FLD_ID, $frm->id());
        $sc->add_where(self::FLD_GRP, $lst->ids());
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * create an SQL statement to retrieve the user changes of the current result
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_user_changes(sql_creator $sc, string $class = self::class): sql_par
    {
        $sc->set_type(sql_db::TBL_RESULT, true);
        return parent::load_sql_user_changes($sc, $class);
    }

    /**
     * load (or force reload from database of) a result by the id
     *
     * @param int $id the unique database id of the result that should be loaded
     * @param string $class always the result class just to be compatible with the parent function
     * @return int true if result has been loaded
     */
    function load_by_id(int $id = 0, string $class = self::class): int
    {
        global $db_con;
        $result = 0;

        if ($id > 0) {
            // if the id is given load the result from the database
            $res_usr = $this->user();
            $this->reset();
            $this->set_user($res_usr);
            $this->id = $id;
        } else {
            // if the id is not given, refresh the object based pn the database
            if ($this->id > 0) {
                $id = $this->id;
                $res_usr = $this->user();
                $this->reset();
                $this->set_user($res_usr);
                $this->id = $id;
            } else {
                log_err('The result id and the user must be set ' .
                    'to load a ' . self::class, self::class . '->load_by_id');
            }
        }
        $qp = $this->load_sql_by_id($db_con->sql_creator(), $id);
        if ($qp->name != '') {
            $db_row = $db_con->get1($qp);
            $this->row_mapper($db_row);
            $result = $this->id();
        }

        return $result;
    }

    /**
     * load all a result by the phrase group id and time phrase
     *
     * @param phrase_group $grp to select the result
     * @return bool true if result has been loaded
     */
    function load_by_grp(phrase_group $grp, ?int $time_phr_id = null): bool
    {
        global $db_con;
        $result = false;

        if ($grp->id() <= 0) {
            log_err('The result phrase group id and the user must be set ' .
                'to load a ' . self::class, self::class . '->load_by_grp');
        } else {
            $res_usr = $this->user();
            $this->reset();
            $this->set_user($res_usr);
            if ($time_phr_id != null) {
                $this->time_id = $time_phr_id;
                $qp = $this->load_sql_by_grp_time($db_con->sql_creator(), $grp, $time_phr_id);
            } else {
                $qp = $this->load_sql_by_grp($db_con->sql_creator(), $grp);
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
     * load all a result by the phrase group id and time phrase
     *
     * @param formula $frm to select the result
     * @param phrase_group $grp to select the result
     * @return bool true if result has been loaded
     */
    function load_by_formula_and_group(formula $frm, phrase_group $grp): bool
    {
        global $db_con;
        $result = false;

        if ($frm->id() <= 0) {
            log_err('The formula id must be set to load a ' . self::class);
        } elseif ($grp->id() <= 0) {
            log_err('The phrase group id must be set to load a ' . self::class);
        } else {
            $res_usr = $this->user();
            $this->reset();
            $this->set_user($res_usr);
            $qp = $this->load_sql_by_frm_grp($db_con->sql_creator(), $frm, $grp);
            if ($qp->name != '') {
                $db_row = $db_con->get1($qp);
                $this->row_mapper($db_row);
                $result = true;
            }
        }

        return $result;
    }

    /**
     * load all a result by the phrase group id and time phrase
     *
     * @param formula $frm to select the result
     * @param phrase_group_list $lst the group used for the selection
     * @return bool true if result has been loaded
     */
    function load_by_formula_and_group_list(formula $frm, phrase_group_list $lst): bool
    {
        global $db_con;
        $result = false;

        if ($frm->id() <= 0) {
            log_err('The formula id must be set to load a ' . self::class);
        } else {
            $res_usr = $this->user();
            $this->reset();
            $this->set_user($res_usr);
            $qp = $this->load_sql_by_frm_grp_lst($db_con->sql_creator(), $frm, $lst);
            if ($qp->name != '') {
                $db_row = $db_con->get1($qp);
                $this->row_mapper($db_row);
                $result = true;
            }
        }

        return $result;
    }

    /**
     * load all a result by a give phrase list and if set the time phrase
     *
     * @return bool true if result has been loaded
     */
    function load_by_phr_lst(phrase_list $phr_lst, ?int $time_phr_id = null): bool
    {
        $result = false;

        if ($phr_lst->is_valid()) {
            $res_usr = $this->user();
            $this->reset();
            $this->set_user($res_usr);
            $grp = $phr_lst->get_grp();
            $result = $this->load_by_grp($grp, $time_phr_id);
        } else {
            log_err('The result phrase list and the user must be set ' .
                'to load a ' . self::class, self::class . '->load_by_phr_lst');
        }

        return $result;
    }

    /**
     * create the SQL to load a results by a given where statement
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
     * set the SQL query parameters to load a result
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_obj_vars_sql(sql_db $db_con): sql_par
    {
        $sql_where = "";
        if ($this->id > 0) {
            $qp = $this->load_sql_by_id($db_con->sql_creator(), $this->id);
        } else {
            // prepare the search by getting the word group based on the word list
            log_debug('not by id');

            // set the result group id if the result list is set, but not the group id
            $phr_grp = null;
            if ($this->grp->id() <= 0) {
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
                }
                if (isset($phr_lst)) {
                    $this->phr_lst = $phr_lst;
                    log_debug('get group for ' . $phr_lst->dsp_name() . ' (including formula name)');
                    $phr_grp = $phr_lst->get_grp();
                    if (isset($phr_grp)) {
                        if ($phr_grp->id() > 0) {
                            $this->grp = $phr_grp;
                        }
                    }
                }
            }
            if ($this->grp->id() <= 0) {
                log_debug('group not found!');
            }

            $db_con->set_type(sql_db::TBL_RESULT);
            $qp = new sql_par(self::class);
            $qp->name = 'res_by_';

            // set the source group id if the source list is set, but not the group id
            if ($this->src_grp->id() <= 0 and $this->src_grp->phr_lst != null) {

                if (!$this->src_grp->phr_lst->is_empty()) {
                    $phr_grp = $this->src_grp->phr_lst->get_grp();
                    if (isset($phr_grp)) {
                        if ($phr_grp->id() > 0) {
                            $this->src_grp->set_id($phr_grp->id());
                        }
                    }
                    log_debug('source group ' . $this->src_grp->dsp_id() . ' found for ' . $this->src_grp->phr_lst->dsp_name());
                }
            }

            $sql_order = '';
            // include the source words in the search if requested
            if ($this->src_grp->id() > 0 and $this->user()->id() > 0) {
                $qp->name .= '_usr_src_phr_grp';
                $db_con->add_par(sql_par_type::INT, $this->src_grp->id());
                $db_con->add_par(sql_par_type::INT, $this->user()->id);
                if ($sql_where != '') {
                    $sql_where .= ' AND ';
                }
                $sql_where .= " source_phrase_group_id = " . $db_con->par_name() . "
                           AND (user_id = " . $db_con->par_name() . " OR user_id = 0 OR user_id IS NULL) ";
                $sql_order = " ORDER BY user_id DESC";
            } else {
                $qp->name .= '_src_phr_grp';
                if ($this->src_grp->id() > 0) {
                    $db_con->add_par(sql_par_type::INT, $this->src_grp->id());
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
            if ($this->grp->id() > 0 and $this->user()->id() > 0) {
                $qp->name .= '_usr_phr_grp';
                $db_con->add_par(sql_par_type::INT, $this->grp->id());
                $db_con->add_par(sql_par_type::INT, $this->user()->id);
                if ($sql_where != '') {
                    $sql_where .= ' AND ';
                }
                $sql_where .= " phrase_group_id = " . $db_con->par_name() . "
                          AND (user_id = " . $db_con->par_name() . " OR user_id = 0 OR user_id IS NULL)";
                $sql_order = " ORDER BY user_id DESC";
            } else {
                if ($this->grp->id() > 0) {
                    $qp->name .= '_phr_grp';
                    $db_con->add_par(sql_par_type::INT, $this->grp->id());
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
                $db_con->add_par(sql_par_type::INT, $this->frm->id());
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
        if ($this->user() == null) {
            log_err("The user id must be set to load a result.", "result->load");
        } else {

            // prepare the selection of the result
            $qp = $this->load_obj_vars_sql($db_con);

            // check if a valid identification is given and load the result
            if (!$qp->has_par()) {
                log_err("Either the database ID (" . $this->id() . ") or the source or result words or word group and the user (" . $this->user()->id() . ") must be set to load a result.", "result->load");
            } else {
                $result = $this->load_rec($qp);

                // if no general value can be found, test if a more specific value can be found in the database
                // e.g. if ABB,Sales,2014 is requested, but there is only a value for ABB,Sales,2014,CHF,million get it
                // similar to the selection in value->load: maybe combine?
                log_debug('check best guess');
                if ($this->id() <= 0) {
                    if (!isset($phr_lst)) {
                        log_debug('no result found for ' . $qp->sql . ', but phrase list is also not set');
                    } else {
                        log_debug('try best guess');
                        if (count($phr_lst->lst) > 0) {
                            // the phrase groups with the least number of additional words that have at least one result
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
                                $db_con->add_par(sql_par_type::INT, $phr->id());
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
                            $sql_val = "SELECT result_id 
                            FROM results
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
                                        $qp->name .= '_guess_res_id';
                                        $db_con->add_par(sql_par_type::INT, $this->id());
                                        $sql_where = "result_id = " . $db_con->par_name();
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


    /*
     * phrase loading methods
     */

    /**
     * update the source phrase list based on the source phrase group id
     * @param bool $force_reload set to true if a loaded phrase list should refresh with database values
     */
    private function load_phr_lst_src(bool $force_reload = false): void
    {
        if ($this->src_grp->id() > 0) {
            if ($this->src_grp->phr_lst == null or $force_reload) {
                log_debug('for source group "' . $this->src_grp->id() . '"');
                $phr_grp = new phrase_group($this->user());
                $phr_grp->load_by_id($this->src_grp->id());
                if (!$phr_grp->phr_lst->empty()) {
                    $this->src_grp->phr_lst = $phr_grp->phr_lst;
                    log_debug('source phrases ' . $this->src_grp->phr_lst->dsp_name() . ' loaded');
                } else {
                    log_debug('no source words found for ' . $this->dsp_id());
                }
            }
        }
        if ($this->src_grp->phr_lst != null) {
            if ($this->src_grp->phr_lst->empty()) {
                log_warning("Missing source words for the calculated value " . $this->id() . ' (group id ' . $this->src_grp->dsp_id() . ').', "result->load_phr_lst_src");
            }
        } else {
            log_warning("Missing source words for the calculated value " . $this->id() . ' (group id ' . $this->src_grp->dsp_id() . ').', "result->load_phr_lst_src");
        }
    }

    /**
     * update the phrase list based on the word group id
     * @param bool $force_reload set to true if a loaded phrase list should refresh with database values
     */
    private function load_phr_lst(bool $force_reload = false): void
    {
        if ($this->grp->id() > 0) {
            if ($this->phr_lst == null or $force_reload) {
                log_debug('for group "' . $this->grp->id() . '"');
                $phr_grp = new phrase_group($this->user());
                $phr_grp->load_by_id($this->grp->id());
                if (!$phr_grp->phr_lst->empty()) {
                    $this->phr_lst = $phr_grp->phr_lst;
                    log_debug('phrases ' . $this->phr_lst->dsp_name() . ' loaded');
                } else {
                    log_debug('no result phrases found for ' . $this->dsp_id());
                }
            }
        }
        if ($this->phr_lst != null) {
            if ($this->phr_lst->empty()) {
                log_warning("Missing result phrases for the calculated value " . $this->id(), "result->load_phr_lst");
            }
        } else {
            log_warning("Missing result phrases for the calculated value " . $this->id(), "result->load_phr_lst");
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
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_obj(array $json_obj, object $test_obj = null): user_message
    {
        log_debug();
        $result = parent::import_db_obj($this, $test_obj);

        if ($test_obj) {
            $do_save = false;
        } else {
            $do_save = true;
        }

        foreach ($json_obj as $key => $res) {

            if ($key == export::WORDS) {
                $phr_lst = new phrase_list($this->user());
                $result->add($phr_lst->import_lst($res, $test_obj));
                if ($result->is_ok()) {
                    $phr_grp = $phr_lst->get_grp($do_save);
                    log_debug('got word group ' . $phr_grp->dsp_id());
                    $this->set_grp($phr_grp);
                    log_debug('set grp id to ' . $this->grp->id());
                }
                $this->phr_lst = $phr_lst;
            }

            /*
            if ($key == exp_obj::FLD_TIMESTAMP) {
                if (strtotime($res)) {
                    $this->time_stamp = get_datetime($res, $this->dsp_id(), 'JSON import');
                } else {
                    log_err('Cannot add timestamp "' . $res . '" when importing ' . $this->dsp_id(), 'value->import_obj');
                }
            }
            */

            if ($key == exp_obj::FLD_NUMBER) {
                $this->value = $res;
            }

        }

        // save the result in the database
        if (!$test_obj) {
            if ($result->is_ok()) {
                $this->save();
                log_debug($this->dsp_id());
            } else {
                log_debug($result->all_message_text());
            }
        }

        return $result;
    }

    /**
     * create an JSON result object for the export
     * to enable the validation of the results during import
     *
     * @param bool $do_load true if the result should be validated again before export
     *                      use false for a faster export
     * @return result_exp the filled formula validation object used for JSON creation
     */
    function export_obj(bool $do_load = true): result_exp
    {
        log_debug();
        $result = new result_exp();

        // reload the value parameters
        if ($do_load) {
            $this->load_by_id();
            log_debug(result::class . '->export_obj load phrases');
            $this->load_phrases();
        }

        // add the phrases
        log_debug(result::class . '->export_obj get phrases');
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
        if (isset($this->src_grp->phr_lst)) {
            // TODO check if the phrases are already loaded
            // $this->src_grp->phr_lst->load();
            // get the word group id (and create the group if needed)
            // TODO include triples
            if (count($this->src_grp->phr_lst->id_lst()) > 0) {
                log_debug("source group for " . $this->src_grp->phr_lst->dsp_id() . ".");
                $grp = new phrase_group($this->user());
                $grp->load_by_lst($this->src_grp->phr_lst);
                $this->src_grp->set_id($grp->get_id());
            }
            log_debug("source group id " . $this->src_grp->dsp_id() . " for " . $this->src_grp->phr_lst->dsp_name() . ".");
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
            $this->grp->set_id($grp->get_id());
            log_debug("group id " . $this->grp->id() . " for " . $this->phr_lst->dsp_name() . ".");
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
            log_debug('result->val_formatted');
            if ($this->phr_lst == null) {
                $this->load_phrases();
                log_debug('result->val_formatted loaded');
            }
            log_debug('result->val_formatted check ' . $this->dsp_id());
            if ($this->phr_lst->has_percent()) {
                $result = round($this->value * 100, $this->user()->percent_decimals) . ' %';
                log_debug('result->val_formatted percent of ' . $this->value);
            } else {
                if ($this->value >= 1000 or $this->value <= -1000) {
                    log_debug('result->val_formatted format');
                    $result .= number_format($this->value, 0, $this->user()->dec_point, $this->user()->thousand_sep);
                } else {
                    log_debug('result->val_formatted round');
                    $result = round($this->value, 2);
                }
            }
        }
        log_debug('result->val_formatted done');
        return $result;
    }

    /**
     * create and return the figure object for the value
     */
    function figure(): figure
    {
        return new figure($this);
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
        if ($this->user() != null) {
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
        log_debug('result->name_linked ');
        $result = '';

        if (isset($this->phr_lst)) {
            $result .= $this->phr_lst->name_linked();
        }
        if (isset($this->time_phr)) {
            $result .= '@' . $this->time_phr->name_linked();
        }

        log_debug('result->name done');
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
                $result .= '<a href="/http/formula_result.php?id=' . $this->id() . '&phrase=' . $lead_phr_id . '&group=' . $this->grp->id() . '&back=' . $back . '"' . $link_format . '>' . $num_text . '</a>';
            }
        }
        return $result;
    }

    // explain a formula result to the user
    // create an HTML page that shows different levels of detail information for one formula result to explain to the user how the value is calculated
    function explain($lead_phr_id, $back): string
    {
        $lib = new library();
        $html = new html_base();
        log_debug('result->explain ' . $this->dsp_id() . ' for user ' . $this->user()->name);
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
        $result .= $html->dsp_text_h1($title);
        log_debug('explain the value for ' . $val_phr_lst->dsp_name() . ' based on ' . $this->src_grp->phr_lst->dsp_name());

        // display the measure and scaling of the value
        if ($val_wrd_lst->has_percent()) {
            $result .= 'from ' . $val_wrd_lst->api_obj()->measure_scale_lst()->dsp_obj()->display();
        } else {
            $result .= 'in ' . $val_wrd_lst->api_obj()->measure_scale_lst()->dsp_obj()->display();
        }
        $result .= '</br></br>' . "\n";

        // display the formula with links
        $frm = new formula($this->user());
        $frm->load_by_id($this->frm->id(), formula::class);
        $frm_html = new formula_dsp($frm->api_json());
        $result .= ' based on</br>' . $frm_html->display_linked($back);
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
        if (!isset($this->src_grp->phr_lst)) {
            log_warning("Missing source words for the calculated value " . $this->dsp_id(), "result->explain");
        } else {

            $elm_nbr = 0;
            foreach ($elm_grp_lst->lst() as $elm_grp) {

                // display the formula element names and create the element group object
                $result .= $elm_grp->dsp_names($back) . ' ';
                log_debug('elm grp name "' . $elm_grp->dsp_names($back) . '" with back "' . $back . '"');


                // exclude the formula word from the words used to select the formula element values
                // so reverse what has been done when saving the result
                $src_grp->phr_lst = clone $this->src_grp->phr_lst;
                $frm_wrd_id = $frm->name_wrd->id();
                $src_grp->phr_lst->diff_by_ids(array($frm_wrd_id));
                log_debug('formula word "' . $frm->name_wrd->name() . '" excluded from ' . $src_grp->phr_lst->dsp_name());

                // select or guess the element time word if needed
                log_debug('guess the time ... ');
                if ($this->time_id > 0) {
                    $elm_time_phr = $this->time_phr;
                    log_debug('time ' . $this->time_phr->name() . ' taken from the result');
                } else {
                    $elm_time_phr = $src_grp->phr_lst->assume_time();
                    log_debug('time ' . $elm_time_phr->name() . ' assumed');
                }

                $elm_grp->phr_lst = $src_grp->phr_lst;
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

    // update (calculate) all results that are depending
    // e.g. if the PE ratio for ABB, 2018 has been updated,
    //      the target price for ABB, 2018 needs to be updated if it is based on the PE ratio
    // so:  get a list of all formulas, where the result is used
    //      based on the frm id and the word group
    function update_depending(): array
    {
        $lib = new library();
        log_debug("(f" . $this->frm->id() . ",t" . $lib->dsp_array($this->phr_ids()) . ",tt" . $this->time_id . ",v" . $this->value . " and user " . $this->user()->name . ")");

        global $db_con;
        $result = array();

        // get depending formulas
        $frm_elm_lst = new formula_element_list($this->user());
        $frm_elm_lst->load_by_frm_and_type_id($this->frm->id(), parameter_type::FORMULA_ID);
        $frm_ids = array();
        foreach ($frm_elm_lst as $frm_elm) {
            if ($frm_elm->obj != null) {
                $frm_ids[] = $frm_elm->obj->id;
            }
        }
        // get formula results that may need an update (maybe include also word groups that have any word of the updated word group)
        if (!empty($frm_ids)) {
            $sql_in = $lib->sql_array($frm_ids, ' formula_id IN (', ') ');
            $sql = "SELECT result_id, formula_id
                FROM results 
               WHERE " . $sql_in . "
                 AND phrase_group_id = " . $this->grp->id() . "
                 AND user_id         = " . $this->user()->id() . ";";
            //$db_con = New mysql;
            $db_con->usr_id = $this->user()->id();
            $val_rows = $db_con->get_old($sql);
            foreach ($val_rows as $val_row) {
                $frm_ids[] = $val_row[formula::FLD_ID];
                $res_upd = new result($this->user());
                $res_upd->load_by_id($val_row[self::FLD_ID]);
                $res_upd->update();
                // if the value is really updated, remember the value is to check if this triggers more updates
                $result[] = $res_upd->save();
            }
        }

        return $result;
    }

    // update the result of this result (without loading or saving)
    function update()
    {
        log_debug('result->update ' . $this->dsp_id());
        // check parameters
        if (!isset($this->phr_lst)) {
            log_err("Phrase list is missing.", "result->update");
        } elseif ($this->frm->id() <= 0) {
            log_err("Formula ID is missing.", "result->update");
        } else {
            // prepare update
            $this->load_phrases();
            $this->load_formula();

            $frm = $this->frm;
            $phr_lst = $this->src_grp->phr_lst;
            $frm->calc($phr_lst, '');

            //$this->save_if_updated ();
            log_debug('result->update ' . $this->dsp_id() . ' to ' . $this->value . ' done');
        }
    }

    private function save_without_time(): string
    {
        $res_no_time = clone $this;
        $res_no_time->src_time_phr = null;
        $res_no_time->time_phr = null;
        return $res_no_time->save();
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
        $res_check = clone $this;
        $phr_lst_ex_time = $res_check->phr_lst;
        $phr_lst_ex_time->ex_time();
        return !$res_check->load_by_phr_lst($phr_lst_ex_time);
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
                //zu_debug('result->save_if_updated -> save '.$this->dsp_id().' not saved because the result has been calculated at '.$this->last_update.' which is after the last parameter update at '.$this->last_update);
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
                    log_warning('The result phrases for ' . $this->dsp_id() . ' are missing.', 'result->save_if_updated');
                }
                if (!isset($this->src_grp->phr_lst)) {
                    log_warning('The source phrases for ' . $this->dsp_id() . ' are missing.', 'result->save_if_updated');
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
                $res_default_time = $lst_ex_time->assume_time(); // must be the same function called used in 2num
                if (isset($res_default_time)) {
                    log_debug('save "' . $this->value . '" for ' . $this->phr_lst->dsp_id() . ' and default time ' . $res_default_time->dsp_id());
                } else {
                    log_debug('save "' . $this->value . '" for ' . $this->phr_lst->dsp_id());
                }

                if (!isset($this->value)) {
                    log_info('No result calculated for "' . $this->frm->name() . '" based on ' . $this->src_grp->phr_lst->dsp_id() . ' for user ' . $this->user()->id() . '.', "result->save_if_updated");
                } else {
                    // save the default value if the result time is the "newest"
                    if (isset($res_default_time)) {
                        log_debug('check if result time ' . $this->time_phr->dsp_id() . ' is the default time ' . $res_default_time->dsp_id());
                        if ($this->time_phr->id() == $res_default_time->id()) {
                            // if there is not yet a general value for all user, save it now
                            $result .= $this->save_without_time();
                        }
                    }

                    // save the value without time if no value without time is yet saved for the phrase group
                    if ($this->has_no_time_value()) {
                        $result .= $this->save_without_time();
                    }

                    // save the result
                    $this->save();
                    $res_id = $this->id();

                    if ($debug > 0) {
                        $debug_txt = 'result = ' . $this->value . ' saved for ' . $this->phr_lst->name_linked();
                        if ($debug > 3) {
                            $debug_txt .= ' (group id "' . $this->grp->id() . '" and the result time is ' . $this->time_phr->name_linked() . ') as id "' . $res_id . '" based on ' . $this->src_grp->phr_lst->name_linked() . ' (group id "' . $this->src_grp->dsp_id() . '" and the result time is ' . $this->src_time_phr->name_linked() . ')';
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
     * @return string the message that should be shown to the user in case something went wrong
     */
    function save(): string
    {

        global $db_con;
        global $debug;
        $result = '';

        // check the parameters e.g. a result must always be linked to a formula
        if ($this->frm->id() <= 0) {
            log_err("Formula id missing.", "result->save");
        } elseif (empty($this->phr_lst)) {
            log_err("No words for the result.", "result->save");
        } elseif (empty($this->src_grp->phr_lst)) {
            log_err("No words for the calculation.", "result->save");
        } elseif ($this->user() == null) {
            log_err("User missing.", "result->save");
        } else {
            if ($debug > 0) {
                $debug_txt = 'result->save (' . $this->value . ' for formula ' . $this->frm->id() . ' with ' . $this->phr_lst->dsp_name() . ' based on ' . $this->src_grp->phr_lst->dsp_name();
                if (!$this->is_std) {
                    $debug_txt .= ' and user ' . $this->user()->id();
                }
                $debug_txt .= ')';
                log_debug($debug_txt);
            }

            // build the database object because the is anyway needed
            //$db_con = new mysql;
            $db_con->set_usr($this->user()->id);
            $db_con->set_type(sql_db::TBL_RESULT);

            // build the word list if needed to separate the time word from the word list
            $this->save_prepare_wrds();
            log_debug("group id " . $this->grp->id() . " and source group id " . $this->src_grp->dsp_id());

            // check if a database update is needed
            // or if a second results object with the database values
            $res_db = clone $this;
            $res_db->load_obj_vars();
            $row_id = $res_db->id;
            $db_val = $res_db->value;

            // if value exists, check it an update is needed
            if ($row_id > 0) {
                if ($db_con->sf($db_val) <> $db_con->sf($this->value)) {
                    $db_con->set_type(sql_db::TBL_RESULT);
                    if ($db_con->update($row_id,
                        array(result::FLD_VALUE, result::FLD_LAST_UPDATE),
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
                $field_names[] = result::FLD_VALUE;
                $field_values[] = $this->value;
                $field_names[] = result::FLD_GRP;
                $field_values[] = $this->grp->id();
                $field_names[] = result::FLD_SOURCE_GRP;
                $field_values[] = $this->src_grp->id();
                if (!$this->is_std) {
                    $field_names[] = user::FLD_ID;
                    $field_values[] = $this->user()->id();
                }
                $field_names[] = result::FLD_LAST_UPDATE;
                //$field_values[] = 'Now()'; // replaced with time of last change that has been included in the calculation
                $field_values[] = $this->last_val_update->format('Y-m-d H:i:s');
                $db_con->set_type(sql_db::TBL_RESULT);
                $id = $db_con->insert($field_names, $field_values);
                $this->id = $id;
                $result = $id;
            }
        }

        log_debug("id (" . $result . ")");
        return $result;

    }
}