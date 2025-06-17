<?php

// standard zukunft header for callable php files to allow debugging and use of the library
global $debug;
$debug = $_GET['debug'] ?? 0;
// TODO check if dirname should be used in all scripts
//define('ROOT_PATH', dirname(__DIR__, 3) . DIRECTORY_SEPARATOR);
const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
const PHP_PATH = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;
include_once PHP_PATH . 'zu_lib.php';

// path for the general tests and test setup
const TEST_PHP_UTIL_PATH = TEST_PHP_PATH . 'utils' . DIRECTORY_SEPARATOR;

// load the base testing functions
include_once TEST_PHP_UTIL_PATH . 'test_base.php';

// load the main test control class
include_once TEST_PHP_UTIL_PATH . 'all_tests.php';

// load the sql sequence check functions
include_once MODEL_DB_PATH . 'sql_sync_sequences.php';

use cfg\db\sql_sync_sequences;
use cfg\user\user;
use test\all_tests;

global $db_con;

// open database and display header
$db_con = prg_start("sql sequence check", '', false);

// load the session user parameters
$start_usr = new user;
$result = $start_usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($start_usr->id() > 0) {
    if ($start_usr->is_admin()) {

        // init tests
        $t = new all_tests();
        $t->header('sql sequence check');

        // run the sql sequence checks
        $sql_seq = new sql_sync_sequences();
        $sql_seq->sync($db_con);

    } else {
        echo 'Only admin users are allowed to force to check the sql sequences.';
    }
}

// Closing connection
prg_end($db_con, false);
