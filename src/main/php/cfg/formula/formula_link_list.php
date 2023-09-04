<?php

/*

    model/formula/formula_link_list.php - a list of formula word links
    -----------------------------------

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

use cfg\db\sql_creator;
use cfg\db\sql_par_type;

class formula_link_list extends sandbox_list
{

    public array $lst; // the list of formula word link objects
    public user $usr;  // the user who wants to see or modify the list


    /*
     * construct and map
     */

    /**
     * fill the formula link list based on a database records
     * @param array $db_rows is an array of an array with the database values
     * @param bool $load_all force to include also the excluded phrases e.g. for admins
     * @return bool true if at least one formula link has been added
     */
    protected function rows_mapper(array $db_rows, bool $load_all = false): bool
    {
        return parent::rows_mapper_obj(new formula_link($this->user()), $db_rows, $load_all);
    }


    /*
     * load
     */

    /**
     * set the SQL query parameters to load a list of formula links
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name of the selection fields to make the query name unique
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name): sql_par
    {
        $qp = new sql_par(self::class);
        $qp->name .= $query_name;

        $sc->set_type(formula_link::class);
        $sc->set_name($qp->name);
        $sc->set_usr($this->user()->id());
        $sc->set_fields(array(formula::FLD_ID, phrase::FLD_ID));
        $sc->set_usr_num_fields(formula_link::FLD_NAMES_NUM_USR);
        // also load the linked user specific phrase with the same SQL statement
        $sc->set_join_fields(
            phrase::FLD_NAMES,
            sql_db::TBL_PHRASE,
            phrase::FLD_ID,
            phrase::FLD_ID
        );
        $sc->set_join_usr_fields(
            phrase::FLD_NAMES_USR,
            sql_db::TBL_PHRASE,
            phrase::FLD_ID,
            phrase::FLD_ID
        );
        $sc->set_join_usr_num_fields(
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
     * @param sql_creator $sc with the target db_type set
     * @param int $frm_id the id of the formula which links should be loaded
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_frm_id(sql_creator $sc, int $frm_id): sql_par
    {
        $qp = $this->load_sql($sc, 'frm_id');
        if ($frm_id > 0) {
            $sc->add_where(formula::FLD_ID, $frm_id);
            $qp->sql = $sc->sql();
        } else {
            $qp->name = '';
        }
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * load a list of formula links with the direct linked phrases related to the given formula id
     * @param int $frm_id the id of the formula which links should be loaded
     * @return bool true if at least one word found
     */
    function load_by_frm_id(int $frm_id): bool
    {
        global $db_con;
        $qp = $this->load_sql_by_frm_id($db_con->sql_creator(), $frm_id);
        return $this->load($qp);
    }

    /**
     * get an array with all phrases linked of this list e.g. linked to one formula
     */
    function phrase_ids($sbx): phr_ids
    {
        log_debug('formula_link_list->ids');
        $result = array();
        $lib = new library();

        foreach ($this->lst() as $frm_lnk) {
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

        log_debug('got ' . $lib->dsp_count($result));
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

        foreach ($this->lst() as $frm_lnk) {
            if ($result == '') {
                if ($frm_lnk->can_change() > 0 and $frm_lnk->not_used()) {
                    //$db_con = new mysql;
                    $db_con->usr_id = $this->user()->id();
                    // delete first all user configuration that have also been excluded
                    $db_con->set_type(sql_db::TBL_USER_PREFIX . sql_db::TBL_FORMULA_LINK);
                    $result = $db_con->delete(array(formula_link::FLD_ID, sandbox::FLD_EXCLUDED), array($frm_lnk->id(), '1'));
                    if ($result == '') {
                        $db_con->set_type(sql_db::TBL_FORMULA_LINK);
                        $result = $db_con->delete(formula_link::FLD_ID, $frm_lnk->id());
                    }
                } else {
                    log_err("Cannot delete a formula word link (id " . $frm_lnk->id() . "), which is used or created by another user.", "formula_link_list->del_without_log");
                }
            }
        }

        log_debug('done');
        return $result;
    }

}