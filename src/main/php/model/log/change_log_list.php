<?php

/*

    model/log/change_log.php - read the changes from the database and forward them to the API
    ------------------------

    for writing the user change to the database the classes model/user/user_log* are used

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

use api\change_log_list_api;
use api\change_log_list_dsp;

class change_log_list extends base_list
{


    // TODO add cast
    // TODO add JSON export test
    // TODO add API controller
    // TODO add API test
    // TODO add table view
    // TODO add table view unit test
    // TODO add table view db read test


    /*
     * cast
     */

    /**
     * @return change_log_list_api the word list object with the display interface functions
     */
    function api_obj(): change_log_list_api
    {
        $api_obj = new change_log_list_api();
        foreach ($this->lst as $chg) {
            $api_obj->add($chg->api_obj());
        }
        return $api_obj;
    }

    /**
     * @return change_log_list_dsp the word list object with the display interface functions
     */
    function dsp_obj(): change_log_list_dsp
    {
        $dsp_obj = new change_log_list_dsp();
        foreach ($this->lst as $chg) {
            $dsp_obj->add($chg->dsp_obj());
        }
        return $dsp_obj;
    }


    /*
     * load
     */

    /**
     * load a list of the view changes of a word
     * @param word $wrd the word to which the view changes should be loaded
     * @param string $field_name the field that has been change e.g. 'view_id'
     *                           if not set, all changes are returned
     * @return bool true if at least one change found
     */
    function load_by_fld_of_wrd(word $wrd, string $field_name = ''): bool
    {
        global $db_con;
        $qp = $this->load_sql_obj_fld(
            $db_con,
            change_log_table::WORD,
            $field_name,
            $field_name . '_of_wrd',
            $wrd->id());
        return $this->load($qp);
    }

    /**
     * load a list of the view changes of a word
     * @param triple $trp the triple to which the view changes should be loaded
     * @param string $field_name the field that has been change e.g. 'view_id'
     *                           if not set, all changes are returned
     * @return bool true if at least one change found
     */
    function load_by_fld_of_trp(triple $trp, string $field_name = ''): bool
    {
        global $db_con;
        $qp = $this->load_sql_obj_fld(
            $db_con,
            change_log_table::TRIPLE,
            $field_name,
            $field_name . '_of_trp',
            $trp->id());
        return $this->load($qp);
    }

    /**
     * prepare sql to get the changes of one field of one user sandbox object
     * e.g. the when and how a user has changed the way a word should be shown in the user interface
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $table_name the table name of the user sandbox object e.g. 'word'
     * @param string $field_name the field that has been change e.g. 'view'
     * @param string $query_ext the name extension to make the query name unique
     * @param int $id the database id of the user sandbox object that has been changed
     * @return sql_par
     */
    public function load_sql_obj_fld(
        sql_db $db_con,
        string $table_name,
        string $field_name,
        string $query_ext,
        int    $id): sql_par
    {
        global $usr;
        global $change_log_tables;
        global $change_log_fields;

        // prepare sql to get the view changes of a triple
        $table_id = $change_log_tables->id($table_name);
        $table_field_name = $table_id . $field_name;
        $field_id = $change_log_fields->id($table_field_name);
        $log_named = new change_log_named();
        $log_named->usr = $usr;
        $qp = $log_named->load_sql($db_con, $query_ext);
        $db_con->set_page();
        $db_con->add_par(sql_db::PAR_INT, $field_id);
        $db_con->add_par(sql_db::PAR_INT, $id);
        $qp->sql = $db_con->select_by_field_list(array(
            change_log_named::FLD_FIELD_ID,
            change_log_named::FLD_ROW_ID));
        $qp->par = $db_con->get_par();
        return $qp;
    }

    /**
     * load this list of changes
     * @param sql_par $qp the SQL statement, the unique name of the SQL statement and the parameter list
     * @return bool true if at least one change found
     */
    function load(sql_par $qp): bool
    {
        global $db_con;
        $result = false;

        if ($qp->name == '') {
            log_err('The query name cannot be created to load a ' . self::class, self::class . '->load');
        } else {
            $db_rows = $db_con->get($qp);
            if ($db_rows != null) {
                foreach ($db_rows as $db_row) {
                    $chg = new change_log_named();
                    $chg->row_mapper($db_row);
                    $this->lst[] = $chg;
                    $result = true;
                }
            }
        }

        return $result;
    }

    /*
     * modification functions
     */

    /**
     * add one named change to the change list
     * @param change_log_named|null $chg_to_add the change that should be added to the list
     * @returns bool true the phrase has been added
     */
    function add(?change_log_named $chg_to_add): bool
    {
        $result = false;
        if ($chg_to_add != null) {
            parent::add_obj($chg_to_add);
            $result = true;
        }
        return $result;
    }

}