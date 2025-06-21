<?php

// Database configuration from environment variables
define('SQL_DB_NAME', getenv('PGSQL_DATABASE') ?: 'zukunft');
define('SQL_DB_HOST', getenv('PGSQL_HOST') ?: 'localhost');
define('SQL_DB_USER', getenv('PGSQL_USERNAME') ?: 'zukunft');
define('SQL_DB_PASSWD', getenv('PGSQL_PASSWORD') ?: 'zukunft');
define('SQL_DB_ADMIN_USER', getenv('PGSQL_ADMIN_USERNAME') ?: 'postgres');
define('SQL_DB_ADMIN_PASSWD', getenv('PGSQL_ADMIN_PASSWORD') ?: 'zukunft');

define('SQL_DB_NAME_MYSQL', getenv('MYSQL_DATABASE') ?: 'zukunft');
define('SQL_DB_HOST_MYSQL', getenv('MYSQL_HOST') ?: 'localhost');
define('SQL_DB_USER_MYSQL', getenv('MYSQL_USERNAME') ?: 'zukunft');
define('SQL_DB_PASSWD_MYSQL', getenv('MYSQL_PASSWORD') ?: 'y9CJxs2JkVKvGmP');
