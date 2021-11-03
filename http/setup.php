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
5. load the coded linked database rows
6. import the initial usr data with JSON
7. on each start it is checked if the local config exists and if no the setup is started

*/

$db_con = prg_start("setup", "center_form");

// load the session user parameters
$usr = new user;
$result = $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id > 0) {
    if ($usr->is_admin()) {

        // recreate the code link database rows
        db_fill_code_links($db_con);
        import_verbs($usr);


        // create all code linked records in the database
        // created in the database because on one hand, they can be used like all user added records
        // on the other side, these records have special function that are defined in the code
        // the code link always is done with the field "code_id"
        // this way the user can give the record another name without using the code link
        // maybe the code link should be shown to the user for
        sql_code_link(view::WORD_ADD, "Add new words", $db_con);
        sql_code_link(view::WORD_EDIT, "Word Edit", $db_con);
        sql_code_link(view::VALUE_ADD, "Add new values", $db_con);
        sql_code_link(view::VALUE_EDIT, "Value Edit", $db_con);
        sql_code_link(view::FORMULA_ADD, "Add new formula", $db_con);
        sql_code_link(view::FORMULA_EDIT, "Formula Edit", $db_con);
        sql_code_link(view::ADD, "Add new view", $db_con);
        sql_code_link(view::EDIT, "view Edit", $db_con);
        sql_code_link(view::IMPORT, "Import", $db_con);

        sql_code_link(word_type_list::DBL_TIME, "Time Word", $db_con);
        //sql_code_link(DBL_LINK_TYPE_IS,      "is a", $db_con);

        // create test records
        // these records are used for the test cases


        log_debug("setup ... done.");
    }}
prg_end($db_con);
