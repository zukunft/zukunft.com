<?php

/*

    formula_list.php - a simple list of formulas
    ----------------

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

use api\formula_list_api;
use html\formula_list_dsp;

class formula_list extends sandbox_list
{
    // the number of formulas that should be updated with one commit if no dependency calculations are expected
    const UPDATE_BLOCK_SIZE = 100;

    // array $lst are the loaded formula objects
    // if user $usr->id() is 0 (not NULL) for standard formulas, otherwise for a user specific formulas

    // TODO deprecate: fields to select the formulas
    public ?word $wrd = null;            // show the formulas related to this word
    public ?phrase_list $phr_lst = null; // show the formulas related to this phrase list
    public ?array $ids = array();        // a list of formula ids to load all formulas at once

    // TODO move to display object: in memory only fields
    public ?string $back = null;         // the calling stack

    /*
     * construct and map
     */

    /**
     * fill the formula list based on a database records
     * @param array $db_rows is an array of an array with the database values
     * @return bool true if at least one formula has been loaded
     */
    private function rows_mapper(array $db_rows): bool
    {
        $result = false;
        if ($db_rows != null) {
            foreach ($db_rows as $db_row) {
                if (is_null($db_row[user_sandbox::FLD_EXCLUDED]) or $db_row[user_sandbox::FLD_EXCLUDED] == 0) {
                    if ($db_row[formula::FLD_ID] > 0) {
                        $frm = new formula($this->user());
                        $frm->row_mapper($db_row);
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
        foreach ($this->lst as $wrd) {
            $api_obj->add($wrd->api_obj());
        }
        return $api_obj;
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
            $db_con->add_par(sql_db::PAR_INT, $phr->id(), false, true);
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
     * set the SQL query parameters to load a set of all formulas
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param int $limit the number of formulas that should be loaded
     * @param int $page the offset
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_all(sql_db $db_con, int $limit, int $page): sql_par
    {
        $db_con->set_type(sql_db::TBL_FORMULA);
        $qp = new sql_par(self::class);
        $db_con->set_usr($this->user()->id());
        $db_con->set_all();
        $qp->name = formula_list::class . '_all';
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
     * load a list of formulas
     * @param sql_par $qp the SQL statement, the unique name of the SQL statement and the parameter list
     * @return bool true if at least one formula has been loaded
     */
    private function load_int(sql_par $qp): bool
    {

        global $db_con;
        $result = false;

        // check the all minimal input parameters are set
        if ($qp->name == '') {
            log_err('The query name cannot be created to load a ' . self::class, self::class . '->load');
        } else {
            $db_lst = $db_con->get($qp);
            $result = $this->rows_mapper($db_lst);
        }
        return $result;
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
        return $this->load_int($qp);
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
        return $this->load_int($qp);
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
        return $this->load_int($qp);
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
        return $this->load_int($qp);
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
        return $this->load_int($qp);
    }

    /*
     * modification
     */

    /**
     * add one formula to the formula list, but only if it is not yet part of the list
     * @param formula|null $obj_to_add the formula backend object that should be added
     * @returns bool true the formula has been added
     */
    function add(?formula $obj_to_add): bool
    {
        return parent::add_obj($obj_to_add);
    }


    /*
     * information
     */

    /**
     * @param sql_db $db_con the active database connection
     * @return int|null the total number of formulas (without user specific changes)
     */
    function count(sql_db $db_con): ?int
    {
        return $db_con->count(sql_db::TBL_FORMULA);
    }

    /*
     * upgrade functions
     */

    function db_ref_refresh(sql_db $db_con): bool
    {
        $result = true;

        $total = $this->count($db_con);
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
            usort($this->lst, array("formula", "cmp"));
            if ($this->lst != null) {
                foreach ($this->lst as $frm) {
                    // formatting should be moved
                    //$resolved_text = str_replace('"','&quot;', $frm->usr_text);
                    //$resolved_text = str_replace('"','&quot;', $frm->dsp_text($this->back));
                    $frm_dsp = $frm->dsp_obj_old();
                    $formula_value = '';
                    if ($frm->name_wrd != null) {
                        $formula_value = $frm_dsp->dsp_result($frm->name_wrd->phrase(), $this->back);
                    }
                    // if the formula value is empty use the id to be able to select the formula
                    if ($formula_value == '') {
                        $result .= $frm_dsp->id();
                    } else {
                        $result .= ' value ' . $formula_value;
                    }
                    $result .= ' ' . $frm_dsp->name_linked($this->back);
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
        if ($total_formulas == 0) {
            $total_formulas = $db_con->count(sql_db::TBL_FORMULA);
        }
        $avg_calc_time = cfg_get(config::AVG_CALC_TIME, $db_con);
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