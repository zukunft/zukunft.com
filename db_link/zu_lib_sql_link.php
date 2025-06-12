<?php

// Database configuration from environment variables
define('SQL_DB_HOST', getenv('DB_HOST') ?: 'db');

define('SQL_DB_USER', getenv('DB_USERNAME') ?: 'zukunft');
define('SQL_DB_PASSWD', getenv('DB_PASSWORD') ?: 'zukunft');

define('SQL_DB_USER_MYSQL', getenv('DB_USERNAME') ?: 'zukunft');
define('SQL_DB_PASSWD_MYSQL', getenv('DB_PASSWORD') ?: 'y9CJxs2JkVKvGmP');
