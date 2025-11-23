<?php

include_once 'test_const.php';

use Zukunft\ZukunftCom\main\php\cfg\application;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

// load the base testing functions
include_once test_paths::UTILS . 'test_base.php';

// load the main test control class
include_once test_paths::UTILS . 'all_tests.php';

// load the sql sequence check functions
use Zukunft\ZukunftCom\main\php\cfg\const\paths;
include_once paths::DB . 'sql_sync_sequences.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_sync_sequences;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\test\php\utils\all_tests;

global $db_con;

// open database and display header
$app = new application();
$db_con = $app->start("sql sequence check", '', false);

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
$app->end($db_con, false);
