<?php 
/*

  Zukunft.com setup
  
  should create the database
  and add the default or code linked database records
  
*/

// standard start for all php code that can be called
use cfg\db\db_check;
use cfg\phrase_type;
use controller\controller;
use cfg\user;

if (isset($_GET['debug'])) { $debug = $_GET['debug']; } else { $debug = 0; }
const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
include_once ROOT_PATH . 'src/main/php/zu_lib.php'; if ($debug > 0) { echo 'libs loaded<br>'; }

/*

The steps should be
1. ask for the database connection and test it
2. ask for the admin user and set the database user
3. write the database connection to a config file, which should not be readable for the www user
4. create the database using src/main/php/db/.../zukunft_structure.sql
5. load the coded linked database rows
6. import the initial usr data with JSON
7. on each start it is checked if the local config exists and if no the setup is started

*/

$db_con = prg_start("setup", "center_form");

// load the session user parameters
$usr = new user;
$result = $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id() > 0) {
    if ($usr->is_admin()) {

        // recreate the code link database rows
        $db_chk = new db_check();

        // with the check the tables will be created and the system data will be loaded
        // TODO compare with test_recreate.php
        $result = $db_chk->db_check($db_con);

        log_debug("setup ... done.");
    }}
prg_end($db_con);
