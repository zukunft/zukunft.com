<?php

/*

    server_admin/db_upgrade.php - CLI database upgrade for the server admin page
    ---------------------------

    Runs the same database check / upgrade as http/setup.php, but from the
    command line: shell access to this web-denied folder is the authorization,
    so there is no interactive admin-session check here.

    It is invoked by `script/server_admin.sh upgrade-database`, which puts the
    optional/database_upgrade.html maintenance page live while this runs.

    This file is part of zukunft.com - calc with words, GNU AGPL v3, see
    <http://www.gnu.org/licenses/agpl.html>. Timon Zielonka <timon@zukunft.com>

*/

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

// standard start for all php code that can be called (mirrors http/setup.php)
global $debug;
$debug = 0;
const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
const PHP_PATH = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;
include_once PHP_PATH . 'init.php';

use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\cfg\db\db_check;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;

// init global objects for the database connection
global $sys;

// init global frontend objects
$msg = new user_message();

// open database
$app = new frontend();
$db_con = $app->start("db_upgrade", $msg);

// recreate the code linked database rows and run the pending upgrades
if (new db_check()->db_check($db_con, $msg)) {
    echo "database upgrade ok\n";
    $app->end($db_con);
    exit(0);
} else {
    fwrite(STDERR, $msg->text() . "\n");
    $app->end($db_con);
    exit(1);
}