<?php 
/*

  Zukunft.com setup
  
  should create the database
  and add the default or code linked database records
  
*/

// refuse unless explicitly enabled via env var that gets unset after install
if (getenv('ZUKUNFT_ALLOW_SETUP') !== '1') {
    http_response_code(404);
    exit;
}

$start_time = microtime(true);

// standard start for all php code that can be called
// keep the requested url debug level untrusted until the environment is known,
// then honor it only in dev so sql and the call graph never leak elsewhere
global $debug;
$debug_requested = $_GET['debug'] ?? 0;
$debug = 0;
const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
const PHP_PATH = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;
include_once PHP_PATH . 'init.php';
if (getenv(ENVIRONMENT) == ENV_DEV) {
    $debug = $debug_requested;
}

use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\cfg\db\db_check;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;

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

$app = new frontend();
global $sys;
$db_con = $app->start("setup");

// load the session user parameters
$usr = new user;
$result = $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id() > 0) {
    if ($usr->is_admin()) {

        // recreate the code link database rows
        $db_chk = new db_check();

        // with the check the tables will be created and the system data will be loaded
        // the check is requested by the admin user, so the changes are done in his name
        // TODO compare with test_recreate.php
        $msg = new user_message($usr);
        if (!$db_chk->db_check($db_con, $msg)) {
            echo $msg->all_message_text();
        }

        log_debug("setup ... done.");
    }}
// close the database and measure the script loading time before the frontend has been created
$app->end($db_con, $start_time);
