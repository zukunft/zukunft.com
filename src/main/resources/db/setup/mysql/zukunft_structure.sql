-- --------------------------------------------------------

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT = @@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS = @@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION = @@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

-- Database:`zukunft`

-- --------------------------------------------------------

--
-- table structure for the core configuration of this pod e.g. the program version or pod url
--

CREATE TABLE IF NOT EXISTS config
(
    config_id   bigint           NOT NULL COMMENT 'the internal unique primary index',
    config_name varchar(255) DEFAULT NULL COMMENT 'short name of the configuration entry to be shown to the admin',
    code_id     varchar(255)     NOT NULL COMMENT 'unique id text to select a configuration value from the code',
    `value`     varchar(255) DEFAULT NULL COMMENT 'the configuration value as a string',
    description text         DEFAULT NULL COMMENT 'text to explain the config value to an admin user'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for the core configuration of this pod e.g. the program version or pod url';

--
-- AUTO_INCREMENT for table config
--
ALTER TABLE config
    MODIFY config_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure for system log types e.g. info,warning and error
--

CREATE TABLE IF NOT EXISTS sys_log_types
(
    sys_log_type_id   smallint         NOT NULL COMMENT 'the internal unique primary index',
    type_name         varchar(255)     NOT NULL COMMENT 'the unique type name as shown to the user and used for the selection',
    code_id           varchar(255) DEFAULT NULL COMMENT 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration',
    description       text         DEFAULT NULL COMMENT 'text to explain the type to the user as a tooltip; to be replaced by a language form entry'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for system log types e.g. info,warning and error';

--
-- AUTO_INCREMENT for table sys_log_types
--
ALTER TABLE sys_log_types
    MODIFY sys_log_type_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure to define the status of internal errors
--

CREATE TABLE IF NOT EXISTS sys_log_status
(
    sys_log_status_id smallint         NOT NULL COMMENT 'the internal unique primary index',
    type_name         varchar(255)     NOT NULL COMMENT 'the unique type name as shown to the user and used for the selection',
    code_id           varchar(255) DEFAULT NULL COMMENT 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration',
    description       text         DEFAULT NULL COMMENT 'text to explain the type to the user as a tooltip; to be replaced by a language form entry',
    action            varchar(255) DEFAULT NULL COMMENT 'description of the action to get to this status'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to define the status of internal errors';

--
-- AUTO_INCREMENT for table sys_log_status
--
ALTER TABLE sys_log_status
    MODIFY sys_log_status_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure to group the system log entries by function
--

CREATE TABLE IF NOT EXISTS sys_log_functions
(
    sys_log_function_id   smallint         NOT NULL COMMENT 'the internal unique primary index',
    sys_log_function_name varchar(255)     NOT NULL COMMENT 'the unique type name as shown to the user and used for the selection',
    code_id               varchar(255) DEFAULT NULL COMMENT 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration',
    description           text         DEFAULT NULL COMMENT 'text to explain the type to the user as a tooltip; to be replaced by a language form entry'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to group the system log entries by function';

--
-- AUTO_INCREMENT for table sys_log_functions
--
ALTER TABLE sys_log_functions
    MODIFY sys_log_function_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure for system error tracking and to measure execution times
--

CREATE TABLE IF NOT EXISTS sys_log
(
    sys_log_id          bigint     NOT NULL COMMENT 'the internal unique primary index',
    sys_log_time        timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp of the creation',
    sys_log_type_id     smallint   NOT NULL COMMENT 'the level e.g. debug,info,warning,error or fatal',
    sys_log_function_id smallint   NOT NULL COMMENT 'the function or function group for the entry e.g. db_write to measure the db write times',
    sys_log_text        text   DEFAULT NULL COMMENT 'the short text of the log entry to identify the error and to reduce the number of double entries',
    sys_log_description text   DEFAULT NULL COMMENT 'the long description with all details of the log entry to solve ti issue',
    sys_log_trace       text   DEFAULT NULL COMMENT 'the generated code trace to local the path to the error cause',
    user_id             bigint DEFAULT NULL COMMENT 'the id of the user who has caused the log entry',
    solver_id           bigint DEFAULT NULL COMMENT 'user id of the user that is trying to solve the problem',
    sys_log_status_id   bigint     NOT NULL DEFAULT 1
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for system error tracking and to measure execution times';

--
-- AUTO_INCREMENT for table sys_log
--
ALTER TABLE sys_log
    MODIFY sys_log_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure to define the execution time groups
--

CREATE TABLE IF NOT EXISTS system_time_types
(
    system_time_type_id smallint         NOT NULL COMMENT 'the internal unique primary index',
    type_name           varchar(255)     NOT NULL COMMENT 'the unique type name as shown to the user and used for the selection',
    code_id             varchar(255) DEFAULT NULL COMMENT 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration',
    description         text         DEFAULT NULL COMMENT 'text to explain the type to the user as a tooltip; to be replaced by a language form entry'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to define the execution time groups';

--
-- AUTO_INCREMENT for table system_time_types
--
ALTER TABLE system_time_types
    MODIFY system_time_type_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure for system execution time tracking
--

CREATE TABLE IF NOT EXISTS system_times
(
    system_time_id      bigint        NOT NULL COMMENT 'the internal unique primary index',
    start_time          timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'start time of the monitoring period',
    end_time            timestamp DEFAULT NULL COMMENT 'end time of the monitoring period',
    system_time_type_id smallint      NOT NULL COMMENT 'the area of the execution time e.g. db write',
    milliseconds        bigint        NOT NULL COMMENT 'the execution time in milliseconds'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for system execution time tracking';

--
-- AUTO_INCREMENT for table system_times
--
ALTER TABLE system_times
    MODIFY system_time_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure for predefined batch jobs that can be triggered by a user action or scheduled e.g. data synchronisation
--

CREATE TABLE IF NOT EXISTS job_types
(
    job_type_id smallint           NOT NULL COMMENT 'the internal unique primary index',
    type_name     varchar(255)     NOT NULL COMMENT 'the unique type name as shown to the user and used for the selection',
    code_id       varchar(255) DEFAULT NULL COMMENT 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration',
    description   text         DEFAULT NULL COMMENT 'text to explain the type to the user as a tooltip; to be replaced by a language form entry'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for predefined batch jobs that can be triggered by a user action or scheduled e.g. data synchronisation';

--
-- AUTO_INCREMENT for table job_types
--
ALTER TABLE job_types
    MODIFY job_type_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure to schedule jobs with predefined parameters
--

CREATE TABLE IF NOT EXISTS job_times
(
    job_time_id bigint          NOT NULL COMMENT 'the internal unique primary index',
    schedule    varchar(20) DEFAULT NULL COMMENT 'the crontab for the job schedule',
    job_type_id smallint        NOT NULL COMMENT 'the id of the job type that should be started',
    user_id     bigint          NOT NULL COMMENT 'the id of the user who edit the scheduler the last time',
    start       timestamp   DEFAULT NULL COMMENT 'the last start of the job',
    parameter   bigint      DEFAULT NULL COMMENT 'the phrase id that contains all parameters for the next job start'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to schedule jobs with predefined parameters';

--
-- AUTO_INCREMENT for table job_times
--
ALTER TABLE job_times
    MODIFY job_time_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure for each concrete job run
--

CREATE TABLE IF NOT EXISTS jobs
(
    job_id          bigint        NOT NULL COMMENT 'the internal unique primary index',
    user_id         bigint        NOT NULL COMMENT 'the id of the user who has requested the job by editing the scheduler the last time',
    job_type_id     smallint      NOT NULL COMMENT 'the id of the job type that should be started',
    request_time    timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp of the request for the job execution',
    start_time      timestamp DEFAULT NULL COMMENT 'timestamp when the system has started the execution',
    end_time        timestamp DEFAULT NULL COMMENT 'timestamp when the job has been completed or canceled',
    parameter       bigint    DEFAULT NULL COMMENT 'id of the phrase with the snapped parameter set for this job start',
    change_field_id smallint  DEFAULT NULL COMMENT 'e.g. for undo jobs the id of the field that should be changed',
    row_id          bigint    DEFAULT NULL COMMENT 'e.g. for undo jobs the id of the row that should be changed',
    source_id       bigint    DEFAULT NULL COMMENT 'used for import to link the source',
    ref_id          bigint    DEFAULT NULL COMMENT 'used for import to link the reference'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for each concrete job run';

--
-- AUTO_INCREMENT for table jobs
--
ALTER TABLE jobs
    MODIFY job_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure for the user types e.g. to set the confirmation level of a user
--

CREATE TABLE IF NOT EXISTS user_types
(
    user_type_id smallint         NOT NULL COMMENT 'the internal unique primary index',
    type_name    varchar(255)     NOT NULL COMMENT 'the unique type name as shown to the user and used for the selection',
    code_id      varchar(255) DEFAULT NULL COMMENT 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration',
    description  text         DEFAULT NULL COMMENT 'text to explain the type to the user as a tooltip; to be replaced by a language form entry'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for the user types e.g. to set the confirmation level of a user';

--
-- AUTO_INCREMENT for table user_types
--
ALTER TABLE user_types
    MODIFY user_type_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure to define the user roles and read and write rights
--

CREATE TABLE IF NOT EXISTS user_profiles
(
    user_profile_id smallint      NOT NULL COMMENT 'the internal unique primary index',
    type_name    varchar(255)     NOT NULL COMMENT 'the unique type name as shown to the user and used for the selection',
    code_id      varchar(255) DEFAULT NULL COMMENT 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration',
    description  text         DEFAULT NULL COMMENT 'text to explain the type to the user as a tooltip; to be replaced by a language form entry',
    right_level  smallint     DEFAULT NULL COMMENT 'the access right level to prevent not permitted right gaining'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to define the user roles and read and write rights';

--
-- AUTO_INCREMENT for table user_profiles
--
ALTER TABLE user_profiles
    MODIFY user_profile_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure for person identification types e.g. passports
--

CREATE TABLE IF NOT EXISTS user_official_types
(
    user_official_type_id smallint NOT NULL COMMENT 'the internal unique primary index',
    type_name    varchar(255)      NOT NULL COMMENT 'the unique type name as shown to the user and used for the selection',
    code_id      varchar(255)  DEFAULT NULL COMMENT 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration',
    description  text          DEFAULT NULL COMMENT 'text to explain the type to the user as a tooltip; to be replaced by a language form entry'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for person identification types e.g. passports';

--
-- AUTO_INCREMENT for table user_official_types
--
ALTER TABLE user_official_types
    MODIFY user_official_type_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure for users including system users; only users can add data
--

CREATE TABLE IF NOT EXISTS users
(
    user_id            bigint           NOT NULL COMMENT 'the internal unique primary index',
    user_name          varchar(255)     NOT NULL COMMENT 'the user name unique for this pod',
    ip_address         varchar(100) DEFAULT NULL COMMENT 'all users a first identified with the ip address',
    password           varchar(255) DEFAULT NULL COMMENT 'the hash value of the password',
    description        text         DEFAULT NULL COMMENT 'for system users the description to explain the profile to human users',
    code_id            varchar(100) DEFAULT NULL COMMENT 'to select e.g. the system batch user',
    user_profile_id    bigint       DEFAULT NULL COMMENT 'to define the user roles and read and write rights',
    user_type_id       bigint       DEFAULT NULL COMMENT 'to set the confirmation level of a user',
    excluded           smallint     DEFAULT NULL COMMENT 'true if the user is deactivated but cannot be deleted due to log entries',
    right_level        smallint     DEFAULT NULL COMMENT 'the access right level to prevent not permitted right gaining',
    email              varchar(255) DEFAULT NULL COMMENT 'the primary email for verification',
    email_status       smallint     DEFAULT NULL COMMENT 'if the email has been verified or if a password reset has been send',
    email_alternative  varchar(255) DEFAULT NULL COMMENT 'an alternative email for account recovery',
    mobile_number      varchar(100) DEFAULT NULL,
    mobile_status      smallint     DEFAULT NULL,
    activation_key     varchar(255) DEFAULT NULL,
    activation_timeout timestamp    DEFAULT NULL,
    first_name         varchar(255) DEFAULT NULL,
    last_name          varchar(255) DEFAULT NULL,
    name_triple_id     bigint       DEFAULT NULL COMMENT 'triple that contains e.g. the given name,family name,selected name or title of the person',
    geo_triple_id      bigint       DEFAULT NULL COMMENT 'the post address with street,city or any other form of geo location for physical transport',
    geo_status_id      smallint     DEFAULT NULL,
    official_id        varchar(255) DEFAULT NULL COMMENT 'e.g. the number of the passport',
    official_id_type   smallint     DEFAULT NULL,
    official_id_status smallint     DEFAULT NULL,
    term_id            bigint       DEFAULT NULL COMMENT 'the last term that the user had used',
    view_id            bigint       DEFAULT NULL COMMENT 'the last mask that the user has used',
    source_id          bigint       DEFAULT NULL COMMENT 'the last source used by this user to have a default for the next value',
    user_status_id     smallint     DEFAULT NULL COMMENT 'e.g. to exclude inactive users',
    created            timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_login         timestamp    DEFAULT NULL,
    last_logoff        timestamp    DEFAULT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for users including system users; only users can add data';

--
-- AUTO_INCREMENT for table users
--
ALTER TABLE users
    MODIFY user_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure of ip addresses that should be blocked
--

CREATE TABLE IF NOT EXISTS ip_ranges
(
    ip_range_id bigint      NOT NULL COMMENT 'the internal unique primary index',
    ip_from     varchar(46) NOT NULL,
    ip_to       varchar(46) NOT NULL,
    reason      text        NOT NULL,
    is_active   smallint    NOT NULL DEFAULT 1
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'of ip addresses that should be blocked';


--
-- AUTO_INCREMENT for table ip_ranges
--
ALTER TABLE ip_ranges
    MODIFY ip_range_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure to control the user frontend sessions
--

CREATE TABLE IF NOT EXISTS sessions
(
    session_id  bigint           NOT NULL COMMENT 'the internal unique primary index',
    uid         bigint           NOT NULL COMMENT 'the user session id as get by the frontend',
    hash        varchar(255)     NOT NULL,
    expire_date timestamp        NOT NULL,
    ip          varchar(46)      NOT NULL,
    agent       varchar(255) DEFAULT NULL,
    cookie_crc  text         DEFAULT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to control the user frontend sessions';

--
-- AUTO_INCREMENT for table sessions
--
ALTER TABLE sessions
    MODIFY session_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure for add,change,delete,undo and redo actions
--

CREATE TABLE IF NOT EXISTS change_actions
(
    change_action_id   smallint     NOT NULL COMMENT 'the internal unique primary index',
    change_action_name varchar(255) NOT NULL,
    code_id            varchar(255) NOT NULL,
    description        text     DEFAULT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for add,change,delete,undo and redo actions';

--
-- AUTO_INCREMENT for table change_actions
--
ALTER TABLE change_actions
    MODIFY change_action_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure to keep the original table name even if a table name has changed and to avoid log changes in case a table is renamed
--

CREATE TABLE IF NOT EXISTS change_tables
(
    change_table_id   smallint         NOT NULL COMMENT 'the internal unique primary index',
    change_table_name varchar(255)     NOT NULL COMMENT 'the real name',
    code_id           varchar(255) DEFAULT NULL COMMENT 'with this field tables can be combined in case of renaming',
    description       text         DEFAULT NULL COMMENT 'the user readable name'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to keep the original table name even if a table name has changed and to avoid log changes in case a table is renamed';

--
-- AUTO_INCREMENT for table change_tables
--
ALTER TABLE change_tables
    MODIFY change_table_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure to keep the original field name even if a table name has changed
--

CREATE TABLE IF NOT EXISTS change_fields
(
    change_field_id   smallint         NOT NULL COMMENT 'the internal unique primary index',
    table_id          bigint           NOT NULL COMMENT 'because every field must only be unique within a table',
    change_field_name varchar(255)     NOT NULL COMMENT 'the real name',
    code_id           varchar(255) DEFAULT NULL COMMENT 'to display the change with some linked information',
    description       text         DEFAULT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to keep the original field name even if a table name has changed';

--
-- AUTO_INCREMENT for table change_fields
--
ALTER TABLE change_fields
    MODIFY change_field_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure to log all changes done by any user on all tables except value and link changes
--

CREATE TABLE IF NOT EXISTS changes
(
    change_id        bigint     NOT NULL COMMENT 'the prime key to identify the change change',
    change_time      timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'time when the user has confirmed the change',
    user_id          bigint     NOT NULL COMMENT 'reference to the user who has done the change',
    change_action_id smallint   NOT NULL COMMENT 'the curl action',
    row_id           bigint DEFAULT NULL COMMENT 'the prime id in the table with the change',
    change_field_id  smallint   NOT NULL,
    old_value        text   DEFAULT NULL,
    new_value        text   DEFAULT NULL,
    old_id           bigint DEFAULT NULL COMMENT 'old value id',
    new_id           bigint DEFAULT NULL COMMENT 'new value id'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to log all changes done by any user on all tables except value and link changes';

--
-- AUTO_INCREMENT for table changes
--
ALTER TABLE changes
    MODIFY change_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure to log all changes done by any user on the group name for values with up to 16 phrases
--

CREATE TABLE IF NOT EXISTS changes_norm
(
    change_id        bigint        NOT NULL COMMENT 'the prime key to identify the change changes_norm',
    change_time      timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'time when the user has confirmed the change',
    user_id          bigint        NOT NULL COMMENT 'reference to the user who has done the change',
    change_action_id smallint      NOT NULL COMMENT 'the curl action',
    row_id           char(112) DEFAULT NULL COMMENT 'the prime id in the table with the change',
    change_field_id  smallint      NOT NULL,
    old_value        text      DEFAULT NULL,
    new_value        text      DEFAULT NULL,
    old_id           char(112) DEFAULT NULL COMMENT 'old value id',
    new_id           char(112) DEFAULT NULL COMMENT 'new value id'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to log all changes done by any user on the group name for values with up to 16 phrases';

--
-- AUTO_INCREMENT for table changes_norm
--
ALTER TABLE changes_norm
    MODIFY change_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure to log all changes done by any user on the group name for values with more than 16 phrases
--

CREATE TABLE IF NOT EXISTS changes_big
(
    change_id        bigint     NOT NULL COMMENT 'the prime key to identify the change changes_big',
    change_time      timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'time when the user has confirmed the change',
    user_id          bigint     NOT NULL COMMENT 'reference to the user who has done the change',
    change_action_id smallint   NOT NULL COMMENT 'the curl action',
    row_id           text   DEFAULT NULL COMMENT 'the prime id in the table with the change',
    change_field_id  smallint   NOT NULL,
    old_value        text   DEFAULT NULL,
    new_value        text   DEFAULT NULL,
    old_id           text   DEFAULT NULL COMMENT 'old value id',
    new_id           text   DEFAULT NULL COMMENT 'new value id'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to log all changes done by any user on the group name for values with more than 16 phrases';

--
-- AUTO_INCREMENT for table changes_big
--
ALTER TABLE changes_big
    MODIFY change_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure to log all changes done by any user on values with a standard group id
--

CREATE TABLE IF NOT EXISTS change_values_norm
(
    change_id        bigint     NOT NULL COMMENT 'the prime key to identify the change change_values_norm',
    change_time      timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'time when the user has confirmed the change',
    user_id          bigint     NOT NULL COMMENT 'reference to the user who has done the change',
    change_action_id smallint   NOT NULL COMMENT 'the curl action',
    group_id         char(112)  NOT NULL,
    change_field_id  smallint   NOT NULL,
    old_value        double DEFAULT NULL,
    new_value        double DEFAULT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to log all changes done by any user on values with a standard group id';

--
-- AUTO_INCREMENT for table change_values_norm
--
ALTER TABLE change_values_norm
    MODIFY change_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure to log all time value changes done by any user on values with a standard group id
--

CREATE TABLE IF NOT EXISTS change_values_time_norm
(
    change_id        bigint     NOT NULL COMMENT 'the prime key to identify the change change_values_time_norm',
    change_time      timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'time when the user has confirmed the change',
    user_id          bigint     NOT NULL COMMENT 'reference to the user who has done the change',
    change_action_id smallint   NOT NULL COMMENT 'the curl action',
    group_id         char(112)  NOT NULL,
    change_field_id  smallint   NOT NULL,
    old_value        timestamp DEFAULT NULL,
    new_value        timestamp DEFAULT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
COMMENT 'to log all time value changes done by any user on values with a standard group id';

--
-- AUTO_INCREMENT for table change_values_time_norm
--
ALTER TABLE change_values_time_norm
    MODIFY change_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure to log all text value changes done by any user on values with a standard group id
--

CREATE TABLE IF NOT EXISTS change_values_text_norm
(
    change_id        bigint     NOT NULL COMMENT 'the prime key to identify the change change_values_text_norm',
    change_time      timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'time when the user has confirmed the change',
    user_id          bigint     NOT NULL COMMENT 'reference to the user who has done the change',
    change_action_id smallint   NOT NULL COMMENT 'the curl action',
    group_id         char(112)  NOT NULL,
    change_field_id  smallint   NOT NULL,
    old_value        text DEFAULT NULL,
    new_value        text DEFAULT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
COMMENT 'to log all text value changes done by any user on values with a standard group id';

--
-- AUTO_INCREMENT for table change_values_text_norm
--
ALTER TABLE change_values_text_norm
    MODIFY change_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure to log all geo value changes done by any user on values with a standard group id
--

CREATE TABLE IF NOT EXISTS change_values_geo_norm
(
    change_id        bigint     NOT NULL COMMENT 'the prime key to identify the change change_values_geo_norm',
    change_time      timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'time when the user has confirmed the change',
    user_id          bigint     NOT NULL COMMENT 'reference to the user who has done the change',
    change_action_id smallint   NOT NULL COMMENT 'the curl action',
    group_id         char(112)  NOT NULL,
    change_field_id  smallint   NOT NULL,
    old_value        point  DEFAULT NULL,
    new_value        point  DEFAULT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
COMMENT 'to log all geo value changes done by any user on values with a standard group id';

--
-- AUTO_INCREMENT for table change_values_geo_norm
--
ALTER TABLE change_values_geo_norm
    MODIFY change_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure to log all changes done by any user on values with a prime group id
--

CREATE TABLE IF NOT EXISTS change_values_prime
(
    change_id        bigint     NOT NULL COMMENT 'the prime key to identify the change change_values_prime',
    change_time      timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'time when the user has confirmed the change',
    user_id          bigint     NOT NULL COMMENT 'reference to the user who has done the change',
    change_action_id smallint   NOT NULL COMMENT 'the curl action',
    group_id         bigint     NOT NULL,
    change_field_id  smallint   NOT NULL,
    old_value        double DEFAULT NULL,
    new_value        double DEFAULT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to log all changes done by any user on values with a prime group id';

--
-- AUTO_INCREMENT for table change_values_prime
--
ALTER TABLE change_values_prime
    MODIFY change_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure to log all time value changes done by any user on values with a prime group id
--

CREATE TABLE IF NOT EXISTS change_values_time_prime
(
    change_id        bigint     NOT NULL COMMENT 'the prime key to identify the change change_values_time_prime',
    change_time      timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'time when the user has confirmed the change',
    user_id          bigint     NOT NULL COMMENT 'reference to the user who has done the change',
    change_action_id smallint   NOT NULL COMMENT 'the curl action',
    group_id         bigint     NOT NULL,
    change_field_id  smallint   NOT NULL,
    old_value        timestamp DEFAULT NULL,
    new_value        timestamp DEFAULT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
COMMENT 'to log all time value changes done by any user on values with a prime group id';

--
-- AUTO_INCREMENT for table change_values_time_prime
--
ALTER TABLE change_values_time_prime
    MODIFY change_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure to log all text value changes done by any user on values with a prime group id
--

CREATE TABLE IF NOT EXISTS change_values_text_prime
(
    change_id        bigint     NOT NULL COMMENT 'the prime key to identify the change change_values_text_prime',
    change_time      timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'time when the user has confirmed the change',
    user_id          bigint     NOT NULL COMMENT 'reference to the user who has done the change',
    change_action_id smallint   NOT NULL COMMENT 'the curl action',
    group_id         bigint     NOT NULL,
    change_field_id  smallint   NOT NULL,
    old_value        text   DEFAULT NULL,
    new_value        text   DEFAULT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
COMMENT 'to log all text value changes done by any user on values with a prime group id';

--
-- AUTO_INCREMENT for table change_values_text_prime
--
ALTER TABLE change_values_text_prime
    MODIFY change_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure to log all geo value changes done by any user on values with a prime group id
--

CREATE TABLE IF NOT EXISTS change_values_geo_prime
(
    change_id        bigint     NOT NULL COMMENT 'the prime key to identify the change change_values_geo_prime',
    change_time      timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'time when the user has confirmed the change',
    user_id          bigint     NOT NULL COMMENT 'reference to the user who has done the change',
    change_action_id smallint   NOT NULL COMMENT 'the curl action',
    group_id         bigint     NOT NULL,
    change_field_id  smallint   NOT NULL,
    old_value        point  DEFAULT NULL,
    new_value        point  DEFAULT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
COMMENT 'to log all geo value changes done by any user on values with a prime group id';

--
-- AUTO_INCREMENT for table change_values_geo_prime
--
ALTER TABLE change_values_geo_prime
    MODIFY change_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure to log all changes done by any user on values with a big group id
--

CREATE TABLE IF NOT EXISTS change_values_big
(
    change_id        bigint     NOT NULL COMMENT 'the prime key to identify the change change_values_big',
    change_time      timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'time when the user has confirmed the change',
    user_id          bigint     NOT NULL COMMENT 'reference to the user who has done the change',
    change_action_id smallint   NOT NULL COMMENT 'the curl action',
    group_id         text       NOT NULL,
    change_field_id  smallint   NOT NULL,
    old_value        double DEFAULT NULL,
    new_value        double DEFAULT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to log all changes done by any user on values with a big group id';

--
-- AUTO_INCREMENT for table change_values_big
--
ALTER TABLE change_values_big
    MODIFY change_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure to log all time value changes done by any user on values with a big group id
--

CREATE TABLE IF NOT EXISTS change_values_time_big
(
    change_id        bigint     NOT NULL COMMENT 'the prime key to identify the change change_values_time_big',
    change_time      timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'time when the user has confirmed the change',
    user_id          bigint     NOT NULL COMMENT 'reference to the user who has done the change',
    change_action_id smallint   NOT NULL COMMENT 'the curl action',
    group_id         text       NOT NULL,
    change_field_id  smallint   NOT NULL,
    old_value        timestamp DEFAULT NULL,
    new_value        timestamp DEFAULT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
COMMENT 'to log all time value changes done by any user on values with a big group id';

--
-- AUTO_INCREMENT for table change_values_time_big
--
ALTER TABLE change_values_time_big
    MODIFY change_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure to log all text value changes done by any user on values with a big group id
--

CREATE TABLE IF NOT EXISTS change_values_text_big
(
    change_id        bigint     NOT NULL COMMENT 'the prime key to identify the change change_values_text_big',
    change_time      timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'time when the user has confirmed the change',
    user_id          bigint     NOT NULL COMMENT 'reference to the user who has done the change',
    change_action_id smallint   NOT NULL COMMENT 'the curl action',
    group_id         text       NOT NULL,
    change_field_id  smallint   NOT NULL,
    old_value        text DEFAULT NULL,
    new_value        text DEFAULT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
COMMENT 'to log all text value changes done by any user on values with a big group id';

--
-- AUTO_INCREMENT for table change_values_text_big
--
ALTER TABLE change_values_text_big
    MODIFY change_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure to log all geo value changes done by any user on values with a big group id
--

CREATE TABLE IF NOT EXISTS change_values_geo_big
(
    change_id        bigint     NOT NULL COMMENT 'the prime key to identify the change change_values_geo_big',
    change_time      timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'time when the user has confirmed the change',
    user_id          bigint     NOT NULL COMMENT 'reference to the user who has done the change',
    change_action_id smallint   NOT NULL COMMENT 'the curl action',
    group_id         text       NOT NULL,
    change_field_id  smallint   NOT NULL,
    old_value        point  DEFAULT NULL,
    new_value        point  DEFAULT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
COMMENT 'to log all geo value changes done by any user on values with a big group id';

--
-- AUTO_INCREMENT for table change_values_geo_big
--
ALTER TABLE change_values_geo_big
    MODIFY change_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure to log the link changes done by the users
--

CREATE TABLE IF NOT EXISTS change_links
(
    change_link_id   bigint     NOT NULL COMMENT 'the prime key to identify the change change_link',
    change_time      timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'time when the user has confirmed the change',
    user_id          bigint     NOT NULL COMMENT 'reference to the user who has done the change',
    change_action_id smallint   NOT NULL COMMENT 'the curl action',
    row_id           bigint DEFAULT NULL COMMENT 'the prime id in the table with the change',
    change_table_id  bigint     NOT NULL,
    old_from_id      bigint DEFAULT NULL,
    old_link_id      bigint DEFAULT NULL,
    old_to_id        bigint DEFAULT NULL,
    old_text_from    text   DEFAULT NULL,
    old_text_link    text   DEFAULT NULL,
    old_text_to      text   DEFAULT NULL,
    new_from_id      bigint DEFAULT NULL,
    new_link_id      bigint DEFAULT NULL,
    new_to_id        bigint DEFAULT NULL COMMENT 'either internal row id or the ref type id of the external system e.g. 2 for wikidata',
    new_text_from    text   DEFAULT NULL,
    new_text_link    text   DEFAULT NULL,
    new_text_to      text   DEFAULT NULL COMMENT 'the fixed text to display to the user or the external reference id e.g. Q1 (for universe) in case of wikidata'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to log the link changes done by the users';

--
-- AUTO_INCREMENT for table change_links
--
ALTER TABLE change_links
    MODIFY change_link_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure for predefined code to a some pods
--

CREATE TABLE IF NOT EXISTS pod_types
(
    pod_type_id smallint         NOT NULL COMMENT 'the internal unique primary index',
    type_name   varchar(255)     NOT NULL COMMENT 'the unique type name as shown to the user and used for the selection',
    code_id     varchar(255) DEFAULT NULL COMMENT 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration',
    description text         DEFAULT NULL COMMENT 'text to explain the type to the user as a tooltip; to be replaced by a language form entry'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for predefined code to a some pods';

--
-- AUTO_INCREMENT for table pod_types
--
ALTER TABLE pod_types
    MODIFY pod_type_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure for the actual status of a pod
--

CREATE TABLE IF NOT EXISTS pod_status
(
    pod_status_id smallint         NOT NULL COMMENT 'the internal unique primary index',
    type_name     varchar(255)     NOT NULL COMMENT 'the unique type name as shown to the user and used for the selection',
    code_id       varchar(255) DEFAULT NULL COMMENT 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration',
    description   text         DEFAULT NULL COMMENT 'text to explain the type to the user as a tooltip; to be replaced by a language form entry'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for the actual status of a pod';

--
-- AUTO_INCREMENT for table pod_status
--
ALTER TABLE pod_status
    MODIFY pod_status_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure for the technical details of the mash network pods
--

CREATE TABLE IF NOT EXISTS pods
(
    pod_id          bigint           NOT NULL COMMENT 'the internal unique primary index',
    type_name       varchar(255)     NOT NULL COMMENT 'the unique type name as shown to the user and used for the selection',
    code_id         varchar(255) DEFAULT NULL COMMENT 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration',
    description     text         DEFAULT NULL COMMENT 'text to explain the type to the user as a tooltip; to be replaced by a language form entry',
    pod_type_id     smallint     DEFAULT NULL,
    pod_url         varchar(255)     NOT NULL,
    pod_status_id   smallint     DEFAULT NULL,
    param_triple_id bigint       DEFAULT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for the technical details of the mash network pods';

--
-- AUTO_INCREMENT for table pods
--
ALTER TABLE pods
    MODIFY pod_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure for the write access control
--

CREATE TABLE IF NOT EXISTS protection_types
(
    protection_type_id smallint         NOT NULL COMMENT 'the internal unique primary index',
    type_name          varchar(255)     NOT NULL COMMENT 'the unique type name as shown to the user and used for the selection',
    code_id            varchar(255) DEFAULT NULL COMMENT 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration',
    description        text         DEFAULT NULL COMMENT 'text to explain the type to the user as a tooltip; to be replaced by a language form entry'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for the write access control';

--
-- AUTO_INCREMENT for table protection_types
--
ALTER TABLE protection_types
    MODIFY protection_type_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure for the read access control
--

CREATE TABLE IF NOT EXISTS share_types
(
    share_type_id smallint         NOT NULL COMMENT 'the internal unique primary index',
    type_name     varchar(255)     NOT NULL COMMENT 'the unique type name as shown to the user and used for the selection',
    code_id       varchar(255) DEFAULT NULL COMMENT 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration',
    description   text         DEFAULT NULL COMMENT 'text to explain the type to the user as a tooltip; to be replaced by a language form entry'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for the read access control';

--
-- AUTO_INCREMENT for table share_types
--
ALTER TABLE share_types
    MODIFY share_type_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure for table languages
--

CREATE TABLE IF NOT EXISTS languages
(
    language_id    smallint         NOT NULL COMMENT 'the internal unique primary index',
    language_name  varchar(255)     NOT NULL,
    code_id        varchar(100) DEFAULT NULL,
    description    text         DEFAULT NULL,
    wikimedia_code varchar(100) DEFAULT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for table languages';

--
-- AUTO_INCREMENT for table languages
--
ALTER TABLE languages
    MODIFY language_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure for language forms like plural
--

CREATE TABLE IF NOT EXISTS language_forms
(
    language_form_id   smallint         NOT NULL COMMENT 'the internal unique primary index',
    language_form_name varchar(255) DEFAULT NULL COMMENT 'type of adjustment of a term in a language e.g. plural',
    code_id            varchar(100) DEFAULT NULL,
    description        text         DEFAULT NULL,
    language_id        bigint       DEFAULT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for language forms like plural';

--
-- AUTO_INCREMENT for table language_forms
--
ALTER TABLE language_forms
    MODIFY language_form_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure for a short text, that can be used to search for values or results with a 64 bit database key because humans will never be able to use more than a few million words
--

CREATE TABLE IF NOT EXISTS words
(
    word_id        bigint       NOT     NULL COMMENT 'the internal unique primary index',
    user_id        bigint       DEFAULT NULL COMMENT 'the owner / creator of the word',
    word_name      varchar(255) NOT     NULL COMMENT 'the text used for searching',
    plural         varchar(255) DEFAULT NULL COMMENT 'to be replaced by a language form entry; TODO to be move to language forms',
    description    text         DEFAULT NULL COMMENT 'to be replaced by a language form entry',
    phrase_type_id smallint     DEFAULT NULL COMMENT 'to link coded functionality to words e.g. to exclude measure words from a percent result',
    view_id        bigint       DEFAULT NULL COMMENT 'the default mask for this word',
    `values`       bigint       DEFAULT NULL COMMENT 'number of values linked to the word, which gives an indication of the importance',
    inactive       smallint     DEFAULT NULL COMMENT 'true if the word is not yet active e.g. because it is moved to the prime words with a 16 bit id',
    code_id        varchar(255) DEFAULT NULL COMMENT 'to link coded functionality to a specific word e.g. to get the values of the system configuration',
    excluded       smallint     DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id  smallint     DEFAULT NULL COMMENT 'to restrict the access',
    protect_id     smallint     DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for a short text, that can be used to search for values or results with a 64 bit database key because humans will never be able to use more than a few million words';

--
-- AUTO_INCREMENT for table words
--
ALTER TABLE words
    MODIFY word_id int(11) NOT NULL AUTO_INCREMENT;

--
-- table structure to save user specific changes for a short text, that can be used to search for values or results with a 64 bit database key because humans will never be able to use more than a few million words
--

CREATE TABLE IF NOT EXISTS user_words
(
    word_id        bigint       NOT NULL              COMMENT 'with the user_id the internal unique primary index',
    user_id        bigint       NOT NULL              COMMENT 'the changer of the word',
    language_id    bigint       NOT NULL DEFAULT 1    COMMENT 'the text used for searching',
    word_name      varchar(255)          DEFAULT NULL COMMENT 'the text used for searching',
    plural         varchar(255)          DEFAULT NULL COMMENT 'to be replaced by a language form entry; TODO to be move to language forms',
    description    text                  DEFAULT NULL COMMENT 'to be replaced by a language form entry',
    phrase_type_id smallint              DEFAULT NULL COMMENT 'to link coded functionality to words e.g. to exclude measure words from a percent result',
    view_id        bigint                DEFAULT NULL COMMENT 'the default mask for this word',
    `values`       bigint                DEFAULT NULL COMMENT 'number of values linked to the word, which gives an indication of the importance',
    excluded       smallint              DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id  smallint              DEFAULT NULL COMMENT 'to restrict the access',
    protect_id     smallint              DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for a short text, that can be used to search for values or results with a 64 bit database key because humans will never be able to use more than a few million words';

-- --------------------------------------------------------

--
-- table structure for verbs / triple predicates to use predefined behavior
--

CREATE TABLE IF NOT EXISTS verbs
(
    verb_id             smallint         NOT NULL COMMENT 'the internal unique primary index',
    verb_name           varchar(255)     NOT NULL COMMENT 'the unique type name as shown to the user and used for the selection',
    code_id             varchar(255) DEFAULT NULL COMMENT 'id text to link coded functionality to a specific verb',
    description         text         DEFAULT NULL COMMENT 'text to explain the type to the user as a tooltip; to be replaced by a language form entry',
    condition_type      bigint       DEFAULT NULL,
    formula_name        varchar(255) DEFAULT NULL COMMENT 'naming used in formulas',
    name_plural_reverse varchar(255) DEFAULT NULL COMMENT 'english description for the reverse list, e.g. Companies are ... TODO move to language forms',
    name_plural         varchar(255) DEFAULT NULL,
    name_reverse        varchar(255) DEFAULT NULL,
    words               bigint       DEFAULT NULL COMMENT 'used for how many phrases or formulas'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for verbs / triple predicates to use predefined behavior';

--
-- AUTO_INCREMENT for table verbs
--
ALTER TABLE verbs
    MODIFY verb_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure to link one word or triple with a verb to another word or triple
--

CREATE TABLE IF NOT EXISTS triples
(
    triple_id           bigint           NOT NULL COMMENT 'the internal unique primary index',
    from_phrase_id      bigint       DEFAULT NULL COMMENT 'the phrase_id that is linked which can be null e.g. if a symbol is assigned to a triple (m/s is symbol for meter per second)',
    verb_id             bigint           NOT NULL COMMENT 'the verb_id that defines how the phrases are linked',
    to_phrase_id        bigint           NOT NULL COMMENT 'the phrase_id to which the first phrase is linked',
    user_id             bigint       DEFAULT NULL COMMENT 'the owner / creator of the triple',
    triple_name         varchar(255) DEFAULT NULL COMMENT 'the name used which must be unique within the terms of the user',
    name_given          varchar(255) DEFAULT NULL COMMENT 'the unique name manually set by the user,which can be null if the generated name should be used',
    name_generated      varchar(255) DEFAULT NULL COMMENT 'the generated name is saved in the database for database base unique check based on the phrases and verb,which can be overwritten by the given name',
    description         text         DEFAULT NULL COMMENT 'text that should be shown to the user in case of mouseover on the triple name',
    triple_condition_id bigint       DEFAULT NULL COMMENT 'formula_id of a formula with a boolean result; the term is only added if formula result is true',
    phrase_type_id      smallint     DEFAULT NULL COMMENT 'to link coded functionality to words e.g. to exclude measure words from a percent result',
    view_id             bigint       DEFAULT NULL COMMENT 'the default mask for this triple',
    `values`            bigint       DEFAULT NULL COMMENT 'number of values linked to the word,which gives an indication of the importance',
    inactive            smallint     DEFAULT NULL COMMENT 'true if the word is not yet active e.g. because it is moved to the prime words with a 16 bit id',
    code_id             varchar(255) DEFAULT NULL COMMENT 'to link coded functionality to a specific triple e.g. to get the values of the system configuration',
    excluded            smallint     DEFAULT NULL COMMENT 'true if a user,but not all,have removed it',
    share_type_id       smallint     DEFAULT NULL COMMENT 'to restrict the access',
    protect_id          smallint     DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to link one word or triple with a verb to another word or triple';

--
-- AUTO_INCREMENT for table triples
--
ALTER TABLE triples
    MODIFY triple_id int(11) NOT NULL AUTO_INCREMENT;

--
-- table structure to save user specific changes to link one word or triple with a verb to another word or triple
--

CREATE TABLE IF NOT EXISTS user_triples
(
    triple_id           bigint           NOT NULL COMMENT 'with the user_id the internal unique primary index',
    user_id             bigint           NOT NULL COMMENT 'the changer of the triple',
    language_id         bigint NOT NULL DEFAULT 1 COMMENT 'the name used which must be unique within the terms of the user',
    triple_name         varchar(255) DEFAULT NULL COMMENT 'the name used which must be unique within the terms of the user',
    name_given          varchar(255) DEFAULT NULL COMMENT 'the unique name manually set by the user,which can be null if the generated name should be used',
    name_generated      varchar(255) DEFAULT NULL COMMENT 'the generated name is saved in the database for database base unique check based on the phrases and verb,which can be overwritten by the given name',
    description         text         DEFAULT NULL COMMENT 'text that should be shown to the user in case of mouseover on the triple name',
    triple_condition_id bigint       DEFAULT NULL COMMENT 'formula_id of a formula with a boolean result; the term is only added if formula result is true',
    phrase_type_id      smallint     DEFAULT NULL COMMENT 'to link coded functionality to words e.g. to exclude measure words from a percent result',
    view_id             bigint       DEFAULT NULL COMMENT 'the default mask for this triple',
    `values`            bigint       DEFAULT NULL COMMENT 'number of values linked to the word,which gives an indication of the importance',
    excluded            smallint     DEFAULT NULL COMMENT 'true if a user,but not all,have removed it',
    share_type_id       smallint     DEFAULT NULL COMMENT 'to restrict the access',
    protect_id          smallint     DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to link one word or triple with a verb to another word or triple';

-- --------------------------------------------------------

--
-- table structure for the actual status of tables for a phrase
--

CREATE TABLE IF NOT EXISTS phrase_table_status
(
    phrase_table_status_id smallint NOT NULL COMMENT 'the internal unique primary index',
    type_name     varchar(255)      NOT NULL COMMENT 'the unique type name as shown to the user and used for the selection',
    code_id       varchar(255)  DEFAULT NULL COMMENT 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration',
    description   text          DEFAULT NULL COMMENT 'text to explain the type to the user as a tooltip; to be replaced by a language form entry'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for the actual status of tables for a phrase';

--
-- AUTO_INCREMENT for table phrase_table_status
--
ALTER TABLE phrase_table_status
    MODIFY phrase_table_status_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure remember which phrases are stored in which table and pod
--

CREATE TABLE IF NOT EXISTS phrase_tables
(
    phrase_table_id        bigint NOT NULL COMMENT 'the internal unique primary index',
    phrase_id              bigint NOT NULL COMMENT 'the values and results of this phrase are primary stored in dynamic tables on the given pod',
    pod_id                 bigint NOT NULL COMMENT 'the primary pod where the values and results related to this phrase saved',
    phrase_table_status_id bigint NOT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'remember which phrases are stored in which table and pod';

--
-- AUTO_INCREMENT for table phrase_tables
--
ALTER TABLE phrase_tables
    MODIFY phrase_table_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure for the phrase type to set the predefined behaviour of a word or triple
--

CREATE TABLE IF NOT EXISTS phrase_types
(
    phrase_type_id smallint     NOT NULL     COMMENT 'the internal unique primary index',
    type_name      varchar(255) NOT NULL     COMMENT 'the unique type name as shown to the user and used for the selection',
    code_id        varchar(255) DEFAULT NULL COMMENT 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration',
    description    text         DEFAULT NULL COMMENT 'text to explain the type to the user as a tooltip; to be replaced by a language form entry',
    scaling_factor bigint       DEFAULT NULL COMMENT 'e.g. for percent the scaling factor is 100',
    word_symbol    varchar(255) DEFAULT NULL COMMENT 'e.g. for percent the symbol is %'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for the phrase type to set the predefined behaviour of a word or triple';

--
-- AUTO_INCREMENT for table phrase_types
--
ALTER TABLE phrase_types
    MODIFY phrase_type_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure to add a user given name using a 512-bit group id index for up to 16 32-bit phrase ids including the order
--

CREATE TABLE IF NOT EXISTS `groups` (
    group_id    char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the group',
    user_id     bigint    DEFAULT NULL COMMENT 'the owner / creator of the group',
    group_name  text      DEFAULT NULL COMMENT 'the user specific group name which can contain the phrase names in a different order to display the group (does not need to be unique)',
    description text      DEFAULT NULL COMMENT 'the user specific description for mouse over helps'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to add a user given name using a 512-bit group id index for up to 16 32-bit phrase ids including the order';

--
-- table structure to save user specific changes to add a user given name using a 512-bit group id index for up to 16 32-bit phrase ids including the order
--

CREATE TABLE IF NOT EXISTS user_groups (
    group_id    char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the user group',
    user_id     bigint        NOT NULL COMMENT 'the changer of the group',
    group_name  text      DEFAULT NULL COMMENT 'the user specific group name which can contain the phrase names in a different order to display the group (does not need to be unique)',
    description text      DEFAULT NULL COMMENT 'the user specific description for mouse over helps'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to add a user given name using a 512-bit group id index for up to 16 32-bit phrase ids including the order';

--
-- table structure to add a user given name using a 64-bit group id index for up to four 16-bit phrase ids including the order
--

CREATE TABLE IF NOT EXISTS groups_prime (
    group_id    bigint     NOT NULL COMMENT 'the 64-bit prime index to find the group',
    user_id     bigint DEFAULT NULL COMMENT 'the owner / creator of the group',
    group_name  text   DEFAULT NULL COMMENT 'the user specific group name which can contain the phrase names in a different order to display the group (does not need to be unique)',
    description text   DEFAULT NULL COMMENT 'the user specific description for mouse over helps'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to add a user given name using a 64-bit group id index for up to four 16-bit phrase ids including the order';

--
-- table structure to save user specific changes to add a user given name using a 64-bit group id index for up to four 16-bit phrase ids including the order
--

CREATE TABLE IF NOT EXISTS user_groups_prime (
    group_id    bigint     NOT NULL COMMENT 'the 64-bit prime index to find the user group',
    user_id     bigint     NOT NULL COMMENT 'the changer of the group',
    group_name  text   DEFAULT NULL COMMENT 'the user specific group name which can contain the phrase names in a different order to display the group (does not need to be unique)',
    description text   DEFAULT NULL COMMENT 'the user specific description for mouse over helps'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to add a user given name using a 64-bit group id index for up to four 16-bit phrase ids including the order';

--
-- table structure to add a user given name using a group id index with a variable length for more than 16 32-bit phrase ids including the order
--

CREATE TABLE IF NOT EXISTS groups_big (
    group_id    char(255)     NOT NULL COMMENT 'the variable text index to find group',
    user_id     bigint    DEFAULT NULL COMMENT 'the owner / creator of the group',
    group_name  text      DEFAULT NULL COMMENT 'the user specific group name which can contain the phrase names in a different order to display the group (does not need to be unique)',
    description text      DEFAULT NULL COMMENT 'the user specific description for mouse over helps'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to add a user given name using a group id index with a variable length for more than 16 32-bit phrase ids including the order';

--
-- table structure to save user specific changes to add a user given name using a group id index with a variable length for more than 16 32-bit phrase ids including the order
--

CREATE TABLE IF NOT EXISTS user_groups_big (
    group_id    char(255)     NOT NULL COMMENT 'the text index for more than 16 phrases to find the group',
    user_id     bigint        NOT NULL COMMENT 'the changer of the group',
    group_name  text      DEFAULT NULL COMMENT 'the user specific group name which can contain the phrase names in a different order to display the group (does not need to be unique)',
    description text      DEFAULT NULL COMMENT 'the user specific description for mouse over helps'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to add a user given name using a group id index with a variable length for more than 16 32-bit phrase ids including the order';

-- --------------------------------------------------------

--
-- table structure to link predefined behaviour to a source
--

CREATE TABLE IF NOT EXISTS source_types
(
    source_type_id smallint        NOT NULL COMMENT 'the internal unique primary index',
    type_name     varchar(255)     NOT NULL COMMENT 'the unique type name as shown to the user and used for the selection',
    code_id       varchar(255) DEFAULT NULL COMMENT 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration',
    description   text         DEFAULT NULL COMMENT 'text to explain the type to the user as a tooltip; to be replaced by a language form entry'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to link predefined behaviour to a source';

--
-- AUTO_INCREMENT for table source_types
--
ALTER TABLE source_types
    MODIFY source_type_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure for the original sources for the numeric, time and geo values
--

CREATE TABLE IF NOT EXISTS sources (
    source_id      bigint           NOT NULL COMMENT 'the internal unique primary index',
    user_id        bigint       DEFAULT NULL COMMENT 'the owner / creator of the source',
    source_name    varchar(255)     NOT NULL COMMENT 'the unique name of the source used e.g. as the primary search key',
    description    text         DEFAULT NULL COMMENT 'the user specific description of the source for mouse over helps',
    source_type_id smallint     DEFAULT NULL COMMENT 'link to the source type',
    `url`          text         DEFAULT NULL COMMENT 'the url of the source',
    code_id        varchar(100) DEFAULT NULL COMMENT 'to select sources used by this program',
    excluded       smallint     DEFAULT NULL COMMENT 'true if a user,but not all,have removed it',
    share_type_id  smallint     DEFAULT NULL COMMENT 'to restrict the access',
    protect_id     smallint     DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for the original sources for the numeric,time and geo values';

--
-- AUTO_INCREMENT for table sources
--
ALTER TABLE sources
    MODIFY source_id int(11) NOT NULL AUTO_INCREMENT;

--
-- table structure to save user specific changes for the original sources for the numeric, time and geo values
--

CREATE TABLE IF NOT EXISTS user_sources (
    source_id      bigint           NOT NULL COMMENT 'with the user_id the internal unique primary index',
    user_id        bigint           NOT NULL COMMENT 'the changer of the source',
    source_name    varchar(255) DEFAULT NULL COMMENT 'the unique name of the source used e.g. as the primary search key',
    description    text         DEFAULT NULL COMMENT 'the user specific description of the source for mouse over helps',
    source_type_id smallint     DEFAULT NULL COMMENT 'link to the source type',
    `url`          text         DEFAULT NULL COMMENT 'the url of the source',
    code_id        varchar(100) DEFAULT NULL COMMENT 'to select sources used by this program',
    excluded       smallint     DEFAULT NULL COMMENT 'true if a user,but not all,have removed it',
    share_type_id  smallint     DEFAULT NULL COMMENT 'to restrict the access',
    protect_id     smallint     DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for the original sources for the numeric,time and geo values';

-- --------------------------------------------------------

--
-- table structure to link code functionality to a list of references
--

CREATE TABLE IF NOT EXISTS ref_types
(
    ref_type_id smallint         NOT NULL COMMENT 'the internal unique primary index',
    type_name   varchar(255)     NOT NULL COMMENT 'the unique type name as shown to the user and used for the selection',
    code_id     varchar(255) DEFAULT NULL COMMENT 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration',
    description text         DEFAULT NULL COMMENT 'text to explain the type to the user as a tooltip; to be replaced by a language form entry',
    base_url    text         DEFAULT NULL COMMENT 'the base url to create the urls for the assigned references'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to link code functionality to a list of references';

--
-- AUTO_INCREMENT for table ref_types
--
ALTER TABLE ref_types
    MODIFY ref_type_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure to link external data to internal for synchronisation
--

CREATE TABLE IF NOT EXISTS refs
(
    ref_id        bigint       NOT NULL COMMENT 'the internal unique primary index',
    user_id       bigint   DEFAULT NULL COMMENT 'the owner / creator of the ref',
    external_key  varchar(255) NOT NULL COMMENT 'the unique external key used in the other system',
    `url`         text     DEFAULT NULL COMMENT 'the concrete url for the entry including the item id',
    source_id     bigint   DEFAULT NULL COMMENT 'if the reference does not allow a full automatic bidirectional update use the source to define an as good as possible import or at least a check if the reference is still valid',
    description   text     DEFAULT NULL,
    phrase_id     bigint   DEFAULT NULL COMMENT 'the phrase for which the external data should be synchronised',
    ref_type_id   bigint       NOT NULL COMMENT 'to link code functionality to a list of references',
    excluded      smallint DEFAULT NULL COMMENT 'true if a user,but not all,have removed it',
    share_type_id smallint DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to link external data to internal for synchronisation';

--
-- AUTO_INCREMENT for table refs
--
ALTER TABLE refs
    MODIFY ref_id int(11) NOT NULL AUTO_INCREMENT;

--
-- table structure to save user specific changes to link external data to internal for synchronisation
--

CREATE TABLE IF NOT EXISTS user_refs
(
    ref_id        bigint           NOT NULL COMMENT 'with the user_id the internal unique primary index',
    user_id       bigint           NOT NULL COMMENT 'the changer of the ref',
    external_key  varchar(255) DEFAULT NULL COMMENT 'the unique external key used in the other system',
    `url`         text          DEFAULT NULL COMMENT 'the concrete url for the entry including the item id',
    source_id     bigint        DEFAULT NULL COMMENT 'if the reference does not allow a full automatic bidirectional update use the source to define an as good as possible import or at least a check if the reference is still valid',
    description   text          DEFAULT NULL,
    excluded      smallint      DEFAULT NULL COMMENT 'true if a user,but not all,have removed it',
    share_type_id smallint      DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint      DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to link external data to internal for synchronisation';

-- --------------------------------------------------------

--
-- table structure for public unprotected numeric values related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS values_standard_prime
(
    phrase_id_1   smallint     NOT NULL COMMENT 'phrase id that is part of the prime key for a numeric value',
    phrase_id_2   smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric value',
    phrase_id_3   smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric value',
    phrase_id_4   smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric value',
    numeric_value double       NOT NULL COMMENT 'the numeric value given by the user',
    source_id     bigint   DEFAULT NULL COMMENT 'the source of the value as given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for public unprotected numeric values related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';

--
-- table structure for public unprotected numeric values that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS values_standard
(
    group_id      char(112) NOT NULL COMMENT 'the 512-bit prime index to find the numeric value',
    numeric_value double    NOT NULL COMMENT 'the numeric value given by the user',
    source_id     bigint    DEFAULT NULL COMMENT 'the source of the value as given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for public unprotected numeric values that have never changed the owner, does not have a description and are rarely updated';

-- --------------------------------------------------------

--
-- table structure for numeric values related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS `values`
(
    group_id      char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the numeric value',
    numeric_value double        NOT NULL COMMENT 'the numeric value given by the user',
    source_id     bigint    DEFAULT NULL COMMENT 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    user_id       bigint    DEFAULT NULL COMMENT 'the owner / creator of the value',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for numeric values related to up to 16 phrases';

--
-- table structure for user specific changes of numeric values related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS user_values
(
    group_id      char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the user numeric value',
    user_id       bigint        NOT NULL COMMENT 'the changer of the numeric value',
    numeric_value double    DEFAULT NULL COMMENT 'the user specific numeric value change',
    source_id     bigint    DEFAULT NULL COMMENT 'one user can add different values from different sources, that have the same group, but a different value, so the source should be included in the unique key numeric value',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for user specific changes of numeric values related to up to 16 phrases';

-- --------------------------------------------------------

--
-- table structure for the most often requested numeric values related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS values_prime
(
    phrase_id_1   smallint      NOT NULL COMMENT 'phrase id that is part of the prime key for a numeric value',
    phrase_id_2   smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric value',
    phrase_id_3   smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric value',
    phrase_id_4   smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric value',
    numeric_value double        NOT NULL COMMENT 'the numeric value given by the user',
    source_id     bigint    DEFAULT NULL COMMENT 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    user_id       bigint    DEFAULT NULL COMMENT 'the owner / creator of the value',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for the most often requested numeric values related up to four prime phrase';

--
-- table structure to store the user specific changes for the most often requested numeric values related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS user_values_prime
(
    phrase_id_1   smallint      NOT NULL COMMENT 'phrase id that is with the user id  part of the prime key for a numeric value',
    phrase_id_2   smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id  part of the prime key for a numeric value',
    phrase_id_3   smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id  part of the prime key for a numeric value',
    phrase_id_4   smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id  part of the prime key for a numeric value',
    user_id       bigint        NOT NULL COMMENT 'the changer of the numeric value',
    numeric_value double    DEFAULT NULL COMMENT 'the user specific numeric value change',
    source_id     bigint    DEFAULT NULL COMMENT 'one user can add different values from different sources, that have the same group, but a different value, so the source should be included in the unique key numeric value',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to store the user specific changes for the most often requested numeric values related up to four prime phrase';

-- --------------------------------------------------------

--
-- table structure for numeric values related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS values_big
(
    group_id      char(255)     NOT NULL COMMENT 'the variable text index to find numeric value',
    numeric_value double        NOT NULL COMMENT 'the numeric value given by the user',
    source_id     bigint    DEFAULT NULL COMMENT 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    user_id       bigint    DEFAULT NULL COMMENT 'the owner / creator of the value',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for numeric values related to more than 16 phrases';

--
-- table structure to store the user specific changes of numeric values related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS user_values_big
(
    group_id      char(255)     NOT NULL COMMENT 'the text index for more than 16 phrases to find the numeric value',
    user_id       bigint        NOT NULL COMMENT 'the changer of the numeric value',
    numeric_value double    DEFAULT NULL COMMENT 'the user specific numeric value change',
    source_id     bigint    DEFAULT NULL COMMENT 'one user can add different values from different sources, that have the same group, but a different value, so the source should be included in the unique key numeric value',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to store the user specific changes of numeric values related to more than 16 phrases';

-- --------------------------------------------------------

--
-- table structure for public unprotected text values related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS values_text_standard_prime
(
    phrase_id_1 smallint     NOT NULL COMMENT 'phrase id that is part of the prime key for a text value',
    phrase_id_2 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text value',
    phrase_id_3 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text value',
    phrase_id_4 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text value',
    text_value  text         NOT NULL COMMENT 'the text value given by the user',
    source_id   bigint   DEFAULT NULL COMMENT 'the source of the value as given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for public unprotected text values related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';

--
-- table structure for public unprotected text values that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS values_text_standard
(
    group_id   char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the text value',
    text_value text          NOT NULL COMMENT 'the text value given by the user',
    source_id  bigint    DEFAULT NULL COMMENT 'the source of the value as given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for public unprotected text values that have never changed the owner, does not have a description and are rarely updated';

-- --------------------------------------------------------

--
-- table structure for text values related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS values_text
(
    group_id      char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the text value',
    text_value    text          NOT NULL COMMENT 'the text value given by the user',
    source_id     bigint    DEFAULT NULL COMMENT 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    user_id       bigint    DEFAULT NULL COMMENT 'the owner / creator of the value',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for text values related to up to 16 phrases';

--
-- table structure for user specific changes of text values related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS user_values_text
(
    group_id      char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the user text value',
    user_id       bigint        NOT NULL COMMENT 'the changer of the text value',
    text_value    text      DEFAULT NULL COMMENT 'the user specific text value change',
    source_id     bigint    DEFAULT NULL COMMENT 'one user can add different values from different sources, that have the same group, but a different value, so the source should be included in the unique key text value',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for user specific changes of text values related to up to 16 phrases';

-- --------------------------------------------------------

--
-- table structure for the most often requested text values related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS values_text_prime
(
    phrase_id_1   smallint      NOT NULL COMMENT 'phrase id that is part of the prime key for a text value',
    phrase_id_2   smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text value',
    phrase_id_3   smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text value',
    phrase_id_4   smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text value',
    text_value    text          NOT NULL COMMENT 'the text value given by the user',
    source_id     bigint    DEFAULT NULL COMMENT 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    user_id       bigint    DEFAULT NULL COMMENT 'the owner / creator of the value',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for the most often requested text values related up to four prime phrase';

--
-- table structure to store the user specific changes for the most often requested text values related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS user_values_text_prime
(
    phrase_id_1   smallint      NOT NULL COMMENT 'phrase id that is with the user id  part of the prime key for a text value',
    phrase_id_2   smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id  part of the prime key for a text value',
    phrase_id_3   smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id  part of the prime key for a text value',
    phrase_id_4   smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id  part of the prime key for a text value',
    user_id       bigint        NOT NULL COMMENT 'the changer of the text value',
    text_value    text      DEFAULT NULL COMMENT 'the user specific text value change',
    source_id     bigint    DEFAULT NULL COMMENT 'one user can add different values from different sources, that have the same group, but a different value, so the source should be included in the unique key text value',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to store the user specific changes for the most often requested text values related up to four prime phrase';

-- --------------------------------------------------------

--
-- table structure for text values related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS values_text_big
(
    group_id      char(255)     NOT NULL COMMENT 'the variable text index to find text value',
    text_value    text          NOT NULL COMMENT 'the text value given by the user',
    source_id     bigint    DEFAULT NULL COMMENT 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    user_id       bigint    DEFAULT NULL COMMENT 'the owner / creator of the value',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for text values related to more than 16 phrases';

--
-- table structure to store the user specific changes of text values related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS user_values_text_big
(
    group_id      char(255)     NOT NULL COMMENT 'the text index for more than 16 phrases to find the text value',
    user_id       bigint        NOT NULL COMMENT 'the changer of the text value',
    text_value    text      DEFAULT NULL COMMENT 'the user specific text value change',
    source_id     bigint    DEFAULT NULL COMMENT 'one user can add different values from different sources, that have the same group, but a different value, so the source should be included in the unique key text value',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to store the user specific changes of text values related to more than 16 phrases';

-- --------------------------------------------------------

--
-- table structure for public unprotected time values related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS values_time_standard_prime
(
    phrase_id_1 smallint     NOT NULL COMMENT 'phrase id that is part of the prime key for a time value',
    phrase_id_2 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time value',
    phrase_id_3 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time value',
    phrase_id_4 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time value',
    time_value  timestamp    NOT NULL COMMENT 'the timestamp given by the user',
    source_id   bigint   DEFAULT NULL COMMENT 'the source of the value as given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for public unprotected time values related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';

--
-- table structure for public unprotected time values that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS values_time_standard
(
    group_id   char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the time value',
    time_value timestamp     NOT NULL COMMENT 'the timestamp given by the user',
    source_id  bigint    DEFAULT NULL COMMENT 'the source of the value as given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for public unprotected time values that have never changed the owner, does not have a description and are rarely updated';

-- --------------------------------------------------------

--
-- table structure for time values related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS values_time
(
    group_id      char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the time value',
    time_value    timestamp     NOT NULL COMMENT 'the timestamp given by the user',
    source_id     bigint    DEFAULT NULL COMMENT 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    user_id       bigint    DEFAULT NULL COMMENT 'the owner / creator of the value',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for time values related to up to 16 phrases';

--
-- table structure for user specific changes of time values related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS user_values_time
(
    group_id      char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the user time value',
    user_id       bigint        NOT NULL COMMENT 'the changer of the time value',
    time_value    timestamp DEFAULT NULL COMMENT 'the user specific timestamp change',
    source_id     bigint    DEFAULT NULL COMMENT 'one user can add different values from different sources, that have the same group, but a different value, so the source should be included in the unique key time value',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for user specific changes of time values related to up to 16 phrases';

-- --------------------------------------------------------

--
-- table structure for the most often requested time values related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS values_time_prime
(
    phrase_id_1   smallint      NOT NULL COMMENT 'phrase id that is part of the prime key for a time value',
    phrase_id_2   smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time value',
    phrase_id_3   smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time value',
    phrase_id_4   smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time value',
    time_value    timestamp     NOT NULL COMMENT 'the timestamp given by the user',
    source_id     bigint    DEFAULT NULL COMMENT 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    user_id       bigint    DEFAULT NULL COMMENT 'the owner / creator of the value',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for the most often requested time values related up to four prime phrase';

--
-- table structure to store the user specific changes for the most often requested time values related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS user_values_time_prime
(
    phrase_id_1   smallint      NOT NULL COMMENT 'phrase id that is with the user id  part of the prime key for a time value',
    phrase_id_2   smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id  part of the prime key for a time value',
    phrase_id_3   smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id  part of the prime key for a time value',
    phrase_id_4   smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id  part of the prime key for a time value',
    user_id       bigint        NOT NULL COMMENT 'the changer of the time value',
    time_value    timestamp DEFAULT NULL COMMENT 'the user specific timestamp change',
    source_id     bigint    DEFAULT NULL COMMENT 'one user can add different values from different sources, that have the same group, but a different value, so the source should be included in the unique key time value',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to store the user specific changes for the most often requested time values related up to four prime phrase';

-- --------------------------------------------------------

--
-- table structure for time values related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS values_time_big
(
    group_id      char(255)     NOT NULL COMMENT 'the variable text index to find time value',
    time_value    timestamp     NOT NULL COMMENT 'the timestamp given by the user',
    source_id     bigint    DEFAULT NULL COMMENT 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    user_id       bigint    DEFAULT NULL COMMENT 'the owner / creator of the value',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for time values related to more than 16 phrases';

--
-- table structure to store the user specific changes of time values related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS user_values_time_big
(
    group_id      char(255)     NOT NULL COMMENT 'the text index for more than 16 phrases to find the time value',
    user_id       bigint        NOT NULL COMMENT 'the changer of the time value',
    time_value    timestamp DEFAULT NULL COMMENT 'the user specific timestamp change',
    source_id     bigint    DEFAULT NULL COMMENT 'one user can add different values from different sources, that have the same group, but a different value, so the source should be included in the unique key time value',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to store the user specific changes of time values related to more than 16 phrases';

-- --------------------------------------------------------

--
-- table structure for public unprotected geo values related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS values_geo_standard_prime
(
    phrase_id_1 smallint     NOT NULL COMMENT 'phrase id that is part of the prime key for a geo value',
    phrase_id_2 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo value',
    phrase_id_3 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo value',
    phrase_id_4 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo value',
    geo_value   point        NOT NULL COMMENT 'the geolocation given by the user',
    source_id   bigint   DEFAULT NULL COMMENT 'the source of the value as given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for public unprotected geo values related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';

--
-- table structure for public unprotected geo values that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS values_geo_standard
(
    group_id   char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the geo value',
    geo_value  point         NOT NULL COMMENT 'the geolocation given by the user',
    source_id  bigint    DEFAULT NULL COMMENT 'the source of the value as given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for public unprotected geo values that have never changed the owner, does not have a description and are rarely updated';

-- --------------------------------------------------------

--
-- table structure for geo values related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS values_geo
(
    group_id      char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the geo value',
    geo_value     point         NOT NULL COMMENT 'the geolocation given by the user',
    source_id     bigint    DEFAULT NULL COMMENT 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    user_id       bigint    DEFAULT NULL COMMENT 'the owner / creator of the value',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for geo values related to up to 16 phrases';

--
-- table structure for user specific changes of geo values related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS user_values_geo
(
    group_id      char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the user geo value',
    user_id       bigint        NOT NULL COMMENT 'the changer of the geo value',
    geo_value     point     DEFAULT NULL COMMENT 'the user specific geolocation change',
    source_id     bigint    DEFAULT NULL COMMENT 'one user can add different values from different sources, that have the same group, but a different value, so the source should be included in the unique key geo value',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for user specific changes of geo values related to up to 16 phrases';

-- --------------------------------------------------------

--
-- table structure for the most often requested geo values related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS values_geo_prime
(
    phrase_id_1   smallint      NOT NULL COMMENT 'phrase id that is part of the prime key for a geo value',
    phrase_id_2   smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo value',
    phrase_id_3   smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo value',
    phrase_id_4   smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo value',
    geo_value     point         NOT NULL COMMENT 'the geolocation given by the user',
    source_id     bigint    DEFAULT NULL COMMENT 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    user_id       bigint    DEFAULT NULL COMMENT 'the owner / creator of the value',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for the most often requested geo values related up to four prime phrase';

--
-- table structure to store the user specific changes for the most often requested geo values related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS user_values_geo_prime
(
    phrase_id_1   smallint      NOT NULL COMMENT 'phrase id that is with the user id  part of the prime key for a geo value',
    phrase_id_2   smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id  part of the prime key for a geo value',
    phrase_id_3   smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id  part of the prime key for a geo value',
    phrase_id_4   smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id  part of the prime key for a geo value',
    user_id       bigint        NOT NULL COMMENT 'the changer of the geo value',
    geo_value     point     DEFAULT NULL COMMENT 'the user specific geolocation change',
    source_id     bigint    DEFAULT NULL COMMENT 'one user can add different values from different sources, that have the same group, but a different value, so the source should be included in the unique key geo value',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to store the user specific changes for the most often requested geo values related up to four prime phrase';

-- --------------------------------------------------------

--
-- table structure for geo values related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS values_geo_big
(
    group_id      char(255)     NOT NULL COMMENT 'the variable text index to find geo value',
    geo_value     point         NOT NULL COMMENT 'the geolocation given by the user',
    source_id     bigint    DEFAULT NULL COMMENT 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    user_id       bigint    DEFAULT NULL COMMENT 'the owner / creator of the value',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for geo values related to more than 16 phrases';

--
-- table structure to store the user specific changes of geo values related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS user_values_geo_big
(
    group_id      char(255)     NOT NULL COMMENT 'the text index for more than 16 phrases to find the geo value',
    user_id       bigint        NOT NULL COMMENT 'the changer of the geo value',
    geo_value     point     DEFAULT NULL COMMENT 'the user specific geolocation change',
    source_id     bigint    DEFAULT NULL COMMENT 'one user can add different values from different sources, that have the same group, but a different value, so the source should be included in the unique key geo value',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to store the user specific changes of geo values related to more than 16 phrases';

-- --------------------------------------------------------

--
-- table structure for the common parameters for a list of numbers that differ only by the timestamp
--

CREATE TABLE IF NOT EXISTS values_time_series
(
    group_id             char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the time_series value',
    value_time_series_id bigint        NOT NULL COMMENT 'the id of the time series as a 64 bit integer value because the number of time series is not expected to be too high',
    source_id            bigint    DEFAULT NULL COMMENT 'the source of the value as given by the user',
    last_update          timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    user_id              bigint    DEFAULT NULL COMMENT 'the owner / creator of the value',
    excluded             smallint  DEFAULT NULL COMMENT 'true if a user,but not all,have removed it',
    share_type_id        smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id           smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for the common parameters for a list of numbers that differ only by the timestamp';

--
-- table structure for the common parameters for a list of numbers that differ only by the timestamp
--

CREATE TABLE IF NOT EXISTS user_values_time_series
(
    group_id             char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the user time_series value',
    user_id              bigint        NOT NULL COMMENT 'the changer of the time_series value',
    value_time_series_id bigint        NOT NULL COMMENT 'the 64 bit integer which is unique for the standard and the user series',
    source_id            bigint    DEFAULT NULL COMMENT 'one user can add different values from different sources,that have the same group,but a different value,so the source should be included in the unique key time_series value',
    last_update          timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    excluded             smallint  DEFAULT NULL COMMENT 'true if a user,but not all,have removed it',
    share_type_id        smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id           smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for the common parameters for a list of numbers that differ only by the timestamp';

-- --------------------------------------------------------

--
-- table structure for the common parameters for a list of numbers that differ only by the timestamp
--

CREATE TABLE IF NOT EXISTS values_time_series_prime
(
    phrase_id_1          smallint      NOT NULL COMMENT 'phrase id that is part of the prime key for a time_series value',
    phrase_id_2          smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time_series value',
    phrase_id_3          smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time_series value',
    phrase_id_4          smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time_series value',
    value_time_series_id bigint        NOT NULL COMMENT 'the id of the time series as a 64 bit integer value because the number of time series is not expected to be too high',
    source_id            bigint    DEFAULT NULL COMMENT 'the source of the value as given by the user',
    last_update          timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    user_id              bigint    DEFAULT NULL COMMENT 'the owner / creator of the value',
    excluded             smallint  DEFAULT NULL COMMENT 'true if a user,but not all,have removed it',
    share_type_id        smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id           smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for the common parameters for a list of numbers that differ only by the timestamp';

--
-- table structure for the common parameters for a list of numbers that differ only by the timestamp
--

CREATE TABLE IF NOT EXISTS user_values_time_series_prime
(
    phrase_id_1          smallint      NOT NULL COMMENT 'phrase id that is with the user id part of the prime key for a time_series value',
    phrase_id_2          smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a time_series value',
    phrase_id_3          smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a time_series value',
    phrase_id_4          smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a time_series value',
    user_id              bigint        NOT NULL COMMENT 'the changer of the time_series value',
    value_time_series_id bigint        NOT NULL COMMENT 'the 64 bit integer which is unique for the standard and the user series',
    source_id            bigint    DEFAULT NULL COMMENT 'one user can add different values from different sources,that have the same group,but a different value,so the source should be included in the unique key time_series value',
    last_update          timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    excluded             smallint  DEFAULT NULL COMMENT 'true if a user,but not all,have removed it',
    share_type_id        smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id           smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for the common parameters for a list of numbers that differ only by the timestamp';

-- --------------------------------------------------------

--
-- table structure for the common parameters for a list of numbers that differ only by the timestamp
--

CREATE TABLE IF NOT EXISTS values_time_series_big
(
    group_id             char(255)     NOT NULL COMMENT 'the variable text index to find time_series value',
    value_time_series_id bigint        NOT NULL COMMENT 'the id of the time series as a 64 bit integer value because the number of time series is not expected to be too high',
    source_id            bigint    DEFAULT NULL COMMENT 'the source of the value as given by the user',
    last_update          timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    user_id              bigint    DEFAULT NULL COMMENT 'the owner / creator of the value',
    excluded             smallint  DEFAULT NULL COMMENT 'true if a user,but not all,have removed it',
    share_type_id        smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id           smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for the common parameters for a list of numbers that differ only by the timestamp';

--
-- table structure for the common parameters for a list of numbers that differ only by the timestamp
--

CREATE TABLE IF NOT EXISTS user_values_time_series_big
(
    group_id             char(255)     NOT NULL COMMENT 'the text index for more than 16 phrases to find the time_series value',
    user_id              bigint        NOT NULL COMMENT 'the changer of the time_series value',
    value_time_series_id bigint        NOT NULL COMMENT 'the 64 bit integer which is unique for the standard and the user series',
    source_id            bigint    DEFAULT NULL COMMENT 'one user can add different values from different sources,that have the same group,but a different value,so the source should be included in the unique key time_series value',
    last_update          timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    excluded             smallint  DEFAULT NULL COMMENT 'true if a user,but not all,have removed it',
    share_type_id        smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id           smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for the common parameters for a list of numbers that differ only by the timestamp';

-- --------------------------------------------------------

--
-- table structure for a single time series value data entry and efficient saving of daily or intra-day values
--

CREATE TABLE IF NOT EXISTS value_ts_data
(
    value_time_series_id bigint     NOT NULL COMMENT 'link to the value time series',
    val_time             timestamp  NOT NULL COMMENT 'short name of the configuration entry to be shown to the admin',
    number               double DEFAULT NULL COMMENT 'the configuration value as a string'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for a single time series value data entry and efficient saving of daily or intra-day values';

-- --------------------------------------------------------

--
-- table structure to assign predefined behaviour to a formula element
--

CREATE TABLE IF NOT EXISTS element_types
(
    element_type_id smallint         NOT NULL COMMENT 'the internal unique primary index',
    type_name       varchar(255)     NOT NULL COMMENT 'the unique type name as shown to the user and used for the selection',
    code_id         varchar(255) DEFAULT NULL COMMENT 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration',
    description     text         DEFAULT NULL COMMENT 'text to explain the type to the user as a tooltip; to be replaced by a language form entry'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to assign predefined behaviour to a formula element';

--
-- AUTO_INCREMENT for table element_types
--
ALTER TABLE element_types
    MODIFY element_type_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure cache for fast update of formula resolved text
--

CREATE TABLE IF NOT EXISTS elements
(
    element_id      bigint     NOT NULL COMMENT 'the internal unique primary index',
    formula_id      bigint     NOT NULL COMMENT 'each element can only be used for one formula',
    order_nbr       bigint     NOT NULL,
    element_type_id smallint   NOT NULL,
    user_id         bigint DEFAULT NULL,
    ref_id          bigint DEFAULT NULL COMMENT 'either a term,verb or formula id',
    resolved_text   varchar(255) DEFAULT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'cache for fast update of formula resolved text';

--
-- AUTO_INCREMENT for table elements
--
ALTER TABLE elements
    MODIFY element_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure to assign predefined behaviour to formulas
--

CREATE TABLE IF NOT EXISTS formula_types
(
    formula_type_id smallint         NOT NULL COMMENT 'the internal unique primary index',
    type_name       varchar(255)     NOT NULL COMMENT 'the unique type name as shown to the user and used for the selection',
    code_id         varchar(255) DEFAULT NULL COMMENT 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration',
    description     text         DEFAULT NULL COMMENT 'text to explain the type to the user as a tooltip; to be replaced by a language form entry'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to assign predefined behaviour to formulas';

--
-- AUTO_INCREMENT for table formula_types
--
ALTER TABLE formula_types
    MODIFY formula_type_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure the mathematical expression to calculate results based on values and results
--

CREATE TABLE IF NOT EXISTS formulas
(
    formula_id        bigint        NOT NULL COMMENT 'the internal unique primary index',
    user_id           bigint    DEFAULT NULL COMMENT 'the owner / creator of the formula',
    formula_name      varchar(255)  NOT NULL COMMENT 'the text used to search for formulas that must also be unique for all terms (words, triples, verbs and formulas)',
    formula_text      text      DEFAULT NULL COMMENT 'the internal formula expression with the database references e.g. {f1} for formula with id 1',
    resolved_text     text      DEFAULT NULL COMMENT 'the formula expression in user readable format as shown to the user which can include formatting for better readability',
    description       text      DEFAULT NULL COMMENT 'text to be shown to the user for mouse over; to be replaced by a language form entry',
    formula_type_id   bigint    DEFAULT NULL COMMENT 'the id of the formula type',
    all_values_needed smallint  DEFAULT NULL COMMENT 'the "calculate only if all values used in the formula exist" flag should be converted to "all needed for calculation" instead of just displaying "1"',
    last_update       timestamp DEFAULT NULL COMMENT 'time of the last calculation relevant update',
    view_id           bigint    DEFAULT NULL COMMENT 'the default mask for this formula',
    `usage`           bigint    DEFAULT NULL COMMENT 'number of results linked to this formula',
    excluded          smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id     smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id        smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'the mathematical expression to calculate results based on values and results';

--
-- AUTO_INCREMENT for table formulas
--
ALTER TABLE formulas
    MODIFY formula_id int(11) NOT NULL AUTO_INCREMENT;

--
-- table structure to save user specific changes the mathematical expression to calculate results based on values and results
--

CREATE TABLE IF NOT EXISTS user_formulas
(
    formula_id        bigint           NOT NULL COMMENT 'with the user_id the internal unique primary index',
    user_id           bigint           NOT NULL COMMENT 'the changer of the formula',
    formula_name      varchar(255) DEFAULT NULL COMMENT 'the text used to search for formulas that must also be unique for all terms (words, triples, verbs and formulas)',
    formula_text      text         DEFAULT NULL COMMENT 'the internal formula expression with the database references e.g. {f1} for formula with id 1',
    resolved_text     text         DEFAULT NULL COMMENT 'the formula expression in user readable format as shown to the user which can include formatting for better readability',
    description       text         DEFAULT NULL COMMENT 'text to be shown to the user for mouse over; to be replaced by a language form entry',
    formula_type_id   bigint       DEFAULT NULL COMMENT 'the id of the formula type',
    all_values_needed smallint     DEFAULT NULL COMMENT 'the "calculate only if all values used in the formula exist" flag should be converted to "all needed for calculation" instead of just displaying "1"',
    last_update       timestamp    DEFAULT NULL COMMENT 'time of the last calculation relevant update',
    view_id           bigint       DEFAULT NULL COMMENT 'the default mask for this formula',
    `usage`           bigint       DEFAULT NULL COMMENT 'number of results linked to this formula',
    excluded          smallint     DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id     smallint     DEFAULT NULL COMMENT 'to restrict the access',
    protect_id        smallint     DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'the mathematical expression to calculate results based on values and results';

-- --------------------------------------------------------

--
-- table structure to assign predefined behaviour to a formula link
--

CREATE TABLE IF NOT EXISTS formula_link_types
(
    formula_link_type_id smallint   NOT NULL COMMENT 'the internal unique primary index',
    type_name      varchar(255)     NOT NULL COMMENT 'the unique type name as shown to the user and used for the selection',
    code_id        varchar(255) DEFAULT NULL COMMENT 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration',
    description    text         DEFAULT NULL COMMENT 'text to explain the type to the user as a tooltip; to be replaced by a language form entry',
    formula_id     bigint           NOT NULL,
    phrase_type_id smallint         NOT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to assign predefined behaviour to a formula link';

--
-- AUTO_INCREMENT for table formula_link_types
--
ALTER TABLE formula_link_types
    MODIFY formula_link_type_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure for the link of a formula to phrases e.g. if the term pattern of a value matches this term pattern
--

CREATE TABLE IF NOT EXISTS formula_links
(
    formula_link_id      bigint       NOT NULL COMMENT 'the internal unique primary index',
    user_id              bigint   DEFAULT NULL COMMENT 'the owner / creator of the formula_link',
    formula_link_type_id smallint DEFAULT NULL,
    order_nbr            bigint   DEFAULT NULL,
    formula_id           bigint       NOT NULL,
    phrase_id            bigint       NOT NULL,
    excluded             smallint DEFAULT NULL COMMENT 'true if a user,but not all,have removed it',
    share_type_id        smallint DEFAULT NULL COMMENT 'to restrict the access',
    protect_id           smallint DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for the link of a formula to phrases e.g. if the term pattern of a value matches this term pattern';

--
-- AUTO_INCREMENT for table formula_links
--
ALTER TABLE formula_links
    MODIFY formula_link_id int(11) NOT NULL AUTO_INCREMENT;

--
-- table structure to save user specific changes for the link of a formula to phrases e.g. if the term pattern of a value matches this term pattern
--

CREATE TABLE IF NOT EXISTS user_formula_links
(
    formula_link_id      bigint       NOT NULL COMMENT 'with the user_id the internal unique primary index',
    user_id              bigint       NOT NULL COMMENT 'the changer of the formula_link',
    formula_link_type_id smallint DEFAULT NULL,
    order_nbr            bigint   DEFAULT NULL,
    excluded             smallint DEFAULT NULL COMMENT 'true if a user,but not all,have removed it',
    share_type_id        smallint DEFAULT NULL COMMENT 'to restrict the access',
    protect_id           smallint DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for the link of a formula to phrases e.g. if the term pattern of a value matches this term pattern';

-- --------------------------------------------------------

--
-- table structure to cache the formula public unprotected numeric results related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_standard_prime
(
    formula_id    smallint     NOT NULL COMMENT 'formula id that is part of the prime key for a numeric result',
    phrase_id_1   smallint     NOT NULL COMMENT 'phrase id that is part of the prime key for a numeric result',
    phrase_id_2   smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric result',
    phrase_id_3   smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric result',
    numeric_value double     NOT NULL COMMENT 'the numeric value given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to cache the formula public unprotected numeric results related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';

--
-- table structure to cache the formula public unprotected numeric results related up to eight prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_standard_main
(
    formula_id    smallint     NOT NULL COMMENT 'formula id that is part of the prime key for a numeric result',
    phrase_id_1   smallint     NOT NULL COMMENT 'phrase id that is part of the prime key for a numeric result',
    phrase_id_2   smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric result',
    phrase_id_3   smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric result',
    phrase_id_4   smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric result',
    phrase_id_5   smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric result',
    phrase_id_6   smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric result',
    phrase_id_7   smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric result',
    numeric_value double     NOT NULL COMMENT 'the numeric value given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to cache the formula public unprotected numeric results related up to eight prime phrase that have never changed the owner, does not have a description and are rarely updated';

--
-- table structure to cache the formula public unprotected numeric results that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_standard
(
    group_id      char(112) NOT NULL COMMENT 'the 512-bit prime index to find the numeric result',
    numeric_value double    NOT NULL COMMENT 'the numeric value given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to cache the formula public unprotected numeric results that have never changed the owner, does not have a description and are rarely updated';

-- --------------------------------------------------------

--
-- table structure to cache the formula numeric results related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS results
(
    group_id        char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the numeric result',
    source_group_id char(112) DEFAULT NULL COMMENT '512-bit reference to the sorted phrase list used to calculate this result',
    numeric_value   double        NOT NULL COMMENT 'the numeric value given by the user',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    user_id         bigint    DEFAULT NULL COMMENT 'the id of the user who has requested the calculation',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to cache the formula numeric results related to up to 16 phrases';

--
-- table structure to cache the user specific changes of numeric results related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS user_results
(
    group_id        char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the user numeric result',
    source_group_id char(112) DEFAULT NULL COMMENT '512-bit reference to the sorted phrase list used to calculate this result',
    user_id         bigint        NOT NULL COMMENT 'the id of the user who has requested the change of the numeric result',
    numeric_value   double    DEFAULT NULL COMMENT 'the user specific numeric value change',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to cache the user specific changes of numeric results related to up to 16 phrases';

-- --------------------------------------------------------

--
-- table structure to cache the formula most often requested numeric results related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS results_prime
(
    phrase_id_1     smallint      NOT NULL COMMENT 'phrase id that is part of the prime key for a numeric result',
    phrase_id_2     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric result',
    phrase_id_3     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric result',
    phrase_id_4     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric result',
    source_group_id bigint    DEFAULT NULL COMMENT '64-bit reference to the sorted phrase list used to calculate this result',
    numeric_value   double        NOT NULL COMMENT 'the numeric value given by the user',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    user_id         bigint    DEFAULT NULL COMMENT 'the id of the user who has requested the calculation',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to cache the formula most often requested numeric results related up to four prime phrase';

--
-- table structure to store the user specific changes for the most often requested numeric results related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS user_results_prime
(
    phrase_id_1     smallint      NOT NULL COMMENT 'phrase id that is with the user id part of the prime key for a numeric result',
    phrase_id_2     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a numeric result',
    phrase_id_3     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a numeric result',
    phrase_id_4     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a numeric result',
    source_group_id bigint    DEFAULT NULL COMMENT '64-bit reference to the sorted phrase list used to calculate this result',
    user_id         bigint        NOT NULL COMMENT 'the id of the user who has requested the change of the numeric result',
    numeric_value   double    DEFAULT NULL COMMENT 'the user specific numeric value change',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to store the user specific changes for the most often requested numeric results related up to four prime phrase';

-- --------------------------------------------------------

--
-- table structure to cache the formula second most often requested numeric results related up to eight prime phrase
--

CREATE TABLE IF NOT EXISTS results_main
(
    phrase_id_1     smallint      NOT NULL COMMENT 'phrase id that is part of the prime key for a numeric result',
    phrase_id_2     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric result',
    phrase_id_3     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric result',
    phrase_id_4     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric result',
    phrase_id_5     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric result',
    phrase_id_6     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric result',
    phrase_id_7     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric result',
    phrase_id_8     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric result',
    source_group_id bigint    DEFAULT NULL COMMENT '64-bit reference to the sorted phrase list used to calculate this result',
    numeric_value   double        NOT NULL COMMENT 'the numeric value given by the user',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    user_id         bigint    DEFAULT NULL COMMENT 'the id of the user who has requested the calculation',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to cache the formula second most often requested numeric results related up to eight prime phrase';

--
-- table structure to store the user specific changes to cache the formula second most often requested numeric results related up to eight prime phrase
--

CREATE TABLE IF NOT EXISTS user_results_main
(
    phrase_id_1     smallint      NOT NULL COMMENT 'phrase id that is with the user id part of the prime key for a numeric result',
    phrase_id_2     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a numeric result',
    phrase_id_3     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a numeric result',
    phrase_id_4     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a numeric result',
    phrase_id_5     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a numeric result',
    phrase_id_6     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a numeric result',
    phrase_id_7     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a numeric result',
    phrase_id_8     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a numeric result',
    source_group_id bigint    DEFAULT NULL COMMENT '64-bit reference to the sorted phrase list used to calculate this result',
    user_id         bigint        NOT NULL COMMENT 'the id of the user who has requested the change of the numeric result',
    numeric_value   double    DEFAULT NULL COMMENT 'the user specific numeric value change',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to store the user specific changes to cache the formula second most often requested numeric results related up to eight prime phrase';

-- --------------------------------------------------------

--
-- table structure to cache the formula numeric results related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS results_big
(
    group_id        char(255)     NOT NULL COMMENT 'the variable text index to find numeric result',
    source_group_id text      DEFAULT NULL COMMENT 'text reference to the sorted phrase list used to calculate this result',
    numeric_value   double        NOT NULL COMMENT 'the numeric value given by the user',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    user_id         bigint    DEFAULT NULL COMMENT 'the id of the user who has requested the calculation',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to cache the formula numeric results related to more than 16 phrases';

--
-- table structure to store the user specific changes of numeric results related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS user_results_big
(
    group_id        char(255)     NOT NULL COMMENT 'the text index for more than 16 phrases to find the numeric result',
    source_group_id text      DEFAULT NULL COMMENT 'text reference to the sorted phrase list used to calculate this result',
    user_id         bigint        NOT NULL COMMENT 'the id of the user who has requested the change of the numeric result',
    numeric_value   double    DEFAULT NULL COMMENT 'the user specific numeric value change',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to store the user specific changes of numeric results related to more than 16 phrases';

-- --------------------------------------------------------

--
-- table structure to cache the formula public unprotected text results related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_text_standard_prime
(
    formula_id smallint      NOT NULL COMMENT 'formula id that is part of the prime key for a text result',
    phrase_id_1 smallint     NOT NULL COMMENT 'phrase id that is part of the prime key for a text result',
    phrase_id_2 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text result',
    phrase_id_3 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text result',
    text_value  text         NOT NULL COMMENT 'the text value given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to cache the formula public unprotected text results related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';

--
-- table structure to cache the formula public unprotected text results related up to eight prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_text_standard_main
(
    formula_id smallint      NOT NULL COMMENT 'formula id that is part of the prime key for a text result',
    phrase_id_1 smallint     NOT NULL COMMENT 'phrase id that is part of the prime key for a text result',
    phrase_id_2 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text result',
    phrase_id_3 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text result',
    phrase_id_4 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text result',
    phrase_id_5 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text result',
    phrase_id_6 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text result',
    phrase_id_7 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text result',
    text_value  text         NOT NULL COMMENT 'the text value given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to cache the formula public unprotected text results related up to eight prime phrase that have never changed the owner, does not have a description and are rarely updated';

--
-- table structure to cache the formula public unprotected text results that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_text_standard
(
    group_id   char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the text result',
    text_value text          NOT NULL COMMENT 'the text value given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to cache the formula public unprotected text results that have never changed the owner, does not have a description and are rarely updated';

-- --------------------------------------------------------

--
-- table structure to cache the formula text results related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS results_text
(
    group_id        char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the text result',
    source_group_id char(112) DEFAULT NULL COMMENT '512-bit reference to the sorted phrase list used to calculate this result',
    text_value      text          NOT NULL COMMENT 'the text value given by the user',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    user_id         bigint    DEFAULT NULL COMMENT 'the id of the user who has requested the calculation',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to cache the formula text results related to up to 16 phrases';

--
-- table structure to cache the user specific changes of text results related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS user_results_text
(
    group_id        char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the user text result',
    source_group_id char(112) DEFAULT NULL COMMENT '512-bit reference to the sorted phrase list used to calculate this result',
    user_id         bigint        NOT NULL COMMENT 'the id of the user who has requested the change of the text result',
    text_value      text      DEFAULT NULL COMMENT 'the user specific text value change',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to cache the user specific changes of text results related to up to 16 phrases';

-- --------------------------------------------------------

--
-- table structure to cache the formula most often requested text results related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS results_text_prime
(
    phrase_id_1     smallint      NOT NULL COMMENT 'phrase id that is part of the prime key for a text result',
    phrase_id_2     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text result',
    phrase_id_3     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text result',
    phrase_id_4     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text result',
    source_group_id bigint    DEFAULT NULL COMMENT '64-bit reference to the sorted phrase list used to calculate this result',
    text_value      text          NOT NULL COMMENT 'the text value given by the user',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    user_id         bigint    DEFAULT NULL COMMENT 'the id of the user who has requested the calculation',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to cache the formula most often requested text results related up to four prime phrase';

--
-- table structure to store the user specific changes for the most often requested text results related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS user_results_text_prime
(
    phrase_id_1     smallint      NOT NULL COMMENT 'phrase id that is with the user id part of the prime key for a text result',
    phrase_id_2     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a text result',
    phrase_id_3     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a text result',
    phrase_id_4     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a text result',
    source_group_id bigint    DEFAULT NULL COMMENT '64-bit reference to the sorted phrase list used to calculate this result',
    user_id         bigint        NOT NULL COMMENT 'the id of the user who has requested the change of the text result',
    text_value      text      DEFAULT NULL COMMENT 'the user specific text value change',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to store the user specific changes for the most often requested text results related up to four prime phrase';

-- --------------------------------------------------------

--
-- table structure to cache the formula second most often requested text results related up to eight prime phrase
--

CREATE TABLE IF NOT EXISTS results_text_main
(
    phrase_id_1     smallint      NOT NULL COMMENT 'phrase id that is part of the prime key for a text result',
    phrase_id_2     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text result',
    phrase_id_3     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text result',
    phrase_id_4     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text result',
    phrase_id_5     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text result',
    phrase_id_6     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text result',
    phrase_id_7     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text result',
    phrase_id_8     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text result',
    source_group_id bigint    DEFAULT NULL COMMENT '64-bit reference to the sorted phrase list used to calculate this result',
    text_value      text          NOT NULL COMMENT 'the text value given by the user',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    user_id         bigint    DEFAULT NULL COMMENT 'the id of the user who has requested the calculation',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to cache the formula second most often requested text results related up to eight prime phrase';

--
-- table structure to store the user specific changes to cache the formula second most often requested text results related up to eight prime phrase
--

CREATE TABLE IF NOT EXISTS user_results_text_main
(
    phrase_id_1     smallint      NOT NULL COMMENT 'phrase id that is with the user id part of the prime key for a text result',
    phrase_id_2     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a text result',
    phrase_id_3     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a text result',
    phrase_id_4     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a text result',
    phrase_id_5     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a text result',
    phrase_id_6     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a text result',
    phrase_id_7     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a text result',
    phrase_id_8     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a text result',
    source_group_id bigint    DEFAULT NULL COMMENT '64-bit reference to the sorted phrase list used to calculate this result',
    user_id         bigint        NOT NULL COMMENT 'the id of the user who has requested the change of the text result',
    text_value      text      DEFAULT NULL COMMENT 'the user specific text value change',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to store the user specific changes to cache the formula second most often requested text results related up to eight prime phrase';

-- --------------------------------------------------------

--
-- table structure to cache the formula text results related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS results_text_big
(
    group_id        char(255)     NOT NULL COMMENT 'the variable text index to find text result',
    source_group_id text      DEFAULT NULL COMMENT 'text reference to the sorted phrase list used to calculate this result',
    text_value      text          NOT NULL COMMENT 'the text value given by the user',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    user_id         bigint    DEFAULT NULL COMMENT 'the id of the user who has requested the calculation',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to cache the formula text results related to more than 16 phrases';

--
-- table structure to store the user specific changes of text results related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS user_results_text_big
(
    group_id        char(255)     NOT NULL COMMENT 'the text index for more than 16 phrases to find the text result',
    source_group_id text      DEFAULT NULL COMMENT 'text reference to the sorted phrase list used to calculate this result',
    user_id         bigint        NOT NULL COMMENT 'the id of the user who has requested the change of the text result',
    text_value      text      DEFAULT NULL COMMENT 'the user specific text value change',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to store the user specific changes of text results related to more than 16 phrases';

-- --------------------------------------------------------

--
-- table structure to cache the formula public unprotected time results related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_time_standard_prime
(
    formula_id  smallint     NOT NULL COMMENT 'formula id that is part of the prime key for a time result',
    phrase_id_1 smallint     NOT NULL COMMENT 'phrase id that is part of the prime key for a time result',
    phrase_id_2 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time result',
    phrase_id_3 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time result',
    time_value  timestamp     NOT NULL COMMENT 'the timestamp given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to cache the formula public unprotected time results related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';

--
-- table structure to cache the formula public unprotected time results related up to eight prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_time_standard_main
(
    formula_id  smallint     NOT NULL COMMENT 'formula id that is part of the prime key for a time result',
    phrase_id_1 smallint     NOT NULL COMMENT 'phrase id that is part of the prime key for a time result',
    phrase_id_2 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time result',
    phrase_id_3 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time result',
    phrase_id_4 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time result',
    phrase_id_5 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time result',
    phrase_id_6 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time result',
    phrase_id_7 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time result',
    time_value  timestamp     NOT NULL COMMENT 'the timestamp given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to cache the formula public unprotected time results related up to eight prime phrase that have never changed the owner, does not have a description and are rarely updated';

--
-- table structure to cache the formula public unprotected time results that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_time_standard
(
    group_id   char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the time result',
    time_value timestamp     NOT NULL COMMENT 'the timestamp given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to cache the formula public unprotected time results that have never changed the owner, does not have a description and are rarely updated';

-- --------------------------------------------------------

--
-- table structure to cache the formula time results related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS results_time
(
    group_id        char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the time result',
    source_group_id char(112) DEFAULT NULL COMMENT '512-bit reference to the sorted phrase list used to calculate this result',
    time_value      timestamp     NOT NULL COMMENT 'the timestamp given by the user',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    user_id         bigint    DEFAULT NULL COMMENT 'the id of the user who has requested the calculation',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to cache the formula time results related to up to 16 phrases';

--
-- table structure to cache the user specific changes of time results related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS user_results_time
(
    group_id        char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the user time result',
    source_group_id char(112) DEFAULT NULL COMMENT '512-bit reference to the sorted phrase list used to calculate this result',
    user_id         bigint        NOT NULL COMMENT 'the id of the user who has requested the change of the time result',
    time_value      timestamp DEFAULT NULL COMMENT 'the user specific timestamp change',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to cache the user specific changes of time results related to up to 16 phrases';

-- --------------------------------------------------------

--
-- table structure to cache the formula most often requested time results related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS results_time_prime
(
    phrase_id_1     smallint      NOT NULL COMMENT 'phrase id that is part of the prime key for a time result',
    phrase_id_2     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time result',
    phrase_id_3     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time result',
    phrase_id_4     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time result',
    source_group_id bigint    DEFAULT NULL COMMENT '64-bit reference to the sorted phrase list used to calculate this result',
    time_value      timestamp     NOT NULL COMMENT 'the timestamp given by the user',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    user_id         bigint    DEFAULT NULL COMMENT 'the id of the user who has requested the calculation',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to cache the formula most often requested time results related up to four prime phrase';

--
-- table structure to store the user specific changes for the most often requested time results related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS user_results_time_prime
(
    phrase_id_1     smallint      NOT NULL COMMENT 'phrase id that is with the user id part of the prime key for a time result',
    phrase_id_2     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a time result',
    phrase_id_3     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a time result',
    phrase_id_4     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a time result',
    source_group_id bigint    DEFAULT NULL COMMENT '64-bit reference to the sorted phrase list used to calculate this result',
    user_id         bigint        NOT NULL COMMENT 'the id of the user who has requested the change of the time result',
    time_value      timestamp DEFAULT NULL COMMENT 'the user specific timestamp change',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to store the user specific changes for the most often requested time results related up to four prime phrase';

-- --------------------------------------------------------

--
-- table structure to cache the formula second most often requested time results related up to eight prime phrase
--

CREATE TABLE IF NOT EXISTS results_time_main
(
    phrase_id_1     smallint      NOT NULL COMMENT 'phrase id that is part of the prime key for a time result',
    phrase_id_2     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time result',
    phrase_id_3     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time result',
    phrase_id_4     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time result',
    phrase_id_5     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time result',
    phrase_id_6     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time result',
    phrase_id_7     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time result',
    phrase_id_8     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time result',
    source_group_id bigint    DEFAULT NULL COMMENT '64-bit reference to the sorted phrase list used to calculate this result',
    time_value      timestamp     NOT NULL COMMENT 'the timestamp given by the user',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    user_id         bigint    DEFAULT NULL COMMENT 'the id of the user who has requested the calculation',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to cache the formula second most often requested time results related up to eight prime phrase';

--
-- table structure to store the user specific changes to cache the formula second most often requested time results related up to eight prime phrase
--

CREATE TABLE IF NOT EXISTS user_results_time_main
(
    phrase_id_1     smallint      NOT NULL COMMENT 'phrase id that is with the user id part of the prime key for a time result',
    phrase_id_2     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a time result',
    phrase_id_3     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a time result',
    phrase_id_4     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a time result',
    phrase_id_5     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a time result',
    phrase_id_6     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a time result',
    phrase_id_7     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a time result',
    phrase_id_8     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a time result',
    source_group_id bigint    DEFAULT NULL COMMENT '64-bit reference to the sorted phrase list used to calculate this result',
    user_id         bigint        NOT NULL COMMENT 'the id of the user who has requested the change of the time result',
    time_value      timestamp DEFAULT NULL COMMENT 'the user specific timestamp change',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to store the user specific changes to cache the formula second most often requested time results related up to eight prime phrase';

-- --------------------------------------------------------

--
-- table structure to cache the formula time results related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS results_time_big
(
    group_id        char(255)     NOT NULL COMMENT 'the variable text index to find time result',
    source_group_id text      DEFAULT NULL COMMENT 'text reference to the sorted phrase list used to calculate this result',
    time_value      timestamp     NOT NULL COMMENT 'the timestamp given by the user',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    user_id         bigint    DEFAULT NULL COMMENT 'the id of the user who has requested the calculation',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to cache the formula time results related to more than 16 phrases';

--
-- table structure to store the user specific changes of time results related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS user_results_time_big
(
    group_id        char(255)     NOT NULL COMMENT 'the text index for more than 16 phrases to find the time result',
    source_group_id text      DEFAULT NULL COMMENT 'text reference to the sorted phrase list used to calculate this result',
    user_id         bigint        NOT NULL COMMENT 'the id of the user who has requested the change of the time result',
    time_value      timestamp DEFAULT NULL COMMENT 'the user specific timestamp change',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to store the user specific changes of time results related to more than 16 phrases';

-- --------------------------------------------------------

--
-- table structure to cache the formula public unprotected geo results related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_geo_standard_prime
(
    formula_id  smallint     NOT NULL COMMENT 'formula id that is part of the prime key for a geo result',
    phrase_id_1 smallint     NOT NULL COMMENT 'phrase id that is part of the prime key for a geo result',
    phrase_id_2 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo result',
    phrase_id_3 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo result',
    geo_value   point         NOT NULL COMMENT 'the geolocation given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to cache the formula public unprotected geo results related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';

--
-- table structure to cache the formula public unprotected geo results related up to eight prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_geo_standard_main
(
    formula_id  smallint     NOT NULL COMMENT 'formula id that is part of the prime key for a geo result',
    phrase_id_1 smallint     NOT NULL COMMENT 'phrase id that is part of the prime key for a geo result',
    phrase_id_2 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo result',
    phrase_id_3 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo result',
    phrase_id_4 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo result',
    phrase_id_5 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo result',
    phrase_id_6 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo result',
    phrase_id_7 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo result',
    geo_value   point         NOT NULL COMMENT 'the geolocation given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to cache the formula public unprotected geo results related up to eight prime phrase that have never changed the owner, does not have a description and are rarely updated';

--
-- table structure to cache the formula public unprotected geo results that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_geo_standard
(
    group_id   char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the geo result',
    geo_value  point         NOT NULL COMMENT 'the geolocation given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to cache the formula public unprotected geo results that have never changed the owner, does not have a description and are rarely updated';

-- --------------------------------------------------------

--
-- table structure to cache the formula geo results related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS results_geo
(
    group_id        char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the geo result',
    source_group_id char(112) DEFAULT NULL COMMENT '512-bit reference to the sorted phrase list used to calculate this result',
    geo_value       point         NOT NULL COMMENT 'the geolocation given by the user',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    user_id         bigint    DEFAULT NULL COMMENT 'the id of the user who has requested the calculation',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to cache the formula geo results related to up to 16 phrases';

--
-- table structure to cache the user specific changes of geo results related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS user_results_geo
(
    group_id        char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the user geo result',
    source_group_id char(112) DEFAULT NULL COMMENT '512-bit reference to the sorted phrase list used to calculate this result',
    user_id         bigint        NOT NULL COMMENT 'the id of the user who has requested the change of the geo result',
    geo_value       point     DEFAULT NULL COMMENT 'the user specific geolocation change',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to cache the user specific changes of geo results related to up to 16 phrases';

-- --------------------------------------------------------

--
-- table structure to cache the formula most often requested geo results related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS results_geo_prime
(
    phrase_id_1     smallint      NOT NULL COMMENT 'phrase id that is part of the prime key for a geo result',
    phrase_id_2     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo result',
    phrase_id_3     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo result',
    phrase_id_4     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo result',
    source_group_id bigint    DEFAULT NULL COMMENT '64-bit reference to the sorted phrase list used to calculate this result',
    geo_value       point         NOT NULL COMMENT 'the geolocation given by the user',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    user_id         bigint    DEFAULT NULL COMMENT 'the id of the user who has requested the calculation',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to cache the formula most often requested geo results related up to four prime phrase';

--
-- table structure to store the user specific changes for the most often requested geo results related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS user_results_geo_prime
(
    phrase_id_1     smallint      NOT NULL COMMENT 'phrase id that is with the user id part of the prime key for a geo result',
    phrase_id_2     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a geo result',
    phrase_id_3     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a geo result',
    phrase_id_4     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a geo result',
    source_group_id bigint    DEFAULT NULL COMMENT '64-bit reference to the sorted phrase list used to calculate this result',
    user_id         bigint        NOT NULL COMMENT 'the id of the user who has requested the change of the geo result',
    geo_value       point     DEFAULT NULL COMMENT 'the user specific geolocation change',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to store the user specific changes for the most often requested geo results related up to four prime phrase';

-- --------------------------------------------------------

--
-- table structure to cache the formula second most often requested geo results related up to eight prime phrase
--

CREATE TABLE IF NOT EXISTS results_geo_main
(
    phrase_id_1     smallint      NOT NULL COMMENT 'phrase id that is part of the prime key for a geo result',
    phrase_id_2     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo result',
    phrase_id_3     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo result',
    phrase_id_4     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo result',
    phrase_id_5     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo result',
    phrase_id_6     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo result',
    phrase_id_7     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo result',
    phrase_id_8     smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo result',
    source_group_id bigint    DEFAULT NULL COMMENT '64-bit reference to the sorted phrase list used to calculate this result',
    geo_value       point         NOT NULL COMMENT 'the geolocation given by the user',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    user_id         bigint    DEFAULT NULL COMMENT 'the id of the user who has requested the calculation',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to cache the formula second most often requested geo results related up to eight prime phrase';

--
-- table structure to store the user specific changes to cache the formula second most often requested geo results related up to eight prime phrase
--

CREATE TABLE IF NOT EXISTS user_results_geo_main
(
    phrase_id_1     smallint      NOT NULL COMMENT 'phrase id that is with the user id part of the prime key for a geo result',
    phrase_id_2     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a geo result',
    phrase_id_3     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a geo result',
    phrase_id_4     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a geo result',
    phrase_id_5     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a geo result',
    phrase_id_6     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a geo result',
    phrase_id_7     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a geo result',
    phrase_id_8     smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a geo result',
    source_group_id bigint    DEFAULT NULL COMMENT '64-bit reference to the sorted phrase list used to calculate this result',
    user_id         bigint        NOT NULL COMMENT 'the id of the user who has requested the change of the geo result',
    geo_value       point     DEFAULT NULL COMMENT 'the user specific geolocation change',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to store the user specific changes to cache the formula second most often requested geo results related up to eight prime phrase';

-- --------------------------------------------------------

--
-- table structure to cache the formula geo results related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS results_geo_big
(
    group_id        char(255)     NOT NULL COMMENT 'the variable text index to find geo result',
    source_group_id text      DEFAULT NULL COMMENT 'text reference to the sorted phrase list used to calculate this result',
    geo_value       point         NOT NULL COMMENT 'the geolocation given by the user',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    user_id         bigint    DEFAULT NULL COMMENT 'the id of the user who has requested the calculation',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to cache the formula geo results related to more than 16 phrases';

--
-- table structure to store the user specific changes of geo results related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS user_results_geo_big
(
    group_id        char(255)     NOT NULL COMMENT 'the text index for more than 16 phrases to find the geo result',
    source_group_id text      DEFAULT NULL COMMENT 'text reference to the sorted phrase list used to calculate this result',
    user_id         bigint        NOT NULL COMMENT 'the id of the user who has requested the change of the geo result',
    geo_value       point     DEFAULT NULL COMMENT 'the user specific geolocation change',
    last_update     timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id      bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    excluded        smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id   smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id      smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB   DEFAULT CHARSET = utf8 COMMENT 'to store the user specific changes of geo results related to more than 16 phrases';

-- --------------------------------------------------------

--
-- table structure for the common parameters for a list of numbers that differ only by the timestamp
--

CREATE TABLE IF NOT EXISTS results_time_series
(
    group_id              char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the time_series result',
    source_group_id       char(112) DEFAULT NULL COMMENT '512-bit reference to the sorted phrase list used to calculate this result',
    result_time_series_id bigint        NOT NULL COMMENT 'the id of the time series as a 64 bit integer value because the number of time series is not expected to be too high',
    last_update           timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id            bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    user_id               bigint    DEFAULT NULL COMMENT 'the id of the user who has requested the calculation',
    excluded              smallint  DEFAULT NULL COMMENT 'true if a user,but not all,have removed it',
    share_type_id         smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id            smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for the common parameters for a list of numbers that differ only by the timestamp';

--
-- table structure for the common parameters for a list of numbers that differ only by the timestamp
--

CREATE TABLE IF NOT EXISTS user_results_time_series
(
    group_id              char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the user time_series result',
    source_group_id       char(112) DEFAULT NULL COMMENT '512-bit reference to the sorted phrase list used to calculate this result',
    user_id               bigint        NOT NULL COMMENT 'the id of the user who has requested the change of the time_series result',
    result_time_series_id bigint        NOT NULL COMMENT 'the 64 bit integer which is unique for the standard and the user series',
    last_update           timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id            bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    excluded              smallint  DEFAULT NULL COMMENT 'true if a user,but not all,have removed it',
    share_type_id         smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id            smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for the common parameters for a list of numbers that differ only by the timestamp';

-- --------------------------------------------------------

--
-- table structure for the common parameters for a list of numbers that differ only by the timestamp
--

CREATE TABLE IF NOT EXISTS results_time_series_prime
(
    phrase_id_1           smallint      NOT NULL COMMENT 'phrase id that is part of the prime key for a time_series result',
    phrase_id_2           smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time_series result',
    phrase_id_3           smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time_series result',
    phrase_id_4           smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time_series result',
    source_group_id       bigint    DEFAULT NULL COMMENT '64-bit reference to the sorted phrase list used to calculate this result',
    result_time_series_id bigint        NOT NULL COMMENT 'the id of the time series as a 64 bit integer value because the number of time series is not expected to be too high',
    last_update           timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id            bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    user_id               bigint    DEFAULT NULL COMMENT 'the id of the user who has requested the calculation',
    excluded              smallint  DEFAULT NULL COMMENT 'true if a user,but not all,have removed it',
    share_type_id         smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id            smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for the common parameters for a list of numbers that differ only by the timestamp';

--
-- table structure for the common parameters for a list of numbers that differ only by the timestamp
--

CREATE TABLE IF NOT EXISTS user_results_time_series_prime
(
    phrase_id_1           smallint      NOT NULL COMMENT 'phrase id that is with the user id part of the prime key for a time_series result',
    phrase_id_2           smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a time_series result',
    phrase_id_3           smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a time_series result',
    phrase_id_4           smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id part of the prime key for a time_series result',
    source_group_id       bigint    DEFAULT NULL COMMENT '64-bit reference to the sorted phrase list used to calculate this result',
    user_id               bigint        NOT NULL COMMENT 'the id of the user who has requested the change of the time_series result',
    result_time_series_id bigint        NOT NULL COMMENT 'the 64 bit integer which is unique for the standard and the user series',
    last_update           timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id            bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    excluded              smallint  DEFAULT NULL COMMENT 'true if a user,but not all,have removed it',
    share_type_id         smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id            smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for the common parameters for a list of numbers that differ only by the timestamp';

-- --------------------------------------------------------

--
-- table structure for the common parameters for a list of numbers that differ only by the timestamp
--

CREATE TABLE IF NOT EXISTS results_time_series_big
(
    group_id              char(255)     NOT NULL COMMENT 'the variable text index to find time_series result',
    source_group_id       text      DEFAULT NULL COMMENT 'text reference to the sorted phrase list used to calculate this result',
    result_time_series_id bigint        NOT NULL COMMENT 'the id of the time series as a 64 bit integer value because the number of time series is not expected to be too high',
    last_update           timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id            bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    user_id               bigint    DEFAULT NULL COMMENT 'the id of the user who has requested the calculation',
    excluded              smallint  DEFAULT NULL COMMENT 'true if a user,but not all,have removed it',
    share_type_id         smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id            smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for the common parameters for a list of numbers that differ only by the timestamp';

--
-- table structure for the common parameters for a list of numbers that differ only by the timestamp
--

CREATE TABLE IF NOT EXISTS user_results_time_series_big
(
    group_id              char(255)     NOT NULL COMMENT 'the text index for more than 16 phrases to find the time_series result',
    source_group_id       text      DEFAULT NULL COMMENT 'text reference to the sorted phrase list used to calculate this result',
    user_id               bigint        NOT NULL COMMENT 'the id of the user who has requested the change of the time_series result',
    result_time_series_id bigint        NOT NULL COMMENT 'the 64 bit integer which is unique for the standard and the user series',
    last_update           timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    formula_id            bigint        NOT NULL COMMENT 'the id of the formula which has been used to calculate this result',
    excluded              smallint  DEFAULT NULL COMMENT 'true if a user,but not all,have removed it',
    share_type_id         smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id            smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for the common parameters for a list of numbers that differ only by the timestamp';

-- --------------------------------------------------------

--
-- table structure to assign predefined behaviour to a view
--

CREATE TABLE IF NOT EXISTS view_types
(
    view_type_id smallint         NOT NULL COMMENT 'the internal unique primary index',
    type_name    varchar(255)     NOT NULL COMMENT 'the unique type name as shown to the user and used for the selection',
    code_id      varchar(255) DEFAULT NULL COMMENT 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration',
    description  text         DEFAULT NULL COMMENT 'text to explain the type to the user as a tooltip; to be replaced by a language form entry'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to assign predefined behaviour to a view';

--
-- AUTO_INCREMENT for table view_types
--
ALTER TABLE view_types
    MODIFY view_type_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure the display style for a view or component e.g. number of columns to use
--

CREATE TABLE IF NOT EXISTS view_styles
(
    view_style_id   smallint         NOT NULL COMMENT 'the internal unique primary index',
    view_style_name varchar(255)     NOT NULL COMMENT 'the unique type name as shown to the user and used for the selection',
    code_id         varchar(255) DEFAULT NULL COMMENT 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration',
    description     text         DEFAULT NULL COMMENT 'text to explain the type to the user as a tooltip; to be replaced by a language form entry'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'the display style for a view or component e.g. number of columns to use';

--
-- AUTO_INCREMENT for table view_styles
--
ALTER TABLE view_styles
    MODIFY view_style_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure to store all user interfaces entry points
--

CREATE TABLE IF NOT EXISTS views
(
    view_id       bigint           NOT NULL COMMENT 'the internal unique primary index',
    user_id       bigint       DEFAULT NULL COMMENT 'the owner / creator of the view',
    view_name     varchar(255)     NOT NULL COMMENT 'the name of the view used for searching',
    description   text         DEFAULT NULL COMMENT 'to explain the view to the user with a mouse over text; to be replaced by a language form entry',
    view_type_id  smallint     DEFAULT NULL COMMENT 'to link coded functionality to views e.g. to use a view for the startup page',
    view_style_id smallint     DEFAULT NULL COMMENT 'the default display style for this view',
    code_id       varchar(255) DEFAULT NULL COMMENT 'to link coded functionality to a specific view e.g. define the internal system views',
    excluded      smallint     DEFAULT NULL COMMENT 'true if a user,but not all,have removed it',
    share_type_id smallint     DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint     DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to store all user interfaces entry points';

--
-- AUTO_INCREMENT for table views
--
ALTER TABLE views
    MODIFY view_id int(11) NOT NULL AUTO_INCREMENT;

--
-- table structure to save user specific changes to store all user interfaces entry points
--

CREATE TABLE IF NOT EXISTS user_views
(
    view_id       bigint NOT NULL              COMMENT 'with the user_id the internal unique primary index',
    user_id       bigint NOT NULL              COMMENT 'the changer of the view',
    language_id   bigint NOT NULL DEFAULT 1    COMMENT 'the name of the view used for searching',
    view_name     varchar(255)    DEFAULT NULL COMMENT 'the name of the view used for searching',
    description   text            DEFAULT NULL COMMENT 'to explain the view to the user with a mouse over text; to be replaced by a language form entry',
    view_type_id  smallint        DEFAULT NULL COMMENT 'to link coded functionality to views e.g. to use a view for the startup page',
    view_style_id smallint        DEFAULT NULL COMMENT 'the default display style for this view',
    excluded      smallint        DEFAULT NULL COMMENT 'true if a user,but not all,have removed it',
    share_type_id smallint        DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint        DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to store all user interfaces entry points';

-- --------------------------------------------------------

--
-- table structure to define the behaviour of the link between a term and a view
--

CREATE TABLE IF NOT EXISTS view_link_types
(
    view_link_type_id smallint      NOT NULL COMMENT 'the internal unique primary index',
    type_name      varchar(255)     NOT NULL COMMENT 'the unique type name as shown to the user and used for the selection',
    code_id        varchar(255) DEFAULT NULL COMMENT 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration',
    description    text         DEFAULT NULL COMMENT 'text to explain the type to the user as a tooltip; to be replaced by a language form entry'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to define the behaviour of the link between a term and a view';

--
-- AUTO_INCREMENT for table view_link_types
--
ALTER TABLE view_link_types
    MODIFY view_link_type_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure to link view to a word,triple,verb or formula with an n:m relation
--

CREATE TABLE IF NOT EXISTS term_views
(
    term_view_id bigint       NOT NULL COMMENT 'the internal unique primary index',
    term_id           bigint       NOT NULL,
    view_id           bigint       NOT NULL,
    view_link_type_id smallint     NOT NULL DEFAULT 1 COMMENT '1 = from_term_id is link the terms table; 2=link to the term_links table;3=to term_groups',
    user_id           bigint   DEFAULT NULL COMMENT 'the owner / creator of the term_view',
    description       text     DEFAULT NULL,
    excluded          smallint DEFAULT NULL COMMENT 'true if a user,but not all,have removed it',
    share_type_id     smallint DEFAULT NULL COMMENT 'to restrict the access',
    protect_id        smallint DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to link view to a word,triple,verb or formula with an n:m relation';

--
-- AUTO_INCREMENT for table term_views
--
ALTER TABLE term_views
    MODIFY term_view_id int(11) NOT NULL AUTO_INCREMENT;

--
-- table structure to save user specific changes to link view to a word,triple,verb or formula with an n:m relation
--

CREATE TABLE IF NOT EXISTS user_term_views
(
    term_view_id bigint       NOT NULL COMMENT 'with the user_id the internal unique primary index',
    user_id           bigint       NOT NULL COMMENT 'the changer of the term_view',
    view_link_type_id smallint DEFAULT NULL,
    description       text     DEFAULT NULL,
    excluded          smallint DEFAULT NULL COMMENT 'true if a user,but not all,have removed it',
    share_type_id     smallint DEFAULT NULL COMMENT 'to restrict the access',
    protect_id        smallint DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to link view to a word,triple,verb or formula with an n:m relation';

-- --------------------------------------------------------

--
-- table structure to assign predefined behaviour to a component view link
--

CREATE TABLE IF NOT EXISTS component_link_types
(
    component_link_type_id smallint         NOT NULL COMMENT 'the internal unique primary index',
    type_name              varchar(255)     NOT NULL COMMENT 'the unique type name as shown to the user and used for the selection',
    code_id                varchar(255) DEFAULT NULL COMMENT 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration',
    description            text         DEFAULT NULL COMMENT 'text to explain the type to the user as a tooltip; to be replaced by a language form entry'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to assign predefined behaviour to a component view link';

--
-- AUTO_INCREMENT for table component_link_types
--
ALTER TABLE component_link_types
    MODIFY component_link_type_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure to define the position of components
--

CREATE TABLE IF NOT EXISTS position_types
(
    position_type_id smallint     NOT NULL COMMENT 'the internal unique primary index',
    type_name    varchar(255)     NOT NULL COMMENT 'the unique type name as shown to the user and used for the selection',
    code_id      varchar(255) DEFAULT NULL COMMENT 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration',
    description  text         DEFAULT NULL COMMENT 'text to explain the type to the user as a tooltip; to be replaced by a language form entry'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to define the position of components';

--
-- AUTO_INCREMENT for table position_types
--
ALTER TABLE position_types
    MODIFY position_type_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure to display e.g. a fixed text, term or formula result
--

CREATE TABLE IF NOT EXISTS component_types
(
    component_type_id smallint    NOT NULL COMMENT 'the internal unique primary index',
    type_name    varchar(255)     NOT NULL COMMENT 'the unique type name as shown to the user and used for the selection',
    code_id      varchar(255) DEFAULT NULL COMMENT 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration',
    description  text         DEFAULT NULL COMMENT 'text to explain the type to the user as a tooltip; to be replaced by a language form entry'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to display e.g. a fixed text, term or formula result';

--
-- AUTO_INCREMENT for table component_types
--
ALTER TABLE component_types
    MODIFY component_type_id int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- table structure for the single components of a view
--

CREATE TABLE IF NOT EXISTS components
(
    component_id           bigint           NOT NULL COMMENT 'the internal unique primary index',
    user_id                bigint       DEFAULT NULL COMMENT 'the owner / creator of the component',
    component_name         varchar(255)     NOT NULL COMMENT 'the unique name used to select a component by the user',
    description            text         DEFAULT NULL COMMENT 'to explain the view component to the user with a mouse over text; to be replaced by a language form entry',
    component_type_id      smallint     DEFAULT NULL COMMENT 'to select the predefined functionality',
    view_style_id          smallint     DEFAULT NULL COMMENT 'the default display style for this component',
    word_id_row            bigint       DEFAULT NULL COMMENT 'for a tree the related value the start node',
    formula_id             bigint       DEFAULT NULL COMMENT 'used for type 6',
    word_id_col            bigint       DEFAULT NULL COMMENT 'to define the type for the table columns',
    word_id_col2           bigint       DEFAULT NULL COMMENT 'e.g. "quarter" to show the quarters between the year columns or the second axis of a chart',
    linked_component_id    bigint       DEFAULT NULL COMMENT 'to link this component to another component',
    component_link_type_id smallint     DEFAULT NULL COMMENT 'to define how this entry links to the other entry',
    link_type_id           smallint     DEFAULT NULL COMMENT 'e.g. for type 4 to select possible terms',
    code_id                varchar(255) DEFAULT NULL COMMENT 'used for system components to select the component by the program code',
    ui_msg_code_id         varchar(255) DEFAULT NULL COMMENT 'used for system components the id to select the language specific user interface message e.g. "add word"',
    excluded               smallint     DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id          smallint     DEFAULT NULL COMMENT 'to restrict the access',
    protect_id             smallint     DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for the single components of a view';

--
-- AUTO_INCREMENT for table components
--
ALTER TABLE components
    MODIFY component_id int(11) NOT NULL AUTO_INCREMENT;

--
-- table structure to save user specific changes for the single components of a view
--

CREATE TABLE IF NOT EXISTS user_components
(
    component_id           bigint       NOT     NULL COMMENT 'with the user_id the internal unique primary index',
    user_id                bigint       NOT     NULL COMMENT 'the changer of the component',
    component_name         varchar(255) DEFAULT NULL COMMENT 'the unique name used to select a component by the user',
    description            text         DEFAULT NULL COMMENT 'to explain the view component to the user with a mouse over text; to be replaced by a language form entry',
    component_type_id      smallint     DEFAULT NULL COMMENT 'to select the predefined functionality',
    view_style_id          smallint     DEFAULT NULL COMMENT 'the default display style for this component',
    word_id_row            bigint       DEFAULT NULL COMMENT 'for a tree the related value the start node',
    formula_id             bigint       DEFAULT NULL COMMENT 'used for type 6',
    word_id_col            bigint       DEFAULT NULL COMMENT 'to define the type for the table columns',
    word_id_col2           bigint       DEFAULT NULL COMMENT 'e.g. "quarter" to show the quarters between the year columns or the second axis of a chart',
    linked_component_id    bigint       DEFAULT NULL COMMENT 'to link this component to another component',
    component_link_type_id smallint     DEFAULT NULL COMMENT 'to define how this entry links to the other entry',
    link_type_id           smallint     DEFAULT NULL COMMENT 'e.g. for type 4 to select possible terms',
    excluded               smallint     DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id          smallint     DEFAULT NULL COMMENT 'to restrict the access',
    protect_id             smallint     DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for the single components of a view';

-- --------------------------------------------------------

--
-- table structure to link components to views with an n:m relation
--

CREATE TABLE IF NOT EXISTS component_links
(
    component_link_id      bigint   NOT NULL COMMENT 'the internal unique primary index',
    view_id                bigint   NOT NULL,
    component_id           bigint   NOT NULL,
    user_id                bigint            DEFAULT NULL COMMENT 'the owner / creator of the component_link',
    order_nbr              bigint   NOT NULL DEFAULT 1,
    component_link_type_id smallint NOT NULL DEFAULT 1,
    position_type_id       smallint NOT NULL DEFAULT 1 COMMENT 'the position of the component e.g. right or below',
    view_style_id          smallint          DEFAULT NULL COMMENT 'the display style for this component link',
    excluded               smallint          DEFAULT NULL COMMENT 'true if a user,but not all,have removed it',
    share_type_id          smallint          DEFAULT NULL COMMENT 'to restrict the access',
    protect_id             smallint          DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to link components to views with an n:m relation';

--
-- AUTO_INCREMENT for table component_links
--
ALTER TABLE component_links
    MODIFY component_link_id int(11) NOT NULL AUTO_INCREMENT;

--
-- table structure to save user specific changes to link components to views with an n:m relation
--

CREATE TABLE IF NOT EXISTS user_component_links
(
    component_link_id      bigint       NOT NULL COMMENT 'with the user_id the internal unique primary index',
    user_id                bigint       NOT NULL COMMENT 'the changer of the component_link',
    order_nbr              bigint   DEFAULT NULL,
    component_link_type_id smallint DEFAULT NULL,
    position_type_id       smallint DEFAULT NULL COMMENT 'the position of the component e.g. right or below',
    view_style_id          smallint DEFAULT NULL COMMENT 'the display style for this component link',
    excluded               smallint DEFAULT NULL COMMENT 'true if a user,but not all,have removed it',
    share_type_id          smallint DEFAULT NULL COMMENT 'to restrict the access',
    protect_id             smallint DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to link components to views with an n:m relation';

-- --------------------------------------------------------

--
-- structure for view prime_phrases (phrases with an id less than 2^16 so that 4 phrase id fit in a 64 bit db key)
--

CREATE OR REPLACE VIEW prime_phrases AS
SELECT w.word_id AS phrase_id,
       w.user_id,
       w.word_name AS phrase_name,
       w.description,
       w.`values`,
       w.phrase_type_id,
       w.excluded,
       w.share_type_id,
       w.protect_id
FROM words AS w
WHERE w.word_id < 32767
UNION
SELECT t.triple_id * -1 AS phrase_id,
       t.user_id,
       IF(t.triple_name IS NULL,
          IF(t.name_given IS NULL,
             t.name_generated,
             t.name_given),
          t.triple_name) AS phrase_name,
       t.description,
       t.`values`,
       t.phrase_type_id,
       t.excluded,
       t.share_type_id,
       t.protect_id
FROM triples AS t
WHERE t.triple_id < 32767;

--
-- structure for view phrases (phrases with an id that is not prime)
--

CREATE OR REPLACE VIEW phrases AS
SELECT w.word_id AS phrase_id,
       w.user_id,
       w.word_name AS phrase_name,
       w.description,
       w.`values`,
       w.phrase_type_id,
       w.excluded,
       w.share_type_id,
       w.protect_id
FROM words AS w
UNION
SELECT t.triple_id * -1 AS phrase_id,
       t.user_id,
       IF(t.triple_name IS NULL,
          IF(t.name_given IS NULL,
             t.name_generated,
             t.name_given),
          t.triple_name) AS phrase_name,
       t.description,
       t.`values`,
       t.phrase_type_id,
       t.excluded,
       t.share_type_id,
       t.protect_id
FROM triples AS t;

--
-- structure for view user_prime_phrases (phrases with an id less than 2^16 so that 4 phrase id fit in a 64 bit db key)
--

CREATE OR REPLACE VIEW user_prime_phrases AS
SELECT w.word_id AS phrase_id,
       w.user_id,
       w.word_name AS phrase_name,
       w.description,
       w.`values`,
       w.phrase_type_id,
       w.excluded,
       w.share_type_id,
       w.protect_id
FROM user_words AS w
WHERE w.word_id < 32767
UNION
SELECT t.triple_id * -1 AS phrase_id,
       t.user_id,
       IF(t.triple_name IS NULL,
          IF(t.name_given IS NULL,
             t.name_generated,
             t.name_given),
          t.triple_name) AS phrase_name,
       t.description,
       t.`values`,
       t.phrase_type_id,
       t.excluded,
       t.share_type_id,
       t.protect_id
FROM user_triples AS t
WHERE t.triple_id < 32767;

--
-- structure for view user_phrases (phrases with an id that is not prime)
--

CREATE OR REPLACE VIEW user_phrases AS
SELECT w.word_id AS phrase_id,
       w.user_id,
       w.word_name AS phrase_name,
       w.description,
       w.`values`,
       w.phrase_type_id,
       w.excluded,
       w.share_type_id,
       w.protect_id
FROM user_words AS w
UNION
SELECT t.triple_id * -1 AS phrase_id,
       t.user_id,
       IF(t.triple_name IS NULL,
          IF(t.name_given IS NULL,
             t.name_generated,
             t.name_given),
          t.triple_name) AS phrase_name,
       t.description,
       t.`values`,
       t.phrase_type_id,
       t.excluded,
       t.share_type_id,
       t.protect_id
FROM user_triples AS t;

-- --------------------------------------------------------

--
-- structure for view prime_terms (terms with an id less than 2^16 so that 4 term id fit in a 64 bit db key)
--

CREATE OR REPLACE VIEW prime_terms AS
    SELECT w.word_id * 2 - 1 AS term_id,
           w.user_id,
           w.word_name       AS term_name,
           w.description,
           w.`values`        AS `usage`,
           w.phrase_type_id  AS term_type_id,
           w.excluded,
           w.share_type_id,
           w.protect_id,
           ''                AS formula_text,
           ''                AS resolved_text
      FROM words AS w
     WHERE (w.phrase_type_id <> 10 OR w.phrase_type_id IS NULL)
       AND w.word_id < 32767
UNION
    SELECT t.triple_id * -2 + 1 AS term_id,
           t.user_id,
           IF(t.triple_name IS NULL,
              IF(t.name_given IS NULL,
                 t.name_generated,
                 t.name_given),
              t.triple_name)    AS term_name,
           t.description,
           t.`values`           AS `usage`,
           t.phrase_type_id     AS term_type_id,
           t.excluded,
           t.share_type_id,
           t.protect_id,
           ''                   AS formula_text,
           ''                   AS resolved_text
      FROM triples AS t
     WHERE t.triple_id < 32767
UNION
    SELECT f.formula_id * 2  AS term_id,
           f.user_id,
           f.formula_name    AS term_name,
           f.description,
           f.`usage`         AS `usage`,
           f.formula_type_id AS term_type_id,
           f.excluded,
           f.share_type_id,
           f.protect_id,
           f.formula_text,
           f.resolved_text
      FROM formulas AS f
     WHERE f.formula_id < 32767
UNION
    SELECT v.verb_id * -2 AS term_id,
           NULL           AS user_id,
           v.verb_name    AS term_name,
           v.description,
           v.words        AS `usage`,
           NULL           AS term_type_id,
           NULL           AS excluded,
           1              AS share_type_id,
           3              AS protect_id,
           ''             AS formula_text,
           ''             AS resolved_text
      FROM verbs AS v
     WHERE v.verb_id < 32767;

--
-- structure for view terms (terms with an id that is not prime)
--

CREATE OR REPLACE VIEW terms AS
    SELECT w.word_id * 2 - 1 AS term_id,
           w.user_id,
           w.word_name       AS term_name,
           w.description,
           w.`values`        AS `usage`,
           w.phrase_type_id  AS term_type_id,
           w.excluded,
           w.share_type_id,
           w.protect_id,
           ''                AS formula_text,
           ''                AS resolved_text
      FROM words AS w
     WHERE (w.phrase_type_id <> 10 OR w.phrase_type_id IS NULL)
UNION
    SELECT t.triple_id * -2 + 1 AS term_id,
           t.user_id,
           IF(t.triple_name IS NULL,
              IF(t.name_given IS NULL,
                 t.name_generated,
                 t.name_given),
              t.triple_name)    AS term_name,
           t.description,
           t.`values`           AS `usage`,
           t.phrase_type_id     AS term_type_id,
           t.excluded,
           t.share_type_id,
           t.protect_id,
           ''                   AS formula_text,
           ''                   AS resolved_text
      FROM triples AS t
UNION
    SELECT f.formula_id * 2  AS term_id,
           f.user_id,
           f.formula_name    AS term_name,
           f.description,
           f.`usage`         AS `usage`,
           f.formula_type_id AS term_type_id,
           f.excluded,
           f.share_type_id,
           f.protect_id,
           f.formula_text,
           f.resolved_text
      FROM formulas AS f
UNION
    SELECT v.verb_id * -2 AS term_id,
           NULL           AS user_id,
           v.verb_name    AS term_name,
           v.description,
           v.words        AS `usage`,
           NULL           AS term_type_id,
           NULL           AS excluded,
           1              AS share_type_id,
           3              AS protect_id,
           ''             AS formula_text,
           ''             AS resolved_text
      FROM verbs AS v;

--
-- structure for view user_prime_terms (terms with an id less than 2^16 so that 4 term id fit in a 64 bit db key)
--

CREATE OR REPLACE VIEW user_prime_terms AS
    SELECT w.word_id * 2 - 1 AS term_id,
           w.user_id,
           w.word_name       AS term_name,
           w.description,
           w.`values`        AS `usage`,
           w.phrase_type_id  AS term_type_id,
           w.excluded,
           w.share_type_id,
           w.protect_id,
           ''                AS formula_text,
           ''                AS resolved_text
      FROM user_words AS w
     WHERE (w.phrase_type_id <> 10 OR w.phrase_type_id IS NULL)
       AND w.word_id < 32767
UNION
    SELECT t.triple_id * -2 + 1 AS term_id,
           t.user_id,
           IF(t.triple_name IS NULL,
              IF(t.name_given IS NULL,
                 t.name_generated,
                 t.name_given),
              t.triple_name)    AS term_name,
           t.description,
           t.`values`           AS `usage`,
           t.phrase_type_id     AS term_type_id,
           t.excluded,
           t.share_type_id,
           t.protect_id,
           ''                   AS formula_text,
           ''                   AS resolved_text
      FROM user_triples AS t
     WHERE t.triple_id < 32767
UNION
    SELECT f.formula_id * 2  AS term_id,
           f.user_id,
           f.formula_name    AS term_name,
           f.description,
           f.`usage`         AS `usage`,
           f.formula_type_id AS term_type_id,
           f.excluded,
           f.share_type_id,
           f.protect_id,
           f.formula_text,
           f.resolved_text
      FROM user_formulas AS f
     WHERE f.formula_id < 32767
UNION
    SELECT v.verb_id * -2 AS term_id,
           NULL           AS user_id,
           v.verb_name    AS term_name,
           v.description,
           v.words        AS `usage`,
           NULL           AS term_type_id,
           NULL           AS excluded,
           1              AS share_type_id,
           3              AS protect_id,
           ''             AS formula_text,
           ''             AS resolved_text
      FROM verbs AS v
     WHERE v.verb_id < 32767;

--
-- structure for view user_terms (terms with an id that is not prime)
--

CREATE OR REPLACE VIEW user_terms AS
    SELECT w.word_id * 2 - 1 AS term_id,
           w.user_id,
           w.word_name       AS term_name,
           w.description,
           w.`values`        AS `usage`,
           w.phrase_type_id  AS term_type_id,
           w.excluded,
           w.share_type_id,
           w.protect_id,
           ''                AS formula_text,
           ''                AS resolved_text
      FROM user_words AS w
     WHERE (w.phrase_type_id <> 10 OR w.phrase_type_id IS NULL)
UNION
    SELECT t.triple_id * -2 + 1 AS term_id,
           t.user_id,
           IF(t.triple_name IS NULL,
              IF(t.name_given IS NULL,
                 t.name_generated,
                 t.name_given),
              t.triple_name)    AS term_name,
           t.description,
           t.`values`           AS `usage`,
           t.phrase_type_id     AS term_type_id,
           t.excluded,
           t.share_type_id,
           t.protect_id,
           ''                   AS formula_text,
           ''                   AS resolved_text
      FROM user_triples AS t
UNION
    SELECT f.formula_id * 2  AS term_id,
           f.user_id,
           f.formula_name    AS term_name,
           f.description,
           f.`usage`         AS `usage`,
           f.formula_type_id AS term_type_id,
           f.excluded,
           f.share_type_id,
           f.protect_id,
           f.formula_text,
           f.resolved_text
      FROM user_formulas AS f
UNION
    SELECT v.verb_id * -2 AS term_id,
           NULL           AS user_id,
           v.verb_name    AS term_name,
           v.description,
           v.words        AS `usage`,
           NULL           AS term_type_id,
           NULL           AS excluded,
           1              AS share_type_id,
           3              AS protect_id,
           ''             AS formula_text,
           ''             AS resolved_text
      FROM verbs AS v;


--
-- structure for view change_table_fields
--

CREATE OR REPLACE  VIEW change_table_fields AS
SELECT f.change_field_id  AS change_table_field_id,
       CONCAT(t.change_table_id, f.change_field_name) AS change_table_field_name,
       f.description,
       IF(f.code_id IS NULL ,
          CONCAT(t.change_table_id, f.change_field_name) ,
          f.code_id) AS code_id
FROM change_tables AS t, change_fields AS f
WHERE t.change_table_id = f.table_id;

-- --------------------------------------------------------
--
-- indexes for tables
-- remark: no index needed for preloaded tables such as phrase types
--
-- --------------------------------------------------------

--
-- indexes for table config
--

ALTER TABLE config
    ADD PRIMARY KEY (config_id),
    ADD KEY config_config_name_idx (config_name),
    ADD KEY config_code_idx (code_id);

-- --------------------------------------------------------

--
-- indexes for table sys_log_types
--

ALTER TABLE sys_log_types
    ADD PRIMARY KEY (sys_log_type_id),
    ADD KEY sys_log_types_type_name_idx (type_name);

-- --------------------------------------------------------

--
-- indexes for table sys_log_status
--

ALTER TABLE sys_log_status
    ADD PRIMARY KEY (sys_log_status_id),
    ADD KEY sys_log_status_type_name_idx (type_name);

-- --------------------------------------------------------

--
-- indexes for table sys_log_functions
--

ALTER TABLE sys_log_functions
    ADD PRIMARY KEY (sys_log_function_id),
    ADD KEY sys_log_functions_sys_log_function_name_idx (sys_log_function_name);

-- --------------------------------------------------------

--
-- indexes for table sys_log
--

ALTER TABLE sys_log
    ADD PRIMARY KEY (sys_log_id),
    ADD KEY sys_log_sys_log_time_idx (sys_log_time),
    ADD KEY sys_log_sys_log_type_idx (sys_log_type_id),
    ADD KEY sys_log_sys_log_function_idx (sys_log_function_id),
    ADD KEY sys_log_user_idx (user_id),
    ADD KEY sys_log_solver_idx (solver_id),
    ADD KEY sys_log_sys_log_status_idx (sys_log_status_id);

-- --------------------------------------------------------

--
-- indexes for table system_time_types
--

ALTER TABLE system_time_types
    ADD PRIMARY KEY (system_time_type_id),
    ADD KEY system_time_types_type_name_idx (type_name);

-- --------------------------------------------------------

--
-- indexes for table system_times
--

ALTER TABLE system_times
    ADD PRIMARY KEY (system_time_id),
    ADD KEY system_times_start_time_idx (start_time),
    ADD KEY system_times_end_time_idx (end_time),
    ADD KEY system_times_system_time_type_idx (system_time_type_id);

-- --------------------------------------------------------

--
-- indexes for table job_types
--

ALTER TABLE job_types
    ADD PRIMARY KEY (job_type_id),
    ADD KEY job_types_type_name_idx (type_name);

-- --------------------------------------------------------

--
-- indexes for table job_times
--

ALTER TABLE job_times
    ADD PRIMARY KEY (job_time_id),
    ADD KEY job_times_schedule_idx (schedule),
    ADD KEY job_times_job_type_idx (job_type_id),
    ADD KEY job_times_user_idx (user_id),
    ADD KEY job_times_parameter_idx (parameter);

-- --------------------------------------------------------

--
-- indexes for table jobs
--

ALTER TABLE jobs
    ADD PRIMARY KEY (job_id),
    ADD KEY jobs_user_idx (user_id),
    ADD KEY jobs_job_type_idx (job_type_id),
    ADD KEY jobs_request_time_idx (request_time),
    ADD KEY jobs_start_time_idx (start_time),
    ADD KEY jobs_end_time_idx (end_time),
    ADD KEY jobs_parameter_idx (parameter),
    ADD KEY jobs_change_field_idx (change_field_id),
    ADD KEY jobs_row_idx (row_id),
    ADD KEY jobs_source_idx (source_id),
    ADD KEY jobs_ref_idx (ref_id);

-- --------------------------------------------------------

--
-- indexes for table user_types
--

ALTER TABLE user_types
    ADD PRIMARY KEY (user_type_id),
    ADD KEY user_types_type_name_idx (type_name);

-- --------------------------------------------------------

--
-- indexes for table user_profiles
--

ALTER TABLE user_profiles
    ADD PRIMARY KEY (user_profile_id),
    ADD KEY user_profiles_type_name_idx (type_name);

-- --------------------------------------------------------

--
-- indexes for table user_official_types
--

ALTER TABLE user_official_types
    ADD PRIMARY KEY (user_official_type_id),
    ADD KEY user_official_types_type_name_idx (type_name);

-- --------------------------------------------------------

--
-- indexes for table users
--

ALTER TABLE users
    ADD PRIMARY KEY (user_id),
    ADD KEY users_user_name_idx (user_name),
    ADD KEY users_ip_address_idx (ip_address),
    ADD KEY users_code_idx (code_id),
    ADD KEY users_user_profile_idx (user_profile_id),
    ADD KEY users_user_type_idx (user_type_id);

-- --------------------------------------------------------

--
-- indexes for table ip_ranges
--

ALTER TABLE ip_ranges
    ADD PRIMARY KEY (ip_range_id),
    ADD KEY ip_ranges_ip_from_idx (ip_from),
    ADD KEY ip_ranges_ip_to_idx (ip_to);

-- --------------------------------------------------------
--
-- indexes for table sessions
--

ALTER TABLE sessions
    ADD PRIMARY KEY (session_id),
    ADD KEY sessions_uid_idx (uid);

-- --------------------------------------------------------

--
-- indexes for table change_actions
--

ALTER TABLE change_actions
    ADD PRIMARY KEY (change_action_id),
    ADD KEY change_actions_change_action_name_idx (change_action_name);

-- --------------------------------------------------------

--
-- indexes for table change_tables
--

ALTER TABLE change_tables
    ADD PRIMARY KEY (change_table_id),
    ADD KEY change_tables_change_table_name_idx (change_table_name);

-- --------------------------------------------------------

--
-- indexes for table change_fields
--

ALTER TABLE change_fields
    ADD PRIMARY KEY (change_field_id),
    ADD UNIQUE KEY change_fields_unique_idx (table_id,change_field_name),
    ADD KEY change_fields_table_idx (table_id),
    ADD KEY change_fields_change_field_name_idx (change_field_name);

-- --------------------------------------------------------

--
-- indexes for table changes
--

ALTER TABLE changes
    ADD PRIMARY KEY (change_id),
    ADD KEY changes_change_idx (change_id),
    ADD KEY changes_change_time_idx (change_time),
    ADD KEY changes_user_idx (user_id);

-- --------------------------------------------------------

--
-- indexes for table changes_norm
--

ALTER TABLE changes_norm
    ADD PRIMARY KEY (change_id),
    ADD KEY changes_norm_change_idx (change_id),
    ADD KEY changes_norm_change_time_idx (change_time),
    ADD KEY changes_norm_user_idx (user_id);

-- --------------------------------------------------------

--
-- indexes for table changes_big
--

ALTER TABLE changes_big
    ADD PRIMARY KEY (change_id),
    ADD KEY changes_big_change_idx (change_id),
    ADD KEY changes_big_change_time_idx (change_time),
    ADD KEY changes_big_user_idx (user_id);

-- --------------------------------------------------------

--
-- indexes for table change_values_prime
--

ALTER TABLE change_values_prime
    ADD PRIMARY KEY (change_id),
    ADD KEY change_values_prime_change_idx (change_id),
    ADD KEY change_values_prime_change_time_idx (change_time),
    ADD KEY change_values_prime_user_idx (user_id);

-- --------------------------------------------------------

--
-- indexes for table change_values_time_prime
--

ALTER TABLE change_values_time_prime
    ADD PRIMARY KEY (change_id),
    ADD KEY change_values_time_prime_change_idx (change_id),
    ADD KEY change_values_time_prime_change_time_idx (change_time),
    ADD KEY change_values_time_prime_user_idx (user_id);

-- --------------------------------------------------------

--
-- indexes for table change_values_text_prime
--

ALTER TABLE change_values_text_prime
    ADD PRIMARY KEY (change_id),
    ADD KEY change_values_text_prime_change_idx (change_id),
    ADD KEY change_values_text_prime_change_time_idx (change_time),
    ADD KEY change_values_text_prime_user_idx (user_id);

-- --------------------------------------------------------

--
-- indexes for table change_values_geo_prime
--

ALTER TABLE change_values_geo_prime
    ADD PRIMARY KEY (change_id),
    ADD KEY change_values_geo_prime_change_idx (change_id),
    ADD KEY change_values_geo_prime_change_time_idx (change_time),
    ADD KEY change_values_geo_prime_user_idx (user_id);

-- --------------------------------------------------------

--
-- indexes for table change_values_norm
--

ALTER TABLE change_values_norm
    ADD PRIMARY KEY (change_id),
    ADD KEY change_values_norm_change_idx (change_id),
    ADD KEY change_values_norm_change_time_idx (change_time),
    ADD KEY change_values_norm_user_idx (user_id);

-- --------------------------------------------------------

--
-- indexes for table change_values_time_norm
--

ALTER TABLE change_values_time_norm
    ADD PRIMARY KEY (change_id),
    ADD KEY change_values_time_norm_change_idx (change_id),
    ADD KEY change_values_time_norm_change_time_idx (change_time),
    ADD KEY change_values_time_norm_user_idx (user_id);

-- --------------------------------------------------------

--
-- indexes for table change_values_text_norm
--

ALTER TABLE change_values_text_norm
    ADD PRIMARY KEY (change_id),
    ADD KEY change_values_text_norm_change_idx (change_id),
    ADD KEY change_values_text_norm_change_time_idx (change_time),
    ADD KEY change_values_text_norm_user_idx (user_id);

-- --------------------------------------------------------

--
-- indexes for table change_values_geo_norm
--

ALTER TABLE change_values_geo_norm
    ADD PRIMARY KEY (change_id),
    ADD KEY change_values_geo_norm_change_idx (change_id),
    ADD KEY change_values_geo_norm_change_time_idx (change_time),
    ADD KEY change_values_geo_norm_user_idx (user_id);

-- --------------------------------------------------------

--
-- indexes for table change_values_big
--

ALTER TABLE change_values_big
    ADD PRIMARY KEY (change_id),
    ADD KEY change_values_big_change_idx (change_id),
    ADD KEY change_values_big_change_time_idx (change_time),
    ADD KEY change_values_big_user_idx (user_id);

-- --------------------------------------------------------

--
-- indexes for table change_values_time_big
--

ALTER TABLE change_values_time_big
    ADD PRIMARY KEY (change_id),
    ADD KEY change_values_time_big_change_idx (change_id),
    ADD KEY change_values_time_big_change_time_idx (change_time),
    ADD KEY change_values_time_big_user_idx (user_id);

-- --------------------------------------------------------

--
-- indexes for table change_values_text_big
--

ALTER TABLE change_values_text_big
    ADD PRIMARY KEY (change_id),
    ADD KEY change_values_text_big_change_idx (change_id),
    ADD KEY change_values_text_big_change_time_idx (change_time),
    ADD KEY change_values_text_big_user_idx (user_id);

-- --------------------------------------------------------

--
-- indexes for table change_values_geo_big
--

ALTER TABLE change_values_geo_big
    ADD PRIMARY KEY (change_id),
    ADD KEY change_values_geo_big_change_idx (change_id),
    ADD KEY change_values_geo_big_change_time_idx (change_time),
    ADD KEY change_values_geo_big_user_idx (user_id);

-- --------------------------------------------------------

--
-- indexes for table change_links
--

ALTER TABLE change_links
    ADD PRIMARY KEY (change_link_id),
    ADD KEY change_links_change_link_idx (change_link_id),
    ADD KEY change_links_change_time_idx (change_time),
    ADD KEY change_links_user_idx (user_id);

-- --------------------------------------------------------

--
-- indexes for table pod_types
--

ALTER TABLE pod_types
    ADD PRIMARY KEY (pod_type_id),
    ADD KEY pod_types_type_name_idx (type_name);

-- --------------------------------------------------------

--
-- indexes for table pod_status
--

ALTER TABLE pod_status
    ADD PRIMARY KEY (pod_status_id),
    ADD KEY pod_status_type_name_idx (type_name);

-- --------------------------------------------------------
--
-- indexes for table pods
--

ALTER TABLE pods
    ADD PRIMARY KEY (pod_id),
    ADD KEY pods_type_name_idx (type_name),
    ADD KEY pods_pod_type_idx (pod_type_id),
    ADD KEY pods_pod_status_idx (pod_status_id);

-- --------------------------------------------------------

--
-- indexes for table protection_types
--

ALTER TABLE protection_types
    ADD PRIMARY KEY (protection_type_id),
    ADD KEY protection_types_type_name_idx (type_name);

-- --------------------------------------------------------

--
-- indexes for table share_types
--

ALTER TABLE share_types
    ADD PRIMARY KEY (share_type_id),
    ADD KEY share_types_type_name_idx (type_name);

-- --------------------------------------------------------

--
-- indexes for table languages
--

ALTER TABLE languages
    ADD PRIMARY KEY (language_id),
    ADD KEY languages_language_name_idx (language_name);

-- --------------------------------------------------------

--
-- indexes for table language_forms
--

ALTER TABLE language_forms
    ADD PRIMARY KEY (language_form_id),
    ADD KEY language_forms_language_form_name_idx (language_form_name),
    ADD KEY language_forms_language_idx (language_id);

-- --------------------------------------------------------

--
-- indexes for table words
--
ALTER TABLE words
    ADD PRIMARY KEY (word_id),
    ADD KEY words_user_idx (user_id),
    ADD KEY words_word_name_idx (word_name),
    ADD KEY words_plural_idx (plural),
    ADD KEY words_phrase_type_idx (phrase_type_id),
    ADD KEY words_view_idx (view_id);

--
-- indexes for table user_words
--
ALTER TABLE user_words
    ADD PRIMARY KEY (word_id, user_id, language_id),
    ADD KEY user_words_word_idx (word_id),
    ADD KEY user_words_user_idx (user_id),
    ADD KEY user_words_language_idx (language_id),
    ADD KEY user_words_word_name_idx (word_name),
    ADD KEY user_words_plural_idx (plural),
    ADD KEY user_words_phrase_type_idx (phrase_type_id),
    ADD KEY user_words_view_idx (view_id);

-- --------------------------------------------------------

--
-- indexes for table verbs
--

ALTER TABLE verbs
    ADD PRIMARY KEY (verb_id),
    ADD KEY verbs_verb_name_idx (verb_name);

-- --------------------------------------------------------

--
-- indexes for table triples
--

ALTER TABLE triples
    ADD PRIMARY KEY (triple_id),
    ADD UNIQUE KEY triples_unique_idx  (from_phrase_id, verb_id, to_phrase_id),
    ADD KEY triples_from_phrase_idx    (from_phrase_id),
    ADD KEY triples_verb_idx           (verb_id),
    ADD KEY triples_to_phrase_idx      (to_phrase_id),
    ADD KEY triples_user_idx           (user_id),
    ADD KEY triples_triple_name_idx    (triple_name),
    ADD KEY triples_name_given_idx     (name_given),
    ADD KEY triples_name_generated_idx (name_generated),
    ADD KEY triples_phrase_type_idx    (phrase_type_id),
    ADD KEY triples_view_idx           (view_id);

--
-- indexes for table user_triples
--

ALTER TABLE user_triples ADD PRIMARY KEY (triple_id, user_id, language_id),
     ADD KEY user_triples_triple_idx         (triple_id),
     ADD KEY user_triples_user_idx           (user_id),
     ADD KEY user_triples_language_idx       (language_id),
     ADD KEY user_triples_triple_name_idx    (triple_name),
     ADD KEY user_triples_name_given_idx     (name_given),
     ADD KEY user_triples_name_generated_idx (name_generated),
     ADD KEY user_triples_phrase_type_idx    (phrase_type_id),
     ADD KEY user_triples_view_idx           (view_id);

-- --------------------------------------------------------

--
-- indexes for table phrase_table_status
--

ALTER TABLE phrase_table_status
    ADD PRIMARY KEY (phrase_table_status_id),
    ADD KEY phrase_table_status_type_name_idx (type_name);

-- --------------------------------------------------------

--
-- indexes for table phrase_tables
--

ALTER TABLE phrase_tables
    ADD PRIMARY KEY (phrase_table_id),
    ADD KEY phrase_tables_phrase_idx (phrase_id),
    ADD KEY phrase_tables_pod_idx (pod_id),
    ADD KEY phrase_tables_phrase_table_status_idx (phrase_table_status_id);

-- --------------------------------------------------------

--
-- indexes for table phrase_types
--

ALTER TABLE phrase_types
    ADD PRIMARY KEY (phrase_type_id),
    ADD KEY phrase_types_type_name_idx (type_name);

-- --------------------------------------------------------

--
-- indexes for table groups
--
ALTER TABLE `groups`
    ADD PRIMARY KEY (group_id),
    ADD KEY groups_user_idx (user_id);

--
-- indexes for table user_groups
--
ALTER TABLE user_groups
    ADD PRIMARY KEY (group_id, user_id),
    ADD KEY user_groups_user_idx (user_id);

--
-- indexes for table groups_prime
--
ALTER TABLE groups_prime
    ADD PRIMARY KEY (group_id),
    ADD KEY groups_prime_user_idx (user_id);

--
-- indexes for table user_groups_prime
--
ALTER TABLE user_groups_prime
    ADD PRIMARY KEY (group_id, user_id),
    ADD KEY user_groups_prime_user_idx (user_id);

--
-- indexes for table groups_big
--
ALTER TABLE groups_big
    ADD PRIMARY KEY (group_id),
    ADD KEY groups_big_user_idx (user_id);

--
-- indexes for table user_groups_big
--
ALTER TABLE user_groups_big
    ADD PRIMARY KEY (group_id, user_id),
    ADD KEY user_groups_big_user_idx (user_id);

-- --------------------------------------------------------

--
-- indexes for table source_types
--

ALTER TABLE source_types
    ADD PRIMARY KEY (source_type_id),
    ADD KEY source_types_type_name_idx (type_name);

-- --------------------------------------------------------

--
-- indexes for table sources
--
ALTER TABLE sources
    ADD PRIMARY KEY (source_id),
    ADD KEY sources_user_idx (user_id),
    ADD KEY sources_source_name_idx (source_name),
    ADD KEY sources_source_type_idx (source_type_id);

--
-- indexes for table user_sources
--
ALTER TABLE user_sources
    ADD PRIMARY KEY (source_id, user_id),
    ADD KEY user_sources_source_idx (source_id),
    ADD KEY user_sources_user_idx (user_id),
    ADD KEY user_sources_source_name_idx (source_name),
    ADD KEY user_sources_source_type_idx (source_type_id);

-- --------------------------------------------------------

--
-- indexes for table ref_types
--

ALTER TABLE ref_types
    ADD PRIMARY KEY (ref_type_id),
    ADD KEY ref_types_type_name_idx (type_name);

-- --------------------------------------------------------

--
-- indexes for table refs
--

ALTER TABLE refs
    ADD PRIMARY KEY (ref_id),
    ADD KEY refs_user_idx (user_id),
    ADD KEY refs_external_key_idx (external_key),
    ADD KEY refs_source_idx (source_id),
    ADD KEY refs_phrase_idx (phrase_id),
    ADD KEY refs_ref_type_idx (ref_type_id);

--
-- indexes for table user_refs
--

ALTER TABLE user_refs
    ADD PRIMARY KEY (ref_id,user_id),
    ADD KEY user_refs_ref_idx (ref_id),
    ADD KEY user_refs_user_idx (user_id),
    ADD KEY user_refs_external_key_idx (external_key),
    ADD KEY user_refs_source_idx (source_id);

-- --------------------------------------------------------

--
-- indexes for table values_standard_prime
--
ALTER TABLE values_standard_prime
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4),
    ADD KEY values_standard_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY values_standard_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY values_standard_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY values_standard_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY values_standard_prime_source_idx (source_id);

--
-- indexes for table values_standard
--
ALTER TABLE values_standard
    ADD PRIMARY KEY (group_id),
    ADD KEY values_standard_source_idx (source_id);

--
-- indexes for table values
--
ALTER TABLE `values`
    ADD PRIMARY KEY (group_id),
    ADD KEY values_source_idx (source_id),
    ADD KEY values_user_idx (user_id);

--
-- indexes for table user_values
--
ALTER TABLE user_values
    ADD PRIMARY KEY (group_id, user_id, source_id),
    ADD KEY user_values_user_idx (user_id),
    ADD KEY user_values_source_idx (source_id);

--
-- indexes for table values_prime
--
ALTER TABLE values_prime
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4),
    ADD KEY values_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY values_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY values_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY values_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY values_prime_source_idx (source_id),
    ADD KEY values_prime_user_idx (user_id);

--
-- indexes for table user_values_prime
--
ALTER TABLE user_values_prime
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, user_id, source_id),
    ADD KEY user_values_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY user_values_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY user_values_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY user_values_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY user_values_prime_user_idx (user_id),
    ADD KEY user_values_prime_source_idx (source_id);

--
-- indexes for table values_big
--
ALTER TABLE values_big
    ADD PRIMARY KEY (group_id),
    ADD KEY values_big_source_idx (source_id),
    ADD KEY values_big_user_idx (user_id);

--
-- indexes for table user_values_big
--
ALTER TABLE user_values_big
    ADD PRIMARY KEY (group_id, user_id, source_id),
    ADD KEY user_values_big_user_idx (user_id),
    ADD KEY user_values_big_source_idx (source_id);

-- --------------------------------------------------------

--
-- indexes for table values_text_standard_prime
--
ALTER TABLE values_text_standard_prime
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4),
    ADD KEY values_text_standard_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY values_text_standard_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY values_text_standard_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY values_text_standard_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY values_text_standard_prime_source_idx (source_id);

--
-- indexes for table values_text_standard
--
ALTER TABLE values_text_standard
    ADD PRIMARY KEY (group_id),
    ADD KEY values_text_standard_source_idx (source_id);
--
-- indexes for table values_text
--
ALTER TABLE values_text
    ADD PRIMARY KEY (group_id),
    ADD KEY values_text_source_idx (source_id),
    ADD KEY values_text_user_idx (user_id);

--
-- indexes for table user_values_text
--
ALTER TABLE user_values_text
    ADD PRIMARY KEY (group_id, user_id, source_id),
    ADD KEY user_values_text_user_idx (user_id),
    ADD KEY user_values_text_source_idx (source_id);

--
-- indexes for table values_text_prime
--
ALTER TABLE values_text_prime
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4),
    ADD KEY values_text_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY values_text_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY values_text_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY values_text_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY values_text_prime_source_idx (source_id),
    ADD KEY values_text_prime_user_idx (user_id);

--
-- indexes for table user_values_text_prime
--
ALTER TABLE user_values_text_prime
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, user_id, source_id),
    ADD KEY user_values_text_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY user_values_text_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY user_values_text_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY user_values_text_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY user_values_text_prime_user_idx (user_id),
    ADD KEY user_values_text_prime_source_idx (source_id);

--
-- indexes for table values_text_big
--
ALTER TABLE values_text_big
    ADD PRIMARY KEY (group_id),
    ADD KEY values_text_big_source_idx (source_id),
    ADD KEY values_text_big_user_idx (user_id);

--
-- indexes for table user_values_text_big
--
ALTER TABLE user_values_text_big
    ADD PRIMARY KEY (group_id, user_id, source_id),
    ADD KEY user_values_text_big_user_idx (user_id),
    ADD KEY user_values_text_big_source_idx (source_id);

-- --------------------------------------------------------

--
-- indexes for table values_time_standard_prime
--
ALTER TABLE values_time_standard_prime
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4),
    ADD KEY values_time_standard_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY values_time_standard_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY values_time_standard_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY values_time_standard_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY values_time_standard_prime_source_idx (source_id);

--
-- indexes for table values_time_standard
--
ALTER TABLE values_time_standard
    ADD PRIMARY KEY (group_id),
    ADD KEY values_time_standard_source_idx (source_id);

--
-- indexes for table values_time
--
ALTER TABLE values_time
    ADD PRIMARY KEY (group_id),
    ADD KEY values_time_source_idx (source_id),
    ADD KEY values_time_user_idx (user_id);

--
-- indexes for table user_values_time
--
ALTER TABLE user_values_time
    ADD PRIMARY KEY (group_id, user_id, source_id),
    ADD KEY user_values_time_user_idx (user_id),
    ADD KEY user_values_time_source_idx (source_id);

--
-- indexes for table values_time_prime
--
ALTER TABLE values_time_prime
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4),
    ADD KEY values_time_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY values_time_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY values_time_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY values_time_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY values_time_prime_source_idx (source_id),
    ADD KEY values_time_prime_user_idx (user_id);

--
-- indexes for table user_values_time_prime
--
ALTER TABLE user_values_time_prime
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, user_id, source_id),
    ADD KEY user_values_time_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY user_values_time_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY user_values_time_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY user_values_time_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY user_values_time_prime_user_idx (user_id),
    ADD KEY user_values_time_prime_source_idx (source_id);

--
-- indexes for table values_time_big
--
ALTER TABLE values_time_big
    ADD PRIMARY KEY (group_id),
    ADD KEY values_time_big_source_idx (source_id),
    ADD KEY values_time_big_user_idx (user_id);

--
-- indexes for table user_values_time_big
--
ALTER TABLE user_values_time_big
    ADD PRIMARY KEY (group_id, user_id, source_id),
    ADD KEY user_values_time_big_user_idx (user_id),
    ADD KEY user_values_time_big_source_idx (source_id);

-- --------------------------------------------------------

--
-- indexes for table values_geo_standard_prime
--
ALTER TABLE values_geo_standard_prime
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4),
    ADD KEY values_geo_standard_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY values_geo_standard_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY values_geo_standard_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY values_geo_standard_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY values_geo_standard_prime_source_idx (source_id);

--
-- indexes for table values_geo_standard
--
ALTER TABLE values_geo_standard
    ADD PRIMARY KEY (group_id),
    ADD KEY values_geo_standard_source_idx (source_id);

--
-- indexes for table values_geo
--
ALTER TABLE values_geo
    ADD PRIMARY KEY (group_id),
    ADD KEY values_geo_source_idx (source_id),
    ADD KEY values_geo_user_idx (user_id);

--
-- indexes for table user_values_geo
--
ALTER TABLE user_values_geo
    ADD PRIMARY KEY (group_id, user_id, source_id),
    ADD KEY user_values_geo_user_idx (user_id),
    ADD KEY user_values_geo_source_idx (source_id);

--
-- indexes for table values_geo_prime
--
ALTER TABLE values_geo_prime
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4),
    ADD KEY values_geo_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY values_geo_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY values_geo_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY values_geo_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY values_geo_prime_source_idx (source_id),
    ADD KEY values_geo_prime_user_idx (user_id);

--
-- indexes for table user_values_geo_prime
--
ALTER TABLE user_values_geo_prime
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, user_id, source_id),
    ADD KEY user_values_geo_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY user_values_geo_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY user_values_geo_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY user_values_geo_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY user_values_geo_prime_user_idx (user_id),
    ADD KEY user_values_geo_prime_source_idx (source_id);

--
-- indexes for table values_geo_big
--
ALTER TABLE values_geo_big
    ADD PRIMARY KEY (group_id),
    ADD KEY values_geo_big_source_idx (source_id),
    ADD KEY values_geo_big_user_idx (user_id);

--
-- indexes for table user_values_geo_big
--
ALTER TABLE user_values_geo_big
    ADD PRIMARY KEY (group_id, user_id, source_id),
    ADD KEY user_values_geo_big_user_idx (user_id),
    ADD KEY user_values_geo_big_source_idx (source_id);

-- --------------------------------------------------------

--
-- indexes for table values_time_series
--
ALTER TABLE values_time_series
    ADD PRIMARY KEY (group_id),
    ADD KEY values_time_series_value_time_series_idx (value_time_series_id),
    ADD KEY values_time_series_source_idx (source_id),
    ADD KEY values_time_series_user_idx (user_id);

--
-- indexes for table user_values_time_series
--
ALTER TABLE user_values_time_series
    ADD PRIMARY KEY (group_id, user_id, source_id),
    ADD KEY user_values_time_series_user_idx (user_id),
    ADD KEY user_values_time_series_value_time_series_idx (value_time_series_id),
    ADD KEY user_values_time_series_source_idx (source_id);

--
-- indexes for table values_time_series_prime
--
ALTER TABLE values_time_series_prime
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4),
    ADD KEY values_time_series_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY values_time_series_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY values_time_series_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY values_time_series_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY values_time_series_prime_value_time_series_idx (value_time_series_id),
    ADD KEY values_time_series_prime_source_idx (source_id),
    ADD KEY values_time_series_prime_user_idx (user_id);

--
-- indexes for table user_values_time_series_prime
--
ALTER TABLE user_values_time_series_prime
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, user_id, source_id),
    ADD KEY user_values_time_series_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY user_values_time_series_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY user_values_time_series_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY user_values_time_series_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY user_values_time_series_prime_user_idx (user_id),
    ADD KEY user_values_time_series_prime_value_time_series_idx (value_time_series_id),
    ADD KEY user_values_time_series_prime_source_idx (source_id);

--
-- indexes for table values_time_series_big
--
ALTER TABLE values_time_series_big
    ADD PRIMARY KEY (group_id),
    ADD KEY values_time_series_big_value_time_series_idx (value_time_series_id),
    ADD KEY values_time_series_big_source_idx (source_id),
    ADD KEY values_time_series_big_user_idx (user_id);

--
-- indexes for table user_values_time_series_big
--
ALTER TABLE user_values_time_series_big
    ADD PRIMARY KEY (group_id, user_id, source_id),
    ADD KEY user_values_time_series_big_user_idx (user_id),
    ADD KEY user_values_time_series_big_value_time_series_idx (value_time_series_id),
    ADD KEY user_values_time_series_big_source_idx (source_id);

-- --------------------------------------------------------

--
-- indexes for table value_ts_data
--

ALTER TABLE value_ts_data
    ADD KEY value_ts_data_value_time_series_idx (value_time_series_id);

-- --------------------------------------------------------

--
-- indexes for table element_types
--

ALTER TABLE element_types
    ADD PRIMARY KEY (element_type_id),
    ADD KEY element_types_type_name_idx (type_name);

-- --------------------------------------------------------

--
-- indexes for table elements
--

ALTER TABLE elements
    ADD PRIMARY KEY (element_id),
    ADD KEY elements_formula_idx (formula_id),
    ADD KEY elements_element_type_idx (element_type_id);

-- --------------------------------------------------------

--
-- indexes for table formula_types
--

ALTER TABLE formula_types
    ADD PRIMARY KEY (formula_type_id),
    ADD KEY formula_types_type_name_idx (type_name);

-- --------------------------------------------------------
--
-- indexes for table formulas
--

ALTER TABLE formulas
    ADD PRIMARY KEY (formula_id),
    ADD KEY formulas_user_idx (user_id),
    ADD KEY formulas_formula_name_idx (formula_name),
    ADD KEY formulas_formula_type_idx (formula_type_id),
    ADD KEY formulas_view_idx (view_id);

--
-- indexes for table user_formulas
--

ALTER TABLE user_formulas
    ADD PRIMARY KEY (formula_id,user_id),
    ADD KEY user_formulas_formula_idx (formula_id),
    ADD KEY user_formulas_user_idx (user_id),
    ADD KEY user_formulas_formula_name_idx (formula_name),
    ADD KEY user_formulas_formula_type_idx (formula_type_id),
    ADD KEY user_formulas_view_idx (view_id);

-- --------------------------------------------------------

--
-- indexes for table formula_link_types
--

ALTER TABLE formula_link_types
    ADD PRIMARY KEY (formula_link_type_id),
    ADD KEY formula_link_types_type_name_idx (type_name);

-- --------------------------------------------------------

--
-- indexes for table formula_links
--

ALTER TABLE formula_links
    ADD PRIMARY KEY (formula_link_id),
    ADD KEY formula_links_user_idx (user_id),
    ADD KEY formula_links_formula_link_type_idx (formula_link_type_id),
    ADD KEY formula_links_formula_idx (formula_id),
    ADD KEY formula_links_phrase_idx (phrase_id);

--
-- indexes for table user_formula_links
--

ALTER TABLE user_formula_links
    ADD PRIMARY KEY (formula_link_id,user_id),
    ADD KEY user_formula_links_formula_link_idx (formula_link_id),
    ADD KEY user_formula_links_user_idx (user_id),
    ADD KEY user_formula_links_formula_link_type_idx (formula_link_type_id);

-- --------------------------------------------------------

--
-- indexes for table results_standard_prime
--
ALTER TABLE results_standard_prime
    ADD PRIMARY KEY (formula_id, phrase_id_1, phrase_id_2, phrase_id_3),
    ADD KEY results_standard_prime_formula_idx (formula_id),
    ADD KEY results_standard_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY results_standard_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY results_standard_prime_phrase_id_3_idx (phrase_id_3);

--
-- indexes for table results_standard_main
--
ALTER TABLE results_standard_main
    ADD PRIMARY KEY (formula_id, phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, phrase_id_5, phrase_id_6, phrase_id_7),
    ADD KEY results_standard_main_formula_idx (formula_id),
    ADD KEY results_standard_main_phrase_id_1_idx (phrase_id_1),
    ADD KEY results_standard_main_phrase_id_2_idx (phrase_id_2),
    ADD KEY results_standard_main_phrase_id_3_idx (phrase_id_3),
    ADD KEY results_standard_main_phrase_id_4_idx (phrase_id_4),
    ADD KEY results_standard_main_phrase_id_5_idx (phrase_id_5),
    ADD KEY results_standard_main_phrase_id_6_idx (phrase_id_6),
    ADD KEY results_standard_main_phrase_id_7_idx (phrase_id_7);

--
-- indexes for table results_standard
--
ALTER TABLE results_standard
    ADD PRIMARY KEY (group_id);

--
-- indexes for table results
--
ALTER TABLE results
    ADD PRIMARY KEY (group_id),
    ADD KEY results_source_group_idx (source_group_id),
    ADD KEY results_formula_idx (formula_id),
    ADD KEY results_user_idx (user_id);

--
-- indexes for table user_results
--
ALTER TABLE user_results
    ADD PRIMARY KEY (group_id, user_id),
    ADD KEY user_results_source_group_idx (source_group_id),
    ADD KEY user_results_user_idx (user_id),
    ADD KEY user_results_formula_idx (formula_id);

--
-- indexes for table results_prime
--
ALTER TABLE results_prime
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4),
    ADD KEY results_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY results_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY results_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY results_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY results_prime_source_group_idx (source_group_id),
    ADD KEY results_prime_formula_idx (formula_id),
    ADD KEY results_prime_user_idx (user_id);

--
-- indexes for table user_results_prime
--
ALTER TABLE user_results_prime
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, user_id),
    ADD KEY user_results_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY user_results_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY user_results_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY user_results_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY user_results_prime_source_group_idx (source_group_id),
    ADD KEY user_results_prime_user_idx (user_id),
    ADD KEY user_results_prime_formula_idx (formula_id);

--
-- indexes for table results_main
--
ALTER TABLE results_main
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, phrase_id_5, phrase_id_6, phrase_id_7, phrase_id_8),
    ADD KEY results_main_phrase_id_1_idx (phrase_id_1),
    ADD KEY results_main_phrase_id_2_idx (phrase_id_2),
    ADD KEY results_main_phrase_id_3_idx (phrase_id_3),
    ADD KEY results_main_phrase_id_4_idx (phrase_id_4),
    ADD KEY results_main_phrase_id_5_idx (phrase_id_5),
    ADD KEY results_main_phrase_id_6_idx (phrase_id_6),
    ADD KEY results_main_phrase_id_7_idx (phrase_id_7),
    ADD KEY results_main_phrase_id_8_idx (phrase_id_8),
    ADD KEY results_main_source_group_idx (source_group_id),
    ADD KEY results_main_formula_idx (formula_id),
    ADD KEY results_main_user_idx (user_id);

--
-- indexes for table user_results_main
--
ALTER TABLE user_results_main
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, phrase_id_5, phrase_id_6, phrase_id_7, phrase_id_8, user_id),
    ADD KEY user_results_main_phrase_id_1_idx (phrase_id_1),
    ADD KEY user_results_main_phrase_id_2_idx (phrase_id_2),
    ADD KEY user_results_main_phrase_id_3_idx (phrase_id_3),
    ADD KEY user_results_main_phrase_id_4_idx (phrase_id_4),
    ADD KEY user_results_main_phrase_id_5_idx (phrase_id_5),
    ADD KEY user_results_main_phrase_id_6_idx (phrase_id_6),
    ADD KEY user_results_main_phrase_id_7_idx (phrase_id_7),
    ADD KEY user_results_main_phrase_id_8_idx (phrase_id_8),
    ADD KEY user_results_main_source_group_idx (source_group_id),
    ADD KEY user_results_main_user_idx (user_id),
    ADD KEY user_results_main_formula_idx (formula_id);

--
-- indexes for table results_big
--
ALTER TABLE results_big
    ADD PRIMARY KEY (group_id),
    ADD KEY results_big_source_group_idx (source_group_id),
    ADD KEY results_big_formula_idx (formula_id),
    ADD KEY results_big_user_idx (user_id);

--
-- indexes for table user_results_big
--
ALTER TABLE user_results_big
    ADD PRIMARY KEY (group_id, user_id),
    ADD KEY user_results_big_source_group_idx (source_group_id),
    ADD KEY user_results_big_user_idx (user_id),
    ADD KEY user_results_big_formula_idx (formula_id);

-- --------------------------------------------------------

--
-- indexes for table results_text_standard_prime
--
ALTER TABLE results_text_standard_prime
    ADD PRIMARY KEY (formula_id, phrase_id_1, phrase_id_2, phrase_id_3),
    ADD KEY results_text_standard_prime_formula_idx (formula_id),
    ADD KEY results_text_standard_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY results_text_standard_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY results_text_standard_prime_phrase_id_3_idx (phrase_id_3);

--
-- indexes for table results_text_standard_main
--
ALTER TABLE results_text_standard_main
    ADD PRIMARY KEY (formula_id, phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, phrase_id_5, phrase_id_6, phrase_id_7),
    ADD KEY results_text_standard_main_formula_idx (formula_id),
    ADD KEY results_text_standard_main_phrase_id_1_idx (phrase_id_1),
    ADD KEY results_text_standard_main_phrase_id_2_idx (phrase_id_2),
    ADD KEY results_text_standard_main_phrase_id_3_idx (phrase_id_3),
    ADD KEY results_text_standard_main_phrase_id_4_idx (phrase_id_4),
    ADD KEY results_text_standard_main_phrase_id_5_idx (phrase_id_5),
    ADD KEY results_text_standard_main_phrase_id_6_idx (phrase_id_6),
    ADD KEY results_text_standard_main_phrase_id_7_idx (phrase_id_7);

--
-- indexes for table results_text_standard
--
ALTER TABLE results_text_standard
    ADD PRIMARY KEY (group_id);

--
-- indexes for table results_text
--
ALTER TABLE results_text
    ADD PRIMARY KEY (group_id),
    ADD KEY results_text_source_group_idx (source_group_id),
    ADD KEY results_text_formula_idx (formula_id),
    ADD KEY results_text_user_idx (user_id);

--
-- indexes for table user_results_text
--
ALTER TABLE user_results_text
    ADD PRIMARY KEY (group_id, user_id),
    ADD KEY user_results_text_source_group_idx (source_group_id),
    ADD KEY user_results_text_user_idx (user_id),
    ADD KEY user_results_text_formula_idx (formula_id);

--
-- indexes for table results_text_prime
--
ALTER TABLE results_text_prime
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4),
    ADD KEY results_text_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY results_text_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY results_text_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY results_text_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY results_text_prime_source_group_idx (source_group_id),
    ADD KEY results_text_prime_formula_idx (formula_id),
    ADD KEY results_text_prime_user_idx (user_id);

--
-- indexes for table user_results_text_prime
--
ALTER TABLE user_results_text_prime
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, user_id),
    ADD KEY user_results_text_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY user_results_text_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY user_results_text_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY user_results_text_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY user_results_text_prime_source_group_idx (source_group_id),
    ADD KEY user_results_text_prime_user_idx (user_id),
    ADD KEY user_results_text_prime_formula_idx (formula_id);

--
-- indexes for table results_text_main
--
ALTER TABLE results_text_main
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, phrase_id_5, phrase_id_6, phrase_id_7, phrase_id_8),
    ADD KEY results_text_main_phrase_id_1_idx (phrase_id_1),
    ADD KEY results_text_main_phrase_id_2_idx (phrase_id_2),
    ADD KEY results_text_main_phrase_id_3_idx (phrase_id_3),
    ADD KEY results_text_main_phrase_id_4_idx (phrase_id_4),
    ADD KEY results_text_main_phrase_id_5_idx (phrase_id_5),
    ADD KEY results_text_main_phrase_id_6_idx (phrase_id_6),
    ADD KEY results_text_main_phrase_id_7_idx (phrase_id_7),
    ADD KEY results_text_main_phrase_id_8_idx (phrase_id_8),
    ADD KEY results_text_main_source_group_idx (source_group_id),
    ADD KEY results_text_main_formula_idx (formula_id),
    ADD KEY results_text_main_user_idx (user_id);

--
-- indexes for table user_results_text_main
--
ALTER TABLE user_results_text_main
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, phrase_id_5, phrase_id_6, phrase_id_7, phrase_id_8, user_id),
    ADD KEY user_results_text_main_phrase_id_1_idx (phrase_id_1),
    ADD KEY user_results_text_main_phrase_id_2_idx (phrase_id_2),
    ADD KEY user_results_text_main_phrase_id_3_idx (phrase_id_3),
    ADD KEY user_results_text_main_phrase_id_4_idx (phrase_id_4),
    ADD KEY user_results_text_main_phrase_id_5_idx (phrase_id_5),
    ADD KEY user_results_text_main_phrase_id_6_idx (phrase_id_6),
    ADD KEY user_results_text_main_phrase_id_7_idx (phrase_id_7),
    ADD KEY user_results_text_main_phrase_id_8_idx (phrase_id_8),
    ADD KEY user_results_text_main_source_group_idx (source_group_id),
    ADD KEY user_results_text_main_user_idx (user_id),
    ADD KEY user_results_text_main_formula_idx (formula_id);

--
-- indexes for table results_text_big
--
ALTER TABLE results_text_big
    ADD PRIMARY KEY (group_id),
    ADD KEY results_text_big_source_group_idx (source_group_id),
    ADD KEY results_text_big_formula_idx (formula_id),
    ADD KEY results_text_big_user_idx (user_id);

--
-- indexes for table user_results_text_big
--
ALTER TABLE user_results_text_big
    ADD PRIMARY KEY (group_id, user_id),
    ADD KEY user_results_text_big_source_group_idx (source_group_id),
    ADD KEY user_results_text_big_user_idx (user_id),
    ADD KEY user_results_text_big_formula_idx (formula_id);

-- --------------------------------------------------------

--
-- indexes for table results_time_standard_prime
--
ALTER TABLE results_time_standard_prime
    ADD PRIMARY KEY (formula_id, phrase_id_1, phrase_id_2, phrase_id_3),
    ADD KEY results_time_standard_prime_formula_idx (formula_id),
    ADD KEY results_time_standard_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY results_time_standard_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY results_time_standard_prime_phrase_id_3_idx (phrase_id_3);

--
-- indexes for table results_time_standard_main
--
ALTER TABLE results_time_standard_main
    ADD PRIMARY KEY (formula_id, phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, phrase_id_5, phrase_id_6, phrase_id_7),
    ADD KEY results_time_standard_main_formula_idx (formula_id),
    ADD KEY results_time_standard_main_phrase_id_1_idx (phrase_id_1),
    ADD KEY results_time_standard_main_phrase_id_2_idx (phrase_id_2),
    ADD KEY results_time_standard_main_phrase_id_3_idx (phrase_id_3),
    ADD KEY results_time_standard_main_phrase_id_4_idx (phrase_id_4),
    ADD KEY results_time_standard_main_phrase_id_5_idx (phrase_id_5),
    ADD KEY results_time_standard_main_phrase_id_6_idx (phrase_id_6),
    ADD KEY results_time_standard_main_phrase_id_7_idx (phrase_id_7);

--
-- indexes for table results_time_standard
--
ALTER TABLE results_time_standard
    ADD PRIMARY KEY (group_id);

--
-- indexes for table results_time
--
ALTER TABLE results_time
    ADD PRIMARY KEY (group_id),
    ADD KEY results_time_source_group_idx (source_group_id),
    ADD KEY results_time_formula_idx (formula_id),
    ADD KEY results_time_user_idx (user_id);

--
-- indexes for table user_results_time
--
ALTER TABLE user_results_time
    ADD PRIMARY KEY (group_id, user_id),
    ADD KEY user_results_time_source_group_idx (source_group_id),
    ADD KEY user_results_time_user_idx (user_id),
    ADD KEY user_results_time_formula_idx (formula_id);

--
-- indexes for table results_time_prime
--
ALTER TABLE results_time_prime
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4),
    ADD KEY results_time_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY results_time_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY results_time_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY results_time_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY results_time_prime_source_group_idx (source_group_id),
    ADD KEY results_time_prime_formula_idx (formula_id),
    ADD KEY results_time_prime_user_idx (user_id);

--
-- indexes for table user_results_time_prime
--
ALTER TABLE user_results_time_prime
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, user_id),
    ADD KEY user_results_time_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY user_results_time_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY user_results_time_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY user_results_time_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY user_results_time_prime_source_group_idx (source_group_id),
    ADD KEY user_results_time_prime_user_idx (user_id),
    ADD KEY user_results_time_prime_formula_idx (formula_id);

--
-- indexes for table results_time_main
--
ALTER TABLE results_time_main
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, phrase_id_5, phrase_id_6, phrase_id_7, phrase_id_8),
    ADD KEY results_time_main_phrase_id_1_idx (phrase_id_1),
    ADD KEY results_time_main_phrase_id_2_idx (phrase_id_2),
    ADD KEY results_time_main_phrase_id_3_idx (phrase_id_3),
    ADD KEY results_time_main_phrase_id_4_idx (phrase_id_4),
    ADD KEY results_time_main_phrase_id_5_idx (phrase_id_5),
    ADD KEY results_time_main_phrase_id_6_idx (phrase_id_6),
    ADD KEY results_time_main_phrase_id_7_idx (phrase_id_7),
    ADD KEY results_time_main_phrase_id_8_idx (phrase_id_8),
    ADD KEY results_time_main_source_group_idx (source_group_id),
    ADD KEY results_time_main_formula_idx (formula_id),
    ADD KEY results_time_main_user_idx (user_id);

--
-- indexes for table user_results_time_main
--
ALTER TABLE user_results_time_main
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, phrase_id_5, phrase_id_6, phrase_id_7, phrase_id_8, user_id),
    ADD KEY user_results_time_main_phrase_id_1_idx (phrase_id_1),
    ADD KEY user_results_time_main_phrase_id_2_idx (phrase_id_2),
    ADD KEY user_results_time_main_phrase_id_3_idx (phrase_id_3),
    ADD KEY user_results_time_main_phrase_id_4_idx (phrase_id_4),
    ADD KEY user_results_time_main_phrase_id_5_idx (phrase_id_5),
    ADD KEY user_results_time_main_phrase_id_6_idx (phrase_id_6),
    ADD KEY user_results_time_main_phrase_id_7_idx (phrase_id_7),
    ADD KEY user_results_time_main_phrase_id_8_idx (phrase_id_8),
    ADD KEY user_results_time_main_source_group_idx (source_group_id),
    ADD KEY user_results_time_main_user_idx (user_id),
    ADD KEY user_results_time_main_formula_idx (formula_id);

--
-- indexes for table results_time_big
--
ALTER TABLE results_time_big
    ADD PRIMARY KEY (group_id),
    ADD KEY results_time_big_source_group_idx (source_group_id),
    ADD KEY results_time_big_formula_idx (formula_id),
    ADD KEY results_time_big_user_idx (user_id);

--
-- indexes for table user_results_time_big
--
ALTER TABLE user_results_time_big
    ADD PRIMARY KEY (group_id, user_id),
    ADD KEY user_results_time_big_source_group_idx (source_group_id),
    ADD KEY user_results_time_big_user_idx (user_id),
    ADD KEY user_results_time_big_formula_idx (formula_id);

-- --------------------------------------------------------

--
-- indexes for table results_geo_standard_prime
--
ALTER TABLE results_geo_standard_prime
    ADD PRIMARY KEY (formula_id, phrase_id_1, phrase_id_2, phrase_id_3),
    ADD KEY results_geo_standard_prime_formula_idx (formula_id),
    ADD KEY results_geo_standard_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY results_geo_standard_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY results_geo_standard_prime_phrase_id_3_idx (phrase_id_3);

--
-- indexes for table results_geo_standard_main
--
ALTER TABLE results_geo_standard_main
    ADD PRIMARY KEY (formula_id, phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, phrase_id_5, phrase_id_6, phrase_id_7),
    ADD KEY results_geo_standard_main_formula_idx (formula_id),
    ADD KEY results_geo_standard_main_phrase_id_1_idx (phrase_id_1),
    ADD KEY results_geo_standard_main_phrase_id_2_idx (phrase_id_2),
    ADD KEY results_geo_standard_main_phrase_id_3_idx (phrase_id_3),
    ADD KEY results_geo_standard_main_phrase_id_4_idx (phrase_id_4),
    ADD KEY results_geo_standard_main_phrase_id_5_idx (phrase_id_5),
    ADD KEY results_geo_standard_main_phrase_id_6_idx (phrase_id_6),
    ADD KEY results_geo_standard_main_phrase_id_7_idx (phrase_id_7);

--
-- indexes for table results_geo_standard
--
ALTER TABLE results_geo_standard
    ADD PRIMARY KEY (group_id);

--
-- indexes for table results_geo
--
ALTER TABLE results_geo
    ADD PRIMARY KEY (group_id),
    ADD KEY results_geo_source_group_idx (source_group_id),
    ADD KEY results_geo_formula_idx (formula_id),
    ADD KEY results_geo_user_idx (user_id);

--
-- indexes for table user_results_geo
--
ALTER TABLE user_results_geo
    ADD PRIMARY KEY (group_id, user_id),
    ADD KEY user_results_geo_source_group_idx (source_group_id),
    ADD KEY user_results_geo_user_idx (user_id),
    ADD KEY user_results_geo_formula_idx (formula_id);

--
-- indexes for table results_geo_prime
--
ALTER TABLE results_geo_prime
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4),
    ADD KEY results_geo_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY results_geo_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY results_geo_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY results_geo_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY results_geo_prime_source_group_idx (source_group_id),
    ADD KEY results_geo_prime_formula_idx (formula_id),
    ADD KEY results_geo_prime_user_idx (user_id);

--
-- indexes for table user_results_geo_prime
--
ALTER TABLE user_results_geo_prime
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, user_id),
    ADD KEY user_results_geo_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY user_results_geo_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY user_results_geo_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY user_results_geo_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY user_results_geo_prime_source_group_idx (source_group_id),
    ADD KEY user_results_geo_prime_user_idx (user_id),
    ADD KEY user_results_geo_prime_formula_idx (formula_id);

--
-- indexes for table results_geo_main
--
ALTER TABLE results_geo_main
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, phrase_id_5, phrase_id_6, phrase_id_7, phrase_id_8),
    ADD KEY results_geo_main_phrase_id_1_idx (phrase_id_1),
    ADD KEY results_geo_main_phrase_id_2_idx (phrase_id_2),
    ADD KEY results_geo_main_phrase_id_3_idx (phrase_id_3),
    ADD KEY results_geo_main_phrase_id_4_idx (phrase_id_4),
    ADD KEY results_geo_main_phrase_id_5_idx (phrase_id_5),
    ADD KEY results_geo_main_phrase_id_6_idx (phrase_id_6),
    ADD KEY results_geo_main_phrase_id_7_idx (phrase_id_7),
    ADD KEY results_geo_main_phrase_id_8_idx (phrase_id_8),
    ADD KEY results_geo_main_source_group_idx (source_group_id),
    ADD KEY results_geo_main_formula_idx (formula_id),
    ADD KEY results_geo_main_user_idx (user_id);

--
-- indexes for table user_results_geo_main
--
ALTER TABLE user_results_geo_main
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, phrase_id_5, phrase_id_6, phrase_id_7, phrase_id_8, user_id),
    ADD KEY user_results_geo_main_phrase_id_1_idx (phrase_id_1),
    ADD KEY user_results_geo_main_phrase_id_2_idx (phrase_id_2),
    ADD KEY user_results_geo_main_phrase_id_3_idx (phrase_id_3),
    ADD KEY user_results_geo_main_phrase_id_4_idx (phrase_id_4),
    ADD KEY user_results_geo_main_phrase_id_5_idx (phrase_id_5),
    ADD KEY user_results_geo_main_phrase_id_6_idx (phrase_id_6),
    ADD KEY user_results_geo_main_phrase_id_7_idx (phrase_id_7),
    ADD KEY user_results_geo_main_phrase_id_8_idx (phrase_id_8),
    ADD KEY user_results_geo_main_source_group_idx (source_group_id),
    ADD KEY user_results_geo_main_user_idx (user_id),
    ADD KEY user_results_geo_main_formula_idx (formula_id);

--
-- indexes for table results_geo_big
--
ALTER TABLE results_geo_big
    ADD PRIMARY KEY (group_id),
    ADD KEY results_geo_big_source_group_idx (source_group_id),
    ADD KEY results_geo_big_formula_idx (formula_id),
    ADD KEY results_geo_big_user_idx (user_id);

--
-- indexes for table user_results_geo_big
--
ALTER TABLE user_results_geo_big
    ADD PRIMARY KEY (group_id, user_id),
    ADD KEY user_results_geo_big_source_group_idx (source_group_id),
    ADD KEY user_results_geo_big_user_idx (user_id),
    ADD KEY user_results_geo_big_formula_idx (formula_id);

-- --------------------------------------------------------

--
-- indexes for table results_time_series
--
ALTER TABLE results_time_series
    ADD PRIMARY KEY (group_id),
    ADD KEY results_time_series_source_group_idx (source_group_id),
    ADD KEY results_time_series_result_time_series_idx (result_time_series_id),
    ADD KEY results_time_series_formula_idx (formula_id),
    ADD KEY results_time_series_user_idx (user_id);

--
-- indexes for table user_results_time_series
--
ALTER TABLE user_results_time_series
    ADD PRIMARY KEY (group_id, user_id),
    ADD KEY user_results_time_series_source_group_idx (source_group_id),
    ADD KEY user_results_time_series_user_idx (user_id),
    ADD KEY user_results_time_series_result_time_series_idx (result_time_series_id),
    ADD KEY user_results_time_series_formula_idx (formula_id);

--
-- indexes for table results_time_series_prime
--
ALTER TABLE results_time_series_prime
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4),
    ADD KEY results_time_series_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY results_time_series_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY results_time_series_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY results_time_series_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY results_time_series_prime_source_group_idx (source_group_id),
    ADD KEY results_time_series_prime_result_time_series_idx (result_time_series_id),
    ADD KEY results_time_series_prime_formula_idx (formula_id),
    ADD KEY results_time_series_prime_user_idx (user_id);

--
-- indexes for table user_results_time_series_prime
--
ALTER TABLE user_results_time_series_prime
    ADD PRIMARY KEY (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, user_id),
    ADD KEY user_results_time_series_prime_phrase_id_1_idx (phrase_id_1),
    ADD KEY user_results_time_series_prime_phrase_id_2_idx (phrase_id_2),
    ADD KEY user_results_time_series_prime_phrase_id_3_idx (phrase_id_3),
    ADD KEY user_results_time_series_prime_phrase_id_4_idx (phrase_id_4),
    ADD KEY user_results_time_series_prime_source_group_idx (source_group_id),
    ADD KEY user_results_time_series_prime_user_idx (user_id),
    ADD KEY user_results_time_series_prime_result_time_series_idx (result_time_series_id),
    ADD KEY user_results_time_series_prime_formula_idx (formula_id);

--
-- indexes for table results_time_series_big
--
ALTER TABLE results_time_series_big
    ADD PRIMARY KEY (group_id),
    ADD KEY results_time_series_big_source_group_idx (source_group_id),
    ADD KEY results_time_series_big_result_time_series_idx (result_time_series_id),
    ADD KEY results_time_series_big_formula_idx (formula_id),
    ADD KEY results_time_series_big_user_idx (user_id);

--
-- indexes for table user_results_time_series_big
--
ALTER TABLE user_results_time_series_big
    ADD PRIMARY KEY (group_id, user_id),
    ADD KEY user_results_time_series_big_source_group_idx (source_group_id),
    ADD KEY user_results_time_series_big_user_idx (user_id),
    ADD KEY user_results_time_series_big_result_time_series_idx (result_time_series_id),
    ADD KEY user_results_time_series_big_formula_idx (formula_id);

-- --------------------------------------------------------

--
-- indexes for table view_types
--

ALTER TABLE view_types
    ADD PRIMARY KEY (view_type_id),
    ADD KEY view_types_type_name_idx (type_name);

-- --------------------------------------------------------

--
-- indexes for table view_styles
--

ALTER TABLE view_styles
    ADD PRIMARY KEY (view_style_id),
    ADD KEY view_styles_view_style_name_idx (view_style_name);

-- --------------------------------------------------------

--
-- indexes for table views
--

ALTER TABLE views
    ADD PRIMARY KEY (view_id),
    ADD KEY views_user_idx (user_id),
    ADD KEY views_view_name_idx (view_name),
    ADD KEY views_view_type_idx (view_type_id),
    ADD KEY views_view_style_idx (view_style_id);

--
-- indexes for table user_views
--

ALTER TABLE user_views
    ADD PRIMARY KEY (view_id,user_id,language_id),
    ADD KEY user_views_view_idx (view_id),
    ADD KEY user_views_user_idx (user_id),
    ADD KEY user_views_language_idx (language_id),
    ADD KEY user_views_view_name_idx (view_name),
    ADD KEY user_views_view_type_idx (view_type_id),
    ADD KEY user_views_view_style_idx (view_style_id);



-- --------------------------------------------------------

--
-- indexes for table view_link_types
--

ALTER TABLE view_link_types
    ADD PRIMARY KEY (view_link_type_id),
    ADD KEY view_link_types_type_name_idx (type_name);

-- --------------------------------------------------------

--
-- indexes for table term_views
--

ALTER TABLE term_views
    ADD PRIMARY KEY (term_view_id),
    ADD KEY term_views_term_idx (term_id),
    ADD KEY term_views_view_idx (view_id),
    ADD KEY term_views_view_link_type_idx (view_link_type_id),
    ADD KEY term_views_user_idx (user_id);

--
-- indexes for table user_term_views
--

ALTER TABLE user_term_views
    ADD PRIMARY KEY (term_view_id,user_id),
    ADD KEY user_term_views_term_view_idx (term_view_id),
    ADD KEY user_term_views_user_idx (user_id),
    ADD KEY user_term_views_view_link_type_idx (view_link_type_id);

-- --------------------------------------------------------

--
-- indexes for table component_link_types
--

ALTER TABLE component_link_types
    ADD PRIMARY KEY (component_link_type_id),
    ADD KEY component_link_types_type_name_idx (type_name);

-- --------------------------------------------------------

--
-- indexes for table position_types
--

ALTER TABLE position_types
    ADD PRIMARY KEY (position_type_id),
    ADD KEY position_types_type_name_idx (type_name);

-- --------------------------------------------------------

--
-- indexes for table component_types
--

ALTER TABLE component_types
    ADD PRIMARY KEY (component_type_id),
    ADD KEY component_types_type_name_idx (type_name);

-- --------------------------------------------------------

--
-- indexes for table components
--

ALTER TABLE components
    ADD PRIMARY KEY (component_id),
    ADD KEY components_user_idx (user_id),
    ADD KEY components_component_name_idx (component_name),
    ADD KEY components_component_type_idx (component_type_id),
    ADD KEY components_view_style_idx (view_style_id),
    ADD KEY components_word_id_row_idx (word_id_row),
    ADD KEY components_formula_idx (formula_id),
    ADD KEY components_word_id_col_idx (word_id_col),
    ADD KEY components_word_id_col2_idx (word_id_col2),
    ADD KEY components_linked_component_idx (linked_component_id),
    ADD KEY components_component_link_type_idx (component_link_type_id),
    ADD KEY components_link_type_idx (link_type_id);

--
-- indexes for table user_components
--

ALTER TABLE user_components
    ADD PRIMARY KEY (component_id,user_id),
    ADD KEY user_components_component_idx (component_id),
    ADD KEY user_components_user_idx (user_id),
    ADD KEY user_components_component_name_idx (component_name),
    ADD KEY user_components_component_type_idx (component_type_id),
    ADD KEY user_components_view_style_idx (view_style_id),
    ADD KEY user_components_word_id_row_idx (word_id_row),
    ADD KEY user_components_formula_idx (formula_id),
    ADD KEY user_components_word_id_col_idx (word_id_col),
    ADD KEY user_components_word_id_col2_idx (word_id_col2),
    ADD KEY user_components_linked_component_idx (linked_component_id),
    ADD KEY user_components_component_link_type_idx (component_link_type_id),
    ADD KEY user_components_link_type_idx (link_type_id);

-- --------------------------------------------------------

--
-- indexes for table component_links
--

ALTER TABLE component_links
    ADD PRIMARY KEY (component_link_id),
    ADD KEY component_links_view_idx (view_id),
    ADD KEY component_links_component_idx (component_id),
    ADD KEY component_links_user_idx (user_id),
    ADD KEY component_links_component_link_type_idx (component_link_type_id),
    ADD KEY component_links_position_type_idx (position_type_id),
    ADD KEY component_links_view_style_idx (view_style_id);

--
-- indexes for table user_component_links
--

ALTER TABLE user_component_links
    ADD PRIMARY KEY (component_link_id,user_id),
    ADD KEY user_component_links_component_link_idx (component_link_id),
    ADD KEY user_component_links_user_idx (user_id),
    ADD KEY user_component_links_component_link_type_idx (component_link_type_id),
    ADD KEY user_component_links_position_type_idx (position_type_id),
    ADD KEY user_component_links_view_style_idx (view_style_id);


-- --------------------------------------------------------
--
-- foreign key constraints and auto_increment for tables
--
-- --------------------------------------------------------

--
-- constraints for table system_times
--

ALTER TABLE system_times
    ADD CONSTRAINT system_times_system_time_type_fk FOREIGN KEY (system_time_type_id) REFERENCES system_time_types (system_time_type_id);

--
-- constraints for table sys_log
--

ALTER TABLE sys_log
    ADD CONSTRAINT sys_log_sys_log_function_fk FOREIGN KEY (sys_log_function_id) REFERENCES sys_log_functions (sys_log_function_id),
    ADD CONSTRAINT sys_log_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT sys_log_user2_fk FOREIGN KEY (solver_id) REFERENCES users (user_id),
    ADD CONSTRAINT sys_log_sys_log_status_fk FOREIGN KEY (sys_log_status_id) REFERENCES sys_log_status (sys_log_status_id);

--
-- constraints for table job_times
--

ALTER TABLE job_times
    ADD CONSTRAINT job_times_job_type_fk FOREIGN KEY (job_type_id) REFERENCES job_types (job_type_id),
    ADD CONSTRAINT job_times_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table jobs
--

ALTER TABLE jobs
    ADD CONSTRAINT jobs_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT jobs_job_type_fk FOREIGN KEY (job_type_id) REFERENCES job_types (job_type_id),
    ADD CONSTRAINT jobs_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT jobs_ref_fk FOREIGN KEY (ref_id) REFERENCES refs (ref_id);

--
-- constraints for table users
--

ALTER TABLE users
    ADD CONSTRAINT users_user_profile_fk FOREIGN KEY (user_profile_id) REFERENCES user_profiles (user_profile_id),
    ADD CONSTRAINT users_user_type_fk FOREIGN KEY (user_type_id) REFERENCES user_types (user_type_id),
    ADD CONSTRAINT users_triple_fk FOREIGN KEY (name_triple_id) REFERENCES triples (triple_id),
    ADD CONSTRAINT users_triple2_fk FOREIGN KEY (geo_triple_id) REFERENCES triples (triple_id),
    ADD CONSTRAINT users_view_fk FOREIGN KEY (view_id) REFERENCES views (view_id),
    ADD CONSTRAINT users_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table change_fields
--

ALTER TABLE change_fields
    ADD CONSTRAINT change_fields_code_id_uk UNIQUE (code_id),
    ADD CONSTRAINT change_fields_change_table_fk FOREIGN KEY (table_id) REFERENCES change_tables (change_table_id);

-- --------------------------------------------------------

--
-- constraints for table changes
--

ALTER TABLE changes
    ADD CONSTRAINT changes_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT changes_change_action_fk FOREIGN KEY (change_action_id) REFERENCES change_actions (change_action_id),
    ADD CONSTRAINT changes_change_field_fk FOREIGN KEY (change_field_id) REFERENCES change_fields (change_field_id);

-- --------------------------------------------------------

--
-- constraints for table changes_norm
--

ALTER TABLE changes_norm
    ADD CONSTRAINT changes_norm_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT changes_norm_change_action_fk FOREIGN KEY (change_action_id) REFERENCES change_actions (change_action_id),
    ADD CONSTRAINT changes_norm_change_field_fk FOREIGN KEY (change_field_id) REFERENCES change_fields (change_field_id);

-- --------------------------------------------------------

--
-- constraints for table changes_big
--

ALTER TABLE changes_big
    ADD CONSTRAINT changes_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT changes_big_change_action_fk FOREIGN KEY (change_action_id) REFERENCES change_actions (change_action_id),
    ADD CONSTRAINT changes_big_change_field_fk FOREIGN KEY (change_field_id) REFERENCES change_fields (change_field_id);

-- --------------------------------------------------------

--
-- constraints for table change_values_norm
--

ALTER TABLE change_values_norm
    ADD CONSTRAINT change_values_norm_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT change_values_norm_change_action_fk FOREIGN KEY (change_action_id) REFERENCES change_actions (change_action_id),
    ADD CONSTRAINT change_values_norm_change_field_fk FOREIGN KEY (change_field_id) REFERENCES change_fields (change_field_id);

-- --------------------------------------------------------

--
-- constraints for table change_values_time_norm
--

ALTER TABLE change_values_time_norm
    ADD CONSTRAINT change_values_time_norm_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT change_values_time_norm_change_action_fk FOREIGN KEY (change_action_id) REFERENCES change_actions (change_action_id),
    ADD CONSTRAINT change_values_time_norm_change_field_fk FOREIGN KEY (change_field_id) REFERENCES change_fields (change_field_id);

-- --------------------------------------------------------

--
-- constraints for table change_values_text_norm
--

ALTER TABLE change_values_text_norm
    ADD CONSTRAINT change_values_text_norm_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT change_values_text_norm_change_action_fk FOREIGN KEY (change_action_id) REFERENCES change_actions (change_action_id),
    ADD CONSTRAINT change_values_text_norm_change_field_fk FOREIGN KEY (change_field_id) REFERENCES change_fields (change_field_id);

-- --------------------------------------------------------

--
-- constraints for table change_values_geo_norm
--

ALTER TABLE change_values_geo_norm
    ADD CONSTRAINT change_values_geo_norm_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT change_values_geo_norm_change_action_fk FOREIGN KEY (change_action_id) REFERENCES change_actions (change_action_id),
    ADD CONSTRAINT change_values_geo_norm_change_field_fk FOREIGN KEY (change_field_id) REFERENCES change_fields (change_field_id);

-- --------------------------------------------------------

--
-- constraints for table change_values_prime
--

ALTER TABLE change_values_prime
    ADD CONSTRAINT change_values_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT change_values_prime_change_action_fk FOREIGN KEY (change_action_id) REFERENCES change_actions (change_action_id),
    ADD CONSTRAINT change_values_prime_change_field_fk FOREIGN KEY (change_field_id) REFERENCES change_fields (change_field_id);

-- --------------------------------------------------------

--
-- constraints for table change_values_time_prime
--

ALTER TABLE change_values_time_prime
    ADD CONSTRAINT change_values_time_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT change_values_time_prime_change_action_fk FOREIGN KEY (change_action_id) REFERENCES change_actions (change_action_id),
    ADD CONSTRAINT change_values_time_prime_change_field_fk FOREIGN KEY (change_field_id) REFERENCES change_fields (change_field_id);

-- --------------------------------------------------------

--
-- constraints for table change_values_text_prime
--

ALTER TABLE change_values_text_prime
    ADD CONSTRAINT change_values_text_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT change_values_text_prime_change_action_fk FOREIGN KEY (change_action_id) REFERENCES change_actions (change_action_id),
    ADD CONSTRAINT change_values_text_prime_change_field_fk FOREIGN KEY (change_field_id) REFERENCES change_fields (change_field_id);

-- --------------------------------------------------------

--
-- constraints for table change_values_geo_prime
--

ALTER TABLE change_values_geo_prime
    ADD CONSTRAINT change_values_geo_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT change_values_geo_prime_change_action_fk FOREIGN KEY (change_action_id) REFERENCES change_actions (change_action_id),
    ADD CONSTRAINT change_values_geo_prime_change_field_fk FOREIGN KEY (change_field_id) REFERENCES change_fields (change_field_id);

-- --------------------------------------------------------

--
-- constraints for table change_values_big
--

ALTER TABLE change_values_big
    ADD CONSTRAINT change_values_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT change_values_big_change_action_fk FOREIGN KEY (change_action_id) REFERENCES change_actions (change_action_id),
    ADD CONSTRAINT change_values_big_change_field_fk FOREIGN KEY (change_field_id) REFERENCES change_fields (change_field_id);

-- --------------------------------------------------------

--
-- constraints for table change_values_time_big
--

ALTER TABLE change_values_time_big
    ADD CONSTRAINT change_values_time_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT change_values_time_big_change_action_fk FOREIGN KEY (change_action_id) REFERENCES change_actions (change_action_id),
    ADD CONSTRAINT change_values_time_big_change_field_fk FOREIGN KEY (change_field_id) REFERENCES change_fields (change_field_id);

-- --------------------------------------------------------

--
-- constraints for table change_values_text_big
--

ALTER TABLE change_values_text_big
    ADD CONSTRAINT change_values_text_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT change_values_text_big_change_action_fk FOREIGN KEY (change_action_id) REFERENCES change_actions (change_action_id),
    ADD CONSTRAINT change_values_text_big_change_field_fk FOREIGN KEY (change_field_id) REFERENCES change_fields (change_field_id);

-- --------------------------------------------------------

--
-- constraints for table change_values_geo_big
--

ALTER TABLE change_values_geo_big
    ADD CONSTRAINT change_values_geo_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT change_values_geo_big_change_action_fk FOREIGN KEY (change_action_id) REFERENCES change_actions (change_action_id),
    ADD CONSTRAINT change_values_geo_big_change_field_fk FOREIGN KEY (change_field_id) REFERENCES change_fields (change_field_id);

-- --------------------------------------------------------

--
-- constraints for table change_links
--

ALTER TABLE change_links
    ADD CONSTRAINT change_links_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT change_links_change_action_fk FOREIGN KEY (change_action_id) REFERENCES change_actions (change_action_id),
    ADD CONSTRAINT change_links_change_table_fk FOREIGN KEY (change_table_id) REFERENCES change_tables (change_table_id);

--
-- constraints for table pods
--

ALTER TABLE pods
    ADD CONSTRAINT pods_type_name_uk UNIQUE (type_name),
    ADD CONSTRAINT pods_code_id_uk UNIQUE (code_id),
    ADD CONSTRAINT pods_pod_type_fk FOREIGN KEY (pod_type_id) REFERENCES pod_types (pod_type_id),
    ADD CONSTRAINT pods_pod_status_fk FOREIGN KEY (pod_status_id) REFERENCES pod_status (pod_status_id),
    ADD CONSTRAINT pods_triple_fk FOREIGN KEY (param_triple_id) REFERENCES triples (triple_id);

--
-- constraints for table language_forms
--

ALTER TABLE language_forms
    ADD CONSTRAINT language_forms_language_form_name_uk UNIQUE (language_form_name),
    ADD CONSTRAINT language_forms_language_fk FOREIGN KEY (language_id) REFERENCES languages (language_id);

-- --------------------------------------------------------

--
-- constraints for table words
--
ALTER TABLE words
    ADD CONSTRAINT words_word_name_uk UNIQUE (word_name),
    ADD CONSTRAINT words_code_id_uk UNIQUE (code_id),
    ADD CONSTRAINT words_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT words_phrase_type_fk FOREIGN KEY (phrase_type_id) REFERENCES phrase_types (phrase_type_id),
    ADD CONSTRAINT words_view_fk FOREIGN KEY (view_id) REFERENCES views (view_id);

--
-- constraints for table user_words
--
ALTER TABLE user_words
    ADD CONSTRAINT user_words_word_fk FOREIGN KEY (word_id) REFERENCES words (word_id),
    ADD CONSTRAINT user_words_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_words_language_fk FOREIGN KEY (language_id) REFERENCES languages (language_id),
    ADD CONSTRAINT user_words_phrase_type_fk FOREIGN KEY (phrase_type_id) REFERENCES phrase_types (phrase_type_id),
    ADD CONSTRAINT user_words_view_fk FOREIGN KEY (view_id) REFERENCES views (view_id);

-- --------------------------------------------------------

--
-- constraints for table triples
--
ALTER TABLE triples
    ADD CONSTRAINT triples_code_id_uk UNIQUE (code_id),
    ADD CONSTRAINT triples_verb_fk        FOREIGN KEY (verb_id)        REFERENCES verbs (verb_id),
    ADD CONSTRAINT triples_user_fk        FOREIGN KEY (user_id)        REFERENCES users (user_id),
    ADD CONSTRAINT triples_phrase_type_fk FOREIGN KEY (phrase_type_id) REFERENCES phrase_types (phrase_type_id),
    ADD CONSTRAINT triples_view_fk        FOREIGN KEY (view_id)        REFERENCES views (view_id);

--
-- constraints for table user_triples
--
ALTER TABLE user_triples
    ADD CONSTRAINT user_triples_triple_fk      FOREIGN KEY (triple_id)      REFERENCES triples (triple_id),
    ADD CONSTRAINT user_triples_user_fk        FOREIGN KEY (user_id)        REFERENCES users (user_id),
    ADD CONSTRAINT user_triples_language_fk    FOREIGN KEY (language_id)    REFERENCES languages (language_id),
    ADD CONSTRAINT user_triples_phrase_type_fk FOREIGN KEY (phrase_type_id) REFERENCES phrase_types (phrase_type_id),
    ADD CONSTRAINT user_triples_view_fk        FOREIGN KEY (view_id)        REFERENCES views (view_id);

--
-- constraints for table phrase_tables
--

ALTER TABLE phrase_tables
    ADD CONSTRAINT phrase_tables_pod_fk FOREIGN KEY (pod_id) REFERENCES pods (pod_id),
    ADD CONSTRAINT phrase_tables_phrase_table_status_fk FOREIGN KEY (phrase_table_status_id) REFERENCES phrase_table_status (phrase_table_status_id);

-- --------------------------------------------------------

--
-- constraints for table groups
--
ALTER TABLE `groups`
    ADD CONSTRAINT groups_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_groups
--
ALTER TABLE user_groups
    ADD CONSTRAINT user_groups_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table groups_prime
--
ALTER TABLE groups_prime
    ADD CONSTRAINT groups_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_groups_prime
--
ALTER TABLE user_groups_prime
    ADD CONSTRAINT user_groups_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table groups_big
--
ALTER TABLE groups_big
    ADD CONSTRAINT groups_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_groups_big
--
ALTER TABLE user_groups_big
    ADD CONSTRAINT user_groups_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

-- --------------------------------------------------------

--
-- constraints for table sources
--
ALTER TABLE sources
    ADD CONSTRAINT sources_source_name_uk UNIQUE (source_name),
    ADD CONSTRAINT sources_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT sources_source_type_fk FOREIGN KEY (source_type_id) REFERENCES source_types (source_type_id);

--
-- constraints for table user_sources
--
ALTER TABLE user_sources
    ADD CONSTRAINT user_sources_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT user_sources_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_sources_source_type_fk FOREIGN KEY (source_type_id) REFERENCES source_types (source_type_id);

-- --------------------------------------------------------

--
-- constraints for table refs
--

ALTER TABLE refs
    ADD CONSTRAINT refs_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT refs_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT refs_ref_type_fk FOREIGN KEY (ref_type_id) REFERENCES ref_types (ref_type_id);

--
-- constraints for table user_refs
--

ALTER TABLE user_refs
    ADD CONSTRAINT user_refs_ref_fk FOREIGN KEY (ref_id) REFERENCES refs (ref_id),
    ADD CONSTRAINT user_refs_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_refs_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

-- --------------------------------------------------------

--
-- constraints for table values_standard_prime
--
ALTER TABLE values_standard_prime

    ADD CONSTRAINT values_standard_prime_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table values_standard
--
ALTER TABLE values_standard
    ADD CONSTRAINT values_standard_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table values
--
ALTER TABLE `values`
    ADD CONSTRAINT values_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT values_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_values
--
ALTER TABLE user_values
    ADD CONSTRAINT user_values_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_values_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table values_prime
--
ALTER TABLE values_prime
    ADD CONSTRAINT values_prime_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT values_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_values_prime
--
ALTER TABLE user_values_prime
    ADD CONSTRAINT user_values_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_values_prime_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table values_big
--
ALTER TABLE values_big
    ADD CONSTRAINT values_big_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT values_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_values_big
--
ALTER TABLE user_values_big
    ADD CONSTRAINT user_values_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_values_big_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

-- --------------------------------------------------------

--
-- constraints for table values_text_standard_prime
--
ALTER TABLE values_text_standard_prime

    ADD CONSTRAINT values_text_standard_prime_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table values_text_standard
--
ALTER TABLE values_text_standard
    ADD CONSTRAINT values_text_standard_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table values_text
--
ALTER TABLE values_text
    ADD CONSTRAINT values_text_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT values_text_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_values_text
--
ALTER TABLE user_values_text
    ADD CONSTRAINT user_values_text_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_values_text_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table values_text_prime
--
ALTER TABLE values_text_prime
    ADD CONSTRAINT values_text_prime_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT values_text_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_values_text_prime
--
ALTER TABLE user_values_text_prime
    ADD CONSTRAINT user_values_text_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_values_text_prime_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table values_text_big
--
ALTER TABLE values_text_big
    ADD CONSTRAINT values_text_big_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT values_text_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_values_text_big
--
ALTER TABLE user_values_text_big
    ADD CONSTRAINT user_values_text_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_values_text_big_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

-- --------------------------------------------------------

--
-- constraints for table values_time_standard_prime
--
ALTER TABLE values_time_standard_prime

    ADD CONSTRAINT values_time_standard_prime_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table values_time_standard
--
ALTER TABLE values_time_standard
    ADD CONSTRAINT values_time_standard_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table values_time
--
ALTER TABLE values_time
    ADD CONSTRAINT values_time_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT values_time_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_values_time
--
ALTER TABLE user_values_time
    ADD CONSTRAINT user_values_time_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_values_time_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table values_time_prime
--
ALTER TABLE values_time_prime
    ADD CONSTRAINT values_time_prime_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT values_time_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_values_time_prime
--
ALTER TABLE user_values_time_prime
    ADD CONSTRAINT user_values_time_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_values_time_prime_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table values_time_big
--
ALTER TABLE values_time_big
    ADD CONSTRAINT values_time_big_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT values_time_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_values_time_big
--
ALTER TABLE user_values_time_big
    ADD CONSTRAINT user_values_time_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_values_time_big_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

-- --------------------------------------------------------

--
-- constraints for table values_geo_standard_prime
--
ALTER TABLE values_geo_standard_prime

    ADD CONSTRAINT values_geo_standard_prime_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table values_geo_standard
--
ALTER TABLE values_geo_standard
    ADD CONSTRAINT values_geo_standard_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table values_geo
--
ALTER TABLE values_geo
    ADD CONSTRAINT values_geo_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT values_geo_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_values_geo
--
ALTER TABLE user_values_geo
    ADD CONSTRAINT user_values_geo_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_values_geo_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table values_geo_prime
--
ALTER TABLE values_geo_prime
    ADD CONSTRAINT values_geo_prime_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT values_geo_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_values_geo_prime
--
ALTER TABLE user_values_geo_prime
    ADD CONSTRAINT user_values_geo_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_values_geo_prime_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table values_geo_big
--
ALTER TABLE values_geo_big
    ADD CONSTRAINT values_geo_big_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT values_geo_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_values_geo_big
--
ALTER TABLE user_values_geo_big
    ADD CONSTRAINT user_values_geo_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_values_geo_big_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

-- --------------------------------------------------------

--
-- constraints for table values_time_series
--
ALTER TABLE values_time_series
    ADD CONSTRAINT values_time_series_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT values_time_series_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_values_time_series
--
ALTER TABLE user_values_time_series
    ADD CONSTRAINT user_values_time_series_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_values_time_series_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table values_time_series_prime
--
ALTER TABLE values_time_series_prime
    ADD CONSTRAINT values_time_series_prime_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT values_time_series_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_values_time_series_prime
--
ALTER TABLE user_values_time_series_prime
    ADD CONSTRAINT user_values_time_series_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_values_time_series_prime_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table values_time_series_big
--
ALTER TABLE values_time_series_big
    ADD CONSTRAINT values_time_series_big_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT values_time_series_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_values_time_series_big
--
ALTER TABLE user_values_time_series_big
    ADD CONSTRAINT user_values_time_series_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_values_time_series_big_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table elements
--

ALTER TABLE elements
    ADD CONSTRAINT elements_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id),
    ADD CONSTRAINT elements_element_type_fk FOREIGN KEY (element_type_id) REFERENCES element_types (element_type_id),
    ADD CONSTRAINT elements_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

-- --------------------------------------------------------

--
-- constraints for table formulas
--
ALTER TABLE formulas
    ADD CONSTRAINT formulas_formula_name_uk UNIQUE (formula_name),
    ADD CONSTRAINT formulas_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT formulas_formula_type_fk FOREIGN KEY (formula_type_id) REFERENCES formula_types (formula_type_id),
    ADD CONSTRAINT formulas_view_fk FOREIGN KEY (view_id) REFERENCES views (view_id);

--
-- constraints for table user_formulas
--
ALTER TABLE user_formulas
    ADD CONSTRAINT user_formulas_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id),
    ADD CONSTRAINT user_formulas_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_formulas_formula_type_fk FOREIGN KEY (formula_type_id) REFERENCES formula_types (formula_type_id),
    ADD CONSTRAINT user_formulas_view_fk FOREIGN KEY (view_id) REFERENCES views (view_id);

-- --------------------------------------------------------

--
-- constraints for table formula_links
--

ALTER TABLE formula_links
    ADD CONSTRAINT formula_links_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT formula_links_formula_link_type_fk FOREIGN KEY (formula_link_type_id) REFERENCES formula_link_types (formula_link_type_id),
    ADD CONSTRAINT formula_links_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

--
-- constraints for table user_formula_links
--

ALTER TABLE user_formula_links
    ADD CONSTRAINT user_formula_links_formula_link_fk FOREIGN KEY (formula_link_id) REFERENCES formula_links (formula_link_id),
    ADD CONSTRAINT user_formula_links_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_formula_links_formula_link_type_fk FOREIGN KEY (formula_link_type_id) REFERENCES formula_link_types (formula_link_type_id);

-- --------------------------------------------------------

--
-- constraints for table results
--
ALTER TABLE results
    ADD CONSTRAINT results_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id),
    ADD CONSTRAINT results_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results
--
ALTER TABLE user_results
    ADD CONSTRAINT user_results_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

--
-- constraints for table results_prime
--
ALTER TABLE results_prime
    ADD CONSTRAINT results_prime_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id),
    ADD CONSTRAINT results_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_prime
--
ALTER TABLE user_results_prime
    ADD CONSTRAINT user_results_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_prime_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

--
-- constraints for table results_main
--
ALTER TABLE results_main
    ADD CONSTRAINT results_main_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id),
    ADD CONSTRAINT results_main_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_main
--
ALTER TABLE user_results_main
    ADD CONSTRAINT user_results_main_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_main_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

--
-- constraints for table results_big
--
ALTER TABLE results_big
    ADD CONSTRAINT results_big_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id),
    ADD CONSTRAINT results_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_big
--
ALTER TABLE user_results_big
    ADD CONSTRAINT user_results_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_big_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

-- --------------------------------------------------------

--
-- constraints for table results_text
--
ALTER TABLE results_text
    ADD CONSTRAINT results_text_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id),
    ADD CONSTRAINT results_text_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_text
--
ALTER TABLE user_results_text
    ADD CONSTRAINT user_results_text_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_text_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

--
-- constraints for table results_text_prime
--
ALTER TABLE results_text_prime
    ADD CONSTRAINT results_text_prime_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id),
    ADD CONSTRAINT results_text_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_text_prime
--
ALTER TABLE user_results_text_prime
    ADD CONSTRAINT user_results_text_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_text_prime_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

--
-- constraints for table results_text_main
--
ALTER TABLE results_text_main
    ADD CONSTRAINT results_text_main_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id),
    ADD CONSTRAINT results_text_main_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_text_main
--
ALTER TABLE user_results_text_main
    ADD CONSTRAINT user_results_text_main_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_text_main_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

--
-- constraints for table results_text_big
--
ALTER TABLE results_text_big
    ADD CONSTRAINT results_text_big_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id),
    ADD CONSTRAINT results_text_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_text_big
--
ALTER TABLE user_results_text_big
    ADD CONSTRAINT user_results_text_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_text_big_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

-- --------------------------------------------------------

--
-- constraints for table results_time
--
ALTER TABLE results_time
    ADD CONSTRAINT results_time_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id),
    ADD CONSTRAINT results_time_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_time
--
ALTER TABLE user_results_time
    ADD CONSTRAINT user_results_time_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_time_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

--
-- constraints for table results_time_prime
--
ALTER TABLE results_time_prime
    ADD CONSTRAINT results_time_prime_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id),
    ADD CONSTRAINT results_time_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_time_prime
--
ALTER TABLE user_results_time_prime
    ADD CONSTRAINT user_results_time_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_time_prime_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

--
-- constraints for table results_time_main
--
ALTER TABLE results_time_main
    ADD CONSTRAINT results_time_main_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id),
    ADD CONSTRAINT results_time_main_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_time_main
--
ALTER TABLE user_results_time_main
    ADD CONSTRAINT user_results_time_main_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_time_main_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

--
-- constraints for table results_time_big
--
ALTER TABLE results_time_big
    ADD CONSTRAINT results_time_big_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id),
    ADD CONSTRAINT results_time_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_time_big
--
ALTER TABLE user_results_time_big
    ADD CONSTRAINT user_results_time_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_time_big_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

-- --------------------------------------------------------

--
-- constraints for table results_geo
--
ALTER TABLE results_geo
    ADD CONSTRAINT results_geo_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id),
    ADD CONSTRAINT results_geo_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_geo
--
ALTER TABLE user_results_geo
    ADD CONSTRAINT user_results_geo_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_geo_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

--
-- constraints for table results_geo_prime
--
ALTER TABLE results_geo_prime
    ADD CONSTRAINT results_geo_prime_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id),
    ADD CONSTRAINT results_geo_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_geo_prime
--
ALTER TABLE user_results_geo_prime
    ADD CONSTRAINT user_results_geo_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_geo_prime_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

--
-- constraints for table results_geo_main
--
ALTER TABLE results_geo_main
    ADD CONSTRAINT results_geo_main_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id),
    ADD CONSTRAINT results_geo_main_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_geo_main
--
ALTER TABLE user_results_geo_main
    ADD CONSTRAINT user_results_geo_main_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_geo_main_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

--
-- constraints for table results_geo_big
--
ALTER TABLE results_geo_big
    ADD CONSTRAINT results_geo_big_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id),
    ADD CONSTRAINT results_geo_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_geo_big
--
ALTER TABLE user_results_geo_big
    ADD CONSTRAINT user_results_geo_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_geo_big_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

-- --------------------------------------------------------

--
-- constraints for table results_time_series
--
ALTER TABLE results_time_series
    ADD CONSTRAINT results_time_series_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id),
    ADD CONSTRAINT results_time_series_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_time_series
--
ALTER TABLE user_results_time_series
    ADD CONSTRAINT user_results_time_series_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_time_series_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

--
-- constraints for table results_time_series_prime
--
ALTER TABLE results_time_series_prime
    ADD CONSTRAINT results_time_series_prime_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id),
    ADD CONSTRAINT results_time_series_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_time_series_prime
--
ALTER TABLE user_results_time_series_prime
    ADD CONSTRAINT user_results_time_series_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_time_series_prime_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

--
-- constraints for table results_time_series_big
--
ALTER TABLE results_time_series_big
    ADD CONSTRAINT results_time_series_big_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id),
    ADD CONSTRAINT results_time_series_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_results_time_series_big
--
ALTER TABLE user_results_time_series_big
    ADD CONSTRAINT user_results_time_series_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_results_time_series_big_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

-- --------------------------------------------------------

--
-- constraints for table views
--

ALTER TABLE views
    ADD CONSTRAINT views_view_name_uk UNIQUE (view_name),
    ADD CONSTRAINT views_code_id_uk UNIQUE (code_id),
    ADD CONSTRAINT views_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT views_view_type_fk FOREIGN KEY (view_type_id) REFERENCES view_types (view_type_id),
    ADD CONSTRAINT views_view_style_fk FOREIGN KEY (view_style_id) REFERENCES view_styles (view_style_id);

--
-- constraints for table user_views
--

ALTER TABLE user_views
    ADD CONSTRAINT user_views_view_fk FOREIGN KEY (view_id) REFERENCES views (view_id),
    ADD CONSTRAINT user_views_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_views_language_fk FOREIGN KEY (language_id) REFERENCES languages (language_id),
    ADD CONSTRAINT user_views_view_type_fk FOREIGN KEY (view_type_id) REFERENCES view_types (view_type_id),
    ADD CONSTRAINT user_views_view_style_fk FOREIGN KEY (view_style_id) REFERENCES view_styles (view_style_id);

-- --------------------------------------------------------

--
-- constraints for table term_views
--

ALTER TABLE term_views
    ADD CONSTRAINT term_views_view_fk FOREIGN KEY (view_id) REFERENCES views (view_id),
    ADD CONSTRAINT term_views_view_link_type_fk FOREIGN KEY (view_link_type_id) REFERENCES view_link_types (view_link_type_id),
    ADD CONSTRAINT term_views_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_term_views
--

ALTER TABLE user_term_views
    ADD CONSTRAINT user_term_views_term_view_fk FOREIGN KEY (term_view_id) REFERENCES term_views (term_view_id),
    ADD CONSTRAINT user_term_views_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_term_views_view_link_type_fk FOREIGN KEY (view_link_type_id) REFERENCES view_link_types (view_link_type_id);

-- --------------------------------------------------------

--
-- constraints for table components
--

ALTER TABLE components
    ADD CONSTRAINT components_component_name_uk UNIQUE (component_name),
    ADD CONSTRAINT components_code_id_uk UNIQUE (code_id),
    ADD CONSTRAINT components_ui_msg_code_id_uk UNIQUE (ui_msg_code_id),
    ADD CONSTRAINT components_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT components_component_type_fk FOREIGN KEY (component_type_id) REFERENCES component_types (component_type_id),
    ADD CONSTRAINT components_view_style_fk FOREIGN KEY (view_style_id) REFERENCES view_styles (view_style_id),
    ADD CONSTRAINT components_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

--
-- constraints for table user_components
--

ALTER TABLE user_components
    ADD CONSTRAINT user_components_component_fk FOREIGN KEY (component_id) REFERENCES components (component_id),
    ADD CONSTRAINT user_components_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_components_component_type_fk FOREIGN KEY (component_type_id) REFERENCES component_types (component_type_id),
    ADD CONSTRAINT user_components_view_style_fk FOREIGN KEY (view_style_id) REFERENCES view_styles (view_style_id),
    ADD CONSTRAINT user_components_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

-- --------------------------------------------------------

--
-- constraints for table component_links
--

ALTER TABLE component_links
    ADD CONSTRAINT component_links_view_fk FOREIGN KEY (view_id) REFERENCES views (view_id),
    ADD CONSTRAINT component_links_component_fk FOREIGN KEY (component_id) REFERENCES components (component_id),
    ADD CONSTRAINT component_links_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT component_links_component_link_type_fk FOREIGN KEY (component_link_type_id) REFERENCES component_link_types (component_link_type_id),
    ADD CONSTRAINT component_links_position_type_fk FOREIGN KEY (position_type_id) REFERENCES position_types (position_type_id),
    ADD CONSTRAINT component_links_view_style_fk FOREIGN KEY (view_style_id) REFERENCES view_styles (view_style_id);

--
-- constraints for table user_component_links
--

ALTER TABLE user_component_links
    ADD CONSTRAINT user_component_links_component_link_fk FOREIGN KEY (component_link_id) REFERENCES component_links (component_link_id),
    ADD CONSTRAINT user_component_links_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_component_links_component_link_type_fk FOREIGN KEY (component_link_type_id) REFERENCES component_link_types (component_link_type_id),
    ADD CONSTRAINT user_component_links_position_type_fk FOREIGN KEY (position_type_id) REFERENCES position_types (position_type_id),
    ADD CONSTRAINT user_component_links_view_style_fk FOREIGN KEY (view_style_id) REFERENCES view_styles (view_style_id);


/*!40101 SET CHARACTER_SET_CLIENT = @OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS = @OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION = @OLD_COLLATION_CONNECTION */;
