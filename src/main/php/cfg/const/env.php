<?php

/*

    model/const/env.php - read the environment and fill up missing const with default values
    -------------------


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

// the name of the environment file
const ENV_FILE = '.env';

// names that can be used in the .env file
const ENV_OS = 'OS';
const ENVIRONMENT = 'ENV';
const ENV_BRANCH = 'BRANCH';
const ENV_DB = 'DB';
const ENV_IP_ADMIN = 'IP_ADMIN';
const ENV_PGSQL_DATABASE = 'PGSQL_DATABASE';
const ENV_PGSQL_USERNAME = 'PGSQL_USERNAME';
const ENV_PGSQL_PASSWORD = 'PGSQL_PASSWORD';
const ENV_PGSQL_ADMIN_DATABASE = 'PGSQL_ADMIN_DATABASE';
const ENV_PGSQL_ADMIN_USERNAME = 'PGSQL_ADMIN_USERNAME';
const ENV_PGSQL_ADMIN_PASSWORD = 'PGSQL_ADMIN_PASSWORD';
const ENV_PGSQL_HOST = 'PGSQL_HOST';
const ENV_PGSQL_PORT = 'PGSQL_PORT';
const ENV_PGSQL_ZUKUNFT_VERSION = 'PGSQL_ZUKUNFT_VERSION';
const ENV_MYSQL_DATABASE = 'MYSQL_DATABASE';
const ENV_MYSQL_USERNAME = 'MYSQL_USERNAME';
const ENV_MYSQL_PASSWORD = 'MYSQL_PASSWORD';
const ENV_MYSQL_ADMIN_DATABASE = 'MYSQL_ADMIN_DATABASE';
const ENV_MYSQL_ADMIN_USERNAME = 'MYSQL_ADMIN_USERNAME';
const ENV_MYSQL_ADMIN_PASSWORD = 'MYSQL_ADMIN_PASSWORD';
const ENV_MYSQL_HOST = 'MYSQL_HOST';
const ENV_MYSQL_PORT = 'MYSQL_PORT';
const ENV_MYSQL_ZUKUNFT_VERSION = 'MYSQL_ZUKUNFT_VERSION';
const ENV_WWW_ROOT = 'WWW_ROOT';

const ENV_VARS = [
    ENV_OS,
    ENVIRONMENT,
    ENV_BRANCH,
    ENV_DB,
    ENV_IP_ADMIN,
    ENV_PGSQL_DATABASE,
    ENV_PGSQL_USERNAME,
    ENV_PGSQL_PASSWORD,
    ENV_PGSQL_ADMIN_DATABASE,
    ENV_PGSQL_ADMIN_USERNAME,
    ENV_PGSQL_ADMIN_PASSWORD,
    ENV_PGSQL_HOST,
    ENV_PGSQL_PORT,
    ENV_PGSQL_ZUKUNFT_VERSION,
    ENV_MYSQL_DATABASE,
    ENV_MYSQL_USERNAME,
    ENV_MYSQL_PASSWORD,
    ENV_MYSQL_ADMIN_DATABASE,
    ENV_MYSQL_ADMIN_USERNAME,
    ENV_MYSQL_ADMIN_PASSWORD,
    ENV_MYSQL_HOST,
    ENV_MYSQL_PORT,
    ENV_MYSQL_ZUKUNFT_VERSION,
    ENV_WWW_ROOT,
];

// the possible environments
const ENV_OS_DEBIAN = 'debian';
const ENV_PROD = 'prod';
const ENV_UA = 'test';
const ENV_DEV = 'dev';

// the possible SQL DB names (must be the same as in sql_db)
const POSTGRES = "postgres";
const MYSQL = "MySQL";

const ENV_OS_LIST = [
    ENV_OS_DEBIAN,
];

const ENV_LEVELS = [
    ENV_PROD,
    ENV_UA,
    ENV_DEV,
];

const ENV_DB_LIST = [
    POSTGRES,
    MYSQL,
];

// the default values used also for unit tests
const SQL_DB_NAME_DEFAULT = 'zukunft';
const SQL_DB_HOST_DEFAULT = '127.0.0.1';
const SQL_DB_USER_DEFAULT = 'zukunft';
const SQL_DB_PASSWD_FALLBACK = 'change_me';
const SYSTEM_ADMIN_IP_FALLBACK = 'localhost';

// temp solution to force reading the .env file
$env = file(ROOT_PATH . ENV_FILE);
foreach ($env as $line) {
    if (!str_starts_with($line, '#') and trim($line) != '') {
        if (str_contains($line, '#')) {
            $line = substr($line, 0, strpos($line, '#'));
        }
        if (str_contains($line, "\n")) {
            $line = substr($line, 0, strpos($line, "\n"));
        }
        $parts = explode('=', $line);
        if (count($parts) != 2) {
            log_err('unexpected line format in ' . $line);
        } else {
            $key = $parts[0];
            $var = $parts[1];
            if (in_array($key, ENV_VARS)) {
                if ($key == ENV_OS) {
                    if (!in_array($var, ENV_OS_LIST)) {
                        log_err($key . ' not expected to be ' . $var . ' (only ' . implode(',', ENV_OS_LIST) . ' allowed)');
                    }
                }
                if ($key == ENVIRONMENT) {
                    if (!in_array($var, ENV_LEVELS)) {
                        log_err($key . ' not expected to be ' . $var . ' (only ' . implode(',', ENV_LEVELS) . ' allowed)');
                    }
                }
                if ($key == ENV_DB) {
                    if (!in_array($var, ENV_DB_LIST)) {
                        log_err($key . ' not expected to be ' . $var . ' (only ' . implode(',', ENV_DB_LIST) . ' allowed)');
                    }
                }
                if ($line != '') {
                    putenv($line);
                }
            } else {
                log_err('name ' . $key . ' not expected in environment file ' . ROOT_PATH . ENV_FILE);
            }
        }
    }
}

// SYSTEM configuration from environment variables or the default fallback value
// fixed IP of the main system admin as a second line of defence to prevent remote manipulation
define('SYSTEM_ADMIN_IP', getenv('IP_ADMIN') ?: SYSTEM_ADMIN_IP_FALLBACK);

// Database configuration from environment variables or the default fallback value
define('SQL_DB_TYPE', getenv(ENV_DB) ?: POSTGRES);
define('SQL_DB_NAME', getenv(ENV_PGSQL_DATABASE) ?: SQL_DB_NAME_DEFAULT);
define('SQL_DB_HOST', getenv(ENV_PGSQL_HOST) ?: SQL_DB_HOST_DEFAULT);
// the technical postgres user that is owner of the zukunft database
define('SQL_DB_USER', getenv(ENV_PGSQL_USERNAME) ?: SQL_DB_USER_DEFAULT);
define('SQL_DB_PASSWD', getenv(ENV_PGSQL_PASSWORD) ?: SQL_DB_PASSWD_FALLBACK);
// the default database for the general postgres server admin user
define('SQL_DB_ADMIN_DB', getenv(ENV_PGSQL_ADMIN_DATABASE) ?: 'postgres');
// the postgres admin user e.g. if several databases are running on the server to recreate the zukunft database and user
define('SQL_DB_ADMIN_USER', getenv(ENV_PGSQL_ADMIN_USERNAME) ?: 'postgres');
define('SQL_DB_ADMIN_PASSWD', getenv(ENV_PGSQL_ADMIN_PASSWORD) ?: '');

define('SQL_DB_NAME_MYSQL', getenv(ENV_MYSQL_DATABASE) ?: SQL_DB_NAME_DEFAULT);
define('SQL_DB_HOST_MYSQL', getenv(ENV_MYSQL_HOST) ?: SQL_DB_HOST_DEFAULT);
define('SQL_DB_USER_MYSQL', getenv(ENV_MYSQL_USERNAME) ?: SQL_DB_USER_DEFAULT);
define('SQL_DB_PASSWD_MYSQL', getenv(ENV_MYSQL_PASSWORD) ?: SQL_DB_PASSWD_FALLBACK);
// the default database for the general postgres server admin user
define('SQL_DB_ADMIN_DB_MYSQL', getenv(ENV_MYSQL_ADMIN_DATABASE) ?: 'mysql');
// the MySQL admin user e.g. if several databases are running on the server to recreate the zukunft database and user
define('SQL_DB_ADMIN_USER_MYSQL', getenv(ENV_MYSQL_ADMIN_USERNAME) ?: 'root');
define('SQL_DB_ADMIN_PASSWD_MYSQL', getenv(ENV_MYSQL_ADMIN_PASSWORD) ?: '');
