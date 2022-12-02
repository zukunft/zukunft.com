<?php

/*

    formula_link_list.php - a list of formula word links
    ---------------------

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

class formula_link_list extends sandbox_list
{

    public array $lst; // the list of formula word link objects
    public user $usr;  // the user who wants to see or modify the list

    /**
     * fill the formula link list based on a database records
     * @param array $db_rows is an array of an array with the database values
     * @return bool true if at least one word found
     */
    private function rows_mapper(array $db_rows): bool
    {
        $result = false;
        if ($db_rows != null) {
            foreach ($db_rows as $db_row) {
                if ($db_row[formula_link::FLD_ID] > 0) {
                    $frm_lnk = new formula_link($this->user());
                    $frm_lnk->row_mapper($db_row);
                    $this->lst[] = $frm_lnk;
                    $result = true;
                }
            }
        }
        return $result;
    }

    /*
     * load functions
     */

    /**
     * set the SQL query parameters to load a list of formula links
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_db $db_con): sql_par
    {
        $db_con->set_type(sql_db::TBL_FORMULA_LINK);
        $qp = new sql_par(self::class);
        $db_con->set_name($qp->name); // assign incomplete name to force the usage of the user as a parameter
        $db_con->set_usr($this->user()->id);
        $db_con->set_link_fields(formula::FLD_ID, phrase::FLD_ID);
        $db_con->set_usr_num_fields(formula_link::FLD_NAMES_NUM_USR);
        // also load the linked user specific phrase with the same SQL statement
        $db_con->set_join_fields(
            phrase::FLD_NAMES,
            sql_db::TBL_PHRASE,
            phrase::FLD_ID,
            phrase::FLD_ID
        );
        $db_con->set_join_usr_fields(
            phrase::FLD_NAMES_USR,
            sql_db::TBL_PHRASE,
            phrase::FLD_ID,
            phrase::FLD_ID
        );
        $db_con->set_join_usr_num_fields(
            phrase::FLD_NAMES_NUM_USR,
            sql_db::TBL_PHRASE,
            phrase::FLD_ID,
            phrase::FLD_ID,
            true
        );
        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of formula links by the formula id
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param int $frm_id the id of the formula which links should be loaded
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_frm_id(sql_db $db_con, int $frm_id): sql_par
    {
        $qp = $this->load_sql($db_con);
        if ($frm_id > 0) {
            $qp->name .= 'frm_id';
            $db_con->set_name($qp->name);
            $db_con->add_par(sql_db::PAR_INT, $frm_id);
            $qp->sql = $db_con->select_by_field(formula::FLD_ID);
        } else {
            $qp->name = '';
        }
        $qp->par = $db_con->get_par();
        return $qp;
    }

    /**
     * load a list of formula links with the direct linked phrases
     * @param sql_par $qp the SQL statement, the unique name of the SQL statement and the parameter list
     * @return bool true if at least one formula link has been loaded
     */
    private function load(sql_par $qp): bool
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
     * @param int $frm_id the id of the formula which links should be loaded
     * @return bool true if at least one word found
     */
    function load_by_frm_id(int $frm_id): bool
    {
        global $db_con;
        $qp = $this->load_sql_by_frm_id($db_con, $frm_id);
        return $this->load($qp);
    }

    /**
     * get an array with all phrases linked of this list e.g. linked to one formula
     */
    function phrase_ids($sbx): phr_ids
    {
        log_debug('formula_link_list->ids');
        $result = array();

        foreach ($this->lst as $frm_lnk) {
            if ($frm_lnk->phrase_id() <> 0) {
                if ($sbx) {
                    if ($frm_lnk->excluded <= 0) {
                        $result[] = $frm_lnk->phrase_id();
                    }
                } else {
                    $result[] = $frm_lnk->phrase_id();
                }
            }
        }

        log_debug('formula_link_list->ids -> got ' . dsp_count($result));
        return (new phr_ids($result));
    }

    /**
     * delete all links without log because this is used only when deleting a formula
     * and the main event of deleting the formula is already logged
     */
    function del_without_log(): string
    {
        log_debug('formula_link_list->del_without_log');

        global $db_con;
        $result = '';

        foreach ($this->lst as $frm_lnk) {
            if ($result == '') {
                if ($frm_lnk->can_change() > 0 and $frm_lnk->not_used()) {
                    //$db_con = new mysql;
                    $db_con->usr_id = $this->user()->id;
                    // delete first all user configuration that have also been excluded
                    $db_con->set_type(sql_db::TBL_USER_PREFIX . sql_db::TBL_FORMULA_LINK);
                    $result = $db_con->delete(array(formula_link::FLD_ID, user_sandbox::FLD_EXCLUDED), array($frm_lnk->id(), '1'));
                    if ($result == '') {
                        $db_con->set_type(sql_db::TBL_FORMULA_LINK);
                        $result = $db_con->delete(formula_link::FLD_ID, $frm_lnk->id());
                    }
                } else {
                    log_err("Cannot delete a formula word link (id " . $frm_lnk->id() . "), which is used or created by another user.", "formula_link_list->del_without_log");
                }
            }
        }

        log_debug('formula_link_list->del_without_log -> done');
        return $result;
    }

}