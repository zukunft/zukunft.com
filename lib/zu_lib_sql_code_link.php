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
  
  Copyright (c) 1995-2020 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

define("LOG_LEVEL", "warning"); // starting from this criticality level messages are written to the log for debuging
define("MSG_LEVEL", "error");   // in case of a error or fatal error 
                                                   // additional the message a link to the system log shown 
                                                   // so that the user can track when the error is solved

// addition reserved field names for zukunft
define("DBL_FIELD", "code_id");   


// link to the predefined edit views
// the code ids must unique over all types 
define("SQL_VIEW_START",                    "dsp_start");   
define("SQL_VIEW_WORD",                     "word_dsp");   
define("SQL_VIEW_WORD_ADD",                 "word_add");   
define("SQL_VIEW_WORD_EDIT",                "word_edit");  
define("SQL_VIEW_WORD_FIND",                "word_find");  
define("SQL_VIEW_WORD_DEL",                 "word_del");  
define("SQL_VIEW_VALUE_ADD",                "value_add");   
define("SQL_VIEW_VALUE_EDIT",               "value_edit");  
define("SQL_VIEW_VALUE_DEL",                "value_del");   
define("SQL_VIEW_VALUE_DISPLAY",            "value_display");   
define("SQL_VIEW_FORMULA_ADD",              "formula_add"); 
define("SQL_VIEW_FORMULA_EDIT",             "formula_edit");
define("SQL_VIEW_FORMULA_DEL",              "formula_del");
define("SQL_VIEW_FORMULA_EXPLAIN",          "formula_explain");
define("SQL_VIEW_FORMULA_TEST",             "formula_test");
define("SQL_VIEW_SOURCE_ADD",               "source_add"); 
define("SQL_VIEW_SOURCE_EDIT",              "source_edit");
define("SQL_VIEW_SOURCE_DEL",               "source_del");
define("SQL_VIEW_VERBS",                    "verbs");
define("SQL_VIEW_VERB_ADD",                 "verb_add");
define("SQL_VIEW_VERB_EDIT",                "verb_edit");
define("SQL_VIEW_VERB_DEL",                 "verb_del");
define("SQL_VIEW_LINK_ADD",                 "triple_add");
define("SQL_VIEW_LINK_EDIT",                "triple_edit");
define("SQL_VIEW_LINK_DEL",                 "triple_del");
define("SQL_VIEW_USER",                     "user");
define("SQL_VIEW_ERR_LOG",                  "error_log");
define("SQL_VIEW_ERR_UPD",                  "error_update");
define("SQL_VIEW_IMPORT",                   "import");
// views to edit views                     
define("SQL_VIEW_ADD",                      "view_add");   
define("SQL_VIEW_EDIT",                     "view_edit");  
define("SQL_VIEW_DEL",                      "view_del");  
define("SQL_VIEW_COMPONENT_ADD",            "view_enty_add");   
define("SQL_VIEW_COMPONENT_EDIT",           "view_enty_edit");  
define("SQL_VIEW_COMPONENT_DEL",            "view_enty_del");  
// views types; using view type instead of a single view, because there maybe several default views for words
define("SQL_VIEW_TYPE_DEFAULT",             "view_type_default");  
define("SQL_VIEW_TYPE_ENTRY",               "entry");  
define("SQL_VIEW_TYPE_WORD_DEFAULT",        "word_default");  
                                          
// views component types                  
define("SQL_VIEW_COMPONENT_TEXT",           "text");   
define("SQL_VIEW_TYPE_WORD",                "fixed");   
define("SQL_VIEW_TYPE_WORDS_UP",            "word_list_up");   
define("SQL_VIEW_TYPE_WORDS_DOWN",          "word_list_down");  
define("SQL_VIEW_TYPE_WORD_NAME",           "word_name");  
define("SQL_VIEW_TYPE_WORD_VALUE",          "word_value_list"); // a list of
define("SQL_VIEW_TYPE_VALUES_ALL",          "values_all");  
define("SQL_VIEW_TYPE_VALUES_RELATED",      "values_related");  
define("SQL_VIEW_TYPE_FORMULAS",            "formula_list");  
define("SQL_VIEW_TYPE_FORMULA_RESULTS",     "formula_results");  
define("SQL_VIEW_TYPE_JSON_EXPORT",         "json_export");  
define("SQL_VIEW_TYPE_XML_EXPORT",          "xml_export");  
define("SQL_VIEW_TYPE_CSV_EXPORT",          "csv_export");  
                                          
define("SQL_WORD_TYPE_NORMAL",              "default");  
define("SQL_WORD_TYPE_TIME",                "time");  
define("SQL_WORD_TYPE_MEASURE",             "measure");  
define("SQL_WORD_TYPE_TIMEJUMP",            "timejump");  
define("SQL_WORD_TYPE_PERCENT",             "percent");  
define("SQL_WORD_TYPE_MEASURE",             "measure");  
define("SQL_WORD_TYPE_SCALING",             "scaling");  
define("SQL_WORD_TYPE_SCALING_HIDDEN",      "scaling_hidden");  
define("SQL_WORD_TYPE_SCALING_PCT",         "scaling_percent");  
define("SQL_WORD_TYPE_SCALED_MEASURE",      "scaled_measure");  
define("SQL_WORD_TYPE_FORMULA_LINK",        "formula_link");  
define("SQL_WORD_TYPE_OTHER",               "type_other");  
define("SQL_WORD_TYPE_NEXT",                "next");  
define("SQL_WORD_TYPE_THIS",                "this");  
define("SQL_WORD_TYPE_PREV",                "previous");  
                                          
define("SQL_FORMULA_TYPE_NEXT",             "time_next");  
define("SQL_FORMULA_TYPE_THIS",             "time_this");  
define("SQL_FORMULA_TYPE_PREV",             "time_prior");  
                                          
define("SQL_FORMULA_PART_TYPE_WORD",        "word");  
define("SQL_FORMULA_PART_TYPE_VERB",        "word_link");  
define("SQL_FORMULA_PART_TYPE_FORMULA",     "formula");  
                                          
// predefined word link types or verbs    
define("SQL_LINK_TYPE_IS",                  "is");  
define("SQL_LINK_TYPE_CONTAIN",             "contains");  
define("SQL_LINK_TYPE_FOLLOW",              "follow");  
define("SQL_LINK_TYPE_DIFFERANTIATOR",      "can_contain");  
                                          
// predefined words                       
define("SQL_WORD_OTHER",                    "other");  // replaced by a word type
                                          
// share types                            
define("DBL_SHARE_PUBLIC",                  "public");  
define("DBL_SHARE_PERSONAL",                "personal");  
define("DBL_SHARE_GROUP",                   "group");  
define("DBL_SHARE_PRIVATE",                 "private");  
                                          
// protection types                            
define("DBL_PROTECT_NO",                    "no_protection");  
define("DBL_PROTECT_USER",                  "user_protection");  
define("DBL_PROTECT_ADMIN",                 "admin_protection");  
define("DBL_PROTECT_NO_CHANGE",             "no_change");  
                                          
// user profiles                          
define("SQL_USER_ADMIN",                    "admin");  
define("SQL_USER_DEV",                      "dev");  
                                          
// single special users                   
define("SQL_USER_SYSTEM",                   "system");  
                                          
// system log stati                       
define("DBL_ERR_NEW",                       "new");  
define("DBL_ERR_ASSIGNED",                  "assigned");  
define("DBL_ERR_RESOLVED",                  "resolved");  
define("DBL_ERR_CLOSED",                    "closed");  
                                          
// system log types                       
define("DBL_SYSLOG_INFO",                   "info");  
define("DBL_SYSLOG_WARNING",                "warning");  
define("DBL_SYSLOG_ERROR",                  "error");  
define("DBL_SYSLOG_FATAL_ERROR",            "fatal");  

define("DBL_SYSLOG_TBL_USR",                "users");  
define("DBL_SYSLOG_TBL_VALUE",              "values");  
define("DBL_SYSLOG_TBL_VALUE_USR",          "user_values");  
define("DBL_SYSLOG_TBL_VALUE_LINK",         "value_links");  
define("DBL_SYSLOG_TBL_WORD",               "words");  
define("DBL_SYSLOG_TBL_WORD_USR",           "user_words");  
define("DBL_SYSLOG_TBL_WORD_LINK",          "word_links");  
define("DBL_SYSLOG_TBL_WORD_LINK_USR",      "user_word_links");  
define("DBL_SYSLOG_TBL_FORMULA",            "formulas");  
define("DBL_SYSLOG_TBL_FORMULA_USR",        "user_formulas");  
define("DBL_SYSLOG_TBL_FORMULA_LINK",       "formula_links");  
define("DBL_SYSLOG_TBL_FORMULA_LINK_USR",   "user_formula_links");  
define("DBL_SYSLOG_TBL_VIEW",               "views");  
define("DBL_SYSLOG_TBL_VIEW_USR",           "user_views");  
define("DBL_SYSLOG_TBL_VIEW_LINK",          "view_component_links");  
define("DBL_SYSLOG_TBL_VIEW_LINK_USR",      "user_view_component_links");  
define("DBL_SYSLOG_TBL_VIEW_COMPONENT",     "view_components");  
define("DBL_SYSLOG_TBL_VIEW_COMPONENT_USR", "user_view_components");  

define("DBL_SYSLOG_STATUS_CLOSE",           "closed");  

// the batch job types to keep the dependencies updated and the database clean
define("DBL_JOB_VALUE_UPDATE",              "job_value_update");  
define("DBL_JOB_VALUE_ADD",                 "job_value_add");  
define("DBL_JOB_VALUE_DEL",                 "job_value_del");  
define("DBL_JOB_FORMULA_UPDATE",            "job_formula_update");  
define("DBL_JOB_FORMULA_ADD",               "job_formula_add");  
define("DBL_JOB_FORMULA_DEL",               "job_formula_del");  
define("DBL_JOB_FORMULA_LINK",              "job_formula_link");  
define("DBL_JOB_FORMULA_UNLINK",            "job_formula_unlink");  
define("DBL_JOB_WORD_LINK",                 "job_word_link");  
define("DBL_JOB_WORD_UNLINK",               "job_word_unlink");  
                                           
// fixed settings without code id for the tripple links
define("DBL_TRIPPLE_LINK_IS_WORD",           1);  
define("DBL_TRIPPLE_LINK_IS_TRIPPLE",        2);  
define("DBL_TRIPPLE_LINK_IS_GROUP",          3);  

// table fields where the change should be encoded before shown to the user
// e.g. the "calculate only if all values used in the formula exist" flag should be converted to "all needed for calculation" instead of just displaying "1"
define("DBL_FLD_FORMULA_ALL_NEEDED",        "all_values_needed");  
define("DBL_FLD_FORMULA_TYPE",              "frm_type");  
// e.g. the formula field "ref_txt" is more a internal field, which should not be shown to the user (only to an admin for debugging)
define("DBL_FLD_FORMULA_REF_TEXT",          "ref_text");  


// global list of database values that cannot be changed by the user 
// these need to be loaded only once to the frontend because only a system upgrade can change them
$dbl_protection_types = array();


// returns the pk / row_id for a given code_id
// if the code_id does not exist the missing record is created
// the code_id is always saved in the 20 char long field code_id
function sql_code_link($code_id, $description, $debug) {
  zu_debug("sql_code_link (".$code_id.",".$description.")", $debug-10);

  // set the table name and the id field
  $table_name = '';
  if ($code_id == SQL_VIEW_START
   OR $code_id == SQL_VIEW_WORD
   OR $code_id == SQL_VIEW_WORD_ADD
   OR $code_id == SQL_VIEW_WORD_EDIT
   OR $code_id == SQL_VIEW_WORD_FIND
   OR $code_id == SQL_VIEW_WORD_DEL
   OR $code_id == SQL_VIEW_VALUE_ADD
   OR $code_id == SQL_VIEW_VALUE_EDIT
   OR $code_id == SQL_VIEW_VALUE_DEL
   OR $code_id == SQL_VIEW_VALUE_DISPLAY
   OR $code_id == SQL_VIEW_FORMULA_ADD
   OR $code_id == SQL_VIEW_FORMULA_EDIT
   OR $code_id == SQL_VIEW_FORMULA_DEL
   OR $code_id == SQL_VIEW_FORMULA_EXPLAIN
   OR $code_id == SQL_VIEW_FORMULA_TEST
   OR $code_id == SQL_VIEW_SOURCE_ADD
   OR $code_id == SQL_VIEW_SOURCE_EDIT
   OR $code_id == SQL_VIEW_SOURCE_DEL
   OR $code_id == SQL_VIEW_VERBS
   OR $code_id == SQL_VIEW_VERB_ADD
   OR $code_id == SQL_VIEW_VERB_EDIT
   OR $code_id == SQL_VIEW_VERB_DEL
   OR $code_id == SQL_VIEW_LINK_ADD
   OR $code_id == SQL_VIEW_LINK_EDIT
   OR $code_id == SQL_VIEW_LINK_DEL
   OR $code_id == SQL_VIEW_USER
   OR $code_id == SQL_VIEW_ERR_LOG
   OR $code_id == SQL_VIEW_ERR_UPD
   OR $code_id == SQL_VIEW_IMPORT
   OR $code_id == SQL_VIEW_ADD
   OR $code_id == SQL_VIEW_EDIT
   OR $code_id == SQL_VIEW_DEL) {
    $db_type = "view";
  }
  if ($code_id == SQL_VIEW_TYPE_DEFAULT
   OR $code_id == SQL_VIEW_TYPE_ENTRY
   OR $code_id == SQL_VIEW_TYPE_WORD_DEFAULT) {
    $db_type = "view_type";
  }
  if ($code_id == SQL_VIEW_COMPONENT_TEXT
   OR $code_id == SQL_VIEW_TYPE_WORD
   OR $code_id == SQL_VIEW_TYPE_WORDS_UP
   OR $code_id == SQL_VIEW_TYPE_WORDS_DOWN
   OR $code_id == SQL_VIEW_TYPE_WORD_NAME
   OR $code_id == SQL_VIEW_TYPE_WORD_VALUE
   OR $code_id == SQL_VIEW_TYPE_VALUES_ALL
   OR $code_id == SQL_VIEW_TYPE_VALUES_RELATED
   OR $code_id == SQL_VIEW_TYPE_FORMULAS
   OR $code_id == SQL_VIEW_TYPE_FORMULA_RESULTS
   OR $code_id == SQL_VIEW_TYPE_JSON_EXPORT
   OR $code_id == SQL_VIEW_TYPE_XML_EXPORT
   OR $code_id == SQL_VIEW_TYPE_CSV_EXPORT) {
    $db_type = "view_component_type";
  }
  if ($code_id == SQL_WORD_TYPE_NORMAL
   OR $code_id == SQL_WORD_TYPE_TIME
   OR $code_id == SQL_WORD_TYPE_TIMEJUMP
   OR $code_id == SQL_WORD_TYPE_MEASURE
   OR $code_id == SQL_WORD_TYPE_PERCENT
   OR $code_id == SQL_WORD_TYPE_MEASURE
   OR $code_id == SQL_WORD_TYPE_SCALING
   OR $code_id == SQL_WORD_TYPE_SCALING_HIDDEN
   OR $code_id == SQL_WORD_TYPE_SCALING_PCT
   OR $code_id == SQL_WORD_TYPE_FORMULA_LINK
   OR $code_id == SQL_WORD_TYPE_OTHER
   OR $code_id == SQL_WORD_TYPE_NEXT
   OR $code_id == SQL_WORD_TYPE_THIS
   OR $code_id == SQL_WORD_TYPE_PREV) {
    $db_type = "word_type";
  }

  if ($code_id == DBL_SHARE_PUBLIC
   OR $code_id == DBL_SHARE_PERSONAL
   OR $code_id == DBL_SHARE_GROUP
   OR $code_id == DBL_SHARE_PRIVATE) {
    $db_type    = "share_type";
  }

  if ($code_id == DBL_PROTECT_NO
   OR $code_id == DBL_PROTECT_USER
   OR $code_id == DBL_PROTECT_ADMIN
   OR $code_id == DBL_PROTECT_NO_CHANGE) {
    $db_type    = "protection_type";
  }

  if ($code_id == SQL_FORMULA_TYPE_NEXT
   OR $code_id == SQL_FORMULA_TYPE_THIS
   OR $code_id == SQL_FORMULA_TYPE_PREV) {
    $db_type = "formula_type";
  }
  if ($code_id == SQL_FORMULA_PART_TYPE_WORD
   OR $code_id == SQL_FORMULA_PART_TYPE_VERB
   OR $code_id == SQL_FORMULA_PART_TYPE_FORMULA) {
    $db_type = "formula_element_type";
  }
  if ($code_id == DBL_SYSLOG_INFO
   OR $code_id == DBL_SYSLOG_WARNING
   OR $code_id == DBL_SYSLOG_ERROR
   OR $code_id == DBL_SYSLOG_FATAL_ERROR
   OR $code_id == LOG_LEVEL) {
    $db_type = "sys_log_type";
  }

  if ($code_id == SQL_USER_ADMIN
   OR $code_id == SQL_USER_DEV) {
    $db_type = "user_profile";
  }

  if ($code_id == SQL_USER_SYSTEM) {
    $db_type = "user";
  }

  if ($code_id == DBL_ERR_NEW
   OR $code_id == DBL_ERR_ASSIGNED
   OR $code_id == DBL_ERR_RESOLVED
   OR $code_id == DBL_ERR_CLOSED) {
    $db_type    = "sys_log_status";
  }

  if ($code_id == DBL_SYSLOG_TBL_VALUE
   OR $code_id == DBL_SYSLOG_TBL_VALUE_USR
   OR $code_id == DBL_SYSLOG_TBL_VALUE_LINK
   OR $code_id == DBL_SYSLOG_TBL_WORD
   OR $code_id == DBL_SYSLOG_TBL_WORD_USR
   OR $code_id == DBL_SYSLOG_TBL_WORD_LINK
   OR $code_id == DBL_SYSLOG_TBL_WORD_LINK_USR
   OR $code_id == DBL_SYSLOG_TBL_FORMULA
   OR $code_id == DBL_SYSLOG_TBL_FORMULA_USR
   OR $code_id == DBL_SYSLOG_TBL_FORMULA_LINK
   OR $code_id == DBL_SYSLOG_TBL_FORMULA_LINK_USR
   OR $code_id == DBL_SYSLOG_TBL_VIEW
   OR $code_id == DBL_SYSLOG_TBL_VIEW_USR
   OR $code_id == DBL_SYSLOG_TBL_VIEW_LINK
   OR $code_id == DBL_SYSLOG_TBL_VIEW_LINK_USR
   OR $code_id == DBL_SYSLOG_TBL_view_component
   OR $code_id == DBL_SYSLOG_TBL_view_component_USR) {
    $db_type = "change_table";
  }

  if ($code_id == DBL_JOB_VALUE_UPDATE
   OR $code_id == DBL_JOB_VALUE_ADD
   OR $code_id == DBL_JOB_VALUE_DEL
   OR $code_id == DBL_JOB_FORMULA_UPDATE
   OR $code_id == DBL_JOB_FORMULA_ADD
   OR $code_id == DBL_JOB_FORMULA_DEL
   OR $code_id == DBL_JOB_FORMULA_LINK
   OR $code_id == DBL_JOB_FORMULA_UNLINK
   OR $code_id == DBL_JOB_WORD_LINK
   OR $code_id == DBL_JOB_WORD_UNLINK) {
    $db_type = "calc_and_cleanup_task_type";
  }

  if ($code_id == DBL_SYSLOG_STATUS_CLOSE) {
    $db_type = "sys_log_status";
  }
  
  if ($code_id == SQL_LINK_TYPE_IS
   OR $code_id == SQL_LINK_TYPE_CONTAIN
   OR $code_id == SQL_LINK_TYPE_FOLLOW
   OR $code_id == SQL_LINK_TYPE_DIFFERANTIATOR) {
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

  if ($table_name == '' AND $db_type == '') {
    zu_debug('table name for code_id '.$code_id.' ('.$name_field.') not found <br>', $debug-14);
  } else {
    $db_con = new mysql;         
    $db_con->usr_id = SYSTEM_USER_ID;         
    $db_con->type = $db_type;         
      
    // get the row_id
    $row_id = $db_con->get_id_from_code($code_id, $debug-14);

    // insert the missing row if needed
    if ($row_id <= 0) {
      $row_id = $db_con->get_id_from_code($code_id, $debug-14);
      if ($db_type == 'view') {
        $result .= $db_con->insert(array(DBL_FIELD, 'user_id'), array($code_id, SYSTEM_USER_ID), $debug-14);
      } else {  
        $result .= $db_con->insert(DBL_FIELD, $code_id, $debug-14);
      }  
      zu_debug ('inserted '.$code_id.'<br>', $debug-14);
      // get the id of the inserted row
      $row_id = $db_con->get_id_from_code($code_id, $debug-14);
      zu_debug ('inserted '.$code_id.' as '.$row_id.'<br>', $debug-14);
    } else {
      zu_debug ('found '.$code_id.' as '.$row_id.'<br>', $debug-14);
    }

    // set the name as default
    if ($row_id > 0 AND $description <> '') {
      $row_name = $db_con->get_name($row_id, $debug-14);
      if ($row_name == '') {
        zu_debug ('add '.$description.'<br>', $debug-14);
        $result .= $db_con->update_name($row_id, $description, $debug-14);
      }
    }
  }

  zu_debug("sql_code_link ... done", $debug-14);

  return $row_id;
} 

// shortcut name for sql_code_link for better code reading
// don't use it for the first call to make sure that the description is in the database
function cl($code_id) {
  return sql_code_link($code_id, "", 0);
}


?>
