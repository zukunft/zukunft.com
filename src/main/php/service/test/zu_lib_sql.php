<?php

/*

  zu_lib_sql.php - olf ZUkunft.com LIBrary SQL link functions  (just just for regression code testing)
  --------------
    
  prefix: zu_sql_* 

  all functions that all directly the sql database

  
  General functions:
  -------
  zu_sql_open     - called from zu_start in all php scripts that can be called by the user
  zu_sql_close    - called at the end of all php scripts that can be called by the user
  zu_sql_add_user - add an new user for authentication and logging


  MySQL functions
  -----

  zu_sql_insert - the MariaSQL insert statement including error handling, but without logging (maybe add logging later)
  zu_sql_update - update one row using the standard zukunft.com id field


  reviewed internal functions without logging that should not be call only from *_db_* lib function that have already done the logging
  -----------------

  zudb_get - always get the complete query result as a named array
             thsi should replace zu_sql_get_all, zu_sql_get and zu_sql_get_lst


  internal change functions without logging that should not be call only from *_db_* lib function that have already done the logging
  --------------

  sql_insert       - insert only if row does not yet exist (maybe replace by zu_sql_insert),
  sql_set_no_log   - (to be renamed to zu_sql_upd_no_log)

  zu_sql_get_all   - backend functions that should only be used in this library
  zu_sql_get       - returns the first array of an SQL query
  zu_sql_get1      - get the first value of an SQL query
  zu_sql_get_lst   - get all values in an array
  zu_sql_get_value - same as zu_sql_get1 but only for one table field
  , zu_sql_get_value_2key
  : 
  zu_sql_word_unlink

  
  standard naming functions: 
  ---------------
  
  zu_sql_get_name, zu_sql_get_id, zu_sql_get_field, zu_sql_log_field
  
  table specific functions that can and should be called from other libraries
  --------------
  
  zu_sql_val_add         - add a new value and link it to words
  zu_sql_tbl_value       - return one value for a table
  zu_sql_word_values     - get only the values related to one word
  zu_sql_word_lst_value  - 
  zu_sql_value_lst_words - get all words related to a value list
  zu_sql_view_components    - all parts of a view 
  zu_sql_verbs           - get all possible word link types
  

  table specific list functions that return a list of items
  --------------
  
  zu_sql_word_ids_linked         - create a list of words that are foaf of the given word
  zu_sql_word_lst_linked         - same as zu_sql_word_ids_linked; only another function name
  
  

  
  table specific queries - this should be replace by functions that return a user and context specific list
  --------------
  
  zu_sql_views           - zu_sql_views
  zu_sql_views_user      - returns all non internal views
  zu_sql_view_types
  zu_sql_view_component_types - returns all view entry types 
  
  
  
  to dismiss: 
  ----------

  zu_sql_words - 
  zu_sql_views, zu_sql_view_types, zu_sql_view_component_types
  
  code ids
  preset records that are linked to the program code
  below a list of predefined verbs that have a fixed predefined behavior as const
  the const is the code_id that is also shown to to user if he/she wants to change the name

  verbs are also named as word_links
  

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

// General SQL functions
// ---------------------



// functions to review
// ---------------------


// returns all results of an SQL query 
function zu_sql_get_all($sql)
{
    global $db_con;
    global $debug;
    global $usr;

    if ($debug > 10) {
        log_debug('zu_sql_get_all (' . $sql . ')');
    } else {
        log_debug('zu_sql_get_all (' . substr($sql, 0, 100) . ' ... )');
    }

    try {
        $result = $db_con->exe($sql);
        //$result = zu_sql_exe($sql, $usr->id, sys_log_level::FATAL, "zu_sql_get_all", (new Exception)->getTraceAsString());
    } catch (Exception $e) {
        log_err('Cannot get all rows with "' . $sql . '" because: ' . $e->getMessage());
    }

    log_debug("zu_sql_get_all ... done");

    return $result;
}

