<?php

/*

    cfg/db/db_env.php - read the environment and fill up missing const with default values
    -----------------


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

    Copyright (c) 1995-2025 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

// the possible SQL DB names (must be the same as in sql_db)
const POSTGRES = "postgres";
const MYSQL = "MySQL";

// the default values used also for unit tests
const SQL_DB_NAME_DEFAULT = 'zukunft';
const SQL_DB_HOST_DEFAULT = '127.0.0.1';
const SQL_DB_USER_DEFAULT = 'zukunft';
const SQL_DB_PASSWD_FALLBACK = 'change_me';
const SYSTEM_ADMIN_IP_FALLBACK = 'localhost';

// temp solution to force reading the .env file
$env = parse_ini_file(ROOT_PATH . '.env');
foreach ($env as $key => $var) {
    $line = $key . '=' .$var;
    if ($line != '') {
        putenv($line);
    }
}

// SYSTEM configuration from environment variables or the default fallback value
// fixed IP of the main system admin as a second line of defence to prevent remote manipulation
define('SYSTEM_ADMIN_IP', getenv('IP_ADMIN') ?: SYSTEM_ADMIN_IP_FALLBACK);

// Database configuration from environment variables or the default fallback value
define('SQL_DB_TYPE', getenv('DB') ?: POSTGRES);
define('SQL_DB_NAME', getenv('PGSQL_DATABASE') ?: SQL_DB_NAME_DEFAULT);
define('SQL_DB_HOST', getenv('PGSQL_HOST') ?: SQL_DB_HOST_DEFAULT);
// the technical postgres user that is owner of the zukunft database
define('SQL_DB_USER', getenv('PGSQL_USERNAME') ?: SQL_DB_USER_DEFAULT);
define('SQL_DB_PASSWD', getenv('PGSQL_PASSWORD') ?: SQL_DB_PASSWD_FALLBACK);
// the default database for the general postgres server admin user
define('SQL_DB_ADMIN_DB', getenv('PGSQL_ADMIN_DATABASE') ?: 'postgres');
// the postgres admin user e.g. if several databases are running on the server to recreate the zukunft database and user
define('SQL_DB_ADMIN_USER', getenv('PGSQL_ADMIN_USERNAME') ?: 'postgres');
define('SQL_DB_ADMIN_PASSWD', getenv('PGSQL_ADMIN_PASSWORD') ?: '');

define('SQL_DB_NAME_MYSQL', getenv('MYSQL_DATABASE') ?: SQL_DB_NAME_DEFAULT);
define('SQL_DB_HOST_MYSQL', getenv('MYSQL_HOST') ?: SQL_DB_HOST_DEFAULT);
define('SQL_DB_USER_MYSQL', getenv('MYSQL_USERNAME') ?: SQL_DB_USER_DEFAULT);
define('SQL_DB_PASSWD_MYSQL', getenv('MYSQL_PASSWORD') ?: SQL_DB_PASSWD_FALLBACK);
// the default database for the general postgres server admin user
define('SQL_DB_ADMIN_DB_MYSQL', getenv('MYSQL_ADMIN_DATABASE') ?: 'mysql');
// the MySQL admin user e.g. if several databases are running on the server to recreate the zukunft database and user
define('SQL_DB_ADMIN_USER_MYSQL', getenv('MYSQL_ADMIN_USERNAME') ?: 'root');
define('SQL_DB_ADMIN_PASSWD_MYSQL', getenv('MYSQL_ADMIN_PASSWORD') ?: '');
