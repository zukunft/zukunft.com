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

namespace cfg;

include_once DB_PATH . 'sql_par_type.php';
include_once API_FORMULA_PATH . 'formula_list.php';

use api\formula_list_api;
use cfg\db\sql_par_type;
use html\formula\formula as formula_dsp;
use html\formula\formula_list as formula_list_dsp;

class formula_list extends sandbox_list
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
                if (is_null($db_row[sandbox::FLD_EXCLUDED]) or $db_row[sandbox::FLD_EXCLUDED] == 0 or $load_all) {
                    $frm_id = $db_row[formula::FLD_ID];
                    if ($frm_id > 0 and !in_array($frm_id, $this->ids())) {
                        $frm = new formula($this->user());
                        $frm->row_mapper_sandbox($db_row);
                        // TODO check if this is really needed
                        if ($frm->name() <> '') {
                            $name_wrd = new word($this->user());
                            $name_wrd->load_by_name($frm->name(), word::class);
                            $frm->name_wrd = $name_wrd;
                        }
                        $this->lst[] = $frm;
                        $result = true;
                    }
                }
            }
        }
        /*
        $result = parent::rows_mapper_obj(new formula_link($this->user()), $db_rows, $load_all);
        // TODO check if this is really needed
        if ($db_rows != null) {
            foreach ($this->lst as $frm) {
                if ($frm->name() <> '') {
                    $name_wrd = new word($this->user());
                    $name_wrd->load_by_name($frm->name(), word::class);
                    $frm->name_wrd = $name_wrd;
                }
            }
        }
        */
        return $result;
    }


    /*
     * cast
     */

    /**
     * @return formula_list_api the formula list object with the display interface functions
     */
    function api_obj(): formula_list_api
    {
        $api_obj = new formula_list_api();
        foreach ($this->lst as $frm) {
            $api_obj->add($frm->api_obj());
        }
        return $api_obj;
    }

    /**
     * @returns string the api json message for the object as a string
     */
    function api_json(): string
    {
        return $this->api_obj()->get_json();
    }

    /**
     * @return formula_list_dsp the formula list object with the display interface functions
     */
    function dsp_obj(): formula_list_dsp
    {
        $dsp_obj = new formula_list_dsp();
        foreach ($this->lst as $wrd) {
            $dsp_obj->add($wrd->dsp_obj());
        }
        return $dsp_obj;
    }


    /*
     * load
     */

    /**
     * set the SQL query parameters to load a list of formulas
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_db $db_con): sql_par
    {
        $db_con->set_type(sql_db::TBL_FORMULA);
        $qp = new sql_par(self::class);
        $db_con->set_name($qp->name); // assign incomplete name to force the usage of the user as a parameter
        $db_con->set_usr($this->user()->id());
        $db_con->set_usr_fields(formula::FLD_NAMES_USR);
        $db_con->set_usr_num_fields(formula::FLD_NAMES_NUM_USR);
        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of formulas by an array of formula ids
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param array $frm_ids an array of formula ids which should be loaded
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_ids(sql_db $db_con, array $frm_ids): sql_par
    {
        $qp = $this->load_sql($db_con);
        if (count($frm_ids) > 0) {
            $qp->name .= 'frm_ids';
            $db_con->set_name($qp->name);
            $db_con->add_par_in_int($frm_ids);
            $qp->sql = $db_con->select_by_field(formula::FLD_ID);
        } else {
            $qp->name = '';
        }
        $qp->par = $db_con->get_par();
        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of formulas by an array of formula names
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param array $names an array of formula names which should be loaded
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_names(sql_db $db_con, array $names): sql_par
    {
        $qp = $this->load_sql($db_con);
        if (count($names) > 0) {
            $qp->name .= 'names';
            $db_con->set_name($qp->name);
            $db_con->add_par_in_txt($names);
            $qp->sql = $db_con->select_by_field(formula::FLD_NAME);
        } else {
            $qp->name = '';
        }
        $qp->par = $db_con->get_par();
        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of formulas linked to one of the phrases from the given list
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param phrase $phr a phrase used to select the formulas
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_phr(sql_db $db_con, phrase $phr): sql_par
    {
        $qp = $this->load_sql($db_con);
        if ($phr->id() <> 0) {
            $qp->name .= 'phr';
            $db_con->set_name($qp->name);
            $db_con->set_join_fields(
                array(phrase::FLD_ID),
                sql_db::TBL_FORMULA_LINK,
                formula::FLD_ID,
                formula::FLD_ID
            );
            $db_con->add_par(sql_par_type::INT, $phr->id(), false, true);
            $qp->sql = $db_con->select_by_field(phrase::FLD_ID);
        } else {
            $qp->name = '';
        }
        $qp->par = $db_con->get_par();
        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of formulas linked to one of the phrases from the given list
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param phrase_list $phr_lst a phrase list used to select the formulas
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_phr_lst(sql_db $db_con, phrase_list $phr_lst): sql_par
    {
        $qp = $this->load_sql($db_con);
        if ($phr_lst->count() > 0) {
            $qp->name .= 'phr_lst';
            $db_con->set_name($qp->name);
            $db_con->set_join_fields(
                array(phrase::FLD_ID),
                sql_db::TBL_FORMULA_LINK,
                formula::FLD_ID,
                formula::FLD_ID
            );
            $db_con->add_par_in_int($phr_lst->id_lst(), false, true);
            $qp->sql = $db_con->select_by_field(phrase::FLD_ID);
        } else {
            $qp->name = '';
        }
        $qp->par = $db_con->get_par();
        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of formulas that
     * use the results of the given word, triple, verb or formula
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param int $ref_id the id of the used object
     * @param int $par_type_id the id of the parameter type
     * @param string $type_query_name the short name of the parameter type to make the query name unique
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_ref(
        sql_db $db_con,
        int    $ref_id,
        int    $par_type_id,
        string $type_query_name): sql_par
    {
        $qp = $this->load_sql($db_con);
        if ($ref_id > 0) {
            $qp->name .= $type_query_name . '_ref';
            $db_con->set_name($qp->name);
            $db_con->set_join_fields(
                array(formula::FLD_ID),
                sql_db::TBL_FORMULA_ELEMENT,
                formula::FLD_ID,
                formula::FLD_ID
            );
            $db_con->add_par_join_int($ref_id);
            $db_con->add_par_join_int($par_type_id);
            $qp->sql = $db_con->select_by_field_list(
                array(formula_element::FLD_REF_ID, formula_element::FLD_TYPE));
        } else {
            $qp->name = '';
        }
        $qp->par = $db_con->get_par();
        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of formulas that
     * use the results of the given word
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param word $wrd the word to which the depending formulas should be loaded
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_word_ref(sql_db $db_con, word $wrd): sql_par
    {
        return $this->load_sql_by_ref(
            $db_con,
            $wrd->id(),
            parameter_type::WORD_ID,
            'wrd');
    }

    /**
     * set the SQL query parameters to load a list of formulas that
     * use the results of the given triple
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param triple $trp the triple to which the depending formulas should be loaded
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_triple_ref(sql_db $db_con, triple $trp): sql_par
    {
        return $this->load_sql_by_ref(
            $db_con,
            $trp->id(),
            parameter_type::TRIPLE_ID,
            'trp');
    }

    /**
     * set the SQL query parameters to load a list of formulas that
     * use the results of the given verb
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param verb $vrb the verb to which the depending formulas should be loaded
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_verb_ref(sql_db $db_con, verb $vrb): sql_par
    {
        return $this->load_sql_by_ref(
            $db_con,
            $vrb->id(),
            parameter_type::VERB_ID,
            'vrb');
    }

    /**
     * set the SQL query parameters to load a list of formulas that
     * use the results of the given formula
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param formula $frm the formula
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_formula_ref(sql_db $db_con, formula $frm): sql_par
    {
        return $this->load_sql_by_ref(
            $db_con,
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
        $db_con->set_type(sql_db::TBL_FORMULA);
        $qp = new sql_par($class);
        $db_con->set_usr($this->user()->id());
        $db_con->set_all();
        $qp->name = $class . '_all';
        $db_con->set_name($qp->name);
        $db_con->set_usr_fields(formula::FLD_NAMES_USR);
        $db_con->set_usr_num_fields(formula::FLD_NAMES_NUM_USR);
        if ($limit > 0) {
            $db_con->set_order(formula::FLD_ID);
            $db_con->set_page_par($limit, $page);
            $qp->sql = $db_con->select_all();
        } else {
            $qp->name = '';
        }
        $qp->par = $db_con->get_par();
        return $qp;
    }

    /**
     * load a list of formulas by the given formula id
     * @param array $frm_ids an array of formula ids which should be loaded
     * @return bool true if at least one word found
     */
    function load_by_ids(array $frm_ids): bool
    {
        global $db_con;
        $qp = $this->load_sql_by_ids($db_con, $frm_ids);
        return $this->load($qp);
    }

    /**
     * load a list of formulas by the given formula names
     * @param array $names an array of formula ids which should be loaded
     * @return bool true if at least one word found
     */
    function load_by_names(array $names): bool
    {
        global $db_con;
        $qp = $this->load_sql_by_names($db_con, $names);
        return $this->load($qp);
    }

    /**
     * load a list of formulas with are linked to one of the gives phrases
     * @param phrase $phr a phrase used to select the formulas
     * @return bool true if at least one word found
     */
    function load_by_phr(phrase $phr): bool
    {
        global $db_con;
        $qp = $this->load_sql_by_phr($db_con, $phr);
        return $this->load($qp);
    }

    /**
     * load a list of formulas with are linked to one of the gives phrases
     * @param phrase_list $phr_lst a phrase list used to select the formulas
     * @return bool true if at least one word found
     */
    function load_by_phr_lst(phrase_list $phr_lst): bool
    {
        global $db_con;
        $qp = $this->load_sql_by_phr_lst($db_con, $phr_lst);
        return $this->load($qp);
    }

    /**
     * load all formulas that use the given word
     * @param word $wrd the word that
     * @return bool true if at least one formula has bee loaded
     */
    function load_by_word_ref(word $wrd): bool
    {
        global $db_con;
        $qp = $this->load_sql_by_word_ref($db_con, $wrd);
        return $this->load($qp);
    }

    /**
     * load all formulas that use the given triple
     * @param triple $trp the triple that
     * @return bool true if at least one formula has bee loaded
     */
    function load_by_triple_ref(triple $trp): bool
    {
        global $db_con;
        $qp = $this->load_sql_by_triple_ref($db_con, $trp);
        return $this->load($qp);
    }

    /**
     * load all formulas that use the given verb
     * @param verb $vrb the verb that
     * @return bool true if at least one formula has bee loaded
     */
    function load_by_verb_ref(verb $vrb): bool
    {
        global $db_con;
        $qp = $this->load_sql_by_verb_ref($db_con, $vrb);
        return $this->load($qp);
    }

    /**
     * load all formulas that use the given formula
     * @param formula $frm the formula that
     * @return bool true if at least one formula has bee loaded
     */
    function load_by_formula_ref(formula $frm): bool
    {
        global $db_con;
        $qp = $this->load_sql_by_formula_ref($db_con, $frm);
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
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_obj(array $json_obj, object $test_obj = null): user_message
    {
        $result = new user_message();
        foreach ($json_obj as $key => $value) {
            $frm = new formula($this->user());
            $result->add($frm->import_obj($value, $test_obj));
            // add a dummy id for unit testing
            if ($test_obj) {
                $frm->set_id($test_obj->seq_id());
            }
            $this->add($frm);
        }

        return $result;
    }

    /**
     * create a list of formula objects for the export
     * @return array with the reduced word objects that can be used to create a JSON message
     */
    function export_obj(): array
    {
        $exp_formulas = array();
        foreach ($this->lst as $frm) {
            if (get_class($frm) == formula::class) {
                $exp_formulas[] = $frm->export_obj();
            } else {
                log_err('The function formula_list->export_obj returns ' . $frm->dsp_id() . ', which is ' . get_class($frm) . ', but not a formula.', 'export->get');
            }
        }
        return $exp_formulas;
    }


    /*
     * modification
     */

    /**
     * add one formula to the formula list, but only if it is not yet part of the list
     * @param formula|null $frm_to_add the formula backend object that should be added
     * @returns bool true the formula has been added
     */
    function add(?formula $frm_to_add): bool
    {
        return parent::add_obj($frm_to_add);
    }


    /*
     * information
     */

    /**
     * @param sql_db $db_con the active database connection
     * @return int|null the total number of formulas (without user specific changes)
     */
    function count_db(sql_db $db_con): ?int
    {
        return $db_con->count(sql_db::TBL_FORMULA);
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
            foreach ($this->lst as $frm) {
                $frm->set_ref_text();
            }
            $msg = $this->save();
            if ($msg != '') {
                $result = false;
            }
        }

        return $result;
    }


    /*
     * display functions
     */

    /**
     * return the loaded formula names for debugging
     */
    function dsp_id(): string
    {
        $result = $this->name();
        if ($result <> '') {
            $result = '"' . $result . '"';
        }
        return $result;
    }

    function name(): string
    {
        $lib = new library();
        return $lib->dsp_array($this->names());
    }

    /**
     * this function is called from dsp_id, so no other call is allowed
     */
    function names(): array
    {
        $result = array();
        if ($this->lst != null) {
            foreach ($this->lst as $frm) {
                $result[] = $frm->name();
            }
        }
        return $result;
    }

    /**
     * lists all formulas with results related to a word
     */
    function display($type = 'short'): string
    {
        log_debug('formula_list->display ' . $this->dsp_id());
        $result = '';

        // list all related formula results
        if ($this->lst != null) {
            usort($this->lst, array(formula::class, "cmp"));
            if ($this->lst != null) {
                foreach ($this->lst as $frm) {
                    // formatting should be moved
                    //$resolved_text = str_replace('"','&quot;', $frm->usr_text);
                    //$resolved_text = str_replace('"','&quot;', $frm->dsp_text($this->back));
                    $frm_dsp = $frm->dsp_obj_old();
                    $frm_html = new formula_dsp($frm->api_json());
                    $result = '';
                    if ($frm->name_wrd != null) {
                        $result = $frm_dsp->dsp_result($frm->name_wrd->phrase(), $this->back);
                    }
                    // if the result is empty use the id to be able to select the formula
                    if ($result == '') {
                        $result .= $frm_dsp->id();
                    } else {
                        $result .= ' value ' . $result;
                    }
                    $result .= ' ' . $frm_html->edit_link($this->back);
                    if ($type == 'short') {
                        $result .= ' ' . $frm_dsp->btn_del($this->back);
                        $result .= ', ';
                    } else {
                        $result .= ' (' . $frm_dsp->dsp_text($this->back) . ')';
                        $result .= ' ' . $frm_dsp->btn_del($this->back);
                        $result .= ' <br> ';
                    }
                }
            }
        }

        log_debug("formula_list->display ... done (" . $result . ")");
        return $result;
    }

    /**
     * @return int the number of suggested calculation blocks to update all formulas
     */
    function calc_blocks(sql_db $db_con, int $total_formulas = 0): int
    {
        $cfg = new config();
        if ($total_formulas == 0) {
            $total_formulas = $db_con->count(sql_db::TBL_FORMULA);
        }
        $avg_calc_time = $cfg->get_db(config::AVG_CALC_TIME, $db_con);
        $total_expected_time = $total_formulas * $avg_calc_time;
        return max(1, round($total_expected_time / (UI_MIN_RESPONSE_TIME * 1000)));
    }

    /**
     * save all formulas of this list
     * TODO create one SQL and commit statement for faster execution
     *
     * @return string the message shown to the user why the action has failed or an empty string if everything is fine
     */
    function save(): string
    {
        $result = '';
        foreach ($this->lst as $frm) {
            $result .= $frm->save();
        }
        return $result;
    }

}