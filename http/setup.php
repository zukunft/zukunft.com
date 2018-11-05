<?php 
/*

  ZUkunft.com setup
  
  should create the database
  and add the defauld or code linked database records
  
*/

// standard start for all php code that can be called
if (isset($_GET['debug'])) { $debug = $_GET['debug']; } else { $debug = 0; }
include_once '../lib/zu_lib.php'; if ($debug > 0) { echo 'libs loaded<br>'; }

$link = zu_start("setup", $debug);

// create all code linked records in the database
// created in the database because on one hand, they can be used like all user added records
// on the other side, these records have special function that are defined in the code
// the code link always is done with the field "code_id"
// this way the user can give the record another name without using the code link
// maybe the code link shoud be shown to the user for 
sql_code_link(SQL_VIEW_WORD_ADD,     "Add new words", $debug);
sql_code_link(SQL_VIEW_WORD_EDIT,    "Word Edit", $debug);
sql_code_link(SQL_VIEW_VALUE_ADD,    "Add new values", $debug);
sql_code_link(SQL_VIEW_VALUE_EDIT,   "Value Edit", $debug);
sql_code_link(SQL_VIEW_FORMULA_ADD,  "Add new formula", $debug);
sql_code_link(SQL_VIEW_FORMULA_EDIT, "Formula Edit", $debug);
sql_code_link(SQL_VIEW_ADD,          "Add new view", $debug);
sql_code_link(SQL_VIEW_EDIT,         "view Edit", $debug);

sql_code_link(SQL_WORD_TYPE_TIME,    "Time Word", $debug);
sql_code_link(SQL_LINK_TYPE_IS,      "is a", $debug);

// create test records
// these records are used for the test cases

/*

create the database using zukunft_structure.sql
set the database user
load the coded linked database rows with zukunft_init_data.sql
import the inital usr data with XML

*/


zu_debug ("setup ... done.", $debug);

zu_end($link, $debug);
?>
