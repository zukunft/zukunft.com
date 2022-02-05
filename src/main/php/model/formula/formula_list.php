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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

class formula_list
{

    public array $lst;           // the list of the loaded formula objects
    public user $usr;            // if 0 (not NULL) for standard formulas, otherwise for a user specific formulas

    // fields to select the formulas
    public ?word $wrd = null;            // show the formulas related to this word
    public ?phrase_list $phr_lst = null; // show the formulas related to this phrase list
    public ?array $ids = array();        // a list of formula ids to load all formulas at once

    // in memory only fields
    public ?string $back = null;         // the calling stack

    /**
     * always set the user because a formula list is always user specific
     * @param user $usr the user who requested to see the formulas
     */
    function __construct(user $usr)
    {
        $this->lst = array();
        $this->usr = $usr;
    }

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
                        $frm = new formula($this->usr);
                        $frm->row_mapper($db_row);
                        if ($frm->name <> '') {
                            $name_wrd = new word_dsp($this->usr);
                            $name_wrd->name = $frm->name;
                            $name_wrd->load();
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
     * load functions
     */

    /**
     * set the SQL query parameters to load a list of formulas
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_db $db_con): sql_par
    {
        $qp = new sql_par(self::class);
        $db_con->set_type(DB_TYPE_FORMULA);
        $db_con->set_usr($this->usr->id);
        $db_con->set_name($qp->name); // assign incomplete name to force the usage of the user as a parameter
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
    function load_sql_by_frm_ids(sql_db $db_con, array $frm_ids): sql_par
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
     * set the SQL query parameters to load a set of all formulas
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param int $limit the number of formulas that should be loaded
     * @param int $page the offset
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_all(sql_db $db_con, int $limit, int $page): sql_par
    {
        $qp = $this->load_sql($db_con);
        if ($limit > 0) {
            $qp->name .= 'all';
            $db_con->set_order(formula::FLD_ID);
            $qp->sql = $db_con->select_by_field(formula::FLD_ID);
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
     * load a list of formula links with the direct linked phrases related to the given formula id
     * @param array $frm_ids an array of formula ids which should be loaded
     * @return bool true if at least one word found
     */
    function load_by_frm_ids(array $frm_ids): bool
    {
        global $db_con;
        $qp = $this->load_sql_by_frm_ids($db_con, $frm_ids);
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

    /**
     * load the missing formula parameters from the database
     * TODO: if this list contains already some formula, don't add them again!
     */
    function load()
    {

        global $db_con;

        // check the all minimal input parameters
        if (!isset($this->usr)) {
            log_err("The user id must be set to load a list of formulas.", "formula_list->load");
        } else {

            // set the where clause depending on the given selection parameters
            // default is to load all formulas to check all formula results
            $sql_from = '';
            $sql_where = 'f.formula_id > 0';
            if (count($this->ids) > 0) {
                $sql_from = 'formulas f';
                $sql_where = 'f.formula_id IN (' . sql_array($this->ids) . ')';
            } elseif (isset($this->wrd)) {
                $sql_from = 'formula_links l, formulas f';
                $sql_where = 'l.phrase_id = ' . $this->wrd->id . ' AND l.formula_id = f.formula_id';
            } elseif (isset($this->phr_lst)) {
                $phr_lst_dsp = $this->phr_lst->dsp_obj();
                if ($phr_lst_dsp->ids_txt() <> '') {
                    $sql_from = 'formula_links l, formulas f';
                    $sql_where = 'l.phrase_id IN (' . $phr_lst_dsp->ids_txt() . ') AND l.formula_id = f.formula_id';
                } else {
                    log_err("A phrase list is set (" . $this->phr_lst->dsp_id() . "), but the id list is " . $phr_lst_dsp->ids_txt() . ".", "formula_list->load");

                    $sql_from = 'formula_links l, formulas f';
                    $sql_where = 'l.formula_id = f.formula_id';
                }
            }

            if ($sql_where == '') {
                // activate this error message for page loading of the complete formula list
                log_err("Either the word or the ID list must be set for loading.", "formula_list->load");
            } else {
                log_debug('formula_list->load by (' . $sql_where . ')');
                // the formula name is excluded from the user sandbox to avoid confusion
                $sql = "SELECT f.formula_id,
                          u.formula_id AS user_formula_id,
                       f.formula_name,
                       f.user_id,
                    " . $db_con->get_usr_field('formula_text', 'f', 'u') . ",
                    " . $db_con->get_usr_field('resolved_text', 'f', 'u') . ",
                    " . $db_con->get_usr_field(sql_db::FLD_DESCRIPTION, 'f', 'u') . ",
                    " . $db_con->get_usr_field('formula_type_id', 'f', 'u', sql_db::FLD_FORMAT_VAL) . ",
                    " . $db_con->get_usr_field(sql_db::FLD_CODE_ID, 't', 'c') . ",
                    " . $db_con->get_usr_field('all_values_needed', 'f', 'u', sql_db::FLD_FORMAT_VAL) . ",
                    " . $db_con->get_usr_field('last_update', 'f', 'u', sql_db::FLD_FORMAT_VAL) . ",
                    " . $db_con->get_usr_field(user_sandbox::FLD_EXCLUDED, 'f', 'u', sql_db::FLD_FORMAT_VAL) . ",
                    " . $db_con->get_usr_field(user_sandbox::FLD_SHARE, 'f', 'u', sql_db::FLD_FORMAT_VAL) . ",
                    " . $db_con->get_usr_field(user_sandbox::FLD_PROTECT, 'f', 'u', sql_db::FLD_FORMAT_VAL) . "
                  FROM " . $sql_from . " 
             LEFT JOIN user_formulas u ON u.formula_id = f.formula_id 
                                      AND u.user_id = " . $this->usr->id . " 
             LEFT JOIN formula_types t ON f.formula_type_id = t.formula_type_id
             LEFT JOIN formula_types c ON u.formula_type_id = c.formula_type_id
                 WHERE " . $sql_where . ";";
                // GROUP BY f.formula_id;";
                $db_con->usr_id = $this->usr->id;
                $db_lst = $db_con->get_old($sql);
                $this->rows_mapper($db_lst);
            }
        }
    }

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
        return dsp_array($this->names());
    }

    /**
     * this function is called from dsp_id, so no other call is allowed
     */
    function names(): array
    {
        $result = array();
        if ($this->lst != null) {
            foreach ($this->lst as $frm) {
                $result[] = $frm->name;
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

        if (isset($this->wrd)) {
            // list all related formula results
            if ($this->lst != null) {
                usort($this->lst, array("formula", "cmp"));
                if ($this->lst != null) {
                    foreach ($this->lst as $frm) {
                        // formatting should be moved
                        //$resolved_text = str_replace('"','&quot;', $frm->usr_text);
                        //$resolved_text = str_replace('"','&quot;', $frm->dsp_text($this->back));
                        $frm_dsp = $frm->dsp_obj();
                        $formula_value = $frm_dsp->dsp_result($this->wrd, $this->back);
                        // if the formula value is empty use the id to be able to select the formula
                        if ($formula_value == '') {
                            $result .= $frm_dsp->id;
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
        }

        log_debug("formula_list->display ... done (" . $result . ")");
        return $result;
    }

}