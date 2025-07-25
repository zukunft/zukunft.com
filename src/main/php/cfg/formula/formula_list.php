<?php

/*

    model/formula/formula_list.php - a simple list of formulas
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

namespace cfg\formula;

include_once SERVICE_PATH . 'config.php';
include_once DB_PATH . 'sql_creator.php';
include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_par.php';
include_once DB_PATH . 'sql_par_type.php';
include_once MODEL_ELEMENT_PATH . 'element.php';
include_once MODEL_IMPORT_PATH . 'import.php';
include_once MODEL_HELPER_PATH . 'data_object.php';
include_once MODEL_PHRASE_PATH . 'phrase.php';
include_once MODEL_PHRASE_PATH . 'phrase_list.php';
include_once MODEL_PHRASE_PATH . 'term.php';
include_once MODEL_PHRASE_PATH . 'term_list.php';
include_once MODEL_SANDBOX_PATH . 'sandbox.php';
include_once MODEL_SANDBOX_PATH . 'sandbox_named.php';
include_once MODEL_SANDBOX_PATH . 'sandbox_list_named.php';
include_once MODEL_USER_PATH . 'user.php';
include_once MODEL_USER_PATH . 'user_message.php';
include_once MODEL_VERB_PATH . 'verb.php';
include_once MODEL_WORD_PATH . 'word.php';
include_once MODEL_WORD_PATH . 'word_list.php';
include_once MODEL_WORD_PATH . 'triple.php';
include_once SHARED_CALC_PATH . 'parameter_type.php';
include_once SHARED_CONST_PATH . 'triples.php';
include_once SHARED_CONST_PATH . 'words.php';
include_once SHARED_ENUM_PATH . 'messages.php';
include_once SHARED_PATH . 'library.php';

use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\db\sql_par;
use cfg\db\sql_par_type;
use cfg\element\element;
use cfg\helper\data_object;
use cfg\import\import;
use cfg\phrase\phrase;
use cfg\phrase\phrase_list;
use cfg\phrase\term;
use cfg\phrase\term_list;
use cfg\sandbox\sandbox_list_named;
use cfg\sandbox\sandbox_named;
use cfg\user\user;
use cfg\user\user_message;
use cfg\verb\verb;
use cfg\word\triple;
use cfg\word\word;
use cfg\word\word_list;
use shared\calc\parameter_type;
use shared\const\triples;
use shared\const\words;
use shared\enum\messages as msg_id;
use shared\library;

class formula_list extends sandbox_list_named
{
    // the number of formulas that should be updated with one commit if no dependency calculations are expected
    const UPDATE_BLOCK_SIZE = 100;

    // array $lst are the loaded formula objects
    // if user $usr->id() is 0 (not NULL) for standard formulas, otherwise for a user specific formulas

    // TODO move to display object: in memory only fields
    public ?string $back = null;         // the calling stack


    /*
     * construct and map
     */

    /**
     * fill the formula list based on a database records
     * @param array $db_rows is an array of an array with the database values
     * @param bool $load_all force to include also the excluded phrases e.g. for admins
     * @return bool true if at least one formula has been loaded
     */
    protected function rows_mapper(array $db_rows, bool $load_all = false): bool
    {
        $result = false;
        if ($db_rows != null) {
            foreach ($db_rows as $db_row) {
                $excluded = null;
                if (array_key_exists(sql_db::FLD_EXCLUDED, $db_row)) {
                    $excluded = $db_row[sql_db::FLD_EXCLUDED];
                }
                if (is_null($excluded) or $excluded == 0 or $load_all) {
                    $frm_id = $db_row[formula_db::FLD_ID];
                    if ($frm_id > 0 and !in_array($frm_id, $this->ids())) {
                        $frm = new formula($this->user());
                        $frm->row_mapper_sandbox($db_row);
                        // TODO check if this is really needed
                        if ($frm->name() <> '') {
                            $name_wrd = new word($this->user());
                            $name_wrd->load_by_name($frm->name());
                            $frm->name_wrd = $name_wrd;
                        }
                        $this->add_obj($frm);
                        $result = true;
                    }
                }
            }
        }
        /*
        $result = parent::rows_mapper_obj(new formula_link($this->user()), $db_rows, $load_all);
        // TODO check if this is really needed
        if ($db_rows != null) {
            foreach ($this->lst() as $frm) {
                if ($frm->name() <> '') {
                    $name_wrd = new word($this->user());
                    $name_wrd->load_by_name($frm->name());
                    $frm->name_wrd = $name_wrd;
                }
            }
        }
        */
        return $result;
    }


    /*
     * load
     */

    /**
     * set the SQL query parameters to load a list of formulas
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name extension to make the query name unique
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name = ''): sql_par
    {
        $sc->set_class(formula::class);
        $qp = new sql_par(self::class);
        $qp->name .= $query_name;
        $sc->set_name($qp->name);
        $sc->set_usr($this->user()->id());
        $sc->set_usr_fields(formula_db::FLD_NAMES_USR);
        $sc->set_usr_num_fields(formula_db::FLD_NAMES_NUM_USR);
        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of formulas by an array of formula ids
     * @param sql_creator $sc with the target db_type set
     * @param array $frm_ids an array of formula ids which should be loaded
     * @param int $limit the number of rows to return
     * @param int $offset jump over these number of pages
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_ids(
        sql_creator $sc,
        array       $frm_ids,
        int         $limit = 0,
        int         $offset = 0
    ): sql_par
    {
        $qp = $this->load_sql($sc, 'frm_ids');
        if (count($frm_ids) > 0) {
            $sc->add_where(formula_db::FLD_ID, $frm_ids, sql_par_type::INT_LIST);
            $qp->sql = $sc->sql();
        } else {
            $qp->name = '';
        }
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of formulas by an array of formula names
     * @param sql_creator $sc with the target db_type set
     * @param array $names an array of formula names which should be loaded
     * @param string $fld the name of the name field
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_names(
        sql_creator $sc,
        array       $names,
        string      $fld = formula_db::FLD_NAME
    ): sql_par
    {
        return parent::load_sql_by_names($sc, $names, $fld);
    }

    /**
     * set the SQL query parameters to load a list of formulas by a pattern
     * @param sql_creator $sc with the target db_type set
     * @param string $pattern the text part that should be used to select the formulas
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_like(sql_creator $sc, string $pattern = ''): sql_par
    {
        $qp = $this->load_sql($sc, 'name_like');
        $sc->add_where(formula_db::FLD_NAME, $pattern, sql_par_type::LIKE_R);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of formulas linked to one of the phrases from the given list
     * @param sql_creator $sc with the target db_type set
     * @param phrase $phr a phrase used to select the formulas
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_phr(sql_creator $sc, phrase $phr): sql_par
    {
        $qp = $this->load_sql($sc, 'phr');
        if ($phr->id() <> 0) {
            $sc->set_join_fields(
                array(phrase::FLD_ID),
                formula_link::class,
                formula_db::FLD_ID,
                formula_db::FLD_ID
            );
            $sc->add_where(phrase::FLD_ID, $phr->id(), null, sql_db::LNK_TBL);
            $qp->sql = $sc->sql();
        } else {
            $qp->name = '';
        }
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of formulas linked to one of the phrases from the given list
     * @param sql_creator $sc with the target db_type set
     * @param phrase_list $phr_lst a phrase list used to select the formulas
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_phr_lst(sql_creator $sc, phrase_list $phr_lst): sql_par
    {
        $qp = $this->load_sql($sc, 'phr_lst');
        if ($phr_lst->count() > 0) {
            $sc->set_join_fields(
                array(phrase::FLD_ID),
                formula_link::class,
                formula_db::FLD_ID,
                formula_db::FLD_ID
            );
            $sc->add_where(phrase::FLD_ID, $phr_lst->id_lst(), null, sql_db::LNK_TBL);
            $qp->sql = $sc->sql();
        } else {
            $qp->name = '';
        }
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of formulas that
     * use the results of the given word, triple, verb or formula
     * @param sql_creator $sc with the target db_type set
     * @param int $ref_id the id of the used object
     * @param int $par_type_id the id of the parameter type
     * @param string $type_query_name the short name of the parameter type to make the query name unique
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_ref(
        sql_creator $sc,
        int         $ref_id,
        int         $par_type_id,
        string      $type_query_name): sql_par
    {
        $qp = $this->load_sql($sc, $type_query_name . '_ref');
        if ($ref_id > 0) {
            $sc->set_join_fields(
                array(formula_db::FLD_ID),
                element::class,
                formula_db::FLD_ID,
                formula_db::FLD_ID
            );
            $sc->add_where(element::FLD_REF_ID, $ref_id, null, sql_db::LNK_TBL);
            $sc->add_where(element::FLD_TYPE, $par_type_id, null, sql_db::LNK_TBL);
            $qp->sql = $sc->sql();
        } else {
            $qp->name = '';
        }
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of formulas that
     * use the results of the given word
     * @param sql_creator $sc with the target db_type set
     * @param word $wrd the word to which the depending on formulas should be loaded
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_word_ref(sql_creator $sc, word $wrd): sql_par
    {
        return $this->load_sql_by_ref(
            $sc,
            $wrd->id(),
            parameter_type::WORD_ID,
            'wrd');
    }

    /**
     * set the SQL query parameters to load a list of formulas that
     * use the results of the given triple
     * @param sql_creator $sc with the target db_type set
     * @param triple $trp the triple to which the depending on formulas should be loaded
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_triple_ref(sql_creator $sc, triple $trp): sql_par
    {
        return $this->load_sql_by_ref(
            $sc,
            $trp->id(),
            parameter_type::TRIPLE_ID,
            'trp');
    }

    /**
     * set the SQL query parameters to load a list of formulas that
     * use the results of the given verb
     * @param sql_creator $sc with the target db_type set
     * @param verb $vrb the verb to which the depending on formulas should be loaded
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_verb_ref(sql_creator $sc, verb $vrb): sql_par
    {
        return $this->load_sql_by_ref(
            $sc,
            $vrb->id(),
            parameter_type::VERB_ID,
            'vrb');
    }

    /**
     * set the SQL query parameters to load a list of formulas that
     * use the results of the given formula
     * @param sql_creator $sc with the target db_type set
     * @param formula $frm the formula
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_formula_ref(sql_creator $sc, formula $frm): sql_par
    {
        return $this->load_sql_by_ref(
            $sc,
            $frm->id(),
            parameter_type::FORMULA_ID,
            'frm');
    }

    /**
     * set the SQL query parameters to load a set of all formulas
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param int $limit the number of formulas that should be loaded
     * @param int $page the offset
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_all(sql_db $db_con, int $limit, int $page): sql_par
    {
        $lib = new library();
        $class = $lib->class_to_name(self::class);
        $db_con->set_class(formula::class);
        $qp = new sql_par($class);
        $db_con->set_usr($this->user()->id());
        $db_con->set_all();
        $qp->name = $class . '_all';
        $db_con->set_name($qp->name);
        $db_con->set_usr_fields(formula_db::FLD_NAMES_USR);
        $db_con->set_usr_num_fields(formula_db::FLD_NAMES_NUM_USR);
        if ($limit > 0) {
            $db_con->set_order(formula_db::FLD_ID);
            $db_con->set_page_par($limit, $page);
            $qp->sql = $db_con->select_all();
        } else {
            $qp->name = '';
        }
        $qp->par = $db_con->get_par();
        return $qp;
    }

    /**
     * load a list of formula names
     * @param string $pattern the pattern to filter the formulas
     * @param int $limit the number of rows to return
     * @param int $offset jump over these number of pages
     * @return bool true if at least one formula found
     */
    function load_names(string $pattern = '', int $limit = 0, int $offset = 0): bool
    {
        return parent::load_sbx_names(new formula($this->user()), $pattern, $limit, $offset);
    }

    /**
     * load a list of formulas by the given formula id
     * @param array $frm_ids an array of formula ids which should be loaded
     * @return bool true if at least one formula found
     */
    function load_by_ids(array $frm_ids): bool
    {
        global $db_con;
        $qp = $this->load_sql_by_ids($db_con->sql_creator(), $frm_ids);
        return $this->load($qp);
    }

    /**
     * load a list of formulas by the given formula names
     * @param array $names an array of formula ids which should be loaded
     * @return bool true if at least one formula found
     */
    function load_by_names(array $names = []): bool
    {
        global $db_con;
        if (count($names) > 0) {
            $qp = $this->load_sql_by_names($db_con->sql_creator(), $names);
            return $this->load($qp);
        } else {
            return false;
        }
    }

    /**
     * load formulas with the given pattern
     *
     * @param string $pattern the text part that should be used to select the formulas
     * @return bool true if at least one formula has been loaded
     */
    function load_like(string $pattern): bool
    {
        global $db_con;
        $qp = $this->load_sql_like($db_con->sql_creator(), $pattern);
        return $this->load($qp);
    }

    /**
     * load a list of formulas with are linked to one of the gives phrases
     * @param phrase $phr a phrase used to select the formulas
     * @return bool true if at least one formula found
     */
    function load_by_phr(phrase $phr): bool
    {
        global $db_con;
        $qp = $this->load_sql_by_phr($db_con->sql_creator(), $phr);
        return $this->load($qp);
    }

    /**
     * load a list of formulas with are linked to one of the gives phrases
     * @param phrase_list $phr_lst a phrase list used to select the formulas
     * @return bool true if at least one formula found
     */
    function load_by_phr_lst(phrase_list $phr_lst): bool
    {
        global $db_con;
        $qp = $this->load_sql_by_phr_lst($db_con->sql_creator(), $phr_lst);
        return $this->load($qp);
    }

    /**
     * load all formulas that use the given word
     * @param word $wrd the word that
     * @return bool true if at least one formula has been loaded
     */
    function load_by_word_ref(word $wrd): bool
    {
        global $db_con;
        $qp = $this->load_sql_by_word_ref($db_con->sql_creator(), $wrd);
        return $this->load($qp);
    }

    /**
     * load all formulas that use the given triple
     * @param triple $trp the triple that
     * @return bool true if at least one formula has been loaded
     */
    function load_by_triple_ref(triple $trp): bool
    {
        global $db_con;
        $qp = $this->load_sql_by_triple_ref($db_con->sql_creator(), $trp);
        return $this->load($qp);
    }

    /**
     * load all formulas that use the given verb
     * @param verb $vrb the verb that
     * @return bool true if at least one formula has been loaded
     */
    function load_by_verb_ref(verb $vrb): bool
    {
        global $db_con;
        $qp = $this->load_sql_by_verb_ref($db_con->sql_creator(), $vrb);
        return $this->load($qp);
    }

    /**
     * load all formulas that use the given formula
     * @param formula $frm the formula that
     * @return bool true if at least one formula has been loaded
     */
    function load_by_formula_ref(formula $frm): bool
    {
        global $db_con;
        $qp = $this->load_sql_by_formula_ref($db_con->sql_creator(), $frm);
        return $this->load($qp);
    }

    /**
     * load a snap of all formulas
     * @param int $limit the number of formulas that should be loaded
     * @param int $page the offset
     * @return bool true if at least one word found
     */
    function load_all(int $limit, int $page): bool
    {
        global $db_con;
        $qp = $this->load_sql_all($db_con, $limit, $page);
        return $this->load($qp);
    }


    /*
     * im- and export
     */

    /**
     * import a list of formulas from a JSON array object
     *
     * @param array $json_obj an array with the data of the json object
     * @param user $usr_req the user how has initiated the import mainly used to prevent any user to gain additional rights
     * @param data_object|null $dto cache of the objects imported until now for the primary references
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_obj(
        array        $json_obj,
        user         $usr_req,
        ?data_object $dto = null,
        object       $test_obj = null
    ): user_message
    {
        $usr_msg = new user_message();
        foreach ($json_obj as $value) {
            $frm = new formula($this->user());
            $usr_msg->add($frm->import_obj($value, $usr_req, $dto, $test_obj));
            // add a dummy id for unit testing
            if ($test_obj) {
                $frm->set_id($test_obj->seq_id());
            }
            $this->add($frm);
        }

        return $usr_msg;
    }

    /**
     * create an array with the export json fields of this formula
     * @param bool $do_load to switch off the database load for unit tests
     * @return array the filled array used to create the user export json
     */
    function export_json(bool $do_load = true): array
    {
        $frm_lst = [];
        foreach ($this->lst() as $frm) {
            if (get_class($frm) == formula::class) {
                $frm_lst[] = $frm->export_json($do_load);
            } else {
                log_err('The function formula_list->export_json returns ' . $frm->dsp_id()
                    . ', which is ' . get_class($frm) . ', but not a formula.', 'export->get');
            }
        }
        return $frm_lst;
    }


    /*
     * modify
     */

    /**
     * add one formula to the formula list, but only if it is not yet part of the list
     * @param formula|sandbox_named|triple|phrase|term|null $to_add the formula backend object that should be added
     * @returns bool true the formula has been added
     */
    function add(formula|sandbox_named|triple|phrase|term|null $to_add): bool
    {
        return parent::add_obj($to_add)->is_ok();
    }


    /*
     * select
     */

    /**
     * @param string $type the ENUM string of the fixed type
     * @return formula_list with the all formulas of the give type
     */
    private function filter(string $type): formula_list
    {
        $result = new formula_list($this->user());
        foreach ($this->lst() as $frm) {
            if ($frm->is_type($type)) {
                $result->add($frm);
            }
        }
        return $result;
    }

    /**
     * diff as a function, because the array_diff does not seem to work for an object list
     *
     * e.g. for "2014", "2015", "2016", "2017"
     * and delete list of "2016", "2017","2018"
     * the result is "2014", "2015"
     *
     * @param formula_list $del_lst is the list of phrases that should be removed from this list object
     */
    private function diff(formula_list $del_lst): void
    {
        if (!$this->is_empty()) {
            $result = array();
            $lst_ids = $del_lst->ids();
            foreach ($this->lst() as $frm) {
                if (!in_array($frm->id(), $lst_ids)) {
                    $result[] = $frm;
                }
            }
            $this->set_lst($result);
        }
    }


    /*
     * info
     */

    /**
     * @param sql_db $db_con the active database connection
     * @return int|null the total number of formulas (without user specific changes)
     */
    function count_db(sql_db $db_con): ?int
    {
        return $db_con->count(formula::class);
    }

    /**
     * convert this formula list object into a term list object
     * and use the name as the unique key instead of the database id
     * used for the data_object based import
     * @return term_list with all formulas of this list as a term
     */
    function term_lst_of_names(): term_list
    {
        $trm_lst = new term_list($this->user());
        foreach ($this->lst() as $frm) {
            if ($frm::class != formula::class) {
                log_err('unexpected class ' . $frm::class . ' in formula list');
            } else {
                $trm_lst->add_by_name($frm->term());
            }
        }
        return $trm_lst;
    }


    /*
     * upgrade functions
     */

    function db_ref_refresh(sql_db $db_con): bool
    {
        $result = true;

        $total = $this->count_db($db_con);
        $page = 1;
        $pages = ceil($total / self::UPDATE_BLOCK_SIZE);
        while ($page <= $pages and $result) {
            $this->load_all(self::UPDATE_BLOCK_SIZE, $page);
            foreach ($this->lst() as $frm) {
                $frm->set_ref_text();
            }
            // TODO Prio 2 review
            $cache = new term_list($this->user());
            $imp = new import();
            $msg = $this->save_with_cache_slow($imp, $cache)->get_last_message();
            if ($msg != '') {
                $result = false;
            }
        }

        return $result;
    }


    /*
     * display
     */

    function name(int $limit = null): string
    {
        $lib = new library();
        return $lib->dsp_array($this->names());
    }

    /**
     * this function is called from dsp_id, so no other call is allowed
     * @param bool $ignore_excluded if true also the excluded names are included
     * @param ?int $limit the max number of ids to show
     * @return array with all names of the list
     */
    function names(bool $ignore_excluded = false, int $limit = null): array
    {
        $result = array();
        if ($this->lst() != null) {
            foreach ($this->lst() as $frm) {
                $result[] = $frm->name($ignore_excluded);
            }
        }
        return $result;
    }

    /**
     * @return int the number of suggested calculation blocks to update all formulas
     */
    function calc_blocks(sql_db $db_con, int $total_formulas = 0): int
    {
        global $cfg;

        // get the configuration
        $avg_calc_time = $cfg->get_by([words::CALCULATION, triples::BLOCK_SIZE, triples::AVERAGE_DELAY]);
        $ui_response_time = $cfg->get_by([triples::RESPONSE_TIME, words::MIN, words::FRONTEND, words::BEHAVIOUR], 1);

        if ($total_formulas == 0) {
            $total_formulas = $db_con->count(formula::class);
        }
        $total_expected_time = $total_formulas * $avg_calc_time;
        return max(1, round($total_expected_time / ($ui_response_time * 1000)));
    }

    /**
     * add or update all formulas to the database
     * starting with the $cache that contains the words, triples, verbs
     * add the formulas that does not yet have a database id
     * similar to triple_list->save_with_cache but using the term_list
     *
     * @param import $imp the import object with the filename and the estimated time of arrival
     * @param term_list $cache the cached phrases that does not need to be loaded from the db again
     * @return user_message the message shown to the user why the action has failed or an empty string if everything is fine
     */
    function save_with_cache(import $imp, term_list $cache): user_message
    {
        global $cfg;

        $usr_msg = new user_message();

        $load_per_sec = $cfg->get_by([words::FORMULAS, words::LOAD, triples::OBJECTS_PER_SECOND, triples::EXPECTED_TIME, words::IMPORT], 1);
        $save_per_sec = $cfg->get_by([words::FORMULAS, words::STORE, triples::OBJECTS_PER_SECOND, triples::EXPECTED_TIME, words::IMPORT], 1);
        $upd_per_sec = $cfg->get_by([words::FORMULAS, words::UPDATE, triples::OBJECTS_PER_SECOND, triples::EXPECTED_TIME, words::IMPORT], 1);
        $del_per_sec = $cfg->get_by([words::FORMULAS, words::DELETE, triples::OBJECTS_PER_SECOND, triples::EXPECTED_TIME, words::IMPORT], 1);
        $max_frm_levels = $cfg->get_by([words::FORMULAS, triples::MAX_LEVELS, words::IMPORT], 99);

        if ($this->is_empty()) {
            log_info('no formulas to save');
        } else {

            // repeat filling the database id to the formula list
            // and adding missing formulas to the database
            // until it is clear that a formula is missing
            $frm_added = true;
            $level = 0;
            $db_lst_all = new formula_list($this->user());
            $add_lst = new formula_list($this->user());
            while ($frm_added and $level < $max_frm_levels) {
                $frm_added = false;
                $usr_msg->unset_added_depending();

                // collect the formulas used in the expressions
                $chk_lst = clone $this;
                foreach ($this->lst() as $frm) {
                    $exp = $frm->expression($cache);
                    if ($exp->is_valid() or $frm->is_special()) {
                        $frm_trm_lst = $exp->terms($cache);
                        foreach ($frm_trm_lst->lst() as $trm) {
                            $frm_trm = $cache->get_by_name($trm->name());
                            if ($frm_trm == null) {
                                $chk_lst->add_by_name($frm_trm);
                            }
                        }
                    }
                }

                // add the database id to the formula list of words and formulas used until now
                $chk_lst->fill_by_name($cache, true, false);

                // get the formulas that needs to be added
                // TODO check if other list save function are using the cache instead of this here
                $load_lst = $chk_lst->missing_ids();

                // load the formulas by name from the database that does not yet have a database id
                $step_time = $load_lst->count() / $load_per_sec;
                $imp->step_start(msg_id::LOAD, formula::class, $load_lst->count(), $step_time);
                $db_lst = new formula_list($this->user());
                // force to load all names including the formulas excluded by the user to potential include the formulas due to the import
                // TODO add load_all = true also to the other objects
                $db_lst->load_by_names($load_lst->names(true), true);
                $imp->step_end($load_lst->count(), $load_per_sec);

                // fill up the cache to prevent loading the same formula again in the next level
                // TODO increase speed!
                $cache = $cache->merge($db_lst->term_list());

                // fill up the overall db list with db value for later detection of the formulas that needs to be updated
                $db_lst_all->merge($db_lst);

                // fill up the loaded list with db value to select only the formulas that really needs to be inserted
                $load_lst->fill_by_name($db_lst, true, false);

                // select the formulas that are ready to be added to the database
                $load_lst = $load_lst->get_ready($usr_msg, $imp->file_name);

                // get the formulas that still needs to be added
                // TODO check if other list save function are using the cache instead of this here
                $add_lst = $load_lst->missing_ids();

                // create any missing sql insert functions and insert the missing formulas
                if (!$add_lst->is_empty()) {

                    // refresh reference text
                    $this->refresh_ref_text($cache, $usr_msg);

                    $step_time = $add_lst->count() / $save_per_sec;
                    $imp->step_start(msg_id::SAVE, formula::class, $add_lst->count(), $step_time);
                    $usr_msg->add($add_lst->insert($cache, true, $imp, formula::class));
                    if ($add_lst->count() > 0) {
                        $usr_msg->set_added_depending();
                        $frm_added = true;
                    }
                    $imp->step_end($add_lst->count(), $save_per_sec);
                }

                // create the related words
                $this->save_formulas_words($imp, $usr_msg);

                $cache->filter_valid();

                $level++;
            }

            // reload the id of the formulas added with the last run
            // TODO use the insert message instead to increase speed
            $db_lst = new formula_list($this->user());
            if (!$add_lst->is_empty()) {
                $db_lst->load_by_names($add_lst->names(true), true);
            }

            // fill up the overall db list with db value for later detection of the formulas that needs to be updated
            $db_lst_all->merge($db_lst);


            // create any missing sql update functions and update the formulas
            $usr_msg->add($this->update($db_lst_all, true, $imp, formula::class, $upd_per_sec));


            // fill up the main list with the words
            $this->fill_by_name($cache, true);
            // fill up the main list with the formulas to check if anything is missing
            $this->fill_by_name($db_lst_all, true);

            // report missing formulas
            $this->report_missing($usr_msg, $cache);


            // create any missing sql delete functions and delete unused sandbox objects
            $usr_msg->add($this->delete($db_lst_all, true, $imp, formula::class, $del_per_sec));

        }

        return $usr_msg;
    }


    private function refresh_ref_text(term_list $trm_lst, user_message $usr_msg): void
    {
        foreach ($this->lst() as $frm) {
            $frm->generate_ref_text($trm_lst, $usr_msg);
        }
    }

    private function save_formulas_words(import $imp, user_message $usr_msg): void
    {
        $wrd_lst = new word_list($this->user());
        foreach ($this->lst() as $frm) {
            $wrd = $frm->formula_word();
            $wrd_lst->add_by_name($wrd);
        }
        $wrd_lst->save($imp);
        foreach ($this->lst() as $frm) {
            $name_wrd = $wrd_lst->get_by_name($frm->name());
            if ($name_wrd->id() > 0) {
                $frm->name_wrd = $name_wrd;
            } else {
                $usr_msg->add_id_with_vars(msg_id::IMPORT_FORMULA_NOT_READY, [
                    msg_id::VAR_WORD_NAME => $frm->name(),
                    msg_id::VAR_FORMULA => $frm->id(),
                ]);
            }
        }
    }

    protected function delete_depending(user_message $usr_msg): void
    {
        foreach ($this->lst() as $frm) {
            $usr_msg->add($frm->del_links());
        }
    }


    /*
     * convert
     */

    /**
     * convert this formula list object into a phrase list object
     * @return term_list with all formulas of this list as a phrase
     */
    function term_list(): term_list
    {
        $trm_lst = new term_list($this->user());
        foreach ($this->lst() as $frm) {
            $trm_lst->add($frm->term());
        }
        return $trm_lst;
    }

    /**
     * @return formula_list with all formulas that does not yet have a database id
     */
    function missing_ids(): formula_list
    {
        $frm_lst = new formula_list($this->user());
        foreach ($this->lst() as $frm) {
            if ($frm->id() == 0) {
                $frm_lst->add_by_name_direct($frm);
            }
        }
        return $frm_lst;
    }

    /**
     * add the formulas of the given list to this list but avoid duplicates
     * merge as a function, because the array_merge does not create an object
     * @param formula_list $lst_to_add with the phrases to be added
     * @return formula_list with all phrases of this list and the given list
     */
    function merge(formula_list $lst_to_add): formula_list
    {
        if (!$lst_to_add->is_empty()) {
            foreach ($lst_to_add->lst() as $frm_to_add) {
                $this->add($frm_to_add);
            }
        }
        return $this;
    }

    /**
     * save all formulas of this list
     * TODO create one SQL and commit statement for faster execution
     *
     * @param import $imp the import object with the filename and the estimated time of arrival
     * @param term_list $cache the cached phrases that does not need to be loaded from the db again
     * @return user_message the message shown to the user why the action has failed or an empty string if everything is fine
     */
    function save_with_cache_slow(import $imp, term_list $cache): user_message
    {
        $usr_msg = new user_message();
        foreach ($this->lst() as $frm) {
            $usr_msg->add($frm->save());
            $cache->add($frm->term());
        }
        return $usr_msg;
    }

    /**
     * get a list of formulas that are ready to be added to the database
     * @return formula_list list of the formulas that have an id or a name
     */
    function get_ready(user_message $usr_msg, string $file_name = ''): formula_list
    {
        $frm_lst = new formula_list($this->user());
        foreach ($this->lst() as $frm) {
            $frm_msg = $frm->db_ready();
            if ($frm_msg->is_ok()) {
                $frm_lst->add_by_name($frm);
            } else {
                $usr_msg->add($frm_msg);
                $usr_msg->add_id_with_vars(msg_id::IMPORT_FORMULA_NOT_READY, [
                    msg_id::VAR_FILE_NAME => $file_name,
                    msg_id::VAR_FORMULA => $frm->dsp_id(),
                ]);
            }
        }
        return $frm_lst;
    }

    /**
     * add the ids and other variables from the given list and add missing words, triples, ...
     * select the related object by the name
     *
     * @param formula_list|sandbox_list_named $db_lst a list of sandbox objects that might have more vars set e.g. the db id
     * @param bool $fill_all force to include also the excluded names e.g. for import
     * @param bool $report_missing if true it is expected that all triples are in the given $db_lst
     * @return user_message a warning in case of a conflict e.g. due to a missing change time
     */
    function fill_by_name(
        formula_list|sandbox_list_named $db_lst,
        bool                            $fill_all = false,
        bool                            $report_missing = true
    ): user_message
    {
        $usr_msg = new user_message();

        // loop over the objects of theis list because it is expected to be smaller than tha cache list
        foreach ($this->lst() as $frm) {
            $this->fill_triple_by_name($db_lst, $frm, $usr_msg, $fill_all, $report_missing);

        }
        return $usr_msg;
    }

    private function fill_triple_by_name(
        formula_list|sandbox_list_named $db_lst,
        formula                         $frm,
        user_message                    $usr_msg,
        bool                            $fill_all = false,
        bool                            $report_missing = true
    ): void
    {
        global $usr;
        if ($frm->id() == 0 and $frm->name($fill_all) != '') {
            $db_obj = $db_lst->get_by_name($frm->name($fill_all), $fill_all);
            if ($db_obj != null) {
                $frm->fill($db_obj, $usr);
            } else {
                if ($report_missing and !$frm->is_excluded()) {
                    $lib = new library();
                    $usr_msg->add_id_with_vars(msg_id::ADDED_OBJECT_NOT_FOUND, [
                        msg_id::VAR_CLASS_NAME => $lib->class_to_name($frm::class),
                        msg_id::VAR_NAME => $frm->dsp_id()
                    ]);
                }
            }
        }
    }

    private function report_missing(user_message $usr_msg, term_list $cache): void
    {
        foreach ($this->lst() as $frm) {
            $exp = $frm->expression($cache);
            $frm_trm_lst = $exp->terms($cache);
            foreach ($frm_trm_lst->lst() as $trm) {
                if ($trm->id() == 0) {
                    $frm_trm = $cache->get_by_name($trm->name());
                    if ($frm_trm == null) {
                        $usr_msg->add_id_with_vars(msg_id::IMPORT_TERM_NOT_FOUND, [
                            msg_id::VAR_NAME => $trm->name(),
                            msg_id::VAR_ID => $frm->dsp_id()
                        ]);
                    }
                }
            }
        }
    }

}