<?php

/*

  zu_lib_sql_code_link.php - SQL code link 
  ________________________

  to link a database row with the program code with the field "code_id"
  e.g. the verb "alias" is used to combine "mio" with "millions"
  this module contains all const and the related functions where a row in the database is link to a special behaviour
  the const is the code_id that is also shown to to user if he/she wants to change the name

  functions
  ---------
  
  sql_code_link - get the id for a predefined item
  cl            - shortcut for sql_Code_Link
  
  Const
  -----
  
  DBL - DataBase Link: link to the database over the special field code_id

  TODO load all code links once at program start, because the ID will never change within the same instance

  TODO use cases:
    these the optimal tax rates are
        from -10% needed to fulfill the basic needed
        to 99% for everything more than the community is able to invest to save one live
        reason: this is the optimal combination between safety and prestige

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
  
  Copyright (c) 1995-2021 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

const LOG_LEVEL = "log_warning"; // starting from this criticality level messages are written to the log for debugging
const MSG_LEVEL = "error";       // in case of an error or fatal error
// additional the message a link to the system log shown
// so that the user can track when the error is solved

// addition reserved field names for zukunft
const DBL_FIELD = "code_id";


// move to reaL EMUN, because it is only internal
const DBL_FORMULA_PART_TYPE_WORD = "frm_elm_word";
const DBL_FORMULA_PART_TYPE_VERB = "frm_elm_verb";
const DBL_FORMULA_PART_TYPE_FORMULA = "frm_elm_formula";

// predefined words
// TODO move to word type
const DBL_WORD_OTHER = "other";  // replaced by a word type

// single special users
// TODO move to user role
const DBL_USER_SYSTEM_TEST = "usr_system_test";
const DBL_USER_SYSTEM = "usr_system";

// system log types
// TODO use a real enum because it is internal and no user can add something new
const DBL_SYSLOG_INFO = "log_info";
const DBL_SYSLOG_WARNING = "log_warning";
const DBL_SYSLOG_ERROR = "log_error";
const DBL_SYSLOG_FATAL_ERROR = "log_fatal";

// TODO use a real enum because it is internal and no user can add something new
const DBL_SYSLOG_TBL_USR = "users";
const DBL_SYSLOG_TBL_VALUE = "values";
const DBL_SYSLOG_TBL_VALUE_USR = "user_values";
const DBL_SYSLOG_TBL_VALUE_LINK = "value_links";
const DBL_SYSLOG_TBL_WORD = "words";
const DBL_SYSLOG_TBL_WORD_USR = "user_words";
const DBL_SYSLOG_TBL_WORD_LINK = "word_links";
const DBL_SYSLOG_TBL_WORD_LINK_USR = "user_word_links";
const DBL_SYSLOG_TBL_FORMULA = "formulas";
const DBL_SYSLOG_TBL_FORMULA_USR = "user_formulas";
const DBL_SYSLOG_TBL_FORMULA_LINK = "formula_links";
const DBL_SYSLOG_TBL_FORMULA_LINK_USR = "user_formula_links";
const DBL_SYSLOG_TBL_VIEW = "views";
const DBL_SYSLOG_TBL_VIEW_USR = "user_views";
const DBL_SYSLOG_TBL_VIEW_LINK = "view_component_links";
const DBL_SYSLOG_TBL_VIEW_LINK_USR = "user_view_component_links";
const DBL_SYSLOG_TBL_VIEW_COMPONENT = "view_components";
const DBL_SYSLOG_TBL_VIEW_COMPONENT_USR = "user_view_components";


// the batch job types to keep the dependencies updated and the database clean
// TODO use a real enum because it is internal and no user can add something new
const DBL_JOB_VALUE_UPDATE = "job_value_update";
const DBL_JOB_VALUE_ADD = "job_value_add";
const DBL_JOB_VALUE_DEL = "job_value_del";
const DBL_JOB_FORMULA_UPDATE = "job_formula_update";
const DBL_JOB_FORMULA_ADD = "job_formula_add";
const DBL_JOB_FORMULA_DEL = "job_formula_del";
const DBL_JOB_FORMULA_LINK = "job_formula_link";
const DBL_JOB_FORMULA_UNLINK = "job_formula_unlink";
const DBL_JOB_WORD_LINK = "job_word_link";
const DBL_JOB_WORD_UNLINK = "job_word_unlink";


// fixed settings without code id for the triple links
// TODO use a real enum because it is internal and no user can add something new
const DBL_TRIPLE_LINK_IS_WORD = 1;
const DBL_TRIPLE_LINK_IS_TRIPLE = 2;
const DBL_TRIPLE_LINK_IS_GROUP = 3;

// table fields where the change should be encoded before shown to the user
// e.g. the "calculate only if all values used in the formula exist" flag should be converted to "all needed for calculation" instead of just displaying "1"
const DBL_FLD_FORMULA_ALL_NEEDED = "all_values_needed";
const DBL_FLD_FORMULA_TYPE = "frm_type";
// e.g. the formula field "ref_txt" is a more internal field, which should not be shown to the user (only to an admin for debugging)
const DBL_FLD_FORMULA_REF_TEXT = "ref_text";


// global list of database values that cannot be changed by the user 
// these need to be loaded only once to the frontend because only a system upgrade can change them
$dbl_protection_types = array();

// shortcut name for sql_code_link for better code reading
// don't use it for the first call to make sure that the description is in the database
function clo($code_id)
{
    global $db_con;
    return sql_code_link($code_id, "", $db_con);
}

// return the default description for any code link
function sql_code_link_description($code_id): string
{
    $result = '';

    switch ($code_id) {

        // system log
        case DBL_SYSLOG_INFO:
            $result = 'info';
            break;
        case DBL_SYSLOG_WARNING:
            $result = 'Warning';
            break;
        case DBL_SYSLOG_ERROR:
            $result = 'Error';
            break;
        case DBL_SYSLOG_FATAL_ERROR:
            $result = 'FATAL ERROR';
            break;
    }

    return $result;
}


// TODO check automatically that the code links are unique
/**
 * returns the pk / row_id for a given code_id
 * if the code_id does not exist the missing record is created
 * the code_id is always saved in the 20 char long field code_id
 * the short form is the cl function without description and db_con as parameter
 *
 * @param $code_id
 * @param $description
 * @param $db_con
 * @return int|mixed
 */
function sql_code_link($code_id, $description, $db_con)
{
    log_debug("sql_code_link (" . $code_id . "," . $description . ")");

    global $word_types_hash;

    $row_id = 0;

    // set the table name and the id field
    $table_name = '';
    $db_type = '';
    if ($code_id == DBL_FORMULA_PART_TYPE_WORD
        or $code_id == DBL_FORMULA_PART_TYPE_VERB
        or $code_id == DBL_FORMULA_PART_TYPE_FORMULA) {
        $db_type = DB_TYPE_FORMULA_ELEMENT_TYPE;
    }
    if ($code_id == DBL_SYSLOG_INFO
        or $code_id == DBL_SYSLOG_WARNING
        or $code_id == DBL_SYSLOG_ERROR
        or $code_id == DBL_SYSLOG_FATAL_ERROR
        or $code_id == LOG_LEVEL) {
        $db_type = "sys_log_type";
    }

    if ($code_id == DBL_USER_SYSTEM) {
        $db_type = "user";
    }

    if ($code_id == DBL_SYSLOG_TBL_VALUE
        or $code_id == DBL_SYSLOG_TBL_VALUE_USR
        or $code_id == DBL_SYSLOG_TBL_VALUE_LINK
        or $code_id == DBL_SYSLOG_TBL_WORD
        or $code_id == DBL_SYSLOG_TBL_WORD_USR
        or $code_id == DBL_SYSLOG_TBL_WORD_LINK
        or $code_id == DBL_SYSLOG_TBL_WORD_LINK_USR
        or $code_id == DBL_SYSLOG_TBL_FORMULA
        or $code_id == DBL_SYSLOG_TBL_FORMULA_USR
        or $code_id == DBL_SYSLOG_TBL_FORMULA_LINK
        or $code_id == DBL_SYSLOG_TBL_FORMULA_LINK_USR
        or $code_id == DBL_SYSLOG_TBL_VIEW
        or $code_id == DBL_SYSLOG_TBL_VIEW_USR
        or $code_id == DBL_SYSLOG_TBL_VIEW_LINK
        or $code_id == DBL_SYSLOG_TBL_VIEW_LINK_USR
        or $code_id == DBL_SYSLOG_TBL_VIEW_COMPONENT
        or $code_id == DBL_SYSLOG_TBL_VIEW_COMPONENT_USR) {
        $db_type = "change_table";
    }

    if ($code_id == DBL_JOB_VALUE_UPDATE
        or $code_id == DBL_JOB_VALUE_ADD
        or $code_id == DBL_JOB_VALUE_DEL
        or $code_id == DBL_JOB_FORMULA_UPDATE
        or $code_id == DBL_JOB_FORMULA_ADD
        or $code_id == DBL_JOB_FORMULA_DEL
        or $code_id == DBL_JOB_FORMULA_LINK
        or $code_id == DBL_JOB_FORMULA_UNLINK
        or $code_id == DBL_JOB_WORD_LINK
        or $code_id == DBL_JOB_WORD_UNLINK) {
        $db_type = "calc_and_cleanup_task_type";
    }

    /*  if ($code_id == EVENT_TYPE_TRADE_MISSING
     OR $code_id == EVENT_TYPE_SQL_ERROR_ID
     OR $code_id == EVENT_TYPE_SYSTEM_EVENT
     OR $code_id == EVENT_TYPE_USER_DAILY
     OR $code_id == EVENT_TYPE_EXPOSURE_LIMIT) {
      $table_name = "event_types";
      $id_field   = "event_type_id";
    } */

    if ($table_name == '' and $db_type == '') {
        log_debug('table name for code_id ' . $code_id . ' (' . $db_type . ') not found <br>');
    } else {
        // get the preloaded types directly from the hash
        if ($db_type == DB_TYPE_WORD_TYPE) {
            $row_id = $word_types_hash[$code_id];
        } else {
            //$db_con = new mysql;
            // remember the db_type
            $db_value_type = $db_con->get_type();
            $db_con->usr_id = SYSTEM_USER_ID;
            $db_con->set_type($db_type);

            // get the row_id
            $row_id = $db_con->get_id_from_code($code_id);

            // insert the missing row if needed
            if ($row_id <= 0) {
                if ($db_type == 'view') {
                    $db_con->insert(array(DBL_FIELD, 'user_id'), array($code_id, SYSTEM_USER_ID));
                } else {
                    // TODO for sys_log_type include the name db field
                    $db_con->insert(DBL_FIELD, $code_id);
                }
                log_debug('inserted ' . $code_id . '<br>');
                // get the id of the inserted row
                $row_id = $db_con->get_id_from_code($code_id);
                log_debug('inserted ' . $code_id . ' as ' . $row_id . '<br>');
            } else {
                log_debug('found ' . $code_id . ' as ' . $row_id . '<br>');
            }

            // set the name as default
            if ($row_id > 0 and $description <> '') {
                $row_name = $db_con->get_name($row_id);
                if ($row_name == '') {
                    log_debug('add ' . $description . '<br>');
                    $db_con->update_name($row_id, $description);
                }
            }
            // restore the db_type
            $db_con->set_type($db_value_type);
        }
    }

    log_debug("sql_code_link ... done");

    return $row_id;
}
