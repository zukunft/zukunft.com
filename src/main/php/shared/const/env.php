<?php

/*

    shared/const/env.php - read the environment and fill up missing const with default values
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
// the name of the version file, which unlike .env is part of the git release
const VERSION_FILE = 'version.txt';

// names that can be used in the .env file
const ENV_OS = 'OS';
const ENVIRONMENT = 'ENV';
const ENV_BRANCH = 'BRANCH';
const ENV_DB = 'DB';
const ENV_CODE_VERSION = 'CODE_VERSION';
const ENV_UI_VERSION = 'UI_VERSION';
const ENV_POD_NAME = 'POD_NAME';
const ENV_THIS_URL = 'THIS_URL';
const ENV_SYS_LOG_URL = 'SYS_LOG_URL';
const ENV_SOURCE_REPO_URL = 'SOURCE_REPO_URL'; // the git repository the program updates are pulled from
const ENV_PGSQL_DATABASE = 'PGSQL_DATABASE';
const ENV_PGSQL_USERNAME = 'PGSQL_USERNAME';
const ENV_PGSQL_PASSWORD = 'PGSQL_PASSWORD';
const ENV_PGSQL_ADMIN_DATABASE = 'PGSQL_ADMIN_DATABASE';
const ENV_PGSQL_ADMIN_USERNAME = 'PGSQL_ADMIN_USERNAME';
const ENV_PGSQL_ADMIN_PASSWORD = 'PGSQL_ADMIN_PASSWORD';
const ENV_PGSQL_HOST = 'PGSQL_HOST';
const ENV_PGSQL_PORT = 'PGSQL_PORT';
const ENV_MYSQL_DATABASE = 'MYSQL_DATABASE';
const ENV_MYSQL_USERNAME = 'MYSQL_USERNAME';
const ENV_MYSQL_PASSWORD = 'MYSQL_PASSWORD';
const ENV_MYSQL_ADMIN_DATABASE = 'MYSQL_ADMIN_DATABASE';
const ENV_MYSQL_ADMIN_USERNAME = 'MYSQL_ADMIN_USERNAME';
const ENV_MYSQL_ADMIN_PASSWORD = 'MYSQL_ADMIN_PASSWORD';
const ENV_MYSQL_HOST = 'MYSQL_HOST';
const ENV_MYSQL_PORT = 'MYSQL_PORT';
const ENV_WWW_ROOT = 'WWW_ROOT';
// path to the git repository (objects + history); kept outside WWW_ROOT so the
// clone's .git cannot be downloaded over the web (see script/install.sh)
const ENV_GIT_DIR = 'ZUKUNFT_GIT_DIR';

// server admin page (http/server_admin.php) settings
// a comma separated list of IPs / CIDR ranges allowed to reach the server admin page
// (also the fixed IP of the main system admin; falls back to 'localhost')
const ENV_SERVER_ADMIN_IP = 'SERVER_ADMIN_IP';
// the full access server admin (may switch the IP whitelist off); username + bcrypt password hash
const ENV_SERVER_ADMIN_USER = 'SERVER_ADMIN_USER';
const ENV_SERVER_ADMIN_PW = 'SERVER_ADMIN_PW';
// two restricted server admins that log in with a fixed username and password (bcrypt hash);
// they may not switch the IP whitelist off (reduce it to the database blacklist)
const ENV_SERVER_ADMIN_2_USER = 'SERVER_ADMIN_2_USER';
const ENV_SERVER_ADMIN_2_PW = 'SERVER_ADMIN_2_PW';
// optional client IP range (inclusive) the restricted admin may log in from; empty = no extra range check
const ENV_SERVER_ADMIN_2_IP_FROM = 'SERVER_ADMIN_2_IP_FROM';
const ENV_SERVER_ADMIN_2_IP_TO = 'SERVER_ADMIN_2_IP_TO';
const ENV_SERVER_ADMIN_3_USER = 'SERVER_ADMIN_3_USER';
const ENV_SERVER_ADMIN_3_PW = 'SERVER_ADMIN_3_PW';
const ENV_SERVER_ADMIN_3_IP_FROM = 'SERVER_ADMIN_3_IP_FROM';
const ENV_SERVER_ADMIN_3_IP_TO = 'SERVER_ADMIN_3_IP_TO';

const ENV_SYSTEM_TIME_LIMIT_INFO = 'SYSTEM_TIME_LIMIT_INFO';
const ENV_SYSTEM_TIME_LIMIT_WARN = 'SYSTEM_TIME_LIMIT_WARN';
const ENV_SYSTEM_TIME_LIMIT_ERR = 'SYSTEM_TIME_LIMIT_ERR';
const ENV_CACHE = 'CACHE';
const ENV_CACHE_DATABASE = 'database';
const ENV_CACHE_FILE = 'file';
const ENV_CACHE_FALLBACK = 'database';
const ENV_CACHE_MAX_AGE = 'CACHE_TIME';
const ENV_CACHE_MAX_AGE_FALLBACK = '-1 day';
const ENV_ADMIN_USER = 'ADMIN_USER';  // can be set for a ui free setup
const ENV_ADMIN_PW = 'ADMIN_PW'; // can be set for a ui free setup; in test and prod should be taken from the secret store
const ENV_ADMIN_MAIL = 'ADMIN_MAIL';
// public admin contact email shown to rejected visitors (whitelist reject pages); defaults to ADMIN_MAIL
const ENV_SERVER_ADMIN_MAIL = 'SERVER_ADMIN_MAIL';
const ENV_CO_ADMIN_USER = 'CO_ADMIN_USER'; // the suggestion is to have always a deputy admin as fallback
const ENV_CO_ADMIN_PW = 'CO_ADMIN_PW'; // if empty requested on initial startup
const ENV_CO_ADMIN_MAIL = 'CO_ADMIN_MAIL';
const ENV_USER_NAME = 'USER_NAME';  // can be set for a ui free setup
const ENV_USER_PW = 'USER_PW'; // can be set for a ui free setup; in test and prod should be taken from the secret store
const ENV_USER_MAIL = 'USER_MAIL';
const ENV_CO_USER_NAME = 'CO_USER_NAME'; // the suggestion is to have always a deputy admin as fallback
const ENV_CO_USER_PW = 'CO_USER_PW'; // if empty requested on initial startup
const ENV_CO_USER_MAIL = 'CO_USER_MAIL';
const POD_NAME_FALLBACK = 'zukunft.com';  // the default pod name if not defined
const THIS_URL_FALLBACK = 'http://localhost/';  // the default pod url if not defined
const SOURCE_REPO_URL_FALLBACK = 'https://github.com/zukunft/zukunft.com';  // the default source repository for program updates

const ENV_VARS = [
    ENV_OS,
    ENVIRONMENT,
    ENV_BRANCH,
    ENV_DB,
    ENV_CODE_VERSION,
    ENV_UI_VERSION,
    ENV_POD_NAME,
    ENV_THIS_URL,
    ENV_SYS_LOG_URL,
    ENV_SOURCE_REPO_URL,
    ENV_PGSQL_DATABASE,
    ENV_PGSQL_USERNAME,
    ENV_PGSQL_PASSWORD,
    ENV_PGSQL_ADMIN_DATABASE,
    ENV_PGSQL_ADMIN_USERNAME,
    ENV_PGSQL_ADMIN_PASSWORD,
    ENV_PGSQL_HOST,
    ENV_PGSQL_PORT,
    ENV_MYSQL_DATABASE,
    ENV_MYSQL_USERNAME,
    ENV_MYSQL_PASSWORD,
    ENV_MYSQL_ADMIN_DATABASE,
    ENV_MYSQL_ADMIN_USERNAME,
    ENV_MYSQL_ADMIN_PASSWORD,
    ENV_MYSQL_HOST,
    ENV_MYSQL_PORT,
    ENV_WWW_ROOT,
    ENV_GIT_DIR,
    ENV_SERVER_ADMIN_IP,
    ENV_SERVER_ADMIN_USER,
    ENV_SERVER_ADMIN_PW,
    ENV_SERVER_ADMIN_2_USER,
    ENV_SERVER_ADMIN_2_PW,
    ENV_SERVER_ADMIN_2_IP_FROM,
    ENV_SERVER_ADMIN_2_IP_TO,
    ENV_SERVER_ADMIN_3_USER,
    ENV_SERVER_ADMIN_3_PW,
    ENV_SERVER_ADMIN_3_IP_FROM,
    ENV_SERVER_ADMIN_3_IP_TO,
    ENV_CACHE,
    ENV_CACHE_MAX_AGE,
    ENV_ADMIN_USER,
    ENV_ADMIN_PW,
    ENV_ADMIN_MAIL,
    ENV_SERVER_ADMIN_MAIL,
    ENV_CO_ADMIN_USER,
    ENV_CO_ADMIN_PW,
    ENV_CO_ADMIN_MAIL,
    ENV_USER_NAME,
    ENV_USER_PW,
    ENV_USER_MAIL,
    ENV_CO_USER_NAME,
    ENV_CO_USER_PW,
    ENV_CO_USER_MAIL,
];

const ENV_SECRETS = [
    ENV_PGSQL_PASSWORD,
    ENV_PGSQL_ADMIN_PASSWORD,
    ENV_MYSQL_PASSWORD,
    ENV_MYSQL_ADMIN_PASSWORD,
    ENV_SERVER_ADMIN_PW,
    ENV_SERVER_ADMIN_2_PW,
    ENV_SERVER_ADMIN_3_PW,
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

const ENV_CACHE_TYPES = [
    ENV_CACHE_DATABASE,
    ENV_CACHE_FILE,
];

// the default values used also for unit tests
const SQL_DB_NAME_DEFAULT = 'zukunft';
const SQL_DB_HOST_DEFAULT = '127.0.0.1';
const SQL_DB_USER_DEFAULT = 'zukunft';
const SQL_DB_PASSWD_FALLBACK = 'change_me';
const SYSTEM_ADMIN_IP_FALLBACK = 'localhost';

// read the environment file: prefer a copy one level above the web root so the secrets are not
// inside the docroot (and cannot be served even if the web server ever ignores the .htaccess
// rules); fall back to the web root for the dev / docker setup that keeps .env in the repo root
$env_path = ROOT_PATH . '..' . DIRECTORY_SEPARATOR . ENV_FILE;
if (!file_exists($env_path)) {
    $env_path = ROOT_PATH . ENV_FILE;
}
$env = file($env_path);
foreach ($env as $line) {
    if (!str_starts_with($line, '#') and trim($line) != '') {
        // everything from the # is a comment, also directly after the value,
        // so a secret value like a password can never contain the # char
        if (str_contains($line, '#')) {
            $line = substr($line, 0, strpos($line, '#'));
        }
        if (str_contains($line, "\n")) {
            $line = substr($line, 0, strpos($line, "\n"));
        }
        // split only on the first = so that a secret value may contain the = char
        $parts = explode('=', $line, 2);
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
                if ($key == ENV_CACHE) {
                    if (!in_array($var, ENV_CACHE_TYPES)) {
                        log_err($key . ' not expected to be ' . $var . ' (only ' . implode(',', ENV_CACHE_TYPES) . ' allowed)');
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

// server admin page authentication (see http/server_admin.php)
// IPs / CIDR ranges allowed to reach the server admin page; falls back to 'localhost'
define('SERVER_ADMIN_IP', getenv(ENV_SERVER_ADMIN_IP) ?: SYSTEM_ADMIN_IP_FALLBACK);

// SYSTEM configuration from environment variables or the default fallback value
// fixed IP of the main system admin as a second line of defence to prevent remote manipulation
define('SYSTEM_ADMIN_IP', SERVER_ADMIN_IP);
// the full access server admin (username + bcrypt password hash); empty = not configured
define('SERVER_ADMIN_USER', getenv(ENV_SERVER_ADMIN_USER) ?: '');
define('SERVER_ADMIN_PW', getenv(ENV_SERVER_ADMIN_PW) ?: '');
// two restricted server admins (username + bcrypt password hash, optional IP range); empty = not configured
define('SERVER_ADMIN_2_USER', getenv(ENV_SERVER_ADMIN_2_USER) ?: '');
define('SERVER_ADMIN_2_PW', getenv(ENV_SERVER_ADMIN_2_PW) ?: '');
define('SERVER_ADMIN_2_IP_FROM', getenv(ENV_SERVER_ADMIN_2_IP_FROM) ?: '');
define('SERVER_ADMIN_2_IP_TO', getenv(ENV_SERVER_ADMIN_2_IP_TO) ?: '');
define('SERVER_ADMIN_3_USER', getenv(ENV_SERVER_ADMIN_3_USER) ?: '');
define('SERVER_ADMIN_3_PW', getenv(ENV_SERVER_ADMIN_3_PW) ?: '');
define('SERVER_ADMIN_3_IP_FROM', getenv(ENV_SERVER_ADMIN_3_IP_FROM) ?: '');
define('SERVER_ADMIN_3_IP_TO', getenv(ENV_SERVER_ADMIN_3_IP_TO) ?: '');

// the program version is read from the version file, never from .env,
// because .env is not part of the release
// and would still contain the version of the release that has created it
$version = [];
foreach (file(ROOT_PATH . VERSION_FILE) as $line) {
    if (!str_starts_with($line, '#') and trim($line) != '') {
        if (str_contains($line, '#')) {
            $line = substr($line, 0, strpos($line, '#'));
        }
        $parts = explode('=', trim($line));
        if (count($parts) != 2) {
            log_err('unexpected line format in ' . ROOT_PATH . VERSION_FILE . ': ' . $line);
        } else {
            $version[trim($parts[0])] = trim($parts[1]);
        }
    }
}

// the version file is part of the release, so the code and the ui version are never missing
// and there is no fallback version in the code which could differ from the version file
if (!key_exists(ENV_CODE_VERSION, $version) or !key_exists(ENV_UI_VERSION, $version)) {
    log_err('the code or the ui version is missing in ' . ROOT_PATH . VERSION_FILE);
}
// the micro version with four parts e.g. 0.0.3.0 where the last part is the build number
define('SYSTEM_CODE_VERSION', $version[ENV_CODE_VERSION] ?? '');
define('SYSTEM_UI_VERSION', $version[ENV_UI_VERSION] ?? '');
// the minor version with three parts e.g. 0.0.3 which changes only if the json format
// or the database structure changes; it is the version of the json exports and of the database
define('SYSTEM_MINOR_VERSION', implode('.', array_slice(explode('.', SYSTEM_CODE_VERSION), 0, 3)));
// the version shown in the page footer: the micro version of the running code, but the minor
// version during a test run, so that a micro release does not change all html test files
define('SYSTEM_PAGE_VERSION', defined('SYSTEM_TEST_RUN') ? SYSTEM_MINOR_VERSION : SYSTEM_CODE_VERSION);
define('POD_NAME', getenv(ENV_POD_NAME) ?: POD_NAME_FALLBACK);
define('THIS_URL', getenv(ENV_THIS_URL) ?: THIS_URL_FALLBACK);
define('SYS_LOG_URL', getenv(ENV_SYS_LOG_URL) ?: '');
define('SOURCE_REPO_URL', getenv(ENV_SOURCE_REPO_URL) ?: SOURCE_REPO_URL_FALLBACK);

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

// system execution time control
// write an info log entry if the execution time of the script is longer than 2 seconds
define('SYSTEM_TIME_TIME_LIMIT_INFO', getenv(ENV_SYSTEM_TIME_LIMIT_INFO) ?: 2);
// write a warning log entry if the execution time of the script is longer than 3 seconds
define('SYSTEM_TIME_TIME_LIMIT_WARN', getenv(ENV_SYSTEM_TIME_LIMIT_WARN) ?: 3);
// write an error log entry if the execution time of the script is longer than 5 seconds
define('SYSTEM_TIME_TIME_LIMIT_ERR', getenv(ENV_SYSTEM_TIME_LIMIT_ERR) ?: 5);

// only used as default values for the initial setup to enable a automatic setup without ui interactions
define('ADMIN_USER', getenv(ENV_ADMIN_USER) ?: '');
define('ADMIN_PW', getenv(ENV_ADMIN_PW) ?: '');
define('ADMIN_MAIL', getenv(ENV_ADMIN_MAIL) ?: '');
// public admin contact email shown on the whitelist reject pages; defaults to the admin mail
define('SERVER_ADMIN_MAIL', getenv(ENV_SERVER_ADMIN_MAIL) ?: ADMIN_MAIL);
define('CO_ADMIN_USER', getenv(ENV_CO_ADMIN_USER) ?: '');
define('CO_ADMIN_PW', getenv(ENV_CO_ADMIN_PW) ?: '');
define('CO_ADMIN_MAIL', getenv(ENV_CO_ADMIN_MAIL) ?: '');
define('USER_NAME', getenv(ENV_USER_NAME) ?: '');
define('USER_PW', getenv(ENV_USER_PW) ?: '');
define('USER_MAIL', getenv(ENV_USER_MAIL) ?: '');
define('CO_USER_NAME', getenv(ENV_CO_USER_NAME) ?: '');
define('CO_USER_PW', getenv(ENV_CO_USER_PW) ?: '');
define('CO_USER_MAIL', getenv(ENV_CO_USER_MAIL) ?: '');

// the location type for the system cache which can be the database or with some permission adjustments a file folder
define('CACHE_LOCATION', getenv(ENV_CACHE) ?: ENV_CACHE_FALLBACK);
define('CACHE_MAX_AGE', getenv(ENV_CACHE_MAX_AGE) ?: ENV_CACHE_MAX_AGE_FALLBACK);
