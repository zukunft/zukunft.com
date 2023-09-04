<?php

/*

    model/log/change_log_list.php - read the changes from the database and forward them to the API
    -----------------------------

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

namespace cfg;

include_once DB_PATH . 'sql_par_type.php';
include_once MODEL_SYSTEM_PATH . 'base_list.php';
include_once API_LOG_PATH . 'change_log_list.php';
include_once WEB_LOG_PATH . 'change_log_list.php';
include_once MODEL_SYSTEM_PATH . 'base_list.php';

use api\change_log_list_api;
use cfg\component\component;
use cfg\db\sql_creator;
use html\log\change_log_list as change_log_list_dsp;

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
        foreach ($this->lst() as $chg) {
            $api_obj->add($chg->api_obj());
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
     * @return change_log_list_dsp the word list object with the display interface functions
     */
    function dsp_obj(): change_log_list_dsp
    {
        $dsp_obj = new change_log_list_dsp();
        foreach ($this->lst() as $chg) {
            $dsp_obj->add($chg->dsp_obj());
        }
        return $dsp_obj;
    }


    /*
     * load interface
     */

    /**
     * load a list of the view changes of a word
     * @param word $wrd the word to which the view changes should be loaded
     * @param string $field_name the field that has been change e.g. 'view_id'
     *                           if not set, all changes are returned
     * @return bool true if at least one change found
     */
    function load_by_fld_of_wrd(word $wrd, user $usr, string $field_name = ''): bool
    {
        global $db_con;
        $qp = $this->load_sql_obj_fld(
            $db_con->sql_creator(),
            change_log_table::WORD,
            $field_name,
            $wrd->id(),
            $usr);
        return $this->load($qp);
    }

    /**
     * load a list of the view changes of a verb
     * @param verb $trp the verb to which the view changes should be loaded
     * @param string $field_name the field that has been change e.g. 'verb_name'
     *                           if not set, all changes are returned
     * @return bool true if at least one change found
     */
    function load_by_fld_of_vrb(verb $trp, user $usr, string $field_name = ''): bool
    {
        global $db_con;
        $qp = $this->load_sql_obj_fld(
            $db_con->sql_creator(),
            change_log_table::VERB,
            $field_name,
            $trp->id(),
            $usr);
        return $this->load($qp);
    }

    /**
     * load a list of the view changes of a triple
     * @param triple $trp the triple to which the view changes should be loaded
     * @param string $field_name the field that has been change e.g. 'view_id'
     *                           if not set, all changes are returned
     * @return bool true if at least one change found
     */
    function load_by_fld_of_trp(triple $trp, user $usr, string $field_name = ''): bool
    {
        global $db_con;
        $qp = $this->load_sql_obj_fld(
            $db_con->sql_creator(),
            change_log_table::TRIPLE,
            $field_name,
            $trp->id(),
            $usr);
        return $this->load($qp);
    }

    /**
     * load a list of the view changes of a value
     * @param value $val the value to which the view changes should be loaded
     * @param string $field_name the field that has been change e.g. 'numeric_value'
     *                           if not set, all changes are returned
     * @return bool true if at least one change found
     */
    function load_by_fld_of_val(value $val, user $usr, string $field_name = ''): bool
    {
        global $db_con;
        $qp = $this->load_sql_obj_fld(
            $db_con->sql_creator(),
            change_log_table::VALUE,
            $field_name,
            $val->id(),
            $usr);
        return $this->load($qp);
    }

    /**
     * load a list of the view changes of a formula
     * @param formula $trp the formula to which the view changes should be loaded
     * @param string $field_name the field that has been change e.g. 'view_id'
     *                           if not set, all changes are returned
     * @return bool true if at least one change found
     */
    function load_by_fld_of_frm(formula $trp, user $usr, string $field_name = ''): bool
    {
        global $db_con;
        $qp = $this->load_sql_obj_fld(
            $db_con->sql_creator(),
            change_log_table::FORMULA,
            $field_name,
            $trp->id(),
            $usr);
        return $this->load($qp);
    }

    /**
     * load a list of the view changes of a source
     * @param source $src the source to which the view changes should be loaded
     * @param string $field_name the field that has been change e.g. 'view_id'
     *                           if not set, all changes are returned
     * @return bool true if at least one change found
     */
    function load_by_fld_of_src(source $src, user $usr, string $field_name = ''): bool
    {
        global $db_con;
        $qp = $this->load_sql_obj_fld(
            $db_con->sql_creator(),
            change_log_table::SOURCE,
            $field_name,
            $src->id(),
            $usr);
        return $this->load($qp);
    }

    /**
     * load a list of the view changes of a view
     * @param view $dsp the view to which the view changes should be loaded
     * @param string $field_name the field that has been change e.g. 'view_id'
     *                           if not set, all changes are returned
     * @return bool true if at least one change found
     */
    function load_by_fld_of_dsp(view $dsp, user $usr, string $field_name = ''): bool
    {
        global $db_con;
        $qp = $this->load_sql_obj_fld(
            $db_con->sql_creator(),
            change_log_table::VIEW,
            $field_name,
            $dsp->id(),
            $usr);
        return $this->load($qp);
    }

    /**
     * load a list of the view changes of a view component
     * @param component $cmp the view to which the view component changes should be loaded
     * @param string $field_name the field that has been change e.g. 'view_id'
     *                           if not set, all changes are returned
     * @return bool true if at least one change found
     */
    function load_by_fld_of_cmp(component $cmp, user $usr, string $field_name = ''): bool
    {
        global $db_con;
        $qp = $this->load_sql_obj_fld(
            $db_con->sql_creator(),
            change_log_table::VIEW_COMPONENT,
            $field_name,
            $cmp->id(),
            $usr);
        return $this->load($qp);
    }


    /*
     * load internals
     */

    private function table_field_to_query_name(string $table_name, string $field_name): string
    {
        $result = '';
        if ($table_name == change_log_table::WORD) {
            if ($field_name == change_log_field::FLD_WORD_VIEW) {
                $result = 'dsp_of_wrd';
            } else {
                $result = $field_name . '_of_wrd';
                log_info('field name ' . $field_name . ' not expected for table ' . $table_name);
            }
        } elseif ($table_name == change_log_table::TRIPLE) {
            if ($field_name == change_log_field::FLD_TRIPLE_VIEW) {
                $result = 'dsp_of_trp';
            } else {
                $result = $field_name . '_of_trp';
                log_info('field name ' . $field_name . ' not expected for table ' . $table_name);
            }
        } elseif ($table_name == change_log_table::VERB) {
            $result = $field_name . '_of_vrb';
            log_info('field name ' . $field_name . ' not expected for table ' . $table_name);
        } elseif ($table_name == change_log_table::VALUE) {
            $result = $field_name . '_of_val';
            log_info('field name ' . $field_name . ' not expected for table ' . $table_name);
        } elseif ($table_name == change_log_table::FORMULA) {
            $result = $field_name . '_of_frm';
            log_info('field name ' . $field_name . ' not expected for table ' . $table_name);
        } elseif ($table_name == change_log_table::SOURCE) {
            $result = $field_name . '_of_src';
            log_info('field name ' . $field_name . ' not expected for table ' . $table_name);
        } elseif ($table_name == change_log_table::VIEW) {
            $result = $field_name . '_of_dsp';
            log_info('field name ' . $field_name . ' not expected for table ' . $table_name);
        } elseif ($table_name == change_log_table::VIEW_COMPONENT) {
            $result = $field_name . '_of_cmp';
            log_info('field name ' . $field_name . ' not expected for table ' . $table_name);
        } else {
            log_err('table name ' . $table_name . ' not expected');
        }
        return $result;
    }

    /**
     * prepare sql to get the changes of one field of one user sandbox object
     * e.g. the when and how a user has changed the way a word should be shown in the user interface
     * only public for SQL unit testing
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $table_name the table name of the user sandbox object e.g. 'word'
     * @param string $field_name the field that has been change e.g. 'view'
     * @param int $id the database id of the user sandbox object that has been changed
     * @param user $usr
     * @return sql_par
     */
    function load_sql_obj_fld(
        sql_creator $sc,
        string $table_name,
        string $field_name,
        int    $id,
        user   $usr): sql_par
    {
        global $change_log_tables;
        global $change_log_fields;

        // prepare sql to get the view changes of a user sandbox object e.g. word
        $table_id = $change_log_tables->id($table_name);
        $table_field_name = $table_id . $field_name;
        $field_id = $change_log_fields->id($table_field_name);
        $log_named = new change_log_named($usr);
        $query_ext = $this->table_field_to_query_name($table_name, $field_name);
        $qp = $log_named->load_sql($sc, $query_ext, self::class);
        $sc->add_where(change_log_named::FLD_FIELD_ID, $field_id);
        $sc->add_where(change_log_named::FLD_ROW_ID, $id);
        $sc->set_page($this->limit, $this->offset());
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * load this list of changes
     * @param sql_par $qp the SQL statement, the unique name of the SQL statement and the parameter list
     * @return bool true if at least one change found
     */
    private function load(sql_par $qp): bool
    {
        global $db_con;
        global $usr;
        $result = false;

        if ($qp->name == '') {
            log_err('The query name cannot be created to load a ' . self::class, self::class . '->load');
        } else {
            $db_rows = $db_con->get($qp);
            if ($db_rows != null) {
                foreach ($db_rows as $db_row) {
                    $chg = new change_log_named($usr);
                    $chg->row_mapper($db_row);
                    $this->add_obj($chg);
                    $result = true;
                }
            }
        }

        return $result;
    }


    /*
     * modify
     */

    /**
     * add one change log entry to the change list
     * @param change_log_named|null $chg_to_add the change that should be added to the list
     * @returns bool true the log entry has been added
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