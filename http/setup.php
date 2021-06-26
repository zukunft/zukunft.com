<?php 
/*

  Zukunft.com setup
  
  should create the database
  and add the default or code linked database records
  
*/

// standard start for all php code that can be called
if (isset($_GET['debug'])) { $debug = $_GET['debug']; } else { $debug = 0; }
include_once '../src/main/php/zu_lib.php'; if ($debug > 0) { echo 'libs loaded<br>'; }

/*

The steps should be
1. ask for the database connection and test it
2. ask for the admin user and set the database user
3. write the database connection to a config file, which should not be readable for the www user
4. create the database using zukunft_structure.sql
5. load the coded linked database rows with zukunft_init_data.sql
6. import the initial usr data with JSON
7. on each start it is checked if the local config exists and if no the setup is started

*/

$db_con = prg_start("setup", "center_form");

// load the coded linked database rows with zukunft_init_data.sql
$sql = $db_con->sql_of_code_linked_db_rows();
if ($sql == false) {
    log_err('Cannot read the initial database data file', 'setup');
} else {
    //$sql_result = $db_con->exe($sql, 'code_linked_db_rows', array(), DBL_SYSLOG_FATAL_ERROR);
    $sql_result = $db_con->exe($sql, '', array(), DBL_SYSLOG_FATAL_ERROR);
}





// create all code linked records in the database
// created in the database because on one hand, they can be used like all user added records
// on the other side, these records have special function that are defined in the code
// the code link always is done with the field "code_id"
// this way the user can give the record another name without using the code link
// maybe the code link should be shown to the user for
sql_code_link(DBL_VIEW_WORD_ADD,     "Add new words");
sql_code_link(DBL_VIEW_WORD_EDIT,    "Word Edit");
sql_code_link(DBL_VIEW_VALUE_ADD,    "Add new values");
sql_code_link(DBL_VIEW_VALUE_EDIT,   "Value Edit");
sql_code_link(DBL_VIEW_FORMULA_ADD,  "Add new formula");
sql_code_link(DBL_VIEW_FORMULA_EDIT, "Formula Edit");
sql_code_link(DBL_VIEW_ADD,          "Add new view");
sql_code_link(DBL_VIEW_EDIT,         "view Edit");
sql_code_link(DBL_VIEW_IMPORT,       "Import");

sql_code_link(DBL_WORD_TYPE_TIME,    "Time Word");
sql_code_link(DBL_LINK_TYPE_IS,      "is a");

// create test records
// these records are used for the test cases


log_debug ("setup ... done.");

prg_end($db_con);
