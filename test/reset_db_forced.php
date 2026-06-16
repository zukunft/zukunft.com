<?php

/*

    reset_db_forced.php - drop the config table, then run the full database reset.
    -------------------

    Use this instead of reset_db.php when the config table schema has changed in a
    way that makes the normal reset_db.php startup fail while it loads the system
    configuration (open_db -> config_numbers->load_cfg). Dropping the config table
    first lets reset_db.php start with the built-in config defaults and recreate the
    config table from scratch together with all other tables.

    TODO FOR DEVELOPMENT ONLY! Remove completely before production.


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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('This script may only be run from the command line.');
}

// bootstrap the const, paths and the global $sys (incl. $sys->times and $sys->log_txt used by drop_table)
include_once 'test_const.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\shared\const\words;

// open a minimal database connection without loading the (possibly incompatible) config
$db_con = new sql_db();
$db_con->db_type = SQL_DB_TYPE;
$db_con->open();
if ($db_con->is_open()) {

    // load the session user parameters
    $start_usr = new user;
    $result = $start_usr->get();

    // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
    if ($start_usr->id() > 0) {
        if ($start_usr->is_admin() or getenv(ENVIRONMENT) == ENV_DEV) {
            echo 'forced reset: dropping the ' . words::CONFIG . ' table before the database reset' . "\n";
            $db_con->drop_table(words::CONFIG);
            $db_con->close();

            // delegate to reset_db.php so that the forced reset loads exactly the same
            // data as the normal reset; with the config table dropped above reset_db.php
            // starts with the built-in config defaults and recreates the config table
            // from scratch together with all other tables and the unit test base data
            include __DIR__ . DIRECTORY_SEPARATOR . 'reset_db.php';
        }
    }
} else {
    echo 'forced reset: cannot connect to the database to drop the ' . words::CONFIG . ' table' . "\n";
}
