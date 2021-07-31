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
const MSG_LEVEL = "error";       // in case of a error or fatal error
// additional the message a link to the system log shown
// so that the user can track when the error is solved

// addition reserved field names for zukunft
const DBL_FIELD = "code_id";


// link to the predefined edit views
// the code ids must unique over all types 
const DBL_VIEW_START = "dsp_start";
const DBL_VIEW_WORD = "dsp_word";
const DBL_VIEW_WORD_ADD = "dsp_word_add";
const DBL_VIEW_WORD_EDIT = "dsp_word_edit";
const DBL_VIEW_WORD_FIND = "dsp_word_find";
const DBL_VIEW_WORD_DEL = "dsp_word_del";
const DBL_VIEW_VALUE_ADD = "dsp_value_add";
const DBL_VIEW_VALUE_EDIT = "dsp_value_edit";
const DBL_VIEW_VALUE_DEL = "dsp_value_del";
const DBL_VIEW_VALUE_DISPLAY = "dsp_value";
const DBL_VIEW_FORMULA_ADD = "dsp_formula_add";
const DBL_VIEW_FORMULA_EDIT = "dsp_formula_edit";
const DBL_VIEW_FORMULA_DEL = "dsp_formula_del";
const DBL_VIEW_FORMULA_EXPLAIN = "dsp_formula_explain";
const DBL_VIEW_FORMULA_TEST = "dsp_formula_test";
const DBL_VIEW_SOURCE_ADD = "dsp_source_add";
const DBL_VIEW_SOURCE_EDIT = "dsp_source_edit";
const DBL_VIEW_SOURCE_DEL = "dsp_source_del";
const DBL_VIEW_VERBS = "dsp_verbs";
const DBL_VIEW_VERB_ADD = "dsp_verb_add";
const DBL_VIEW_VERB_EDIT = "dsp_verb_edit";
const DBL_VIEW_VERB_DEL = "dsp_verb_del";
const DBL_VIEW_LINK_ADD = "dsp_triple_add";
const DBL_VIEW_LINK_EDIT = "dsp_triple_edit";
const DBL_VIEW_LINK_DEL = "dsp_triple_del";
const DBL_VIEW_USER = "dsp_user";
const DBL_VIEW_ERR_LOG = "dsp_error_log";
const DBL_VIEW_ERR_UPD = "dsp_error_update";
const DBL_VIEW_IMPORT = "dsp_import";
// views to edit views                     
const DBL_VIEW_ADD = "dsp_view_add";
const DBL_VIEW_EDIT = "dsp_view_edit";
const DBL_VIEW_DEL = "dsp_view_del";
const DBL_VIEW_COMPONENT_ADD = "dsp_view_entry_add";
const DBL_VIEW_COMPONENT_EDIT = "dsp_view_entry_edit";
const DBL_VIEW_COMPONENT_DEL = "dsp_view_entry_del";
// views types; using view type instead of a single view, because there maybe several default views for words
const DBL_VIEW_TYPE_DEFAULT = "dsp_type_default";
const DBL_VIEW_TYPE_ENTRY = "dsp_type_entry";
const DBL_VIEW_TYPE_MASK_DEFAULT = "dsp_type_mask_default";
const DBL_VIEW_TYPE_PRESENT = "dsp_type_presentation";
const DBL_VIEW_TYPE_WORD_DEFAULT = "dsp_type_word_default";

// views component types                  
const DBL_VIEW_COMP_TYPE_TEXT = "dsp_comp_type_text";
const DBL_VIEW_COMP_TYPE_WORD = "dsp_comp_type_fixed";
const DBL_VIEW_COMP_TYPE_WORD_SELECT = "dsp_comp_type_word_select";
const DBL_VIEW_COMP_TYPE_WORDS_UP = "dsp_comp_type_word_list_up";
const DBL_VIEW_COMP_TYPE_WORDS_DOWN = "dsp_comp_type_word_list_down";
const DBL_VIEW_COMP_TYPE_WORD_NAME = "dsp_comp_type_word_name";
const DBL_VIEW_COMP_TYPE_WORD_VALUE = "dsp_comp_type_word_value_list"; // a list of
const DBL_VIEW_COMP_TYPE_VALUES_ALL = "dsp_comp_type_values_all";
const DBL_VIEW_COMP_TYPE_VALUES_RELATED = "dsp_comp_type_values_related";
const DBL_VIEW_COMP_TYPE_FORMULAS = "dsp_comp_type_formula_list";
const DBL_VIEW_COMP_TYPE_FORMULA_RESULTS = "dsp_comp_type_formula_results";
const DBL_VIEW_COMP_TYPE_JSON_EXPORT = "dsp_comp_type_json_export";
const DBL_VIEW_COMP_TYPE_XML_EXPORT = "dsp_comp_type_xml_export";
const DBL_VIEW_COMP_TYPE_CSV_EXPORT = "dsp_comp_type_csv_export";
const DBL_VIEW_COMP_TYPE_VIEW_SELECT = "dsp_comp_type_view_select";
const DBL_VIEW_COMP_TYPE_LINK = "dsp_comp_type_link";

const DBL_WORD_TYPE_NORMAL = "wrd_type_default";
const DBL_WORD_TYPE_TIME = "wrd_type_time";
const DBL_WORD_TYPE_TIME_JUMP = "wrd_type_time_jump";
const DBL_WORD_TYPE_PERCENT = "wrd_type_percent";
const DBL_WORD_TYPE_MEASURE = "wrd_type_measure";
const DBL_WORD_TYPE_SCALING = "wrd_type_scaling";
const DBL_WORD_TYPE_SCALING_HIDDEN = "wrd_type_scaling_hidden";
const DBL_WORD_TYPE_SCALING_PCT = "wrd_type_scaling_percent";
const DBL_WORD_TYPE_SCALED_MEASURE = "wrd_type_scaled_measure";
const DBL_WORD_TYPE_FORMULA_LINK = "wrd_type_formula_link";
const DBL_WORD_TYPE_CALC = "wrd_type_calc";
const DBL_WORD_TYPE_LAYER = "wrd_type_view";
const DBL_WORD_TYPE_OTHER = "wrd_type_type_other";
const DBL_WORD_TYPE_NEXT = "wrd_type_next";
const DBL_WORD_TYPE_THIS = "wrd_type_this";
const DBL_WORD_TYPE_PREV = "wrd_type_previous";

const DBL_FORMULA_TYPE_CALC= "frm_type_default";
const DBL_FORMULA_TYPE_NEXT = "frm_type_time_next";
const DBL_FORMULA_TYPE_THIS = "frm_type_time_this";
const DBL_FORMULA_TYPE_PREV = "frm_type_time_prior";
const DBL_FORMULA_TYPE_CONST = "frm_type_const";

const DBL_FORMULA_PART_TYPE_WORD = "frm_elm_word";
const DBL_FORMULA_PART_TYPE_VERB = "frm_elm_verb";
const DBL_FORMULA_PART_TYPE_FORMULA = "frm_elm_formula";

// predefined word link types or verbs    
const DBL_LINK_TYPE_IS = "vrb_is";
const DBL_LINK_TYPE_CONTAIN = "vrb_contains";
const DBL_LINK_TYPE_FOLLOW = "vrb_follow";
const DBL_LINK_TYPE_DIFFERENTIATOR = "vrb_can_contain";
const DBL_LINK_TYPE_CAN_BE = "vrb_can_be";

// predefined words                       
const DBL_WORD_OTHER = "other";  // replaced by a word type

// share types                            
const DBL_SHARE_PUBLIC = "share_public";
const DBL_SHARE_PERSONAL = "share_personal";
const DBL_SHARE_GROUP = "share_group";
const DBL_SHARE_PRIVATE = "share_private";

// protection types                            
const DBL_PROTECT_NO = "no_protection";
const DBL_PROTECT_USER = "user_protection";
const DBL_PROTECT_ADMIN = "admin_protection";
const DBL_PROTECT_NO_CHANGE = "no_change";

// links to external systems
const DBL_REF_WIKIPEDIA = "ref_wikipedia";
const DBL_REF_WIKIDATA = "ref_wikidata";

// user profiles                          
const DBL_USER_NORMAL = "usr_role_normal";
const DBL_USER_ADMIN = "usr_role_admin";
const DBL_USER_DEV = "usr_role_dev";

// single special users                   
const DBL_USER_SYSTEM_TEST = "usr_system_test";
const DBL_USER_SYSTEM = "usr_system";

// system log stati                       
const DBL_ERR_NEW = "log_status_new";
const DBL_ERR_ASSIGNED = "log_status_assigned";
const DBL_ERR_RESOLVED = "log_status_resolved";
const DBL_ERR_CLOSED = "log_status_closed";

// system log types                       
const DBL_SYSLOG_INFO = "log_info";
const DBL_SYSLOG_WARNING = "log_warning";
const DBL_SYSLOG_ERROR = "log_error";
const DBL_SYSLOG_FATAL_ERROR = "log_fatal";

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
const DBL_TRIPLE_LINK_IS_WORD = 1;
const DBL_TRIPLE_LINK_IS_TRIPLE = 2;
const DBL_TRIPLE_LINK_IS_GROUP = 3;

// table fields where the change should be encoded before shown to the user
// e.g. the "calculate only if all values used in the formula exist" flag should be converted to "all needed for calculation" instead of just displaying "1"
const DBL_FLD_FORMULA_ALL_NEEDED = "all_values_needed";
const DBL_FLD_FORMULA_TYPE = "frm_type";
// e.g. the formula field "ref_txt" is more a internal field, which should not be shown to the user (only to an admin for debugging)
const DBL_FLD_FORMULA_REF_TEXT = "ref_text";


// global list of database values that cannot be changed by the user 
// these need to be loaded only once to the frontend because only a system upgrade can change them
$dbl_protection_types = array();

// shortcut name for sql_code_link for better code reading
// don't use it for the first call to make sure that the description is in the database
function cl($code_id)
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
    if ($code_id == DBL_VIEW_START
        or $code_id == DBL_VIEW_WORD
        or $code_id == DBL_VIEW_WORD_ADD
        or $code_id == DBL_VIEW_WORD_EDIT
        or $code_id == DBL_VIEW_WORD_FIND
        or $code_id == DBL_VIEW_WORD_DEL
        or $code_id == DBL_VIEW_VALUE_ADD
        or $code_id == DBL_VIEW_VALUE_EDIT
        or $code_id == DBL_VIEW_VALUE_DEL
        or $code_id == DBL_VIEW_VALUE_DISPLAY
        or $code_id == DBL_VIEW_FORMULA_ADD
        or $code_id == DBL_VIEW_FORMULA_EDIT
        or $code_id == DBL_VIEW_FORMULA_DEL
        or $code_id == DBL_VIEW_FORMULA_EXPLAIN
        or $code_id == DBL_VIEW_FORMULA_TEST
        or $code_id == DBL_VIEW_SOURCE_ADD
        or $code_id == DBL_VIEW_SOURCE_EDIT
        or $code_id == DBL_VIEW_SOURCE_DEL
        or $code_id == DBL_VIEW_VERBS
        or $code_id == DBL_VIEW_VERB_ADD
        or $code_id == DBL_VIEW_VERB_EDIT
        or $code_id == DBL_VIEW_VERB_DEL
        or $code_id == DBL_VIEW_LINK_ADD
        or $code_id == DBL_VIEW_LINK_EDIT
        or $code_id == DBL_VIEW_LINK_DEL
        or $code_id == DBL_VIEW_USER
        or $code_id == DBL_VIEW_ERR_LOG
        or $code_id == DBL_VIEW_ERR_UPD
        or $code_id == DBL_VIEW_IMPORT
        or $code_id == DBL_VIEW_ADD
        or $code_id == DBL_VIEW_EDIT
        or $code_id == DBL_VIEW_DEL) {
        $db_type = DB_TYPE_VIEW;
    }
    if ($code_id == DBL_VIEW_TYPE_DEFAULT
        or $code_id == DBL_VIEW_TYPE_ENTRY
        or $code_id == DBL_VIEW_TYPE_MASK_DEFAULT
        or $code_id == DBL_VIEW_TYPE_PRESENT
        or $code_id == DBL_VIEW_TYPE_WORD_DEFAULT) {
        $db_type = DB_TYPE_VIEW_TYPE;
    }
    if ($code_id == DBL_VIEW_COMP_TYPE_TEXT
        or $code_id == DBL_VIEW_COMP_TYPE_WORD
        or $code_id == DBL_VIEW_COMP_TYPE_WORDS_UP
        or $code_id == DBL_VIEW_COMP_TYPE_WORDS_DOWN
        or $code_id == DBL_VIEW_COMP_TYPE_WORD_NAME
        or $code_id == DBL_VIEW_COMP_TYPE_WORD_VALUE
        or $code_id == DBL_VIEW_COMP_TYPE_VALUES_ALL
        or $code_id == DBL_VIEW_COMP_TYPE_VALUES_RELATED
        or $code_id == DBL_VIEW_COMP_TYPE_FORMULAS
        or $code_id == DBL_VIEW_COMP_TYPE_FORMULA_RESULTS
        or $code_id == DBL_VIEW_COMP_TYPE_JSON_EXPORT
        or $code_id == DBL_VIEW_COMP_TYPE_XML_EXPORT
        or $code_id == DBL_VIEW_COMP_TYPE_CSV_EXPORT) {
        $db_type = DB_TYPE_VIEW_COMPONENT_TYPE;
    }
    if ($code_id == DBL_WORD_TYPE_NORMAL
        or $code_id == DBL_WORD_TYPE_TIME
        or $code_id == DBL_WORD_TYPE_TIME_JUMP
        or $code_id == DBL_WORD_TYPE_MEASURE
        or $code_id == DBL_WORD_TYPE_PERCENT
        or $code_id == DBL_WORD_TYPE_MEASURE
        or $code_id == DBL_WORD_TYPE_SCALING
        or $code_id == DBL_WORD_TYPE_SCALING_HIDDEN
        or $code_id == DBL_WORD_TYPE_SCALING_PCT
        or $code_id == DBL_WORD_TYPE_FORMULA_LINK
        or $code_id == DBL_WORD_TYPE_OTHER
        or $code_id == DBL_WORD_TYPE_NEXT
        or $code_id == DBL_WORD_TYPE_THIS
        or $code_id == DBL_WORD_TYPE_PREV) {
        $db_type = DB_TYPE_WORD_TYPE;
    }

    if ($code_id == DBL_SHARE_PUBLIC
        or $code_id == DBL_SHARE_PERSONAL
        or $code_id == DBL_SHARE_GROUP
        or $code_id == DBL_SHARE_PRIVATE) {
        $db_type = "share_type";
    }

    if ($code_id == DBL_PROTECT_NO
        or $code_id == DBL_PROTECT_USER
        or $code_id == DBL_PROTECT_ADMIN
        or $code_id == DBL_PROTECT_NO_CHANGE) {
        $db_type = "protection_type";
    }

    if ($code_id == DBL_REF_WIKIPEDIA
        or $code_id == DBL_REF_WIKIDATA) {
        $db_type = DB_TYPE_REF_TYPE;
    }

    if ($code_id == DBL_FORMULA_TYPE_NEXT
        or $code_id == DBL_FORMULA_TYPE_THIS
        or $code_id == DBL_FORMULA_TYPE_PREV) {
        $db_type = DB_TYPE_FORMULA_TYPE;
    }
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

    if ($code_id == DBL_USER_ADMIN
        or $code_id == DBL_USER_DEV) {
        $db_type = "user_profile";
    }

    if ($code_id == DBL_USER_SYSTEM) {
        $db_type = "user";
    }

    if ($code_id == DBL_ERR_NEW
        or $code_id == DBL_ERR_ASSIGNED
        or $code_id == DBL_ERR_RESOLVED
        or $code_id == DBL_ERR_CLOSED) {
        $db_type = "sys_log_status";
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

    if ($code_id == DBL_ERR_CLOSED) {
        $db_type = "sys_log_status";
    }

    if ($code_id == DBL_LINK_TYPE_IS
        or $code_id == DBL_LINK_TYPE_CONTAIN
        or $code_id == DBL_LINK_TYPE_FOLLOW
        or $code_id == DBL_LINK_TYPE_DIFFERENTIATOR) {
        $db_type = "verb";
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
