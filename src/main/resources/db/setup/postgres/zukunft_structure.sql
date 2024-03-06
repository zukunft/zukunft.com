--
-- Database: zukunft
--

-- --------------------------------------------------------

ALTER DATABASE zukunft SET search_path TO public;

-- --------------------------------------------------------

--
-- table structure for the core configuration of this pod e.g. the program version or pod url
--

CREATE TABLE IF NOT EXISTS config
(
    config_id BIGSERIAL PRIMARY KEY,
    config_name varchar(255) DEFAULT NULL,
    code_id     varchar(255)     NOT NULL,
    value       varchar(255) DEFAULT NULL,
    description text         DEFAULT NULL
);

COMMENT ON TABLE config IS 'for the core configuration of this pod e.g. the program version or pod url';
COMMENT ON COLUMN config.config_id IS 'the internal unique primary index';
COMMENT ON COLUMN config.config_name IS 'short name of the configuration entry to be shown to the admin';
COMMENT ON COLUMN config.code_id IS 'unique id text to select a configuration value from the code';
COMMENT ON COLUMN config.value IS 'the configuration value as a string';
COMMENT ON COLUMN config.description IS 'text to explain the config value to an admin user';

-- --------------------------------------------------------

--
-- table structure for system log types e.g. info, warning and error
-- TODO change to an enum because this will probably never change
--

CREATE TABLE IF NOT EXISTS sys_log_types
(
    sys_log_type_id BIGSERIAL PRIMARY KEY,
    type_name       varchar(255) NOT NULL,
    code_id         varchar(255) NOT NULL,
    description     text     DEFAULT NULL
);

COMMENT ON TABLE sys_log_types IS 'system log types e.g. info, warning and error';

-- --------------------------------------------------------

--
-- table structure to define the status of internal errors
--

CREATE TABLE IF NOT EXISTS sys_log_status
(
    sys_log_status_id BIGSERIAL PRIMARY KEY,
    type_name         varchar(255)     NOT NULL,
    code_id           varchar(255) DEFAULT NULL,
    description       text         DEFAULT NULL,
    action            varchar(255) DEFAULT NULL
);

COMMENT ON TABLE sys_log_status IS 'to define the status of internal errors';
COMMENT ON COLUMN sys_log_status.sys_log_status_id IS 'the internal unique primary index';
COMMENT ON COLUMN sys_log_status.type_name IS 'the unique type name as shown to the user and used for the selection';
COMMENT ON COLUMN sys_log_status.code_id IS 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration';
COMMENT ON COLUMN sys_log_status.description IS 'text to explain the type to the user as a tooltip; to be replaced by a language form entry';
COMMENT ON COLUMN sys_log_status.action IS 'description of the action to get to this status';


-- --------------------------------------------------------

--
-- table structure to group the system log entries by function
--

CREATE TABLE IF NOT EXISTS sys_log_functions
(
    sys_log_function_id   BIGSERIAL PRIMARY KEY,
    sys_log_function_name varchar(255)     NOT NULL,
    code_id               varchar(255) DEFAULT NULL,
    description           text         DEFAULT NULL
);

COMMENT ON TABLE sys_log_functions IS 'to group the system log entries by function';
COMMENT ON COLUMN sys_log_functions.sys_log_function_id IS 'the internal unique primary index';
COMMENT ON COLUMN sys_log_functions.sys_log_function_name IS 'the unique type name as shown to the user and used for the selection';
COMMENT ON COLUMN sys_log_functions.code_id IS 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration';
COMMENT ON COLUMN sys_log_functions.description IS 'text to explain the type to the user as a tooltip; to be replaced by a language form entry';

-- --------------------------------------------------------

--
-- table structure for system error traking and to measure execution times
--

CREATE TABLE IF NOT EXISTS sys_log
(
    sys_log_id          BIGSERIAL PRIMARY KEY,
    sys_log_time        timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP,
    sys_log_type_id     bigint     NOT NULL,
    sys_log_function_id bigint     NOT NULL,
    sys_log_text        text   DEFAULT NULL,
    sys_log_description text   DEFAULT NULL,
    sys_log_trace       text   DEFAULT NULL,
    user_id             bigint DEFAULT NULL,
    solver_id           bigint DEFAULT NULL,
    sys_log_status_id   bigint     NOT NULL DEFAULT 1
);

COMMENT ON TABLE sys_log IS 'for system error traking and to measure execution times';
COMMENT ON COLUMN sys_log.sys_log_id IS 'the internal unique primary index';
COMMENT ON COLUMN sys_log.sys_log_time IS 'timestamp of the creation';
COMMENT ON COLUMN sys_log.sys_log_type_id IS 'the level e.g. debug,info,warning,error or fatal';
COMMENT ON COLUMN sys_log.sys_log_function_id IS 'the function or function group for the entry e.g. db_write to measure the db write times';
COMMENT ON COLUMN sys_log.sys_log_text IS 'the short text of the log entry to indentify the error and to reduce the number of double entries';
COMMENT ON COLUMN sys_log.sys_log_description IS 'the lond description with all details of the log entry to solve ti issue';
COMMENT ON COLUMN sys_log.sys_log_trace IS 'the generated code trace to local the path to the error cause';
COMMENT ON COLUMN sys_log.user_id IS 'the id of the user who has caused the log entry';
COMMENT ON COLUMN sys_log.solver_id IS 'user id of the user that is trying to solve the problem';

-- --------------------------------------------------------

--
-- table structure for predefined batch jobs that can be triggered by a user action or scheduled e.g. data synchronisation
--

CREATE TABLE IF NOT EXISTS job_types
(
    job_type_id BIGSERIAL PRIMARY KEY,
    type_name   varchar(255) NOT NULL,
    code_id     varchar(255) DEFAULT NULL,
    description text         DEFAULT NULL
);

COMMENT ON TABLE job_types IS 'for predefined batch jobs that can be triggered by a user action or scheduled e.g. data synchronisation';
COMMENT ON COLUMN job_types.job_type_id IS 'the internal unique primary index';
COMMENT ON COLUMN job_types.type_name IS 'the unique type name as shown to the user and used for the selection';
COMMENT ON COLUMN job_types.code_id IS 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration';
COMMENT ON COLUMN job_types.description IS 'text to explain the type to the user as a tooltip; to be replaced by a language form entry';

-- --------------------------------------------------------

--
-- table structure to schedule jobs with predefined parameters
--

CREATE TABLE IF NOT EXISTS job_times
(
    job_time_id BIGSERIAL PRIMARY KEY,
    schedule    varchar(20) DEFAULT NULL,
    job_type_id bigint          NOT NULL,
    user_id     bigint          NOT NULL,
    start       timestamp   DEFAULT NULL,
    parameter   bigint      DEFAULT NULL
);

COMMENT ON TABLE job_times IS 'to schedule jobs with predefined parameters';
COMMENT ON COLUMN job_times.job_time_id IS 'the internal unique primary index';
COMMENT ON COLUMN job_times.schedule IS 'the crontab for the job schedule';
COMMENT ON COLUMN job_times.job_type_id IS 'the id of the job type that should be started';
COMMENT ON COLUMN job_times.user_id IS 'the id of the user who edit the scheduler the last time';
COMMENT ON COLUMN job_times.start IS 'the last start of the job';
COMMENT ON COLUMN job_times.parameter IS 'the phrase id that contains all parameters for the next job start';

-- --------------------------------------------------------

--
-- table structure for each concrete job run
--

CREATE TABLE IF NOT EXISTS jobs
(
    job_id BIGSERIAL PRIMARY KEY,
    user_id         bigint NOT NULL,
    job_type_id     bigint NOT NULL,
    request_time    timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    start_time      timestamp DEFAULT NULL,
    end_time        timestamp DEFAULT NULL,
    parameter       bigint DEFAULT NULL,
    change_field_id bigint DEFAULT NULL,
    row_id          bigint DEFAULT NULL
);

COMMENT ON TABLE jobs IS 'for each concrete job run';
COMMENT ON COLUMN jobs.job_id IS 'the internal unique primary index';
COMMENT ON COLUMN jobs.user_id IS 'the id of the user who has requested the job by editing the scheduler the last time';
COMMENT ON COLUMN jobs.job_type_id IS 'the id of the job type that should be started';
COMMENT ON COLUMN jobs.request_time IS 'timestamp of the request for the job execution';
COMMENT ON COLUMN jobs.start_time IS 'timestamp when the system has started the execution';
COMMENT ON COLUMN jobs.end_time IS 'timestamp when the job has been completed or canceled';
COMMENT ON COLUMN jobs.parameter IS 'id of the phrase with the snaped parameter set for this job start';
COMMENT ON COLUMN jobs.change_field_id IS 'e.g. for undo jobs the id of the field that should be changed';
COMMENT ON COLUMN jobs.row_id IS 'e.g. for undo jobs the id of the row that should be changed';

-- --------------------------------------------------------

--
-- table structure for the user types e.g. to set the confirmation level of a user
--

CREATE TABLE IF NOT EXISTS user_types
(
    user_type_id BIGSERIAL PRIMARY KEY,
    type_name    varchar(255) NOT NULL,
    code_id      varchar(255) DEFAULT NULL,
    description  text DEFAULT NULL
);

COMMENT ON TABLE user_types IS 'for the user types e.g. to set the confirmation level of a user';
COMMENT ON COLUMN user_types.user_type_id IS 'the internal unique primary index';
COMMENT ON COLUMN user_types.type_name IS 'the unique type name as shown to the user and used for the selection';
COMMENT ON COLUMN user_types.code_id IS 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration';
COMMENT ON COLUMN user_types.description IS 'text to explain the type to the user as a tooltip; to be replaced by a language form entry';

-- --------------------------------------------------------

--
-- table structure to define the user roles and read and write rights
--

CREATE TABLE IF NOT EXISTS user_profiles
(
    user_profile_id BIGSERIAL PRIMARY KEY,
    type_name    varchar(255) NOT NULL,
    code_id      varchar(255) DEFAULT NULL,
    description  text         DEFAULT NULL,
    right_level  smallint     DEFAULT NULL
);

COMMENT ON TABLE user_profiles IS 'to define the user roles and read and write rights';
COMMENT ON COLUMN user_profiles.user_profile_id IS 'the internal unique primary index';
COMMENT ON COLUMN user_profiles.type_name IS 'the unique type name as shown to the user and used for the selection';
COMMENT ON COLUMN user_profiles.code_id IS 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration';
COMMENT ON COLUMN user_profiles.description IS 'text to explain the type to the user as a tooltip; to be replaced by a language form entry';
COMMENT ON COLUMN user_profiles.right_level IS 'the access right level to prevent unpermitted right gaining';

-- --------------------------------------------------------

--
-- table structure for person identification types e.g. passports
--

CREATE TABLE IF NOT EXISTS user_official_types
(
    user_official_type_id BIGSERIAL PRIMARY KEY,
    type_name             varchar(255) NOT NULL,
    code_id               varchar(255) DEFAULT NULL,
    description           text         DEFAULT NULL
);

COMMENT ON TABLE user_official_types IS 'for person identification types e.g. passports';
COMMENT ON COLUMN user_official_types.user_official_type_id IS 'the internal unique primary index';
COMMENT ON COLUMN user_official_types.type_name IS 'the unique type name as shown to the user and used for the selection';
COMMENT ON COLUMN user_official_types.code_id IS 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration';
COMMENT ON COLUMN user_official_types.description IS 'text to explain the type to the user as a tooltip; to be replaced by a language form entry';

-- --------------------------------------------------------

--
-- table structure for users including system users; only users can add data
--

CREATE TABLE IF NOT EXISTS users
(
    user_id            BIGSERIAL PRIMARY KEY,
    user_name          varchar(255) NOT NULL,
    ip_address         varchar(100) DEFAULT NULL,
    password           varchar(255) DEFAULT NULL,
    description        text         DEFAULT NULL,
    code_id            varchar(100) DEFAULT NULL,
    user_profile_id    bigint       DEFAULT NULL,
    user_type_id       bigint       DEFAULT NULL,
    right_level        smallint     DEFAULT NULL,
    email              varchar(255) DEFAULT NULL,
    email_status       smallint     DEFAULT NULL,
    email_alternative  varchar(255) DEFAULT NULL,
    mobile_number      varchar(100) DEFAULT NULL,
    mobile_status      smallint     DEFAULT NULL,
    activation_key     varchar(255) DEFAULT NULL,
    activation_timeout timestamp    DEFAULT NULL,
    first_name         varchar(255) DEFAULT NULL,
    last_name          varchar(255) DEFAULT NULL,
    name_triple_id     bigint       DEFAULT NULL,
    geo_triple_id      bigint       DEFAULT NULL,
    geo_status_id      smallint     DEFAULT NULL,
    official_id        varchar(255) DEFAULT NULL,
    official_id_type   smallint     DEFAULT NULL,
    official_id_status smallint     DEFAULT NULL,
    term_id            bigint       DEFAULT NULL,
    view_id            bigint       DEFAULT NULL,
    source_id          bigint       DEFAULT NULL,
    user_status_id     smallint     DEFAULT NULL,
    created            timestamp        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_login         timestamp    DEFAULT NULL,
    last_logoff        timestamp    DEFAULT NULL
);

COMMENT ON TABLE users IS 'for users including system users; only users can add data';
COMMENT ON COLUMN users.user_id IS 'the internal unique primary index';
COMMENT ON COLUMN users.user_name IS 'the user name unique for this pod';
COMMENT ON COLUMN users.ip_address IS 'all users a first identified with the ip address';
COMMENT ON COLUMN users.password IS 'the hash value of the password';
COMMENT ON COLUMN users.description IS 'for system users the description to expain the profile to human users';
COMMENT ON COLUMN users.code_id IS 'to select e.g. the system batch user';
COMMENT ON COLUMN users.user_profile_id IS 'to define the user roles and read and write rights';
COMMENT ON COLUMN users.user_type_id IS 'to set the confirmation level of a user';
COMMENT ON COLUMN users.right_level IS 'the access right level to prevent unpermitted right gaining';
COMMENT ON COLUMN users.email IS 'the primary email for verification';
COMMENT ON COLUMN users.email_status IS 'if the email has been verified or if a password reset has been send';
COMMENT ON COLUMN users.email_alternative IS 'an alternative email for account recovery';
COMMENT ON COLUMN users.name_triple_id IS 'triple that contains e.g. the given name, family name, selected name or title of the person';
COMMENT ON COLUMN users.geo_triple_id IS 'the post address with street,city or any other form of geo location for physical transport';
COMMENT ON COLUMN users.official_id IS 'e.g. the number of the passport';
COMMENT ON COLUMN users.term_id IS 'the last term that the user had used';
COMMENT ON COLUMN users.view_id IS 'the last mask that the user has used';
COMMENT ON COLUMN users.source_id IS 'the last source used by this user to have a default for the next value';
COMMENT ON COLUMN users.user_status_id IS 'e.g. to exclude inactive users';

-- --------------------------------------------------------

--
-- table structure of ip addresses that should be blocked
--

CREATE TABLE IF NOT EXISTS ip_ranges
(
    ip_range_id BIGSERIAL PRIMARY KEY,
    ip_from     varchar(46) NOT NULL,
    ip_to       varchar(46) NOT NULL,
    reason      text        NOT NULL,
    is_active   smallint    NOT NULL DEFAULT 1
);

COMMENT ON TABLE ip_ranges IS 'of ip addresses that should be blocked';
COMMENT ON COLUMN ip_ranges.ip_range_id IS 'the internal unique primary index';

-- --------------------------------------------------------

--
-- table structure to control the user frontend sessions
--

CREATE TABLE IF NOT EXISTS sessions
(
    session_id  BIGSERIAL PRIMARY KEY,
    uid         bigint           NOT NULL,
    hash        varchar(255)     NOT NULL,
    expire_date timestamp        NOT NULL,
    ip          varchar(46)      NOT NULL,
    agent       varchar(255) DEFAULT NULL,
    cookie_crc  text         DEFAULT NULL
);

COMMENT ON TABLE sessions IS 'to control the user frontend sessions';
COMMENT ON COLUMN sessions.session_id IS 'the internal unique primary index';
COMMENT ON COLUMN sessions.uid IS 'the user session id as get by the frontend';

-- --------------------------------------------------------

--
-- table structure for add,change,delete,undo and redo actions
--

CREATE TABLE IF NOT EXISTS change_actions
(
    change_action_id BIGSERIAL PRIMARY KEY,
    change_action_name varchar(255) NOT NULL,
    code_id            varchar(255) NOT NULL,
    description        text     DEFAULT NULL
);

COMMENT ON TABLE change_actions IS 'for add,change,delete,undo and redo actions';
COMMENT ON COLUMN change_actions.change_action_id IS 'the internal unique primary index';

-- --------------------------------------------------------

--
-- table structure to keep the original table name even if a table name has changed and to avoid log changes in case a table is renamed
--

CREATE TABLE IF NOT EXISTS change_tables
(
    change_table_id   BIGSERIAL PRIMARY KEY,
    change_table_name varchar(255)     NOT NULL,
    code_id           varchar(255) DEFAULT NULL,
    description       text         DEFAULT NULL
);

COMMENT ON TABLE change_tables IS 'to keep the original table name even if a table name has changed and to avoid log changes in case a table is renamed';
COMMENT ON COLUMN change_tables.change_table_id IS 'the internal unique primary index';
COMMENT ON COLUMN change_tables.change_table_name IS 'the real name';
COMMENT ON COLUMN change_tables.code_id IS 'with this field tables can be combined in case of renaming';
COMMENT ON COLUMN change_tables.description IS 'the user readable name';

-- --------------------------------------------------------

--
-- table structure to keep the original field name even if a table name has changed
--

CREATE TABLE IF NOT EXISTS change_fields
(
    change_field_id   BIGSERIAL PRIMARY KEY,
    table_id          bigint           NOT NULL,
    change_field_name varchar(255)     NOT NULL,
    code_id           varchar(255) DEFAULT NULL,
    description       text         DEFAULT NULL
);

COMMENT ON TABLE change_fields IS 'to keep the original field name even if a table name has changed';
COMMENT ON COLUMN change_fields.change_field_id IS 'the internal unique primary index';
COMMENT ON COLUMN change_fields.table_id IS 'because every field must only be unique within a table';
COMMENT ON COLUMN change_fields.change_field_name IS 'the real name';
COMMENT ON COLUMN change_fields.code_id IS 'to display the change with some linked information';

-- --------------------------------------------------------

--
-- table structure to log all changes done by any user on all tables except value and link changes
--

CREATE TABLE IF NOT EXISTS changes
(
    change_id        BIGSERIAL PRIMARY KEY,
    change_time      timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id          bigint     NOT NULL,
    change_action_id smallint   NOT NULL,
    row_id           bigint DEFAULT NULL,
    change_field_id  bigint     NOT NULL,
    old_value        text   DEFAULT NULL,
    new_value        text   DEFAULT NULL,
    old_id           bigint DEFAULT NULL,
    new_id           bigint DEFAULT NULL
);

COMMENT ON TABLE changes IS 'to log all changes done by any user on all tables except value and link changes';
COMMENT ON COLUMN changes.change_id IS 'the prime key to identify the change change';
COMMENT ON COLUMN changes.change_time IS 'time when the user has confirmed the change';
COMMENT ON COLUMN changes.user_id IS 'reference to the user who has done the change';
COMMENT ON COLUMN changes.change_action_id IS 'the curl action';
COMMENT ON COLUMN changes.row_id IS 'the prime id in the table with the change';
COMMENT ON COLUMN changes.old_id IS 'old value id';
COMMENT ON COLUMN changes.new_id IS 'new value id';

-- --------------------------------------------------------

--
-- table structure to log all changes done by any user on values with a standard group id
--

CREATE TABLE IF NOT EXISTS change_standard_values
(
    change_id        BIGSERIAL PRIMARY KEY,
    change_time      timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id          bigint    NOT NULL,
    change_action_id smallint  NOT NULL,
    group_id         char(112) NOT NULL,
    change_field_id  bigint    NOT NULL,
    old_value        double precision DEFAULT NULL,
    new_value        double precision DEFAULT NULL
);

COMMENT ON TABLE change_standard_values IS 'to log all changes done by any user on values with a standard group id';
COMMENT ON COLUMN change_standard_values.change_id IS 'the prime key to identify the change change_standard_value';
COMMENT ON COLUMN change_standard_values.change_time IS 'time when the user has confirmed the change';
COMMENT ON COLUMN change_standard_values.user_id IS 'reference to the user who has done the change';
COMMENT ON COLUMN change_standard_values.change_action_id IS 'the curl action';

-- --------------------------------------------------------

--
-- table structure to log all changes done by any user on values with a prime group id
--

CREATE TABLE IF NOT EXISTS change_prime_values
(
    change_id        BIGSERIAL PRIMARY KEY,
    change_time      timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id          bigint    NOT NULL,
    change_action_id smallint  NOT NULL,
    group_id         bigint    NOT NULL,
    change_field_id  bigint    NOT NULL,
    old_value        double precision DEFAULT NULL,
    new_value        double precision DEFAULT NULL
);

COMMENT ON TABLE change_prime_values IS 'to log all changes done by any user on values with a prime group id';
COMMENT ON COLUMN change_prime_values.change_id IS 'the prime key to identify the change change_prime_value';
COMMENT ON COLUMN change_prime_values.change_time IS 'time when the user has confirmed the change';
COMMENT ON COLUMN change_prime_values.user_id IS 'reference to the user who has done the change';
COMMENT ON COLUMN change_prime_values.change_action_id IS 'the curl action';

-- --------------------------------------------------------

--
-- table structure to log all changes done by any user on values with a big group id
--

CREATE TABLE IF NOT EXISTS change_big_values
(
    change_id        BIGSERIAL PRIMARY KEY,
    change_time      timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id          bigint    NOT NULL,
    change_action_id smallint  NOT NULL,
    group_id         text      NOT NULL,
    change_field_id  bigint    NOT NULL,
    old_value        double precision DEFAULT NULL,
    new_value        double precision DEFAULT NULL
);

COMMENT ON TABLE change_big_values IS 'to log all changes done by any user on values with a big group id';
COMMENT ON COLUMN change_big_values.change_id IS 'the prime key to identify the change change_big_value';
COMMENT ON COLUMN change_big_values.change_time IS 'time when the user has confirmed the change';
COMMENT ON COLUMN change_big_values.user_id IS 'reference to the user who has done the change';
COMMENT ON COLUMN change_big_values.change_action_id IS 'the curl action';

-- --------------------------------------------------------

--
-- table structure to log the link changes done by the users
--

CREATE TABLE IF NOT EXISTS change_links
(
    change_link_id BIGSERIAL PRIMARY KEY,
    change_time      timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id          bigint     NOT NULL,
    change_action_id smallint   NOT NULL,
    row_id           bigint DEFAULT NULL,
    change_table_id  bigint     NOT NULL,
    old_from_id      bigint DEFAULT NULL,
    old_link_id      bigint DEFAULT NULL,
    old_to_id        bigint DEFAULT NULL,
    old_text_from    text   DEFAULT NULL,
    old_text_link    text   DEFAULT NULL,
    old_text_to      text   DEFAULT NULL,
    new_from_id      bigint DEFAULT NULL,
    new_link_id      bigint DEFAULT NULL,
    new_to_id        bigint DEFAULT NULL,
    new_text_from    text   DEFAULT NULL,
    new_text_link    text   DEFAULT NULL,
    new_text_to      text   DEFAULT NULL
);

COMMENT ON TABLE change_links IS 'to log the link changes done by the users';
COMMENT ON COLUMN change_links.change_link_id IS 'the prime key to identify the change change_link';
COMMENT ON COLUMN change_links.change_time IS 'time when the user has confirmed the change';
COMMENT ON COLUMN change_links.user_id IS 'reference to the user who has done the change';
COMMENT ON COLUMN change_links.change_action_id IS 'the curl action';
COMMENT ON COLUMN change_links.row_id IS 'the prime id in the table with the change';
COMMENT ON COLUMN change_links.new_to_id IS 'either internal row id or the ref type id of the external system e.g. 2 for wikidata';
COMMENT ON COLUMN change_links.new_text_to IS 'the fixed text to display to the user or the external reference id e.g. Q1 (for universe) in case of wikidata';

-- --------------------------------------------------------

--
-- table structure for predefined code to a some pods
--

CREATE TABLE IF NOT EXISTS pod_types
(
    pod_type_id BIGSERIAL PRIMARY KEY,
    type_name   varchar(255)     NOT NULL,
    code_id     varchar(255) DEFAULT NULL,
    description text         DEFAULT NULL
);

COMMENT ON TABLE pod_types IS 'for predefined code to a some pods';
COMMENT ON COLUMN pod_types.pod_type_id IS 'the internal unique primary index';
COMMENT ON COLUMN pod_types.type_name IS 'the unique type name as shown to the user and used for the selection';
COMMENT ON COLUMN pod_types.code_id IS 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration';
COMMENT ON COLUMN pod_types.description IS 'text to explain the type to the user as a tooltip; to be replaced by a language form entry';

-- --------------------------------------------------------

--
-- table structure for the actual status of a pod
--

CREATE TABLE IF NOT EXISTS pod_status
(
    pod_status_id BIGSERIAL PRIMARY KEY,
    type_name     varchar(255)     NOT NULL,
    code_id       varchar(255) DEFAULT NULL,
    description   text         DEFAULT NULL
);

COMMENT ON TABLE pod_status IS 'for the actual status of a pod';
COMMENT ON COLUMN pod_status.pod_status_id IS 'the internal unique primary index';
COMMENT ON COLUMN pod_status.type_name IS 'the unique type name as shown to the user and used for the selection';
COMMENT ON COLUMN pod_status.code_id IS 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration';
COMMENT ON COLUMN pod_status.description IS 'text to explain the type to the user as a tooltip; to be replaced by a language form entry';

-- --------------------------------------------------------

--
-- table structure for the technical details of the mash network pods
--

CREATE TABLE IF NOT EXISTS pods
(
    pod_id          BIGSERIAL PRIMARY KEY,
    type_name       varchar(255)     NOT NULL,
    code_id         varchar(255) DEFAULT NULL,
    description     text         DEFAULT NULL,
    pod_type_id     bigint       DEFAULT NULL,
    pod_url         varchar(255)     NOT NULL,
    pod_status_id   bigint       DEFAULT NULL,
    param_triple_id bigint       DEFAULT NULL
);

COMMENT ON TABLE pods IS 'for the technical details of the mash network pods';
COMMENT ON COLUMN pods.pod_id IS 'the internal unique primary index';
COMMENT ON COLUMN pods.type_name IS 'the unique type name as shown to the user and used for the selection';
COMMENT ON COLUMN pods.code_id IS 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration';
COMMENT ON COLUMN pods.description IS 'text to explain the type to the user as a tooltip; to be replaced by a language form entry';

-- --------------------------------------------------------

--
-- table structure for the write access control
--

CREATE TABLE IF NOT EXISTS protection_types
(
    protection_type_id BIGSERIAL PRIMARY KEY,
    type_name          varchar(255) NOT NULL,
    code_id            varchar(255) DEFAULT NULL,
    description        text         DEFAULT NULL
);

COMMENT ON TABLE protection_types IS 'for the write access control';
COMMENT ON COLUMN protection_types.protection_type_id IS 'the internal unique primary index';
COMMENT ON COLUMN protection_types.type_name IS 'the unique type name as shown to the user and used for the selection';
COMMENT ON COLUMN protection_types.code_id IS 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration';
COMMENT ON COLUMN protection_types.description IS 'text to explain the type to the user as a tooltip; to be replaced by a language form entry';

-- --------------------------------------------------------

--
-- table structure for the read access control
--

CREATE TABLE IF NOT EXISTS share_types
(
    share_type_id BIGSERIAL PRIMARY KEY,
    type_name     varchar(255) NOT NULL,
    code_id       varchar(255) DEFAULT NULL,
    description   text         DEFAULT NULL
);

COMMENT ON TABLE share_types IS 'for the read access control';
COMMENT ON COLUMN share_types.share_type_id IS 'the internal unique primary index';
COMMENT ON COLUMN share_types.type_name IS 'the unique type name as shown to the user and used for the selection';
COMMENT ON COLUMN share_types.code_id IS 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration';
COMMENT ON COLUMN share_types.description IS 'text to explain the type to the user as a tooltip; to be replaced by a language form entry';

-- --------------------------------------------------------

--
-- table structure for the phrase type to set the predefined behaviour of a word or triple
--

CREATE TABLE IF NOT EXISTS phrase_types
(
    phrase_type_id BIGSERIAL PRIMARY KEY,
    type_name      varchar(255) NOT NULL,
    code_id        varchar(255) DEFAULT NULL,
    description    text         DEFAULT NULL,
    scaling_factor bigint       DEFAULT NULL,
    word_symbol    varchar(255) DEFAULT NULL
);

COMMENT ON TABLE phrase_types IS 'for the phrase type to set the predefined behaviour of a word or triple';
COMMENT ON COLUMN phrase_types.phrase_type_id IS 'the internal unique primary index';
COMMENT ON COLUMN phrase_types.type_name IS 'the unique type name as shown to the user and used for the selection';
COMMENT ON COLUMN phrase_types.code_id IS 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration';
COMMENT ON COLUMN phrase_types.description IS 'text to explain the type to the user as a tooltip; to be replaced by a language form entry';
COMMENT ON COLUMN phrase_types.scaling_factor IS 'e.g. for percent the scaling factor is 100';
COMMENT ON COLUMN phrase_types.word_symbol IS 'e.g. for percent the symbol is %';

-- --------------------------------------------------------

--
-- table structure for table languages
--

CREATE TABLE IF NOT EXISTS languages
(
    language_id    BIGSERIAL PRIMARY KEY,
    language_name  varchar(255)     NOT NULL,
    code_id        varchar(100) DEFAULT NULL,
    description    text         DEFAULT NULL,
    wikimedia_code varchar(100) DEFAULT NULL
);

COMMENT ON TABLE languages IS 'for table languages';
COMMENT ON COLUMN languages.language_id IS 'the internal unique primary index';

-- --------------------------------------------------------

--
-- table structure for language forms like plural
--

CREATE TABLE IF NOT EXISTS language_forms
(
    language_form_id   BIGSERIAL PRIMARY KEY,
    language_form_name varchar(255) DEFAULT NULL,
    code_id            varchar(100) DEFAULT NULL,
    description        text         DEFAULT NULL,
    language_id        bigint       DEFAULT NULL
);

COMMENT ON TABLE language_forms IS 'for language forms like plural';
COMMENT ON COLUMN language_forms.language_form_id IS 'the internal unique primary index';
COMMENT ON COLUMN language_forms.language_form_name IS 'type of adjustment of a term in a language e.g. plural';

-- --------------------------------------------------------

--
-- table structure for a short text, that can be used to search for values or results with a 64 bit database key because humans will never be able to use more than a few million words
--

CREATE TABLE IF NOT EXISTS words
(
    word_id        BIGSERIAL PRIMARY KEY,
    user_id        bigint                DEFAULT NULL,
    word_name      varchar(255) NOT NULL,
    plural         varchar(255)          DEFAULT NULL,
    description    text                  DEFAULT NULL,
    phrase_type_id bigint                DEFAULT NULL,
    view_id        bigint                DEFAULT NULL,
    values         bigint                DEFAULT NULL,
    inactive       smallint              DEFAULT NULL,
    code_id        varchar(255)          DEFAULT NULL,
    excluded       smallint              DEFAULT NULL,
    share_type_id  smallint              DEFAULT NULL,
    protect_id     smallint              DEFAULT NULL
);

COMMENT ON TABLE words IS 'for a short text, that can be used to search for values or results with a 64 bit database key because humans will never be able to use more than a few million words';
COMMENT ON COLUMN words.word_id IS 'the internal unique primary index';
COMMENT ON COLUMN words.user_id IS 'the owner / creator of the word';
COMMENT ON COLUMN words.word_name IS 'the text used for searching';
COMMENT ON COLUMN words.plural IS 'to be replaced by a language form entry; TODO to be move to language forms';
COMMENT ON COLUMN words.description IS 'to be replaced by a language form entry';
COMMENT ON COLUMN words.phrase_type_id IS 'to link coded functionality to words e.g. to exclude measure words from a percent result';
COMMENT ON COLUMN words.view_id IS 'the default mask for this word';
COMMENT ON COLUMN words.values IS 'number of values linked to the word, which gives an indication of the importance';
COMMENT ON COLUMN words.inactive IS 'true if the word is not yet active e.g. because it is moved to the prime words with a 16 bit id';
COMMENT ON COLUMN words.code_id IS 'to link coded functionality to a specific word e.g. to get the values of the system configuration';
COMMENT ON COLUMN words.excluded IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN words.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN words.protect_id IS 'to protect against unwanted changes';

--
-- table structure to save user specific changes for a short text, that can be used to search for values or results with a 64 bit database key because humans will never be able to use more than a few million words
--

CREATE TABLE IF NOT EXISTS user_words
(
    word_id        bigint   NOT NULL,
    user_id        bigint   NOT NULL,
    language_id    bigint   NOT NULL DEFAULT 1,
    word_name      varchar(255)      DEFAULT NULL,
    plural         varchar(255)      DEFAULT NULL,
    description    text              DEFAULT NULL,
    phrase_type_id bigint            DEFAULT NULL,
    view_id        bigint            DEFAULT NULL,
    values         bigint            DEFAULT NULL,
    excluded       smallint          DEFAULT NULL,
    share_type_id  smallint          DEFAULT NULL,
    protect_id     smallint          DEFAULT NULL
);

COMMENT ON TABLE user_words IS 'for a short text, that can be used to search for values or results with a 64 bit database key because humans will never be able to use more than a few million words';
COMMENT ON COLUMN user_words.word_id IS 'with the user_id the internal unique primary index';
COMMENT ON COLUMN user_words.user_id IS 'the changer of the word';
COMMENT ON COLUMN user_words.language_id IS 'the text used for searching';
COMMENT ON COLUMN user_words.word_name IS 'the text used for searching';
COMMENT ON COLUMN user_words.plural IS 'to be replaced by a language form entry; TODO to be move to language forms';
COMMENT ON COLUMN user_words.description IS 'to be replaced by a language form entry';
COMMENT ON COLUMN user_words.phrase_type_id IS 'to link coded functionality to words e.g. to exclude measure words from a percent result';
COMMENT ON COLUMN user_words.view_id IS 'the default mask for this word';
COMMENT ON COLUMN user_words.values IS 'number of values linked to the word,which gives an indication of the importance';
COMMENT ON COLUMN user_words.excluded IS 'true if a user,but not all, have removed it';
COMMENT ON COLUMN user_words.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_words.protect_id IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure for verbs / triple predicates to use predefined behavior
--

CREATE TABLE IF NOT EXISTS verbs
(
    verb_id             BIGSERIAL PRIMARY KEY,
    verb_name           varchar(255)     NOT NULL,
    code_id             varchar(255) DEFAULT NULL,
    description         text         DEFAULT NULL,
    condition_type      bigint       DEFAULT NULL,
    formula_name        varchar(255) DEFAULT NULL,
    name_plural_reverse varchar(255) DEFAULT NULL,
    name_plural         varchar(255) DEFAULT NULL,
    name_reverse        varchar(255) DEFAULT NULL,
    words               bigint       DEFAULT NULL
);

COMMENT ON TABLE verbs IS 'for verbs / triple predicates to use predefined behavior';
COMMENT ON COLUMN verbs.verb_id IS 'the internal unique primary index';
COMMENT ON COLUMN verbs.verb_name IS 'the unique type name as shown to the user and used for the selection';
COMMENT ON COLUMN verbs.code_id IS 'id text to link coded functionality to a specific verb';
COMMENT ON COLUMN verbs.description IS 'text to explain the type to the user as a tooltip; to be replaced by a language form entry';
COMMENT ON COLUMN verbs.formula_name IS 'naming used in formulas';
COMMENT ON COLUMN verbs.name_plural_reverse IS 'english description for the reverse list, e.g. Companies are ... TODO move to language forms';
COMMENT ON COLUMN verbs.words IS 'used for how many phrases or formulas';

-- --------------------------------------------------------

--
-- table structure to link one word or triple with a verb to another word or triple
--

CREATE TABLE IF NOT EXISTS triples
(
    triple_id           BIGSERIAL PRIMARY KEY,
    user_id             bigint            DEFAULT NULL,
    from_phrase_id      bigint   NOT NULL,
    verb_id             bigint   NOT NULL,
    to_phrase_id        bigint   NOT NULL,
    triple_name         varchar(255)      DEFAULT NULL,
    name_given          varchar(255)      DEFAULT NULL,
    name_generated      varchar(255)      DEFAULT NULL,
    description         text              DEFAULT NULL,
    triple_condition_id bigint            DEFAULT NULL,
    phrase_type_id      bigint            DEFAULT NULL,
    view_id             bigint            DEFAULT NULL,
    values              bigint            DEFAULT NULL,
    inactive            smallint          DEFAULT NULL,
    code_id             varchar(255)      DEFAULT NULL,
    excluded            smallint          DEFAULT NULL,
    share_type_id       smallint          DEFAULT NULL,
    protect_id          smallint          DEFAULT NULL
);

COMMENT ON TABLE triples IS 'to link one word or triple with a verb to another word or triple';
COMMENT ON COLUMN triples.triple_id IS 'the internal unique primary index';
COMMENT ON COLUMN triples.user_id IS 'the owner / creator of the triple';
COMMENT ON COLUMN triples.from_phrase_id IS 'the phrase_id that is linked';
COMMENT ON COLUMN triples.verb_id IS 'the verb_id that defines how the phrases are linked';
COMMENT ON COLUMN triples.to_phrase_id IS 'the phrase_id to which the first phrase is linked';
COMMENT ON COLUMN triples.triple_name IS 'the name used which must be unique within the terms of the user';
COMMENT ON COLUMN triples.name_given IS 'the unique name manually set by the user,which can be null if the generated name should be used';
COMMENT ON COLUMN triples.name_generated IS 'the generated name is saved in the database for database base unique check based on the phrases and verb,which can be overwritten by the given name';
COMMENT ON COLUMN triples.description IS 'text that should be shown to the user in case of mouseover on the triple name';
COMMENT ON COLUMN triples.triple_condition_id IS 'formula_id of a formula with a boolean result; the term is only added if formula result is true';
COMMENT ON COLUMN triples.phrase_type_id IS 'to link coded functionality to words e.g. to exclude measure words from a percent result';
COMMENT ON COLUMN triples.view_id IS 'the default mask for this triple';
COMMENT ON COLUMN triples.values IS 'number of values linked to the word,which gives an indication of the importance';
COMMENT ON COLUMN triples.inactive IS 'true if the word is not yet active e.g. because it is moved to the prime words with a 16 bit id';
COMMENT ON COLUMN triples.code_id IS 'to link coded functionality to a specific triple e.g. to get the values of the system configuration';
COMMENT ON COLUMN triples.excluded IS 'true if a user,but not all,have removed it';
COMMENT ON COLUMN triples.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN triples.protect_id IS 'to protect against unwanted changes';

--
-- table structure to save user specific changes to link one word or triple with a verb to another word or triple
--

CREATE TABLE IF NOT EXISTS user_triples
(
    triple_id           bigint   NOT NULL,
    user_id             bigint   NOT NULL,
    language_id         bigint   NOT NULL DEFAULT 1,
    triple_name         varchar(255)      DEFAULT NULL,
    name_given          varchar(255)      DEFAULT NULL,
    name_generated      varchar(255)      DEFAULT NULL,
    description         text              DEFAULT NULL,
    triple_condition_id bigint            DEFAULT NULL,
    phrase_type_id      bigint            DEFAULT NULL,
    view_id             bigint            DEFAULT NULL,
    values              bigint            DEFAULT NULL,
    excluded            smallint          DEFAULT NULL,
    share_type_id       smallint          DEFAULT NULL,
    protect_id          smallint          DEFAULT NULL
);

COMMENT ON TABLE user_triples IS 'to link one word or triple with a verb to another word or triple';
COMMENT ON COLUMN user_triples.triple_id IS 'with the user_id the internal unique primary index';
COMMENT ON COLUMN user_triples.user_id IS 'the changer of the triple';
COMMENT ON COLUMN user_triples.language_id IS 'the name used which must be unique within the terms of the user';
COMMENT ON COLUMN user_triples.triple_name IS 'the name used which must be unique within the terms of the user';
COMMENT ON COLUMN user_triples.name_given IS 'the unique name manually set by the user,which can be null if the generated name should be used';
COMMENT ON COLUMN user_triples.name_generated IS 'the generated name is saved in the database for database base unique check based on the phrases and verb,which can be overwritten by the given name';
COMMENT ON COLUMN user_triples.description IS 'text that should be shown to the user in case of mouseover on the triple name';
COMMENT ON COLUMN user_triples.triple_condition_id IS 'formula_id of a formula with a boolean result; the term is only added if formula result is true';
COMMENT ON COLUMN user_triples.phrase_type_id IS 'to link coded functionality to words e.g. to exclude measure words from a percent result';
COMMENT ON COLUMN user_triples.view_id IS 'the default mask for this triple';
COMMENT ON COLUMN user_triples.values IS 'number of values linked to the word,which gives an indication of the importance';
COMMENT ON COLUMN user_triples.excluded IS 'true if a user,but not all,have removed it';
COMMENT ON COLUMN user_triples.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_triples.protect_id IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure for the actual status of tables for a phrase
--

CREATE TABLE IF NOT EXISTS phrase_table_status
(
    phrase_table_status_id BIGSERIAL PRIMARY KEY,
    type_name     varchar(255)     NOT NULL,
    code_id       varchar(255) DEFAULT NULL,
    description   text         DEFAULT NULL
);

COMMENT ON TABLE phrase_table_status IS 'for the actual status of tables for a phrase';
COMMENT ON COLUMN phrase_table_status.phrase_table_status_id IS 'the internal unique primary index';
COMMENT ON COLUMN phrase_table_status.type_name IS 'the unique type name as shown to the user and used for the selection';
COMMENT ON COLUMN phrase_table_status.code_id IS 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration';
COMMENT ON COLUMN phrase_table_status.description IS 'text to explain the type to the user as a tooltip; to be replaced by a language form entry';

-- --------------------------------------------------------

--
-- table structure remember which phrases are stored in which table and pod
--

CREATE TABLE IF NOT EXISTS phrase_tables
(
    phrase_table_id BIGSERIAL PRIMARY KEY,
    phrase_id              bigint NOT NULL,
    pod_id                 bigint NOT NULL,
    phrase_table_status_id bigint NOT NULL
);

COMMENT ON TABLE phrase_tables IS 'remember which phrases are stored in which table and pod';
COMMENT ON COLUMN phrase_tables.phrase_table_id IS 'the internal unique primary index';
COMMENT ON COLUMN phrase_tables.phrase_id IS 'the values and results of this phrase are primary stored in dynamic tables on the given pod';
COMMENT ON COLUMN phrase_tables.pod_id IS 'the primary pod where the values and results related to this phrase saved';

-- --------------------------------------------------------

--
-- table structure to add a user given name using a 512-bit group id index for up to 16 32-bit phrase ids including the order
--

CREATE TABLE IF NOT EXISTS groups
(
    group_id    char(112) PRIMARY KEY,
    user_id     bigint    DEFAULT NULL,
    group_name  text      DEFAULT NULL,
    description text      DEFAULT NULL
);

COMMENT ON TABLE groups IS 'to add a user given name using a 512-bit group id index for up to 16 32-bit phrase ids including the order';
COMMENT ON COLUMN groups.group_id IS 'the 512-bit prime index to find the group';
COMMENT ON COLUMN groups.user_id IS 'the owner / creator of the group';
COMMENT ON COLUMN groups.group_name IS 'the user specific group name which can contain the phrase names in a different order to display the group (does not need to be unique)';
COMMENT ON COLUMN groups.description IS 'the user specific description for mouse over helps';

--
-- table structure to save user specific changes to add a user given name using a 512-bit group id index for up to 16 32-bit phrase ids including the order
--

CREATE TABLE IF NOT EXISTS user_groups
(
    group_id    char(112)     NOT NULL,
    user_id     bigint        NOT NULL,
    group_name  text      DEFAULT NULL,
    description text      DEFAULT NULL
);

COMMENT ON TABLE user_groups IS 'to add a user given name using a 512-bit group id index for up to 16 32-bit phrase ids including the order';
COMMENT ON COLUMN user_groups.group_id IS 'the 512-bit prime index to find the user group';
COMMENT ON COLUMN user_groups.user_id IS 'the changer of the group';
COMMENT ON COLUMN user_groups.group_name IS 'the user specific group name which can contain the phrase names in a different order to display the group (does not need to be unique)';
COMMENT ON COLUMN user_groups.description IS 'the user specific description for mouse over helps';

--
-- table structure to add a user given name using a 64-bit group id index for up to four 16-bit phrase ids including the order
--

CREATE TABLE IF NOT EXISTS groups_prime
(
    group_id    bigint    PRIMARY KEY,
    user_id     bigint    DEFAULT NULL,
    group_name  text      DEFAULT NULL,
    description text      DEFAULT NULL
);

COMMENT ON TABLE groups_prime IS 'to add a user given name using a 64-bit group id index for up to four 16-bit phrase ids including the order';
COMMENT ON COLUMN groups_prime.group_id IS 'the 64-bit prime index to find the group';
COMMENT ON COLUMN groups_prime.user_id IS 'the owner / creator of the group';
COMMENT ON COLUMN groups_prime.group_name IS 'the user specific group name which can contain the phrase names in a different order to display the group (does not need to be unique)';
COMMENT ON COLUMN groups_prime.description IS 'the user specific description for mouse over helps';

--
-- table structure to save user specific changes to add a user given name using a 64-bit group id index for up to four 16-bit phrase ids including the order
--

CREATE TABLE IF NOT EXISTS user_groups_prime
(
    group_id    bigint        NOT NULL,
    user_id     bigint        NOT NULL,
    group_name  text      DEFAULT NULL,
    description text      DEFAULT NULL
);

COMMENT ON TABLE user_groups_prime IS 'to add a user given name using a 64-bit group id index for up to four 16-bit phrase ids including the order';
COMMENT ON COLUMN user_groups_prime.group_id IS 'the 64-bit prime index to find the user group';
COMMENT ON COLUMN user_groups_prime.user_id IS 'the changer of the group';
COMMENT ON COLUMN user_groups_prime.group_name IS 'the user specific group name which can contain the phrase names in a different order to display the group (does not need to be unique)';
COMMENT ON COLUMN user_groups_prime.description IS 'the user specific description for mouse over helps';

--
-- table structure to add a user given name using a group id index with a variable length for more than 16 32-bit phrase ids including the order
--

CREATE TABLE IF NOT EXISTS groups_big
(
    group_id    text      PRIMARY KEY,
    user_id     bigint    DEFAULT NULL,
    group_name  text      DEFAULT NULL,
    description text      DEFAULT NULL
);

COMMENT ON TABLE groups_big IS 'to add a user given name using a group id index with a variable length for more than 16 32-bit phrase ids including the order';
COMMENT ON COLUMN groups_big.group_id IS 'the variable text index to find group';
COMMENT ON COLUMN groups_big.user_id IS 'the owner / creator of the group';
COMMENT ON COLUMN groups_big.group_name IS 'the user specific group name which can contain the phrase names in a different order to display the group (does not need to be unique)';
COMMENT ON COLUMN groups_big.description IS 'the user specific description for mouse over helps';

--
-- table structure to save user specific changes to add a user given name using a group id index with a variable length for more than 16 32-bit phrase ids including the order
--

CREATE TABLE IF NOT EXISTS user_groups_big
(
    group_id    text          NOT NULL,
    user_id     bigint        NOT NULL,
    group_name  text      DEFAULT NULL,
    description text      DEFAULT NULL
);

COMMENT ON TABLE user_groups_big IS 'to add a user given name using a group id index with a variable length for more than 16 32-bit phrase ids including the order';
COMMENT ON COLUMN user_groups_big.group_id IS 'the text index for more than 16 phrases to find the group';
COMMENT ON COLUMN user_groups_big.user_id IS 'the changer of the group';
COMMENT ON COLUMN user_groups_big.group_name IS 'the user specific group name which can contain the phrase names in a different order to display the group (does not need to be unique)';
COMMENT ON COLUMN user_groups_big.description IS 'the user specific description for mouse over helps';

-- --------------------------------------------------------

--
-- table structure to link predefined behaviour to a source
--

CREATE TABLE IF NOT EXISTS source_types
(
    source_type_id BIGSERIAL PRIMARY KEY,
    type_name      varchar(255) NOT NULL,
    code_id        varchar(255) DEFAULT NULL,
    description    text         DEFAULT NULL
);

COMMENT ON TABLE source_types IS 'to link predefined behaviour to a source';
COMMENT ON COLUMN source_types.source_type_id IS 'the internal unique primary index';
COMMENT ON COLUMN source_types.type_name IS 'the unique type name as shown to the user and used for the selection';
COMMENT ON COLUMN source_types.code_id IS 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration';
COMMENT ON COLUMN source_types.description IS 'text to explain the type to the user as a tooltip; to be replaced by a language form entry';

-- --------------------------------------------------------

--
-- table structure for the original sources for the numeric, time and geo values
--

CREATE TABLE IF NOT EXISTS sources
(
    source_id      BIGSERIAL    PRIMARY KEY,
    user_id        bigint       DEFAULT NULL,
    source_name    varchar(255)     NOT NULL,
    description    text         DEFAULT NULL,
    source_type_id bigint       DEFAULT NULL,
    url            text         DEFAULT NULL,
    code_id        varchar(100) DEFAULT NULL,
    excluded       smallint     DEFAULT NULL,
    share_type_id  smallint     DEFAULT NULL,
    protect_id     smallint     DEFAULT NULL
);

COMMENT ON TABLE sources                 IS 'for the original sources for the numeric, time and geo values';
COMMENT ON COLUMN sources.source_id      IS 'the internal unique primary index';
COMMENT ON COLUMN sources.user_id        IS 'the owner / creator of the source';
COMMENT ON COLUMN sources.source_name    IS 'the unique name of the source used e.g. as the primary search key';
COMMENT ON COLUMN sources.description    IS 'the user specific description of the source for mouse over helps';
COMMENT ON COLUMN sources.source_type_id IS 'link to the source type';
COMMENT ON COLUMN sources.url            IS 'the url of the source';
COMMENT ON COLUMN sources.code_id        IS 'to select sources used by this program';
COMMENT ON COLUMN sources.excluded       IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN sources.share_type_id  IS 'to restrict the access';
COMMENT ON COLUMN sources.protect_id     IS 'to protect against unwanted changes';

--
-- table structure to save user specific changes for the original sources for the numeric, time and geo values
--

CREATE TABLE IF NOT EXISTS user_sources
(
    source_id      bigint           NOT NULL,
    user_id        bigint           NOT NULL,
    source_name    varchar(255) DEFAULT NULL,
    description    text         DEFAULT NULL,
    source_type_id bigint       DEFAULT NULL,
    url            text         DEFAULT NULL,
    code_id        varchar(100) DEFAULT NULL,
    excluded       smallint     DEFAULT NULL,
    share_type_id  smallint     DEFAULT NULL,
    protect_id     smallint     DEFAULT NULL
);

COMMENT ON TABLE user_sources                 IS 'for the original sources for the numeric, time and geo values';
COMMENT ON COLUMN user_sources.source_id      IS 'with the user_id the internal unique primary index';
COMMENT ON COLUMN user_sources.user_id        IS 'the changer of the source';
COMMENT ON COLUMN user_sources.source_name    IS 'the unique name of the source used e.g. as the primary search key';
COMMENT ON COLUMN user_sources.description    IS 'the user specific description of the source for mouse over helps';
COMMENT ON COLUMN user_sources.source_type_id IS 'link to the source type';
COMMENT ON COLUMN user_sources.url            IS 'the url of the source';
COMMENT ON COLUMN user_sources.code_id        IS 'to select sources used by this program';
COMMENT ON COLUMN user_sources.excluded       IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_sources.share_type_id  IS 'to restrict the access';
COMMENT ON COLUMN user_sources.protect_id     IS 'to protect against unwanted changes';


--
-- table structure for table source_values
-- TODO review
--

CREATE TABLE IF NOT EXISTS source_values
(
    group_id     BIGSERIAL PRIMARY KEY,
    source_id    bigint           NOT NULL,
    user_id      bigint           NOT NULL,
    source_value double precision NOT NULL
);

COMMENT ON TABLE source_values IS 'one user can add different value, which should be the same, but are different';

--
-- table structure for table import_source
--
-- TODO review
--

CREATE TABLE IF NOT EXISTS import_source
(
    import_source_id BIGSERIAL PRIMARY KEY,
    name             varchar(100) NOT NULL,
    import_type      bigint       NOT NULL,
    word_id          bigint       NOT NULL
);

COMMENT ON TABLE import_source IS 'many replace by a term';
COMMENT ON COLUMN import_source.word_id IS 'the name as a term';

--
-- table structure for table source_api
-- TODO check if used and needed
--

CREATE TABLE IF NOT EXISTS source_api
(
    source_api_id       BIGSERIAL PRIMARY KEY,
    source_api_name     varchar(200) NOT NULL,
    open_api_definition text DEFAULT NULL
);

--
-- table structure for table source_api_user
-- TODO check if used and needed
--

CREATE TABLE IF NOT EXISTS source_api_user
(
    source_api_id BIGSERIAL,
    user_id       BIGSERIAL,
    source_api_     varchar(200) NOT NULL,
    open_api_definition text DEFAULT NULL
);

-- --------------------------------------------------------

--
-- table structure for table ref_types
-- TODO generate
--

CREATE TABLE IF NOT EXISTS ref_types
(
    ref_type_id BIGSERIAL PRIMARY KEY,
    type_name   varchar(200) NOT NULL,
    code_id     varchar(100) NOT NULL,
    description text         NOT NULL,
    base_url    text         NOT NULL
);

--
-- table structure for table refs
-- TODO generate
--

CREATE TABLE IF NOT EXISTS refs
(
    ref_id       BIGSERIAL PRIMARY KEY,
    user_id      bigint       DEFAULT NULL,
    phrase_id    bigint       NOT NULL,
    external_key varchar(250) NOT NULL,
    ref_type_id  bigint       NOT NULL,
    source_id    bigint       DEFAULT NULL,
    url          text         DEFAULT NULL,
    description  text         DEFAULT NULL,
    excluded     smallint     DEFAULT NULL
);

--
-- table structure for table user_refs
-- TODO generate
--

CREATE TABLE IF NOT EXISTS user_refs
(
    ref_id         bigint NOT NULL,
    user_id        bigint NOT NULL,
    url            text         DEFAULT NULL,
    description    text         DEFAULT NULL,
    excluded       smallint     DEFAULT NULL
);


-- --------------------------------------------------------

--
-- table structure for public unprotected numeric values related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS values_standard_prime
(
    phrase_id_1   smallint         NOT NULL,
    phrase_id_2   smallint         DEFAULT 0,
    phrase_id_3   smallint         DEFAULT 0,
    phrase_id_4   smallint         DEFAULT 0,
    numeric_value double precision NOT NULL,
    source_id     bigint           DEFAULT NULL
);

COMMENT ON TABLE values_standard_prime                IS 'for public unprotected numeric values related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN values_standard_prime.phrase_id_1   IS 'phrase id that is part of the prime key for a numeric value';
COMMENT ON COLUMN values_standard_prime.phrase_id_2   IS 'phrase id that is part of the prime key for a numeric value';
COMMENT ON COLUMN values_standard_prime.phrase_id_3   IS 'phrase id that is part of the prime key for a numeric value';
COMMENT ON COLUMN values_standard_prime.phrase_id_4   IS 'phrase id that is part of the prime key for a numeric value';
COMMENT ON COLUMN values_standard_prime.numeric_value IS 'the numeric value given by the user';
COMMENT ON COLUMN values_standard_prime.source_id     IS 'the source of the value as given by the user';

--
-- table structure for public unprotected numeric values that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS values_standard
(
    group_id      char(112)        PRIMARY KEY,
    numeric_value double precision NOT NULL,
    source_id     bigint           DEFAULT NULL
);

COMMENT ON TABLE values_standard                IS 'for public unprotected numeric values that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN values_standard.group_id      IS 'the 512-bit prime index to find the numeric value';
COMMENT ON COLUMN values_standard.numeric_value IS 'the numeric value given by the user';
COMMENT ON COLUMN values_standard.source_id     IS 'the source of the value as given by the user';

-- --------------------------------------------------------

--
-- table structure for numeric values related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS values
(
    group_id        char(112)        PRIMARY KEY,
    numeric_value   double precision NOT NULL,
    source_id       bigint           DEFAULT NULL,
    last_update     timestamp        DEFAULT NULL,
    user_id         bigint           DEFAULT NULL,
    excluded        smallint         DEFAULT NULL,
    share_type_id   smallint         DEFAULT NULL,
    protect_id      smallint         DEFAULT NULL
);

COMMENT ON TABLE values                IS 'for numeric values related to up to 16 phrases';
COMMENT ON COLUMN values.group_id      IS 'the 512-bit prime index to find the numeric value';
COMMENT ON COLUMN values.numeric_value IS 'the numeric value given by the user';
COMMENT ON COLUMN values.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN values.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN values.user_id       IS 'the owner / creator of the value';
COMMENT ON COLUMN values.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN values.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN values.protect_id    IS 'to protect against unwanted changes';

--
-- table structure for user specific changes of numeric values related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS user_values
(
    group_id      char(112)        NOT NULL,
    user_id       bigint           NOT NULL,
    numeric_value double precision DEFAULT NULL,
    source_id     bigint           DEFAULT NULL,
    last_update   timestamp        DEFAULT NULL,
    excluded      smallint         DEFAULT NULL,
    share_type_id smallint         DEFAULT NULL,
    protect_id    smallint         DEFAULT NULL
);

COMMENT ON TABLE user_values                IS 'for user specific changes of numeric values related to up to 16 phrases';
COMMENT ON COLUMN user_values.group_id      IS 'the 512-bit prime index to find the user numeric value';
COMMENT ON COLUMN user_values.user_id       IS 'the changer of the numeric value';
COMMENT ON COLUMN user_values.numeric_value IS 'the user specific numeric value change';
COMMENT ON COLUMN user_values.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN user_values.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_values.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_values.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_values.protect_id    IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure for the most often requested numeric values related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS values_prime
(
    phrase_id_1     smallint         NOT NULL,
    phrase_id_2     smallint         DEFAULT 0,
    phrase_id_3     smallint         DEFAULT 0,
    phrase_id_4     smallint         DEFAULT 0,
    numeric_value   double precision NOT NULL,
    source_id       bigint           DEFAULT NULL,
    last_update     timestamp        DEFAULT NULL,
    user_id         bigint           DEFAULT NULL,
    excluded        smallint         DEFAULT NULL,
    share_type_id   smallint         DEFAULT NULL,
    protect_id      smallint         DEFAULT NULL
);

COMMENT ON TABLE values_prime                IS 'for the most often requested numeric values related up to four prime phrase';
COMMENT ON COLUMN values_prime.phrase_id_1   IS 'phrase id that is part of the prime key for a numeric value';
COMMENT ON COLUMN values_prime.phrase_id_2   IS 'phrase id that is part of the prime key for a numeric value';
COMMENT ON COLUMN values_prime.phrase_id_3   IS 'phrase id that is part of the prime key for a numeric value';
COMMENT ON COLUMN values_prime.phrase_id_4   IS 'phrase id that is part of the prime key for a numeric value';
COMMENT ON COLUMN values_prime.numeric_value IS 'the numeric value given by the user';
COMMENT ON COLUMN values_prime.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN values_prime.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN values_prime.user_id       IS 'the owner / creator of the value';
COMMENT ON COLUMN values_prime.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN values_prime.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN values_prime.protect_id    IS 'to protect against unwanted changes';

--
-- table structure to store the user specific changes for the most often requested numeric values related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS user_values_prime
(
    phrase_id_1     smallint         NOT NULL,
    phrase_id_2     smallint         DEFAULT 0,
    phrase_id_3     smallint         DEFAULT 0,
    phrase_id_4     smallint         DEFAULT 0,
    user_id         bigint           NOT NULL,
    numeric_value   double precision DEFAULT NULL,
    source_id       bigint           DEFAULT NULL,
    last_update     timestamp        DEFAULT NULL,
    excluded        smallint         DEFAULT NULL,
    share_type_id   smallint         DEFAULT NULL,
    protect_id      smallint         DEFAULT NULL
);

COMMENT ON TABLE user_values_prime                IS 'to store the user specific changes for the most often requested numeric values related up to four prime phrase';
COMMENT ON COLUMN user_values_prime.phrase_id_1   IS 'phrase id that is with the user id part of the prime key for a numeric value';
COMMENT ON COLUMN user_values_prime.phrase_id_2   IS 'phrase id that is with the user id part of the prime key for a numeric value';
COMMENT ON COLUMN user_values_prime.phrase_id_3   IS 'phrase id that is with the user id part of the prime key for a numeric value';
COMMENT ON COLUMN user_values_prime.phrase_id_4   IS 'phrase id that is with the user id part of the prime key for a numeric value';
COMMENT ON COLUMN user_values_prime.user_id       IS 'the changer of the numeric value';
COMMENT ON COLUMN user_values_prime.numeric_value IS 'the user specific numeric value change';
COMMENT ON COLUMN user_values_prime.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN user_values_prime.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_values_prime.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_values_prime.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_values_prime.protect_id    IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure for numeric values related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS values_big
(
    group_id        text             PRIMARY KEY,
    numeric_value   double precision NOT NULL,
    source_id       bigint           DEFAULT NULL,
    last_update     timestamp        DEFAULT NULL,
    user_id         bigint           DEFAULT NULL,
    excluded        smallint         DEFAULT NULL,
    share_type_id   smallint         DEFAULT NULL,
    protect_id      smallint         DEFAULT NULL
);

COMMENT ON TABLE values_big                IS 'for numeric values related to more than 16 phrases';
COMMENT ON COLUMN values_big.group_id      IS 'the variable text index to find numeric value';
COMMENT ON COLUMN values_big.numeric_value IS 'the numeric value given by the user';
COMMENT ON COLUMN values_big.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN values_big.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN values_big.user_id       IS 'the owner / creator of the value';
COMMENT ON COLUMN values_big.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN values_big.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN values_big.protect_id    IS 'to protect against unwanted changes';

--
-- table structure to store the user specific changes of numeric values related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS user_values_big
(
    group_id        text             NOT NULL,
    user_id         bigint           NOT NULL,
    numeric_value   double precision DEFAULT NULL,
    source_id       bigint           DEFAULT NULL,
    last_update     timestamp        DEFAULT NULL,
    excluded        smallint         DEFAULT NULL,
    share_type_id   smallint         DEFAULT NULL,
    protect_id      smallint         DEFAULT NULL
);

COMMENT ON TABLE user_values_big                IS 'to store the user specific changes of numeric values related to more than 16 phrases';
COMMENT ON COLUMN user_values_big.group_id      IS 'the text index for more than 16 phrases to find the numeric value';
COMMENT ON COLUMN user_values_big.user_id       IS 'the changer of the numeric value';
COMMENT ON COLUMN user_values_big.numeric_value IS 'the user specific numeric value change';
COMMENT ON COLUMN user_values_big.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN user_values_big.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_values_big.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_values_big.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_values_big.protect_id    IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure for public unprotected text values related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS values_text_standard_prime
(
    phrase_id_1 smallint  NOT NULL,
    phrase_id_2 smallint  DEFAULT 0,
    phrase_id_3 smallint  DEFAULT 0,
    phrase_id_4 smallint  DEFAULT 0,
    text_value  text      NOT NULL,
    source_id   bigint    DEFAULT NULL
);

COMMENT ON TABLE values_text_standard_prime             IS 'for public unprotected text values related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN values_text_standard_prime.phrase_id_1   IS 'phrase id that is part of the prime key for a text value';
COMMENT ON COLUMN values_text_standard_prime.phrase_id_2   IS 'phrase id that is part of the prime key for a text value';
COMMENT ON COLUMN values_text_standard_prime.phrase_id_3   IS 'phrase id that is part of the prime key for a text value';
COMMENT ON COLUMN values_text_standard_prime.phrase_id_4   IS 'phrase id that is part of the prime key for a text value';
COMMENT ON COLUMN values_text_standard_prime.text_value IS 'the text value given by the user';
COMMENT ON COLUMN values_text_standard_prime.source_id  IS 'the source of the value as given by the user';

--
-- table structure for public unprotected text values that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS values_text_standard
(
    group_id   char(112) PRIMARY KEY,
    text_value text      NOT NULL,
    source_id  bigint    DEFAULT NULL
);

COMMENT ON TABLE values_text_standard             IS 'for public unprotected text values that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN values_text_standard.group_id   IS 'the 512-bit prime index to find the text value';
COMMENT ON COLUMN values_text_standard.text_value IS 'the text value given by the user';
COMMENT ON COLUMN values_text_standard.source_id  IS 'the source of the value as given by the user';

-- --------------------------------------------------------

--
-- table structure for text values related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS values_text
(
    group_id      char(112) PRIMARY KEY,
    text_value    text      NOT NULL,
    source_id     bigint    DEFAULT NULL,
    last_update   timestamp DEFAULT NULL,
    user_id       bigint    DEFAULT NULL,
    excluded      smallint  DEFAULT NULL,
    share_type_id smallint  DEFAULT NULL,
    protect_id    smallint  DEFAULT NULL
);

COMMENT ON TABLE values_text                IS 'for text values related to up to 16 phrases';
COMMENT ON COLUMN values_text.group_id      IS 'the 512-bit prime index to find the text value';
COMMENT ON COLUMN values_text.text_value    IS 'the text value given by the user';
COMMENT ON COLUMN values_text.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN values_text.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN values_text.user_id       IS 'the owner / creator of the value';
COMMENT ON COLUMN values_text.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN values_text.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN values_text.protect_id    IS 'to protect against unwanted changes';

--
-- table structure for user specific changes of text values related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS user_values_text
(
    group_id      char(112) NOT NULL,
    user_id       bigint    NOT NULL,
    text_value    text      DEFAULT NULL,
    source_id     bigint    DEFAULT NULL,
    last_update   timestamp DEFAULT NULL,
    excluded      smallint  DEFAULT NULL,
    share_type_id smallint  DEFAULT NULL,
    protect_id    smallint  DEFAULT NULL
);

COMMENT ON TABLE user_values_text                IS 'for user specific changes of text values related to up to 16 phrases';
COMMENT ON COLUMN user_values_text.group_id      IS 'the 512-bit prime index to find the user text value';
COMMENT ON COLUMN user_values_text.user_id       IS 'the changer of the text value';
COMMENT ON COLUMN user_values_text.text_value    IS 'the user specific text value change';
COMMENT ON COLUMN user_values_text.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN user_values_text.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_values_text.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_values_text.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_values_text.protect_id    IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure for the most often requested text values related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS values_text_prime
(
    phrase_id_1   smallint  NOT NULL,
    phrase_id_2   smallint  DEFAULT 0,
    phrase_id_3   smallint  DEFAULT 0,
    phrase_id_4   smallint  DEFAULT 0,
    text_value    text      NOT NULL,
    source_id     bigint    DEFAULT NULL,
    last_update   timestamp DEFAULT NULL,
    user_id       bigint    DEFAULT NULL,
    excluded      smallint  DEFAULT NULL,
    share_type_id smallint  DEFAULT NULL,
    protect_id    smallint  DEFAULT NULL
);

COMMENT ON TABLE values_text_prime                IS 'for the most often requested text values related up to four prime phrase';
COMMENT ON COLUMN values_text_prime.phrase_id_1   IS 'phrase id that is part of the prime key for a text value';
COMMENT ON COLUMN values_text_prime.phrase_id_2   IS 'phrase id that is part of the prime key for a text value';
COMMENT ON COLUMN values_text_prime.phrase_id_3   IS 'phrase id that is part of the prime key for a text value';
COMMENT ON COLUMN values_text_prime.phrase_id_4   IS 'phrase id that is part of the prime key for a text value';
COMMENT ON COLUMN values_text_prime.text_value    IS 'the text value given by the user';
COMMENT ON COLUMN values_text_prime.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN values_text_prime.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN values_text_prime.user_id       IS 'the owner / creator of the value';
COMMENT ON COLUMN values_text_prime.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN values_text_prime.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN values_text_prime.protect_id    IS 'to protect against unwanted changes';

--
-- table structure to store the user specific changes for the most often requested text values related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS user_values_text_prime
(
    phrase_id_1   smallint  NOT NULL,
    phrase_id_2   smallint  DEFAULT 0,
    phrase_id_3   smallint  DEFAULT 0,
    phrase_id_4   smallint  DEFAULT 0,
    user_id       bigint    NOT NULL,
    text_value    text      DEFAULT NULL,
    source_id     bigint    DEFAULT NULL,
    last_update   timestamp DEFAULT NULL,
    excluded      smallint  DEFAULT NULL,
    share_type_id smallint  DEFAULT NULL,
    protect_id    smallint  DEFAULT NULL
);

COMMENT ON TABLE user_values_text_prime                IS 'to store the user specific changes for the most often requested text values related up to four prime phrase';
COMMENT ON COLUMN user_values_text_prime.phrase_id_1   IS 'phrase id that is with the user id part of the prime key for a text value';
COMMENT ON COLUMN user_values_text_prime.phrase_id_2   IS 'phrase id that is with the user id part of the prime key for a text value';
COMMENT ON COLUMN user_values_text_prime.phrase_id_3   IS 'phrase id that is with the user id part of the prime key for a text value';
COMMENT ON COLUMN user_values_text_prime.phrase_id_4   IS 'phrase id that is with the user id part of the prime key for a text value';
COMMENT ON COLUMN user_values_text_prime.user_id       IS 'the changer of the text value';
COMMENT ON COLUMN user_values_text_prime.text_value    IS 'the user specific text value change';
COMMENT ON COLUMN user_values_text_prime.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN user_values_text_prime.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_values_text_prime.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_values_text_prime.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_values_text_prime.protect_id    IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure for text values related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS values_text_big
(
    group_id      text      PRIMARY KEY,
    text_value    text      NOT NULL,
    source_id     bigint    DEFAULT NULL,
    last_update   timestamp DEFAULT NULL,
    user_id       bigint    DEFAULT NULL,
    excluded      smallint  DEFAULT NULL,
    share_type_id smallint  DEFAULT NULL,
    protect_id    smallint  DEFAULT NULL
);

COMMENT ON TABLE values_text_big                IS 'for text values related to more than 16 phrases';
COMMENT ON COLUMN values_text_big.group_id      IS 'the variable text index to find text value';
COMMENT ON COLUMN values_text_big.text_value    IS 'the text value given by the user';
COMMENT ON COLUMN values_text_big.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN values_text_big.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN values_text_big.user_id       IS 'the owner / creator of the value';
COMMENT ON COLUMN values_text_big.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN values_text_big.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN values_text_big.protect_id    IS 'to protect against unwanted changes';

--
-- table structure to store the user specific changes of text values related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS user_values_text_big
(
    group_id      text      NOT NULL,
    user_id       bigint    NOT NULL,
    text_value    text      DEFAULT NULL,
    source_id     bigint    DEFAULT NULL,
    last_update   timestamp DEFAULT NULL,
    excluded      smallint  DEFAULT NULL,
    share_type_id smallint  DEFAULT NULL,
    protect_id    smallint  DEFAULT NULL
);

COMMENT ON TABLE user_values_text_big                IS 'to store the user specific changes of text values related to more than 16 phrases';
COMMENT ON COLUMN user_values_text_big.group_id      IS 'the text index for more than 16 phrases to find the text value';
COMMENT ON COLUMN user_values_text_big.user_id       IS 'the changer of the text value';
COMMENT ON COLUMN user_values_text_big.text_value    IS 'the user specific text value change';
COMMENT ON COLUMN user_values_text_big.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN user_values_text_big.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_values_text_big.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_values_text_big.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_values_text_big.protect_id    IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure for public unprotected time values related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS values_time_standard_prime
(
    phrase_id_1 smallint  NOT NULL,
    phrase_id_2 smallint  DEFAULT 0,
    phrase_id_3 smallint  DEFAULT 0,
    phrase_id_4 smallint  DEFAULT 0,
    time_value  timestamp NOT NULL,
    source_id   bigint    DEFAULT NULL
);

COMMENT ON TABLE values_time_standard_prime             IS 'for public unprotected time values related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN values_time_standard_prime.phrase_id_1   IS 'phrase id that is part of the prime key for a time value';
COMMENT ON COLUMN values_time_standard_prime.phrase_id_2   IS 'phrase id that is part of the prime key for a time value';
COMMENT ON COLUMN values_time_standard_prime.phrase_id_3   IS 'phrase id that is part of the prime key for a time value';
COMMENT ON COLUMN values_time_standard_prime.phrase_id_4   IS 'phrase id that is part of the prime key for a time value';
COMMENT ON COLUMN values_time_standard_prime.time_value IS 'the timestamp given by the user';
COMMENT ON COLUMN values_time_standard_prime.source_id  IS 'the source of the value as given by the user';

--
-- table structure for public unprotected time values that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS values_time_standard
(
    group_id   char(112) PRIMARY KEY,
    time_value timestamp NOT NULL,
    source_id  bigint    DEFAULT NULL
);

COMMENT ON TABLE values_time_standard             IS 'for public unprotected time values that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN values_time_standard.group_id   IS 'the 512-bit prime index to find the time value';
COMMENT ON COLUMN values_time_standard.time_value IS 'the timestamp given by the user';
COMMENT ON COLUMN values_time_standard.source_id  IS 'the source of the value as given by the user';

-- --------------------------------------------------------

--
-- table structure for time values related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS values_time
(
    group_id      char(112) PRIMARY KEY,
    time_value    timestamp NOT NULL,
    source_id     bigint    DEFAULT NULL,
    last_update   timestamp DEFAULT NULL,
    user_id       bigint    DEFAULT NULL,
    excluded      smallint  DEFAULT NULL,
    share_type_id smallint  DEFAULT NULL,
    protect_id    smallint  DEFAULT NULL
);

COMMENT ON TABLE values_time                IS 'for time values related to up to 16 phrases';
COMMENT ON COLUMN values_time.group_id      IS 'the 512-bit prime index to find the time value';
COMMENT ON COLUMN values_time.time_value    IS 'the timestamp given by the user';
COMMENT ON COLUMN values_time.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN values_time.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN values_time.user_id       IS 'the owner / creator of the value';
COMMENT ON COLUMN values_time.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN values_time.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN values_time.protect_id    IS 'to protect against unwanted changes';

--
-- table structure for user specific changes of time values related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS user_values_time
(
    group_id      char(112) NOT NULL,
    user_id       bigint    NOT NULL,
    time_value    timestamp DEFAULT NULL,
    source_id     bigint    DEFAULT NULL,
    last_update   timestamp DEFAULT NULL,
    excluded      smallint  DEFAULT NULL,
    share_type_id smallint  DEFAULT NULL,
    protect_id    smallint  DEFAULT NULL
);

COMMENT ON TABLE user_values_time                IS 'for user specific changes of time values related to up to 16 phrases';
COMMENT ON COLUMN user_values_time.group_id      IS 'the 512-bit prime index to find the user time value';
COMMENT ON COLUMN user_values_time.user_id       IS 'the changer of the time value';
COMMENT ON COLUMN user_values_time.time_value    IS 'the user specific timestamp change';
COMMENT ON COLUMN user_values_time.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN user_values_time.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_values_time.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_values_time.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_values_time.protect_id    IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure for the most often requested time values related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS values_time_prime
(
    phrase_id_1   smallint  NOT NULL,
    phrase_id_2   smallint  DEFAULT 0,
    phrase_id_3   smallint  DEFAULT 0,
    phrase_id_4   smallint  DEFAULT 0,
    time_value    timestamp NOT NULL,
    source_id     bigint    DEFAULT NULL,
    last_update   timestamp DEFAULT NULL,
    user_id       bigint    DEFAULT NULL,
    excluded      smallint  DEFAULT NULL,
    share_type_id smallint  DEFAULT NULL,
    protect_id    smallint  DEFAULT NULL
);

COMMENT ON TABLE values_time_prime                IS 'for the most often requested time values related up to four prime phrase';
COMMENT ON COLUMN values_time_prime.phrase_id_1   IS 'phrase id that is part of the prime key for a time value';
COMMENT ON COLUMN values_time_prime.phrase_id_2   IS 'phrase id that is part of the prime key for a time value';
COMMENT ON COLUMN values_time_prime.phrase_id_3   IS 'phrase id that is part of the prime key for a time value';
COMMENT ON COLUMN values_time_prime.phrase_id_4   IS 'phrase id that is part of the prime key for a time value';
COMMENT ON COLUMN values_time_prime.time_value    IS 'the timestamp given by the user';
COMMENT ON COLUMN values_time_prime.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN values_time_prime.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN values_time_prime.user_id       IS 'the owner / creator of the value';
COMMENT ON COLUMN values_time_prime.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN values_time_prime.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN values_time_prime.protect_id    IS 'to protect against unwanted changes';

--
-- table structure to store the user specific changes for the most often requested time values related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS user_values_time_prime
(
    phrase_id_1   smallint  NOT NULL,
    phrase_id_2   smallint  DEFAULT 0,
    phrase_id_3   smallint  DEFAULT 0,
    phrase_id_4   smallint  DEFAULT 0,
    user_id       bigint    NOT NULL,
    time_value    timestamp DEFAULT NULL,
    source_id     bigint    DEFAULT NULL,
    last_update   timestamp DEFAULT NULL,
    excluded      smallint  DEFAULT NULL,
    share_type_id smallint  DEFAULT NULL,
    protect_id    smallint  DEFAULT NULL
);

COMMENT ON TABLE user_values_time_prime                IS 'to store the user specific changes for the most often requested time values related up to four prime phrase';
COMMENT ON COLUMN user_values_time_prime.phrase_id_1   IS 'phrase id that is with the user id part of the prime key for a time value';
COMMENT ON COLUMN user_values_time_prime.phrase_id_2   IS 'phrase id that is with the user id part of the prime key for a time value';
COMMENT ON COLUMN user_values_time_prime.phrase_id_3   IS 'phrase id that is with the user id part of the prime key for a time value';
COMMENT ON COLUMN user_values_time_prime.phrase_id_4   IS 'phrase id that is with the user id part of the prime key for a time value';
COMMENT ON COLUMN user_values_time_prime.user_id       IS 'the changer of the time value';
COMMENT ON COLUMN user_values_time_prime.time_value    IS 'the user specific timestamp change';
COMMENT ON COLUMN user_values_time_prime.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN user_values_time_prime.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_values_time_prime.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_values_time_prime.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_values_time_prime.protect_id    IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure for time values related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS values_time_big
(
    group_id      text      PRIMARY KEY,
    time_value    timestamp NOT NULL,
    source_id     bigint    DEFAULT NULL,
    last_update   timestamp DEFAULT NULL,
    user_id       bigint    DEFAULT NULL,
    excluded      smallint  DEFAULT NULL,
    share_type_id smallint  DEFAULT NULL,
    protect_id    smallint  DEFAULT NULL
);

COMMENT ON TABLE values_time_big                IS 'for time values related to more than 16 phrases';
COMMENT ON COLUMN values_time_big.group_id      IS 'the variable text index to find time value';
COMMENT ON COLUMN values_time_big.time_value    IS 'the timestamp given by the user';
COMMENT ON COLUMN values_time_big.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN values_time_big.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN values_time_big.user_id       IS 'the owner / creator of the value';
COMMENT ON COLUMN values_time_big.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN values_time_big.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN values_time_big.protect_id    IS 'to protect against unwanted changes';

--
-- table structure to store the user specific changes of time values related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS user_values_time_big
(
    group_id      text      NOT NULL,
    user_id       bigint    NOT NULL,
    time_value    timestamp DEFAULT NULL,
    source_id     bigint    DEFAULT NULL,
    last_update   timestamp DEFAULT NULL,
    excluded      smallint  DEFAULT NULL,
    share_type_id smallint  DEFAULT NULL,
    protect_id    smallint  DEFAULT NULL
);

COMMENT ON TABLE user_values_time_big                IS 'to store the user specific changes of time values related to more than 16 phrases';
COMMENT ON COLUMN user_values_time_big.group_id      IS 'the text index for more than 16 phrases to find the time value';
COMMENT ON COLUMN user_values_time_big.user_id       IS 'the changer of the time value';
COMMENT ON COLUMN user_values_time_big.time_value    IS 'the user specific timestamp change';
COMMENT ON COLUMN user_values_time_big.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN user_values_time_big.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_values_time_big.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_values_time_big.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_values_time_big.protect_id    IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure for public unprotected geo values related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS values_geo_standard_prime
(
    phrase_id_1 smallint  NOT NULL,
    phrase_id_2 smallint  DEFAULT 0,
    phrase_id_3 smallint  DEFAULT 0,
    phrase_id_4 smallint  DEFAULT 0,
    geo_value   point     NOT NULL,
    source_id   bigint    DEFAULT NULL
);

COMMENT ON TABLE values_geo_standard_prime             IS 'for public unprotected geo values related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN values_geo_standard_prime.phrase_id_1   IS 'phrase id that is part of the prime key for a geo value';
COMMENT ON COLUMN values_geo_standard_prime.phrase_id_2   IS 'phrase id that is part of the prime key for a geo value';
COMMENT ON COLUMN values_geo_standard_prime.phrase_id_3   IS 'phrase id that is part of the prime key for a geo value';
COMMENT ON COLUMN values_geo_standard_prime.phrase_id_4   IS 'phrase id that is part of the prime key for a geo value';
COMMENT ON COLUMN values_geo_standard_prime.geo_value  IS 'the geolocation given by the user';
COMMENT ON COLUMN values_geo_standard_prime.source_id  IS 'the source of the value as given by the user';

--
-- table structure for public unprotected geo values that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS values_geo_standard
(
    group_id   char(112) PRIMARY KEY,
    geo_value  point     NOT NULL,
    source_id  bigint    DEFAULT NULL
);

COMMENT ON TABLE values_geo_standard             IS 'for public unprotected geo values that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN values_geo_standard.group_id   IS 'the 512-bit prime index to find the geo value';
COMMENT ON COLUMN values_geo_standard.geo_value  IS 'the geolocation given by the user';
COMMENT ON COLUMN values_geo_standard.source_id  IS 'the source of the value as given by the user';

-- --------------------------------------------------------

--
-- table structure for geo values related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS values_geo
(
    group_id      char(112) PRIMARY KEY,
    geo_value     point     NOT NULL,
    source_id     bigint    DEFAULT NULL,
    last_update   timestamp DEFAULT NULL,
    user_id       bigint    DEFAULT NULL,
    excluded      smallint  DEFAULT NULL,
    share_type_id smallint  DEFAULT NULL,
    protect_id    smallint  DEFAULT NULL
);

COMMENT ON TABLE values_geo                IS 'for geo values related to up to 16 phrases';
COMMENT ON COLUMN values_geo.group_id      IS 'the 512-bit prime index to find the geo value';
COMMENT ON COLUMN values_geo.geo_value     IS 'the geolocation given by the user';
COMMENT ON COLUMN values_geo.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN values_geo.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN values_geo.user_id       IS 'the owner / creator of the value';
COMMENT ON COLUMN values_geo.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN values_geo.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN values_geo.protect_id    IS 'to protect against unwanted changes';

--
-- table structure for user specific changes of geo values related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS user_values_geo
(
    group_id      char(112) NOT NULL,
    user_id       bigint    NOT NULL,
    geo_value     point     DEFAULT NULL,
    source_id     bigint    DEFAULT NULL,
    last_update   timestamp DEFAULT NULL,
    excluded      smallint  DEFAULT NULL,
    share_type_id smallint  DEFAULT NULL,
    protect_id    smallint  DEFAULT NULL
);

COMMENT ON TABLE user_values_geo                IS 'for user specific changes of geo values related to up to 16 phrases';
COMMENT ON COLUMN user_values_geo.group_id      IS 'the 512-bit prime index to find the user geo value';
COMMENT ON COLUMN user_values_geo.user_id       IS 'the changer of the geo value';
COMMENT ON COLUMN user_values_geo.geo_value     IS 'the user specific geolocation change';
COMMENT ON COLUMN user_values_geo.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN user_values_geo.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_values_geo.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_values_geo.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_values_geo.protect_id    IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure for the most often requested geo values related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS values_geo_prime
(
    phrase_id_1   smallint  NOT NULL,
    phrase_id_2   smallint  DEFAULT 0,
    phrase_id_3   smallint  DEFAULT 0,
    phrase_id_4   smallint  DEFAULT 0,
    geo_value     point     NOT NULL,
    source_id     bigint    DEFAULT NULL,
    last_update   timestamp DEFAULT NULL,
    user_id       bigint    DEFAULT NULL,
    excluded      smallint  DEFAULT NULL,
    share_type_id smallint  DEFAULT NULL,
    protect_id    smallint  DEFAULT NULL
);

COMMENT ON TABLE values_geo_prime                IS 'for the most often requested geo values related up to four prime phrase';
COMMENT ON COLUMN values_geo_prime.phrase_id_1   IS 'phrase id that is part of the prime key for a geo value';
COMMENT ON COLUMN values_geo_prime.phrase_id_2   IS 'phrase id that is part of the prime key for a geo value';
COMMENT ON COLUMN values_geo_prime.phrase_id_3   IS 'phrase id that is part of the prime key for a geo value';
COMMENT ON COLUMN values_geo_prime.phrase_id_4   IS 'phrase id that is part of the prime key for a geo value';
COMMENT ON COLUMN values_geo_prime.geo_value     IS 'the geolocation given by the user';
COMMENT ON COLUMN values_geo_prime.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN values_geo_prime.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN values_geo_prime.user_id       IS 'the owner / creator of the value';
COMMENT ON COLUMN values_geo_prime.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN values_geo_prime.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN values_geo_prime.protect_id    IS 'to protect against unwanted changes';

--
-- table structure to store the user specific changes for the most often requested geo values related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS user_values_geo_prime
(
    phrase_id_1   smallint  NOT NULL,
    phrase_id_2   smallint  DEFAULT 0,
    phrase_id_3   smallint  DEFAULT 0,
    phrase_id_4   smallint  DEFAULT 0,
    user_id       bigint    NOT NULL,
    geo_value     point     DEFAULT NULL,
    source_id     bigint    DEFAULT NULL,
    last_update   timestamp DEFAULT NULL,
    excluded      smallint  DEFAULT NULL,
    share_type_id smallint  DEFAULT NULL,
    protect_id    smallint  DEFAULT NULL
);

COMMENT ON TABLE user_values_geo_prime                IS 'to store the user specific changes for the most often requested geo values related up to four prime phrase';
COMMENT ON COLUMN user_values_geo_prime.phrase_id_1   IS 'phrase id that is with the user id part of the prime key for a geo value';
COMMENT ON COLUMN user_values_geo_prime.phrase_id_2   IS 'phrase id that is with the user id part of the prime key for a geo value';
COMMENT ON COLUMN user_values_geo_prime.phrase_id_3   IS 'phrase id that is with the user id part of the prime key for a geo value';
COMMENT ON COLUMN user_values_geo_prime.phrase_id_4   IS 'phrase id that is with the user id part of the prime key for a geo value';
COMMENT ON COLUMN user_values_geo_prime.user_id       IS 'the changer of the geo value';
COMMENT ON COLUMN user_values_geo_prime.geo_value     IS 'the user specific geolocation change';
COMMENT ON COLUMN user_values_geo_prime.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN user_values_geo_prime.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_values_geo_prime.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_values_geo_prime.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_values_geo_prime.protect_id    IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure for geo values related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS values_geo_big
(
    group_id      text      PRIMARY KEY,
    geo_value     point     NOT NULL,
    source_id     bigint    DEFAULT NULL,
    last_update   timestamp DEFAULT NULL,
    user_id       bigint    DEFAULT NULL,
    excluded      smallint  DEFAULT NULL,
    share_type_id smallint  DEFAULT NULL,
    protect_id    smallint  DEFAULT NULL
);

COMMENT ON TABLE values_geo_big                IS 'for geo values related to more than 16 phrases';
COMMENT ON COLUMN values_geo_big.group_id      IS 'the variable text index to find geo value';
COMMENT ON COLUMN values_geo_big.geo_value     IS 'the geolocation given by the user';
COMMENT ON COLUMN values_geo_big.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN values_geo_big.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN values_geo_big.user_id       IS 'the owner / creator of the value';
COMMENT ON COLUMN values_geo_big.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN values_geo_big.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN values_geo_big.protect_id    IS 'to protect against unwanted changes';

--
-- table structure to store the user specific changes of geo values related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS user_values_geo_big
(
    group_id      text      NOT NULL,
    user_id       bigint    NOT NULL,
    geo_value     point     DEFAULT NULL,
    source_id     bigint    DEFAULT NULL,
    last_update   timestamp DEFAULT NULL,
    excluded      smallint  DEFAULT NULL,
    share_type_id smallint  DEFAULT NULL,
    protect_id    smallint  DEFAULT NULL
);

COMMENT ON TABLE user_values_geo_big                IS 'to store the user specific changes of geo values related to more than 16 phrases';
COMMENT ON COLUMN user_values_geo_big.group_id      IS 'the text index for more than 16 phrases to find the geo value';
COMMENT ON COLUMN user_values_geo_big.user_id       IS 'the changer of the geo value';
COMMENT ON COLUMN user_values_geo_big.geo_value     IS 'the user specific geolocation change';
COMMENT ON COLUMN user_values_geo_big.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN user_values_geo_big.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_values_geo_big.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_values_geo_big.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_values_geo_big.protect_id    IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure for the header of a list of numbers that differ only by the timestamp
--

CREATE TABLE IF NOT EXISTS value_time_series
(
    value_time_series_id BIGSERIAL      PRIMARY KEY,
    user_id              bigint         NOT     NULL,
    source_id            bigint         DEFAULT NULL,
    group_id             char(112)      NOT NULL,
    excluded             smallint       DEFAULT NULL,
    share_type_id        smallint         DEFAULT NULL,
    protect_id           smallint         NOT     NULL,
    last_update          timestamp NULL DEFAULT NULL
);

COMMENT ON TABLE value_time_series IS 'common parameters for a list of numbers that differ only by the timestamp';
COMMENT ON COLUMN value_time_series.user_id IS 'the owner / creator of the number list';

-- --------------------------------------------------------

--
-- table structure for specific user changes in a of numbers that differ only by the timestamp
--

CREATE TABLE IF NOT EXISTS user_value_time_series
(
    value_time_series_id BIGSERIAL          NOT NULL,
    user_id              bigint             NOT NULL,
    source_id            bigint         DEFAULT NULL,
    excluded             smallint       DEFAULT NULL,
    share_type_id        smallint         DEFAULT NULL,
    protect_id           smallint             NOT NULL,
    last_update          timestamp NULL DEFAULT NULL
);

COMMENT ON TABLE user_value_time_series IS 'common parameters for a list of numbers that differ only by the timestamp';

--
-- table structure for table value_time_series_prime
--

CREATE TABLE IF NOT EXISTS value_time_series_prime
(
    value_time_series_id BIGSERIAL      PRIMARY KEY,
    user_id              bigint         NOT     NULL,
    source_id            bigint         DEFAULT NULL,
    group_id             bigint         NOT NULL,
    excluded             smallint       DEFAULT NULL,
    share_type_id        smallint         DEFAULT NULL,
    protect_id           smallint         NOT     NULL,
    last_update          timestamp NULL DEFAULT NULL
);

COMMENT ON TABLE value_time_series IS 'common parameters for a list of intra-day values';

-- --------------------------------------------------------

--
-- table structure for table value_time_series
--

CREATE TABLE IF NOT EXISTS user_value_time_series_prime
(
    value_time_series_id BIGSERIAL          NOT NULL,
    user_id              bigint             NOT NULL,
    source_id            bigint         DEFAULT NULL,
    excluded             smallint       DEFAULT NULL,
    share_type_id        smallint         DEFAULT NULL,
    protect_id           smallint             NOT NULL,
    last_update          timestamp NULL DEFAULT NULL
);

COMMENT ON TABLE user_value_time_series IS 'common parameters for a user specific list of intra-day values';

-- --------------------------------------------------------

--
-- table structure for table value_ts_data
-- TODO generate
--

CREATE TABLE IF NOT EXISTS value_ts_data
(
    value_time_series_id BIGSERIAL PRIMARY KEY,
    val_time             timestamp NOT NULL,
    number               float     NOT NULL
);

COMMENT ON TABLE value_ts_data IS 'for efficient saving of daily or intra-day values';

-- --------------------------------------------------------

--
-- table structure for table formula_element_types
-- TODO generate type
--

CREATE TABLE IF NOT EXISTS formula_element_types
(
    formula_element_type_id BIGSERIAL     PRIMARY KEY,
    type_name               varchar(200)  NOT NULL,
    code_id                 varchar(100)  DEFAULT NULL,
    description             text
);

--
-- table structure for table formula_elements
-- TODO generate
--

CREATE TABLE IF NOT EXISTS formula_elements
(
    formula_element_id      BIGSERIAL PRIMARY KEY,
    formula_id              bigint NOT NULL,
    user_id                 bigint NOT NULL,
    order_nbr               bigint NOT NULL,
    formula_element_type_id bigint NOT NULL,
    ref_id                  bigint       DEFAULT NULL,
    resolved_text           varchar(200) DEFAULT NULL
);

COMMENT ON TABLE formula_elements IS 'cache for fast update of formula resolved text';
COMMENT ON COLUMN formula_elements.ref_id IS 'either a term, verb or formula id';

-- --------------------------------------------------------

--
-- table structure to assign predefined behaviour to formulas
--

CREATE TABLE IF NOT EXISTS formula_types
(
    formula_type_id BIGSERIAL PRIMARY KEY,
    type_name       varchar(255) NOT NULL,
    code_id         varchar(255) DEFAULT NULL,
    description     text         DEFAULT NULL
);

COMMENT ON TABLE formula_types IS 'to assign predefined behaviour to formulas';
COMMENT ON COLUMN formula_types.formula_type_id IS 'the internal unique primary index';
COMMENT ON COLUMN formula_types.type_name IS 'the unique type name as shown to the user and used for the selection';
COMMENT ON COLUMN formula_types.code_id IS 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration';
COMMENT ON COLUMN formula_types.description IS 'text to explain the type to the user as a tooltip; to be replaced by a language form entry';


-- --------------------------------------------------------

--
-- table structure the mathematical expression to calculate results based on values and results
--

CREATE TABLE IF NOT EXISTS formulas
(
    formula_id BIGSERIAL PRIMARY KEY,
    user_id           bigint   DEFAULT NULL,
    formula_name      varchar(255) NOT NULL,
    formula_text      text         NOT NULL,
    resolved_text     text         NOT NULL,
    description       text     DEFAULT NULL,
    formula_type_id   bigint   DEFAULT NULL,
    all_values_needed smallint DEFAULT NULL,
    last_update       timestamp DEFAULT NULL,
    view_id           bigint   DEFAULT NULL,
    usage             bigint   DEFAULT NULL,
    excluded          smallint DEFAULT NULL,
    share_type_id     smallint DEFAULT NULL,
    protect_id        smallint DEFAULT NULL
);

COMMENT ON TABLE formulas IS 'the mathematical expression to calculate results based on values and results';
COMMENT ON COLUMN formulas.formula_id IS 'the internal unique primary index';
COMMENT ON COLUMN formulas.user_id IS 'the owner / creator of the formula';
COMMENT ON COLUMN formulas.formula_name IS 'the text used to search for formulas that must also be unique for all terms (words,triples,verbs and formulas)';
COMMENT ON COLUMN formulas.formula_text IS 'the internal formula expression with the database references e.g. {f1} for formula with id 1';
COMMENT ON COLUMN formulas.resolved_text IS 'the formula expression in user readable format as shown to the user which can include formatting for better readability';
COMMENT ON COLUMN formulas.description IS 'text to be shown to the user for mouse over; to be replaced by a language form entry';
COMMENT ON COLUMN formulas.formula_type_id IS 'the id of the formula type';
COMMENT ON COLUMN formulas.all_values_needed IS 'the "calculate only if all values used in the formula exist" flag should be converted to "all needed for calculation" instead of just displaying "1"';
COMMENT ON COLUMN formulas.last_update IS 'time of the last calculation relevant update';
COMMENT ON COLUMN formulas.view_id IS 'the default mask for this formula';
COMMENT ON COLUMN formulas.usage IS 'number of results linked to this formula';
COMMENT ON COLUMN formulas.excluded IS 'true if a user,but not all,have removed it';
COMMENT ON COLUMN formulas.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN formulas.protect_id IS 'to protect against unwanted changes';

--
-- table structure to save user specific changes the mathematical expression to calculate results based on values and results
--

CREATE TABLE IF NOT EXISTS user_formulas
(
    formula_id        bigint           NOT NULL,
    user_id           bigint           NOT NULL,
    formula_name      varchar(255) DEFAULT NULL,
    formula_text      text         DEFAULT NULL,
    resolved_text     text         DEFAULT NULL,
    description       text         DEFAULT NULL,
    formula_type_id   bigint       DEFAULT NULL,
    all_values_needed smallint     DEFAULT NULL,
    last_update       timestamp    DEFAULT NULL,
    view_id           bigint       DEFAULT NULL,
    usage             bigint       DEFAULT NULL,
    excluded          smallint     DEFAULT NULL,
    share_type_id     smallint     DEFAULT NULL,
    protect_id        smallint     DEFAULT NULL
);

COMMENT ON TABLE user_formulas IS 'the mathematical expression to calculate results based on values and results';
COMMENT ON COLUMN user_formulas.formula_id IS 'with the user_id the internal unique primary index';
COMMENT ON COLUMN user_formulas.user_id IS 'the changer of the formula';
COMMENT ON COLUMN user_formulas.formula_name IS 'the text used to search for formulas that must also be unique for all terms (words,triples,verbs and formulas)';
COMMENT ON COLUMN user_formulas.formula_text IS 'the internal formula expression with the database references e.g. {f1} for formula with id 1';
COMMENT ON COLUMN user_formulas.resolved_text IS 'the formula expression in user readable format as shown to the user which can include formatting for better readability';
COMMENT ON COLUMN user_formulas.description IS 'text to be shown to the user for mouse over; to be replaced by a language form entry';
COMMENT ON COLUMN user_formulas.formula_type_id IS 'the id of the formula type';
COMMENT ON COLUMN user_formulas.all_values_needed IS 'the "calculate only if all values used in the formula exist" flag should be converted to "all needed for calculation" instead of just displaying "1"';
COMMENT ON COLUMN user_formulas.last_update IS 'time of the last calculation relevant update';
COMMENT ON COLUMN user_formulas.view_id IS 'the default mask for this formula';
COMMENT ON COLUMN user_formulas.usage IS 'number of results linked to this formula';
COMMENT ON COLUMN user_formulas.excluded IS 'true if a user,but not all,have removed it';
COMMENT ON COLUMN user_formulas.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_formulas.protect_id IS 'to protect against unwanted changes';

--
-- table structure for table formula_link_types
-- TODO generate type
--

CREATE TABLE IF NOT EXISTS formula_link_types
(
    formula_link_type_id BIGSERIAL PRIMARY KEY,
    type_name            varchar(200) NOT NULL,
    code_id              varchar(100)          DEFAULT NULL,
    formula_id           bigint       NOT NULL DEFAULT 1,
    phrase_type_id         bigint       NOT NULL,
    link_type_id         bigint       NOT NULL,
    description          text
);

--
-- table structure for table formula_links
-- TODO generate link
--

CREATE TABLE IF NOT EXISTS formula_links
(
    formula_link_id BIGSERIAL PRIMARY KEY,
    user_id         bigint   DEFAULT NULL,
    formula_id      bigint NOT NULL,
    phrase_id       bigint NOT NULL,
    link_type_id    bigint   DEFAULT NULL,
    order_nbr       bigint DEFAULT NULL,
    excluded        smallint DEFAULT NULL
);

COMMENT ON TABLE formula_links IS 'if the term pattern of a value matches this term pattern';

--
-- table structure for table user_formula_links
-- TODO generate link
--

CREATE TABLE IF NOT EXISTS user_formula_links
(
    formula_link_id BIGSERIAL NOT NULL,
    user_id         bigint    NOT NULL,
    link_type_id    bigint    DEFAULT NULL,
    excluded        smallint  DEFAULT NULL
);

COMMENT ON TABLE user_formula_links IS 'if the term pattern of a value matches this term pattern ';


-- --------------------------------------------------------

--
-- table structure to cache the formula public unprotected numeric results related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_standard_prime
(
    formula_id    smallint         NOT NULL,
    phrase_id_1   smallint         NOT NULL,
    phrase_id_2   smallint         DEFAULT 0,
    phrase_id_3   smallint         DEFAULT 0,
    numeric_value double precision NOT NULL
);

COMMENT ON TABLE results_standard_prime                IS 'to cache the formula public unprotected numeric results related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN results_standard_prime.formula_id    IS 'formula id that is part of the prime key for a numeric result';
COMMENT ON COLUMN results_standard_prime.phrase_id_1   IS 'phrase id that is part of the prime key for a numeric result';
COMMENT ON COLUMN results_standard_prime.phrase_id_2   IS 'phrase id that is part of the prime key for a numeric result';
COMMENT ON COLUMN results_standard_prime.phrase_id_3   IS 'phrase id that is part of the prime key for a numeric result';
COMMENT ON COLUMN results_standard_prime.numeric_value IS 'the numeric value given by the user';

--
-- table structure to cache the formula public unprotected numeric results related up to eight prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_standard_main
(
    formula_id    smallint         NOT NULL,
    phrase_id_1   smallint         NOT NULL,
    phrase_id_2   smallint         DEFAULT 0,
    phrase_id_3   smallint         DEFAULT 0,
    phrase_id_4   smallint         DEFAULT 0,
    phrase_id_5   smallint         DEFAULT 0,
    phrase_id_6   smallint         DEFAULT 0,
    phrase_id_7   smallint         DEFAULT 0,
    numeric_value double precision NOT NULL
);

COMMENT ON TABLE results_standard_main                IS 'to cache the formula public unprotected numeric results related up to eight prime phrase that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN results_standard_main.formula_id    IS 'formula id that is part of the prime key for a numeric result';
COMMENT ON COLUMN results_standard_main.phrase_id_1   IS 'phrase id that is part of the prime key for a numeric result';
COMMENT ON COLUMN results_standard_main.phrase_id_2   IS 'phrase id that is part of the prime key for a numeric result';
COMMENT ON COLUMN results_standard_main.phrase_id_3   IS 'phrase id that is part of the prime key for a numeric result';
COMMENT ON COLUMN results_standard_main.phrase_id_4   IS 'phrase id that is part of the prime key for a numeric result';
COMMENT ON COLUMN results_standard_main.phrase_id_5   IS 'phrase id that is part of the prime key for a numeric result';
COMMENT ON COLUMN results_standard_main.phrase_id_6   IS 'phrase id that is part of the prime key for a numeric result';
COMMENT ON COLUMN results_standard_main.phrase_id_7   IS 'phrase id that is part of the prime key for a numeric result';
COMMENT ON COLUMN results_standard_main.numeric_value IS 'the numeric value given by the user';

--
-- table structure to cache the formula public unprotected numeric results that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_standard
(
    group_id      char(112)        PRIMARY KEY,
    numeric_value double precision NOT NULL
);

COMMENT ON TABLE results_standard                IS 'to cache the formula public unprotected numeric results that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN results_standard.group_id      IS 'the 512-bit prime index to find the numeric result';
COMMENT ON COLUMN results_standard.numeric_value IS 'the numeric value given by the user';

-- --------------------------------------------------------

--
-- table structure to cache the formula numeric results related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS results
(
    group_id        char(112)        PRIMARY KEY,
    source_group_id char(112)        DEFAULT NULL,
    numeric_value   double precision     NOT NULL,
    last_update     timestamp        DEFAULT NULL,
    formula_id      bigint               NOT NULL,
    user_id         bigint           DEFAULT NULL,
    excluded        smallint         DEFAULT NULL,
    share_type_id   smallint         DEFAULT NULL,
    protect_id      smallint         DEFAULT NULL
);

COMMENT ON TABLE results                  IS 'to cache the formula numeric results related to up to 16 phrases';
COMMENT ON COLUMN results.group_id        IS 'the 512-bit prime index to find the numeric result';
COMMENT ON COLUMN results.source_group_id IS '512-bit reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN results.numeric_value   IS 'the numeric value given by the user';
COMMENT ON COLUMN results.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN results.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN results.user_id         IS 'the id of the user who has requested the calculation';
COMMENT ON COLUMN results.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN results.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN results.protect_id      IS 'to protect against unwanted changes';

--
-- table structure to cache the user specific changes of numeric results related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS user_results
(
    group_id        char(112)            NOT NULL,
    source_group_id char(112)        DEFAULT NULL,
    user_id         bigint               NOT NULL,
    numeric_value   double precision DEFAULT NULL,
    last_update     timestamp        DEFAULT NULL,
    formula_id      bigint               NOT NULL,
    excluded        smallint         DEFAULT NULL,
    share_type_id   smallint         DEFAULT NULL,
    protect_id      smallint         DEFAULT NULL
);

COMMENT ON TABLE user_results                  IS 'to cache the user specific changes of numeric results related to up to 16 phrases';
COMMENT ON COLUMN user_results.group_id        IS 'the 512-bit prime index to find the user numeric result';
COMMENT ON COLUMN user_results.source_group_id IS '512-bit reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN user_results.user_id         IS 'the id of the user who has requested the change of the numeric result';
COMMENT ON COLUMN user_results.numeric_value   IS 'the user specific numeric value change';
COMMENT ON COLUMN user_results.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_results.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN user_results.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_results.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN user_results.protect_id      IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure to cache the formula most often requested numeric results related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS results_prime
(
    phrase_id_1     smallint         NOT NULL,
    phrase_id_2     smallint         DEFAULT 0,
    phrase_id_3     smallint         DEFAULT 0,
    phrase_id_4     smallint         DEFAULT 0,
    source_group_id bigint           DEFAULT NULL,
    numeric_value   double precision     NOT NULL,
    last_update     timestamp        DEFAULT NULL,
    formula_id      bigint               NOT NULL,
    user_id         bigint           DEFAULT NULL,
    excluded        smallint         DEFAULT NULL,
    share_type_id   smallint         DEFAULT NULL,
    protect_id      smallint         DEFAULT NULL
);

COMMENT ON TABLE results_prime                  IS 'to cache the formula most often requested numeric results related up to four prime phrase';
COMMENT ON COLUMN results_prime.phrase_id_1     IS 'phrase id that is part of the prime key for a numeric result';
COMMENT ON COLUMN results_prime.phrase_id_2     IS 'phrase id that is part of the prime key for a numeric result';
COMMENT ON COLUMN results_prime.phrase_id_3     IS 'phrase id that is part of the prime key for a numeric result';
COMMENT ON COLUMN results_prime.phrase_id_4     IS 'phrase id that is part of the prime key for a numeric result';
COMMENT ON COLUMN results_prime.source_group_id IS '64-bit reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN results_prime.numeric_value   IS 'the numeric value given by the user';
COMMENT ON COLUMN results_prime.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN results_prime.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN results_prime.user_id         IS 'the id of the user who has requested the calculation';
COMMENT ON COLUMN results_prime.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN results_prime.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN results_prime.protect_id      IS 'to protect against unwanted changes';

--
-- table structure to store the user specific changes for the most often requested numeric results related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS user_results_prime
(
    phrase_id_1     smallint         NOT NULL,
    phrase_id_2     smallint         DEFAULT 0,
    phrase_id_3     smallint         DEFAULT 0,
    phrase_id_4     smallint         DEFAULT 0,
    source_group_id bigint           DEFAULT NULL,
    user_id         bigint               NOT NULL,
    numeric_value   double precision DEFAULT NULL,
    last_update     timestamp        DEFAULT NULL,
    formula_id      bigint               NOT NULL,
    excluded        smallint         DEFAULT NULL,
    share_type_id   smallint         DEFAULT NULL,
    protect_id      smallint         DEFAULT NULL
);

COMMENT ON TABLE user_results_prime                  IS 'to store the user specific changes for the most often requested numeric results related up to four prime phrase';
COMMENT ON COLUMN user_results_prime.phrase_id_1     IS 'phrase id that is with the user id part of the prime key for a numeric result';
COMMENT ON COLUMN user_results_prime.phrase_id_2     IS 'phrase id that is with the user id part of the prime key for a numeric result';
COMMENT ON COLUMN user_results_prime.phrase_id_3     IS 'phrase id that is with the user id part of the prime key for a numeric result';
COMMENT ON COLUMN user_results_prime.phrase_id_4     IS 'phrase id that is with the user id part of the prime key for a numeric result';
COMMENT ON COLUMN user_results_prime.source_group_id IS '64-bit reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN user_results_prime.user_id         IS 'the id of the user who has requested the change of the numeric result';
COMMENT ON COLUMN user_results_prime.numeric_value   IS 'the user specific numeric value change';
COMMENT ON COLUMN user_results_prime.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_results_prime.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN user_results_prime.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_results_prime.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN user_results_prime.protect_id      IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure to cache the formula second most often requested numeric results related up to eight prime phrase
--

CREATE TABLE IF NOT EXISTS results_main
(
    phrase_id_1     smallint         NOT NULL,
    phrase_id_2     smallint         DEFAULT 0,
    phrase_id_3     smallint         DEFAULT 0,
    phrase_id_4     smallint         DEFAULT 0,
    phrase_id_5     smallint         DEFAULT 0,
    phrase_id_6     smallint         DEFAULT 0,
    phrase_id_7     smallint         DEFAULT 0,
    phrase_id_8     smallint         DEFAULT 0,
    source_group_id bigint           DEFAULT NULL,
    numeric_value   double precision     NOT NULL,
    last_update     timestamp        DEFAULT NULL,
    formula_id      bigint               NOT NULL,
    user_id         bigint           DEFAULT NULL,
    excluded        smallint         DEFAULT NULL,
    share_type_id   smallint         DEFAULT NULL,
    protect_id      smallint         DEFAULT NULL
);

COMMENT ON TABLE results_main                  IS 'to cache the formula second most often requested numeric results related up to eight prime phrase';
COMMENT ON COLUMN results_main.phrase_id_1     IS 'phrase id that is part of the prime key for a numeric result';
COMMENT ON COLUMN results_main.phrase_id_2     IS 'phrase id that is part of the prime key for a numeric result';
COMMENT ON COLUMN results_main.phrase_id_3     IS 'phrase id that is part of the prime key for a numeric result';
COMMENT ON COLUMN results_main.phrase_id_4     IS 'phrase id that is part of the prime key for a numeric result';
COMMENT ON COLUMN results_main.phrase_id_5     IS 'phrase id that is part of the prime key for a numeric result';
COMMENT ON COLUMN results_main.phrase_id_6     IS 'phrase id that is part of the prime key for a numeric result';
COMMENT ON COLUMN results_main.phrase_id_7     IS 'phrase id that is part of the prime key for a numeric result';
COMMENT ON COLUMN results_main.phrase_id_8     IS 'phrase id that is part of the prime key for a numeric result';
COMMENT ON COLUMN results_main.source_group_id IS '64-bit reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN results_main.numeric_value   IS 'the numeric value given by the user';
COMMENT ON COLUMN results_main.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN results_main.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN results_main.user_id         IS 'the id of the user who has requested the calculation';
COMMENT ON COLUMN results_main.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN results_main.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN results_main.protect_id      IS 'to protect against unwanted changes';

--
-- table structure to store the user specific changes to cache the formula second most often requested numeric results related up to eight prime phrase
--

CREATE TABLE IF NOT EXISTS user_results_main
(
    phrase_id_1     smallint         NOT NULL,
    phrase_id_2     smallint         DEFAULT 0,
    phrase_id_3     smallint         DEFAULT 0,
    phrase_id_4     smallint         DEFAULT 0,
    phrase_id_5     smallint         DEFAULT 0,
    phrase_id_6     smallint         DEFAULT 0,
    phrase_id_7     smallint         DEFAULT 0,
    phrase_id_8     smallint         DEFAULT 0,
    source_group_id bigint           DEFAULT NULL,
    user_id         bigint               NOT NULL,
    numeric_value   double precision DEFAULT NULL,
    last_update     timestamp        DEFAULT NULL,
    formula_id      bigint               NOT NULL,
    excluded        smallint         DEFAULT NULL,
    share_type_id   smallint         DEFAULT NULL,
    protect_id      smallint         DEFAULT NULL
);

COMMENT ON TABLE user_results_main                  IS 'to store the user specific changes to cache the formula second most often requested numeric results related up to eight prime phrase';
COMMENT ON COLUMN user_results_main.phrase_id_1     IS 'phrase id that is with the user id part of the prime key for a numeric result';
COMMENT ON COLUMN user_results_main.phrase_id_2     IS 'phrase id that is with the user id part of the prime key for a numeric result';
COMMENT ON COLUMN user_results_main.phrase_id_3     IS 'phrase id that is with the user id part of the prime key for a numeric result';
COMMENT ON COLUMN user_results_main.phrase_id_4     IS 'phrase id that is with the user id part of the prime key for a numeric result';
COMMENT ON COLUMN user_results_main.phrase_id_5     IS 'phrase id that is with the user id part of the prime key for a numeric result';
COMMENT ON COLUMN user_results_main.phrase_id_6     IS 'phrase id that is with the user id part of the prime key for a numeric result';
COMMENT ON COLUMN user_results_main.phrase_id_7     IS 'phrase id that is with the user id part of the prime key for a numeric result';
COMMENT ON COLUMN user_results_main.phrase_id_8     IS 'phrase id that is with the user id part of the prime key for a numeric result';
COMMENT ON COLUMN user_results_main.source_group_id IS '64-bit reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN user_results_main.user_id         IS 'the id of the user who has requested the change of the numeric result';
COMMENT ON COLUMN user_results_main.numeric_value   IS 'the user specific numeric value change';
COMMENT ON COLUMN user_results_main.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_results_main.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN user_results_main.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_results_main.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN user_results_main.protect_id      IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure to cache the formula numeric results related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS results_big
(
    group_id        text             PRIMARY KEY,
    source_group_id text             DEFAULT NULL,
    numeric_value   double precision     NOT NULL,
    last_update     timestamp        DEFAULT NULL,
    formula_id      bigint               NOT NULL,
    user_id         bigint           DEFAULT NULL,
    excluded        smallint         DEFAULT NULL,
    share_type_id   smallint         DEFAULT NULL,
    protect_id      smallint         DEFAULT NULL
);

COMMENT ON TABLE results_big                  IS 'to cache the formula numeric results related to more than 16 phrases';
COMMENT ON COLUMN results_big.group_id        IS 'the variable text index to find numeric result';
COMMENT ON COLUMN results_big.source_group_id IS 'text reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN results_big.numeric_value   IS 'the numeric value given by the user';
COMMENT ON COLUMN results_big.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN results_big.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN results_big.user_id         IS 'the id of the user who has requested the calculation';
COMMENT ON COLUMN results_big.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN results_big.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN results_big.protect_id      IS 'to protect against unwanted changes';

--
-- table structure to store the user specific changes of numeric results related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS user_results_big
(
    group_id        text                 NOT NULL,
    source_group_id text             DEFAULT NULL,
    user_id         bigint               NOT NULL,
    numeric_value   double precision DEFAULT NULL,
    last_update     timestamp        DEFAULT NULL,
    formula_id      bigint               NOT NULL,
    excluded        smallint         DEFAULT NULL,
    share_type_id   smallint         DEFAULT NULL,
    protect_id      smallint         DEFAULT NULL
);

COMMENT ON TABLE user_results_big                  IS 'to store the user specific changes of numeric results related to more than 16 phrases';
COMMENT ON COLUMN user_results_big.group_id        IS 'the text index for more than 16 phrases to find the numeric result';
COMMENT ON COLUMN user_results_big.source_group_id IS 'text reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN user_results_big.user_id         IS 'the id of the user who has requested the change of the numeric result';
COMMENT ON COLUMN user_results_big.numeric_value   IS 'the user specific numeric value change';
COMMENT ON COLUMN user_results_big.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_results_big.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN user_results_big.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_results_big.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN user_results_big.protect_id      IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure to cache the formula public unprotected text results related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_text_standard_prime
(
    formula_id  smallint NOT NULL,
    phrase_id_1 smallint NOT NULL,
    phrase_id_2 smallint DEFAULT 0,
    phrase_id_3 smallint DEFAULT 0,
    text_value  text     NOT NULL
);

COMMENT ON TABLE results_text_standard_prime              IS 'to cache the formula public unprotected text results related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN results_text_standard_prime.formula_id  IS 'formula id that is part of the prime key for a text result';
COMMENT ON COLUMN results_text_standard_prime.phrase_id_1 IS 'phrase id that is part of the prime key for a text result';
COMMENT ON COLUMN results_text_standard_prime.phrase_id_2 IS 'phrase id that is part of the prime key for a text result';
COMMENT ON COLUMN results_text_standard_prime.phrase_id_3 IS 'phrase id that is part of the prime key for a text result';
COMMENT ON COLUMN results_text_standard_prime.text_value  IS 'the text value given by the user';

--
-- table structure to cache the formula public unprotected text results related up to eight prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_text_standard_main
(
    formula_id  smallint NOT NULL,
    phrase_id_1 smallint NOT NULL,
    phrase_id_2 smallint DEFAULT 0,
    phrase_id_3 smallint DEFAULT 0,
    phrase_id_4 smallint DEFAULT 0,
    phrase_id_5 smallint DEFAULT 0,
    phrase_id_6 smallint DEFAULT 0,
    phrase_id_7 smallint DEFAULT 0,
    text_value  text     NOT NULL
);

COMMENT ON TABLE results_text_standard_main              IS 'to cache the formula public unprotected text results related up to eight prime phrase that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN results_text_standard_main.formula_id  IS 'formula id that is part of the prime key for a text result';
COMMENT ON COLUMN results_text_standard_main.phrase_id_1 IS 'phrase id that is part of the prime key for a text result';
COMMENT ON COLUMN results_text_standard_main.phrase_id_2 IS 'phrase id that is part of the prime key for a text result';
COMMENT ON COLUMN results_text_standard_main.phrase_id_3 IS 'phrase id that is part of the prime key for a text result';
COMMENT ON COLUMN results_text_standard_main.phrase_id_4 IS 'phrase id that is part of the prime key for a text result';
COMMENT ON COLUMN results_text_standard_main.phrase_id_5 IS 'phrase id that is part of the prime key for a text result';
COMMENT ON COLUMN results_text_standard_main.phrase_id_6 IS 'phrase id that is part of the prime key for a text result';
COMMENT ON COLUMN results_text_standard_main.phrase_id_7 IS 'phrase id that is part of the prime key for a text result';
COMMENT ON COLUMN results_text_standard_main.text_value  IS 'the text value given by the user';

--
-- table structure to cache the formula public unprotected text results that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_text_standard
(
    group_id   char(112) PRIMARY KEY,
    text_value text      NOT NULL
);

COMMENT ON TABLE results_text_standard             IS 'to cache the formula public unprotected text results that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN results_text_standard.group_id   IS 'the 512-bit prime index to find the text result';
COMMENT ON COLUMN results_text_standard.text_value IS 'the text value given by the user';

-- --------------------------------------------------------

--
-- table structure to cache the formula text results related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS results_text
(
    group_id        char(112) PRIMARY KEY,
    source_group_id char(112) DEFAULT NULL,
    text_value      text          NOT NULL,
    last_update     timestamp DEFAULT NULL,
    formula_id      bigint        NOT NULL,
    user_id         bigint    DEFAULT NULL,
    excluded        smallint  DEFAULT NULL,
    share_type_id   smallint  DEFAULT NULL,
    protect_id      smallint  DEFAULT NULL
);

COMMENT ON TABLE results_text                  IS 'to cache the formula text results related to up to 16 phrases';
COMMENT ON COLUMN results_text.group_id        IS 'the 512-bit prime index to find the text result';
COMMENT ON COLUMN results_text.source_group_id IS '512-bit reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN results_text.text_value      IS 'the text value given by the user';
COMMENT ON COLUMN results_text.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN results_text.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN results_text.user_id         IS 'the id of the user who has requested the calculation';
COMMENT ON COLUMN results_text.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN results_text.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN results_text.protect_id      IS 'to protect against unwanted changes';

--
-- table structure to cache the user specific changes of text results related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS user_results_text
(
    group_id        char(112)     NOT NULL,
    source_group_id char(112) DEFAULT NULL,
    user_id         bigint        NOT NULL,
    text_value      text      DEFAULT NULL,
    last_update     timestamp DEFAULT NULL,
    formula_id      bigint        NOT NULL,
    excluded        smallint  DEFAULT NULL,
    share_type_id   smallint  DEFAULT NULL,
    protect_id      smallint  DEFAULT NULL
);

COMMENT ON TABLE user_results_text                  IS 'to cache the user specific changes of text results related to up to 16 phrases';
COMMENT ON COLUMN user_results_text.group_id        IS 'the 512-bit prime index to find the user text result';
COMMENT ON COLUMN user_results_text.source_group_id IS '512-bit reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN user_results_text.user_id         IS 'the id of the user who has requested the change of the text result';
COMMENT ON COLUMN user_results_text.text_value      IS 'the user specific text value change';
COMMENT ON COLUMN user_results_text.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_results_text.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN user_results_text.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_results_text.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN user_results_text.protect_id      IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure to cache the formula most often requested text results related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS results_text_prime
(
    phrase_id_1     smallint  NOT NULL,
    phrase_id_2     smallint  DEFAULT 0,
    phrase_id_3     smallint  DEFAULT 0,
    phrase_id_4     smallint  DEFAULT 0,
    source_group_id bigint    DEFAULT NULL,
    text_value      text          NOT NULL,
    last_update     timestamp DEFAULT NULL,
    formula_id      bigint        NOT NULL,
    user_id         bigint    DEFAULT NULL,
    excluded        smallint  DEFAULT NULL,
    share_type_id   smallint  DEFAULT NULL,
    protect_id      smallint  DEFAULT NULL
);

COMMENT ON TABLE results_text_prime                  IS 'to cache the formula most often requested text results related up to four prime phrase';
COMMENT ON COLUMN results_text_prime.phrase_id_1     IS 'phrase id that is part of the prime key for a text result';
COMMENT ON COLUMN results_text_prime.phrase_id_2     IS 'phrase id that is part of the prime key for a text result';
COMMENT ON COLUMN results_text_prime.phrase_id_3     IS 'phrase id that is part of the prime key for a text result';
COMMENT ON COLUMN results_text_prime.phrase_id_4     IS 'phrase id that is part of the prime key for a text result';
COMMENT ON COLUMN results_text_prime.source_group_id IS '64-bit reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN results_text_prime.text_value      IS 'the text value given by the user';
COMMENT ON COLUMN results_text_prime.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN results_text_prime.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN results_text_prime.user_id         IS 'the id of the user who has requested the calculation';
COMMENT ON COLUMN results_text_prime.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN results_text_prime.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN results_text_prime.protect_id      IS 'to protect against unwanted changes';

--
-- table structure to store the user specific changes for the most often requested text results related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS user_results_text_prime
(
    phrase_id_1     smallint  NOT NULL,
    phrase_id_2     smallint  DEFAULT 0,
    phrase_id_3     smallint  DEFAULT 0,
    phrase_id_4     smallint  DEFAULT 0,
    source_group_id bigint    DEFAULT NULL,
    user_id         bigint        NOT NULL,
    text_value      text      DEFAULT NULL,
    last_update     timestamp DEFAULT NULL,
    formula_id      bigint        NOT NULL,
    excluded        smallint  DEFAULT NULL,
    share_type_id   smallint  DEFAULT NULL,
    protect_id      smallint  DEFAULT NULL
);

COMMENT ON TABLE user_results_text_prime                  IS 'to store the user specific changes for the most often requested text results related up to four prime phrase';
COMMENT ON COLUMN user_results_text_prime.phrase_id_1     IS 'phrase id that is with the user id part of the prime key for a text result';
COMMENT ON COLUMN user_results_text_prime.phrase_id_2     IS 'phrase id that is with the user id part of the prime key for a text result';
COMMENT ON COLUMN user_results_text_prime.phrase_id_3     IS 'phrase id that is with the user id part of the prime key for a text result';
COMMENT ON COLUMN user_results_text_prime.phrase_id_4     IS 'phrase id that is with the user id part of the prime key for a text result';
COMMENT ON COLUMN user_results_text_prime.source_group_id IS '64-bit reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN user_results_text_prime.user_id         IS 'the id of the user who has requested the change of the text result';
COMMENT ON COLUMN user_results_text_prime.text_value      IS 'the user specific text value change';
COMMENT ON COLUMN user_results_text_prime.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_results_text_prime.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN user_results_text_prime.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_results_text_prime.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN user_results_text_prime.protect_id      IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure to cache the formula second most often requested text results related up to eight prime phrase
--

CREATE TABLE IF NOT EXISTS results_text_main
(
    phrase_id_1     smallint  NOT NULL,
    phrase_id_2     smallint  DEFAULT 0,
    phrase_id_3     smallint  DEFAULT 0,
    phrase_id_4     smallint  DEFAULT 0,
    phrase_id_5     smallint  DEFAULT 0,
    phrase_id_6     smallint  DEFAULT 0,
    phrase_id_7     smallint  DEFAULT 0,
    phrase_id_8     smallint  DEFAULT 0,
    source_group_id bigint    DEFAULT NULL,
    text_value      text      NOT NULL,
    last_update     timestamp DEFAULT NULL,
    formula_id      bigint        NOT NULL,
    user_id         bigint    DEFAULT NULL,
    excluded        smallint  DEFAULT NULL,
    share_type_id   smallint  DEFAULT NULL,
    protect_id      smallint  DEFAULT NULL
);

COMMENT ON TABLE results_text_main                  IS 'to cache the formula second most often requested text results related up to eight prime phrase';
COMMENT ON COLUMN results_text_main.phrase_id_1     IS 'phrase id that is part of the prime key for a text result';
COMMENT ON COLUMN results_text_main.phrase_id_2     IS 'phrase id that is part of the prime key for a text result';
COMMENT ON COLUMN results_text_main.phrase_id_3     IS 'phrase id that is part of the prime key for a text result';
COMMENT ON COLUMN results_text_main.phrase_id_4     IS 'phrase id that is part of the prime key for a text result';
COMMENT ON COLUMN results_text_main.phrase_id_5     IS 'phrase id that is part of the prime key for a text result';
COMMENT ON COLUMN results_text_main.phrase_id_6     IS 'phrase id that is part of the prime key for a text result';
COMMENT ON COLUMN results_text_main.phrase_id_7     IS 'phrase id that is part of the prime key for a text result';
COMMENT ON COLUMN results_text_main.phrase_id_8     IS 'phrase id that is part of the prime key for a text result';
COMMENT ON COLUMN results_text_main.source_group_id IS '64-bit reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN results_text_main.text_value      IS 'the text value given by the user';
COMMENT ON COLUMN results_text_main.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN results_text_main.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN results_text_main.user_id         IS 'the id of the user who has requested the calculation';
COMMENT ON COLUMN results_text_main.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN results_text_main.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN results_text_main.protect_id      IS 'to protect against unwanted changes';

--
-- table structure to store the user specific changes to cache the formula second most often requested text results related up to eight prime phrase
--

CREATE TABLE IF NOT EXISTS user_results_text_main
(
    phrase_id_1     smallint  NOT NULL,
    phrase_id_2     smallint  DEFAULT 0,
    phrase_id_3     smallint  DEFAULT 0,
    phrase_id_4     smallint  DEFAULT 0,
    phrase_id_5     smallint  DEFAULT 0,
    phrase_id_6     smallint  DEFAULT 0,
    phrase_id_7     smallint  DEFAULT 0,
    phrase_id_8     smallint  DEFAULT 0,
    source_group_id bigint    DEFAULT NULL,
    user_id         bigint        NOT NULL,
    text_value      text      DEFAULT NULL,
    last_update     timestamp DEFAULT NULL,
    formula_id      bigint        NOT NULL,
    excluded        smallint  DEFAULT NULL,
    share_type_id   smallint  DEFAULT NULL,
    protect_id      smallint  DEFAULT NULL
);

COMMENT ON TABLE user_results_text_main                  IS 'to store the user specific changes to cache the formula second most often requested text results related up to eight prime phrase';
COMMENT ON COLUMN user_results_text_main.phrase_id_1     IS 'phrase id that is with the user id part of the prime key for a text result';
COMMENT ON COLUMN user_results_text_main.phrase_id_2     IS 'phrase id that is with the user id part of the prime key for a text result';
COMMENT ON COLUMN user_results_text_main.phrase_id_3     IS 'phrase id that is with the user id part of the prime key for a text result';
COMMENT ON COLUMN user_results_text_main.phrase_id_4     IS 'phrase id that is with the user id part of the prime key for a text result';
COMMENT ON COLUMN user_results_text_main.phrase_id_5     IS 'phrase id that is with the user id part of the prime key for a text result';
COMMENT ON COLUMN user_results_text_main.phrase_id_6     IS 'phrase id that is with the user id part of the prime key for a text result';
COMMENT ON COLUMN user_results_text_main.phrase_id_7     IS 'phrase id that is with the user id part of the prime key for a text result';
COMMENT ON COLUMN user_results_text_main.phrase_id_8     IS 'phrase id that is with the user id part of the prime key for a text result';
COMMENT ON COLUMN user_results_text_main.source_group_id IS '64-bit reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN user_results_text_main.user_id         IS 'the id of the user who has requested the change of the text result';
COMMENT ON COLUMN user_results_text_main.text_value      IS 'the user specific text value change';
COMMENT ON COLUMN user_results_text_main.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_results_text_main.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN user_results_text_main.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_results_text_main.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN user_results_text_main.protect_id      IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure to cache the formula text results related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS results_text_big
(
    group_id        text      PRIMARY KEY,
    source_group_id text      DEFAULT NULL,
    text_value      text          NOT NULL,
    last_update     timestamp DEFAULT NULL,
    formula_id      bigint        NOT NULL,
    user_id         bigint    DEFAULT NULL,
    excluded        smallint  DEFAULT NULL,
    share_type_id   smallint  DEFAULT NULL,
    protect_id      smallint  DEFAULT NULL
);

COMMENT ON TABLE results_text_big                  IS 'to cache the formula text results related to more than 16 phrases';
COMMENT ON COLUMN results_text_big.group_id        IS 'the variable text index to find text result';
COMMENT ON COLUMN results_text_big.source_group_id IS 'text reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN results_text_big.text_value      IS 'the text value given by the user';
COMMENT ON COLUMN results_text_big.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN results_text_big.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN results_text_big.user_id         IS 'the id of the user who has requested the calculation';
COMMENT ON COLUMN results_text_big.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN results_text_big.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN results_text_big.protect_id      IS 'to protect against unwanted changes';

--
-- table structure to store the user specific changes of text results related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS user_results_text_big
(
    group_id        text          NOT NULL,
    source_group_id text      DEFAULT NULL,
    user_id         bigint        NOT NULL,
    text_value      text      DEFAULT NULL,
    last_update     timestamp DEFAULT NULL,
    formula_id      bigint        NOT NULL,
    excluded        smallint  DEFAULT NULL,
    share_type_id   smallint  DEFAULT NULL,
    protect_id      smallint  DEFAULT NULL
);

COMMENT ON TABLE user_results_text_big                  IS 'to store the user specific changes of text results related to more than 16 phrases';
COMMENT ON COLUMN user_results_text_big.group_id        IS 'the text index for more than 16 phrases to find the text result';
COMMENT ON COLUMN user_results_text_big.source_group_id IS 'text reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN user_results_text_big.user_id         IS 'the id of the user who has requested the change of the text result';
COMMENT ON COLUMN user_results_text_big.text_value      IS 'the user specific text value change';
COMMENT ON COLUMN user_results_text_big.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_results_text_big.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN user_results_text_big.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_results_text_big.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN user_results_text_big.protect_id      IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure to cache the formula public unprotected time results related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_time_standard_prime
(
    formula_id  smallint  NOT NULL,
    phrase_id_1 smallint  NOT NULL,
    phrase_id_2 smallint  DEFAULT 0,
    phrase_id_3 smallint  DEFAULT 0,
    time_value  timestamp NOT NULL
);

COMMENT ON TABLE results_time_standard_prime              IS 'to cache the formula public unprotected time results related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN results_time_standard_prime.formula_id  IS 'formula id that is part of the prime key for a time result';
COMMENT ON COLUMN results_time_standard_prime.phrase_id_1 IS 'phrase id that is part of the prime key for a time result';
COMMENT ON COLUMN results_time_standard_prime.phrase_id_2 IS 'phrase id that is part of the prime key for a time result';
COMMENT ON COLUMN results_time_standard_prime.phrase_id_3 IS 'phrase id that is part of the prime key for a time result';
COMMENT ON COLUMN results_time_standard_prime.time_value  IS 'the timestamp given by the user';

--
-- table structure to cache the formula public unprotected time results related up to eight prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_time_standard_main
(
    formula_id  smallint  NOT NULL,
    phrase_id_1 smallint  NOT NULL,
    phrase_id_2 smallint  DEFAULT 0,
    phrase_id_3 smallint  DEFAULT 0,
    phrase_id_4 smallint  DEFAULT 0,
    phrase_id_5 smallint  DEFAULT 0,
    phrase_id_6 smallint  DEFAULT 0,
    phrase_id_7 smallint  DEFAULT 0,
    time_value  timestamp NOT NULL
);

COMMENT ON TABLE results_time_standard_main              IS 'to cache the formula public unprotected time results related up to eight prime phrase that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN results_time_standard_main.formula_id  IS 'formula id that is part of the prime key for a time result';
COMMENT ON COLUMN results_time_standard_main.phrase_id_1 IS 'phrase id that is part of the prime key for a time result';
COMMENT ON COLUMN results_time_standard_main.phrase_id_2 IS 'phrase id that is part of the prime key for a time result';
COMMENT ON COLUMN results_time_standard_main.phrase_id_3 IS 'phrase id that is part of the prime key for a time result';
COMMENT ON COLUMN results_time_standard_main.phrase_id_4 IS 'phrase id that is part of the prime key for a time result';
COMMENT ON COLUMN results_time_standard_main.phrase_id_5 IS 'phrase id that is part of the prime key for a time result';
COMMENT ON COLUMN results_time_standard_main.phrase_id_6 IS 'phrase id that is part of the prime key for a time result';
COMMENT ON COLUMN results_time_standard_main.phrase_id_7 IS 'phrase id that is part of the prime key for a time result';
COMMENT ON COLUMN results_time_standard_main.time_value  IS 'the timestamp given by the user';

--
-- table structure to cache the formula public unprotected time results that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_time_standard
(
    group_id   char(112) PRIMARY KEY,
    time_value timestamp NOT NULL
);

COMMENT ON TABLE results_time_standard             IS 'to cache the formula public unprotected time results that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN results_time_standard.group_id   IS 'the 512-bit prime index to find the time result';
COMMENT ON COLUMN results_time_standard.time_value IS 'the timestamp given by the user';

-- --------------------------------------------------------

--
-- table structure to cache the formula time results related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS results_time
(
    group_id        char(112) PRIMARY KEY,
    source_group_id char(112) DEFAULT NULL,
    time_value      timestamp     NOT NULL,
    last_update     timestamp DEFAULT NULL,
    formula_id      bigint        NOT NULL,
    user_id         bigint    DEFAULT NULL,
    excluded        smallint  DEFAULT NULL,
    share_type_id   smallint  DEFAULT NULL,
    protect_id      smallint  DEFAULT NULL
);

COMMENT ON TABLE results_time                  IS 'to cache the formula time results related to up to 16 phrases';
COMMENT ON COLUMN results_time.group_id        IS 'the 512-bit prime index to find the time result';
COMMENT ON COLUMN results_time.source_group_id IS '512-bit reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN results_time.time_value      IS 'the timestamp given by the user';
COMMENT ON COLUMN results_time.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN results_time.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN results_time.user_id         IS 'the id of the user who has requested the calculation';
COMMENT ON COLUMN results_time.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN results_time.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN results_time.protect_id      IS 'to protect against unwanted changes';

--
-- table structure to cache the user specific changes of time results related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS user_results_time
(
    group_id        char(112)     NOT NULL,
    source_group_id char(112) DEFAULT NULL,
    user_id         bigint        NOT NULL,
    time_value      timestamp DEFAULT NULL,
    last_update     timestamp DEFAULT NULL,
    formula_id      bigint        NOT NULL,
    excluded        smallint  DEFAULT NULL,
    share_type_id   smallint  DEFAULT NULL,
    protect_id      smallint  DEFAULT NULL
);

COMMENT ON TABLE user_results_time                  IS 'to cache the user specific changes of time results related to up to 16 phrases';
COMMENT ON COLUMN user_results_time.group_id        IS 'the 512-bit prime index to find the user time result';
COMMENT ON COLUMN user_results_time.source_group_id IS '512-bit reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN user_results_time.user_id         IS 'the id of the user who has requested the change of the time result';
COMMENT ON COLUMN user_results_time.time_value      IS 'the user specific timestamp change';
COMMENT ON COLUMN user_results_time.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_results_time.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN user_results_time.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_results_time.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN user_results_time.protect_id      IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure to cache the formula most often requested time results related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS results_time_prime
(
    phrase_id_1     smallint  NOT NULL,
    phrase_id_2     smallint  DEFAULT 0,
    phrase_id_3     smallint  DEFAULT 0,
    phrase_id_4     smallint  DEFAULT 0,
    source_group_id bigint    DEFAULT NULL,
    time_value      timestamp     NOT NULL,
    last_update     timestamp DEFAULT NULL,
    formula_id      bigint        NOT NULL,
    user_id         bigint    DEFAULT NULL,
    excluded        smallint  DEFAULT NULL,
    share_type_id   smallint  DEFAULT NULL,
    protect_id      smallint  DEFAULT NULL
);

COMMENT ON TABLE results_time_prime                  IS 'to cache the formula most often requested time results related up to four prime phrase';
COMMENT ON COLUMN results_time_prime.phrase_id_1     IS 'phrase id that is part of the prime key for a time result';
COMMENT ON COLUMN results_time_prime.phrase_id_2     IS 'phrase id that is part of the prime key for a time result';
COMMENT ON COLUMN results_time_prime.phrase_id_3     IS 'phrase id that is part of the prime key for a time result';
COMMENT ON COLUMN results_time_prime.phrase_id_4     IS 'phrase id that is part of the prime key for a time result';
COMMENT ON COLUMN results_time_prime.source_group_id IS '64-bit reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN results_time_prime.time_value      IS 'the timestamp given by the user';
COMMENT ON COLUMN results_time_prime.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN results_time_prime.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN results_time_prime.user_id         IS 'the id of the user who has requested the calculation';
COMMENT ON COLUMN results_time_prime.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN results_time_prime.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN results_time_prime.protect_id      IS 'to protect against unwanted changes';

--
-- table structure to store the user specific changes for the most often requested time results related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS user_results_time_prime
(
    phrase_id_1     smallint  NOT NULL,
    phrase_id_2     smallint  DEFAULT 0,
    phrase_id_3     smallint  DEFAULT 0,
    phrase_id_4     smallint  DEFAULT 0,
    source_group_id bigint    DEFAULT NULL,
    user_id         bigint        NOT NULL,
    time_value      timestamp DEFAULT NULL,
    last_update     timestamp DEFAULT NULL,
    formula_id      bigint        NOT NULL,
    excluded        smallint  DEFAULT NULL,
    share_type_id   smallint  DEFAULT NULL,
    protect_id      smallint  DEFAULT NULL
);

COMMENT ON TABLE user_results_time_prime                  IS 'to store the user specific changes for the most often requested time results related up to four prime phrase';
COMMENT ON COLUMN user_results_time_prime.phrase_id_1     IS 'phrase id that is with the user id part of the prime key for a time result';
COMMENT ON COLUMN user_results_time_prime.phrase_id_2     IS 'phrase id that is with the user id part of the prime key for a time result';
COMMENT ON COLUMN user_results_time_prime.phrase_id_3     IS 'phrase id that is with the user id part of the prime key for a time result';
COMMENT ON COLUMN user_results_time_prime.phrase_id_4     IS 'phrase id that is with the user id part of the prime key for a time result';
COMMENT ON COLUMN user_results_time_prime.source_group_id IS '64-bit reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN user_results_time_prime.user_id         IS 'the id of the user who has requested the change of the time result';
COMMENT ON COLUMN user_results_time_prime.time_value      IS 'the user specific timestamp change';
COMMENT ON COLUMN user_results_time_prime.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_results_time_prime.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN user_results_time_prime.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_results_time_prime.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN user_results_time_prime.protect_id      IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure to cache the formula second most often requested time results related up to eight prime phrase
--

CREATE TABLE IF NOT EXISTS results_time_main
(
    phrase_id_1     smallint  NOT NULL,
    phrase_id_2     smallint  DEFAULT 0,
    phrase_id_3     smallint  DEFAULT 0,
    phrase_id_4     smallint  DEFAULT 0,
    phrase_id_5     smallint  DEFAULT 0,
    phrase_id_6     smallint  DEFAULT 0,
    phrase_id_7     smallint  DEFAULT 0,
    phrase_id_8     smallint  DEFAULT 0,
    source_group_id bigint    DEFAULT NULL,
    time_value      timestamp NOT NULL,
    last_update     timestamp DEFAULT NULL,
    formula_id      bigint        NOT NULL,
    user_id         bigint    DEFAULT NULL,
    excluded        smallint  DEFAULT NULL,
    share_type_id   smallint  DEFAULT NULL,
    protect_id      smallint  DEFAULT NULL
);

COMMENT ON TABLE results_time_main                  IS 'to cache the formula second most often requested time results related up to eight prime phrase';
COMMENT ON COLUMN results_time_main.phrase_id_1     IS 'phrase id that is part of the prime key for a time result';
COMMENT ON COLUMN results_time_main.phrase_id_2     IS 'phrase id that is part of the prime key for a time result';
COMMENT ON COLUMN results_time_main.phrase_id_3     IS 'phrase id that is part of the prime key for a time result';
COMMENT ON COLUMN results_time_main.phrase_id_4     IS 'phrase id that is part of the prime key for a time result';
COMMENT ON COLUMN results_time_main.phrase_id_5     IS 'phrase id that is part of the prime key for a time result';
COMMENT ON COLUMN results_time_main.phrase_id_6     IS 'phrase id that is part of the prime key for a time result';
COMMENT ON COLUMN results_time_main.phrase_id_7     IS 'phrase id that is part of the prime key for a time result';
COMMENT ON COLUMN results_time_main.phrase_id_8     IS 'phrase id that is part of the prime key for a time result';
COMMENT ON COLUMN results_time_main.source_group_id IS '64-bit reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN results_time_main.time_value      IS 'the timestamp given by the user';
COMMENT ON COLUMN results_time_main.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN results_time_main.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN results_time_main.user_id         IS 'the id of the user who has requested the calculation';
COMMENT ON COLUMN results_time_main.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN results_time_main.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN results_time_main.protect_id      IS 'to protect against unwanted changes';

--
-- table structure to store the user specific changes to cache the formula second most often requested time results related up to eight prime phrase
--

CREATE TABLE IF NOT EXISTS user_results_time_main
(
    phrase_id_1     smallint  NOT NULL,
    phrase_id_2     smallint  DEFAULT 0,
    phrase_id_3     smallint  DEFAULT 0,
    phrase_id_4     smallint  DEFAULT 0,
    phrase_id_5     smallint  DEFAULT 0,
    phrase_id_6     smallint  DEFAULT 0,
    phrase_id_7     smallint  DEFAULT 0,
    phrase_id_8     smallint  DEFAULT 0,
    source_group_id bigint    DEFAULT NULL,
    user_id         bigint        NOT NULL,
    time_value      timestamp DEFAULT NULL,
    last_update     timestamp DEFAULT NULL,
    formula_id      bigint        NOT NULL,
    excluded        smallint  DEFAULT NULL,
    share_type_id   smallint  DEFAULT NULL,
    protect_id      smallint  DEFAULT NULL
);

COMMENT ON TABLE user_results_time_main                  IS 'to store the user specific changes to cache the formula second most often requested time results related up to eight prime phrase';
COMMENT ON COLUMN user_results_time_main.phrase_id_1     IS 'phrase id that is with the user id part of the prime key for a time result';
COMMENT ON COLUMN user_results_time_main.phrase_id_2     IS 'phrase id that is with the user id part of the prime key for a time result';
COMMENT ON COLUMN user_results_time_main.phrase_id_3     IS 'phrase id that is with the user id part of the prime key for a time result';
COMMENT ON COLUMN user_results_time_main.phrase_id_4     IS 'phrase id that is with the user id part of the prime key for a time result';
COMMENT ON COLUMN user_results_time_main.phrase_id_5     IS 'phrase id that is with the user id part of the prime key for a time result';
COMMENT ON COLUMN user_results_time_main.phrase_id_6     IS 'phrase id that is with the user id part of the prime key for a time result';
COMMENT ON COLUMN user_results_time_main.phrase_id_7     IS 'phrase id that is with the user id part of the prime key for a time result';
COMMENT ON COLUMN user_results_time_main.phrase_id_8     IS 'phrase id that is with the user id part of the prime key for a time result';
COMMENT ON COLUMN user_results_time_main.source_group_id IS '64-bit reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN user_results_time_main.user_id         IS 'the id of the user who has requested the change of the time result';
COMMENT ON COLUMN user_results_time_main.time_value      IS 'the user specific timestamp change';
COMMENT ON COLUMN user_results_time_main.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_results_time_main.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN user_results_time_main.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_results_time_main.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN user_results_time_main.protect_id      IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure to cache the formula time results related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS results_time_big
(
    group_id        text      PRIMARY KEY,
    source_group_id text      DEFAULT NULL,
    time_value      timestamp     NOT NULL,
    last_update     timestamp DEFAULT NULL,
    formula_id      bigint        NOT NULL,
    user_id         bigint    DEFAULT NULL,
    excluded        smallint  DEFAULT NULL,
    share_type_id   smallint  DEFAULT NULL,
    protect_id      smallint  DEFAULT NULL
);

COMMENT ON TABLE results_time_big                  IS 'to cache the formula time results related to more than 16 phrases';
COMMENT ON COLUMN results_time_big.group_id        IS 'the variable text index to find time result';
COMMENT ON COLUMN results_time_big.source_group_id IS 'text reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN results_time_big.time_value      IS 'the timestamp given by the user';
COMMENT ON COLUMN results_time_big.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN results_time_big.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN results_time_big.user_id         IS 'the id of the user who has requested the calculation';
COMMENT ON COLUMN results_time_big.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN results_time_big.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN results_time_big.protect_id      IS 'to protect against unwanted changes';

--
-- table structure to store the user specific changes of time results related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS user_results_time_big
(
    group_id        text          NOT NULL,
    source_group_id text      DEFAULT NULL,
    user_id         bigint        NOT NULL,
    time_value      timestamp DEFAULT NULL,
    last_update     timestamp DEFAULT NULL,
    formula_id      bigint        NOT NULL,
    excluded        smallint  DEFAULT NULL,
    share_type_id   smallint  DEFAULT NULL,
    protect_id      smallint  DEFAULT NULL
);

COMMENT ON TABLE user_results_time_big                  IS 'to store the user specific changes of time results related to more than 16 phrases';
COMMENT ON COLUMN user_results_time_big.group_id        IS 'the text index for more than 16 phrases to find the time result';
COMMENT ON COLUMN user_results_time_big.source_group_id IS 'text reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN user_results_time_big.user_id         IS 'the id of the user who has requested the change of the time result';
COMMENT ON COLUMN user_results_time_big.time_value      IS 'the user specific timestamp change';
COMMENT ON COLUMN user_results_time_big.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_results_time_big.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN user_results_time_big.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_results_time_big.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN user_results_time_big.protect_id      IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure to cache the formula public unprotected geo results related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_geo_standard_prime
(
    formula_id  smallint  NOT NULL,
    phrase_id_1 smallint  NOT NULL,
    phrase_id_2 smallint  DEFAULT 0,
    phrase_id_3 smallint  DEFAULT 0,
    geo_value   point     NOT NULL
);

COMMENT ON TABLE results_geo_standard_prime              IS 'to cache the formula public unprotected geo results related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN results_geo_standard_prime.formula_id  IS 'formula id that is part of the prime key for a geo result';
COMMENT ON COLUMN results_geo_standard_prime.phrase_id_1 IS 'phrase id that is part of the prime key for a geo result';
COMMENT ON COLUMN results_geo_standard_prime.phrase_id_2 IS 'phrase id that is part of the prime key for a geo result';
COMMENT ON COLUMN results_geo_standard_prime.phrase_id_3 IS 'phrase id that is part of the prime key for a geo result';
COMMENT ON COLUMN results_geo_standard_prime.geo_value   IS 'the geolocation given by the user';

--
-- table structure to cache the formula public unprotected geo results related up to eight prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_geo_standard_main
(
    formula_id  smallint  NOT NULL,
    phrase_id_1 smallint  NOT NULL,
    phrase_id_2 smallint  DEFAULT 0,
    phrase_id_3 smallint  DEFAULT 0,
    phrase_id_4 smallint  DEFAULT 0,
    phrase_id_5 smallint  DEFAULT 0,
    phrase_id_6 smallint  DEFAULT 0,
    phrase_id_7 smallint  DEFAULT 0,
    geo_value   point     NOT NULL
);

COMMENT ON TABLE results_geo_standard_main              IS 'to cache the formula public unprotected geo results related up to eight prime phrase that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN results_geo_standard_main.formula_id  IS 'formula id that is part of the prime key for a geo result';
COMMENT ON COLUMN results_geo_standard_main.phrase_id_1 IS 'phrase id that is part of the prime key for a geo result';
COMMENT ON COLUMN results_geo_standard_main.phrase_id_2 IS 'phrase id that is part of the prime key for a geo result';
COMMENT ON COLUMN results_geo_standard_main.phrase_id_3 IS 'phrase id that is part of the prime key for a geo result';
COMMENT ON COLUMN results_geo_standard_main.phrase_id_4 IS 'phrase id that is part of the prime key for a geo result';
COMMENT ON COLUMN results_geo_standard_main.phrase_id_5 IS 'phrase id that is part of the prime key for a geo result';
COMMENT ON COLUMN results_geo_standard_main.phrase_id_6 IS 'phrase id that is part of the prime key for a geo result';
COMMENT ON COLUMN results_geo_standard_main.phrase_id_7 IS 'phrase id that is part of the prime key for a geo result';
COMMENT ON COLUMN results_geo_standard_main.geo_value   IS 'the geolocation given by the user';

--
-- table structure to cache the formula public unprotected geo results that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_geo_standard
(
    group_id   char(112) PRIMARY KEY,
    geo_value  point     NOT NULL
);

COMMENT ON TABLE results_geo_standard             IS 'to cache the formula public unprotected geo results that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN results_geo_standard.group_id   IS 'the 512-bit prime index to find the geo result';
COMMENT ON COLUMN results_geo_standard.geo_value  IS 'the geolocation given by the user';

-- --------------------------------------------------------

--
-- table structure to cache the formula geo results related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS results_geo
(
    group_id        char(112) PRIMARY KEY,
    source_group_id char(112) DEFAULT NULL,
    geo_value       point         NOT NULL,
    last_update     timestamp DEFAULT NULL,
    formula_id      bigint        NOT NULL,
    user_id         bigint    DEFAULT NULL,
    excluded        smallint  DEFAULT NULL,
    share_type_id   smallint  DEFAULT NULL,
    protect_id      smallint  DEFAULT NULL
);

COMMENT ON TABLE results_geo                  IS 'to cache the formula geo results related to up to 16 phrases';
COMMENT ON COLUMN results_geo.group_id        IS 'the 512-bit prime index to find the geo result';
COMMENT ON COLUMN results_geo.source_group_id IS '512-bit reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN results_geo.geo_value       IS 'the geolocation given by the user';
COMMENT ON COLUMN results_geo.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN results_geo.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN results_geo.user_id         IS 'the id of the user who has requested the calculation';
COMMENT ON COLUMN results_geo.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN results_geo.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN results_geo.protect_id      IS 'to protect against unwanted changes';

--
-- table structure to cache the user specific changes of geo results related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS user_results_geo
(
    group_id        char(112)     NOT NULL,
    source_group_id char(112) DEFAULT NULL,
    user_id         bigint        NOT NULL,
    geo_value       point     DEFAULT NULL,
    last_update     timestamp DEFAULT NULL,
    formula_id      bigint        NOT NULL,
    excluded        smallint  DEFAULT NULL,
    share_type_id   smallint  DEFAULT NULL,
    protect_id      smallint  DEFAULT NULL
);

COMMENT ON TABLE user_results_geo                  IS 'to cache the user specific changes of geo results related to up to 16 phrases';
COMMENT ON COLUMN user_results_geo.group_id        IS 'the 512-bit prime index to find the user geo result';
COMMENT ON COLUMN user_results_geo.source_group_id IS '512-bit reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN user_results_geo.user_id         IS 'the id of the user who has requested the change of the geo result';
COMMENT ON COLUMN user_results_geo.geo_value       IS 'the user specific geolocation change';
COMMENT ON COLUMN user_results_geo.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_results_geo.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN user_results_geo.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_results_geo.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN user_results_geo.protect_id      IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure to cache the formula most often requested geo results related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS results_geo_prime
(
    phrase_id_1     smallint  NOT NULL,
    phrase_id_2     smallint  DEFAULT 0,
    phrase_id_3     smallint  DEFAULT 0,
    phrase_id_4     smallint  DEFAULT 0,
    source_group_id bigint    DEFAULT NULL,
    geo_value       point         NOT NULL,
    last_update     timestamp DEFAULT NULL,
    formula_id      bigint        NOT NULL,
    user_id         bigint    DEFAULT NULL,
    excluded        smallint  DEFAULT NULL,
    share_type_id   smallint  DEFAULT NULL,
    protect_id      smallint  DEFAULT NULL
);

COMMENT ON TABLE results_geo_prime                  IS 'to cache the formula most often requested geo results related up to four prime phrase';
COMMENT ON COLUMN results_geo_prime.phrase_id_1     IS 'phrase id that is part of the prime key for a geo result';
COMMENT ON COLUMN results_geo_prime.phrase_id_2     IS 'phrase id that is part of the prime key for a geo result';
COMMENT ON COLUMN results_geo_prime.phrase_id_3     IS 'phrase id that is part of the prime key for a geo result';
COMMENT ON COLUMN results_geo_prime.phrase_id_4     IS 'phrase id that is part of the prime key for a geo result';
COMMENT ON COLUMN results_geo_prime.source_group_id IS '64-bit reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN results_geo_prime.geo_value       IS 'the geolocation given by the user';
COMMENT ON COLUMN results_geo_prime.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN results_geo_prime.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN results_geo_prime.user_id         IS 'the id of the user who has requested the calculation';
COMMENT ON COLUMN results_geo_prime.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN results_geo_prime.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN results_geo_prime.protect_id      IS 'to protect against unwanted changes';

--
-- table structure to store the user specific changes for the most often requested geo results related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS user_results_geo_prime
(
    phrase_id_1     smallint  NOT NULL,
    phrase_id_2     smallint  DEFAULT 0,
    phrase_id_3     smallint  DEFAULT 0,
    phrase_id_4     smallint  DEFAULT 0,
    source_group_id bigint    DEFAULT NULL,
    user_id         bigint        NOT NULL,
    geo_value       point     DEFAULT NULL,
    last_update     timestamp DEFAULT NULL,
    formula_id      bigint        NOT NULL,
    excluded        smallint  DEFAULT NULL,
    share_type_id   smallint  DEFAULT NULL,
    protect_id      smallint  DEFAULT NULL
);

COMMENT ON TABLE user_results_geo_prime                  IS 'to store the user specific changes for the most often requested geo results related up to four prime phrase';
COMMENT ON COLUMN user_results_geo_prime.phrase_id_1     IS 'phrase id that is with the user id part of the prime key for a geo result';
COMMENT ON COLUMN user_results_geo_prime.phrase_id_2     IS 'phrase id that is with the user id part of the prime key for a geo result';
COMMENT ON COLUMN user_results_geo_prime.phrase_id_3     IS 'phrase id that is with the user id part of the prime key for a geo result';
COMMENT ON COLUMN user_results_geo_prime.phrase_id_4     IS 'phrase id that is with the user id part of the prime key for a geo result';
COMMENT ON COLUMN user_results_geo_prime.source_group_id IS '64-bit reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN user_results_geo_prime.user_id         IS 'the id of the user who has requested the change of the geo result';
COMMENT ON COLUMN user_results_geo_prime.geo_value       IS 'the user specific geolocation change';
COMMENT ON COLUMN user_results_geo_prime.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_results_geo_prime.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN user_results_geo_prime.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_results_geo_prime.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN user_results_geo_prime.protect_id      IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure to cache the formula second most often requested geo results related up to eight prime phrase
--

CREATE TABLE IF NOT EXISTS results_geo_main
(
    phrase_id_1     smallint  NOT NULL,
    phrase_id_2     smallint  DEFAULT 0,
    phrase_id_3     smallint  DEFAULT 0,
    phrase_id_4     smallint  DEFAULT 0,
    phrase_id_5     smallint  DEFAULT 0,
    phrase_id_6     smallint  DEFAULT 0,
    phrase_id_7     smallint  DEFAULT 0,
    phrase_id_8     smallint  DEFAULT 0,
    source_group_id bigint    DEFAULT NULL,
    geo_value       point NOT NULL,
    last_update     timestamp DEFAULT NULL,
    formula_id      bigint        NOT NULL,
    user_id         bigint    DEFAULT NULL,
    excluded        smallint  DEFAULT NULL,
    share_type_id   smallint  DEFAULT NULL,
    protect_id      smallint  DEFAULT NULL
);

COMMENT ON TABLE results_geo_main                  IS 'to cache the formula second most often requested geo results related up to eight prime phrase';
COMMENT ON COLUMN results_geo_main.phrase_id_1     IS 'phrase id that is part of the prime key for a geo result';
COMMENT ON COLUMN results_geo_main.phrase_id_2     IS 'phrase id that is part of the prime key for a geo result';
COMMENT ON COLUMN results_geo_main.phrase_id_3     IS 'phrase id that is part of the prime key for a geo result';
COMMENT ON COLUMN results_geo_main.phrase_id_4     IS 'phrase id that is part of the prime key for a geo result';
COMMENT ON COLUMN results_geo_main.phrase_id_5     IS 'phrase id that is part of the prime key for a geo result';
COMMENT ON COLUMN results_geo_main.phrase_id_6     IS 'phrase id that is part of the prime key for a geo result';
COMMENT ON COLUMN results_geo_main.phrase_id_7     IS 'phrase id that is part of the prime key for a geo result';
COMMENT ON COLUMN results_geo_main.phrase_id_8     IS 'phrase id that is part of the prime key for a geo result';
COMMENT ON COLUMN results_geo_main.source_group_id IS '64-bit reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN results_geo_main.geo_value       IS 'the geolocation given by the user';
COMMENT ON COLUMN results_geo_main.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN results_geo_main.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN results_geo_main.user_id         IS 'the id of the user who has requested the calculation';
COMMENT ON COLUMN results_geo_main.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN results_geo_main.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN results_geo_main.protect_id      IS 'to protect against unwanted changes';

--
-- table structure to store the user specific changes to cache the formula second most often requested geo results related up to eight prime phrase
--

CREATE TABLE IF NOT EXISTS user_results_geo_main
(
    phrase_id_1     smallint  NOT NULL,
    phrase_id_2     smallint  DEFAULT 0,
    phrase_id_3     smallint  DEFAULT 0,
    phrase_id_4     smallint  DEFAULT 0,
    phrase_id_5     smallint  DEFAULT 0,
    phrase_id_6     smallint  DEFAULT 0,
    phrase_id_7     smallint  DEFAULT 0,
    phrase_id_8     smallint  DEFAULT 0,
    source_group_id bigint    DEFAULT NULL,
    user_id         bigint        NOT NULL,
    geo_value       point DEFAULT NULL,
    last_update     timestamp DEFAULT NULL,
    formula_id      bigint        NOT NULL,
    excluded        smallint  DEFAULT NULL,
    share_type_id   smallint  DEFAULT NULL,
    protect_id      smallint  DEFAULT NULL
);

COMMENT ON TABLE user_results_geo_main                  IS 'to store the user specific changes to cache the formula second most often requested geo results related up to eight prime phrase';
COMMENT ON COLUMN user_results_geo_main.phrase_id_1     IS 'phrase id that is with the user id part of the prime key for a geo result';
COMMENT ON COLUMN user_results_geo_main.phrase_id_2     IS 'phrase id that is with the user id part of the prime key for a geo result';
COMMENT ON COLUMN user_results_geo_main.phrase_id_3     IS 'phrase id that is with the user id part of the prime key for a geo result';
COMMENT ON COLUMN user_results_geo_main.phrase_id_4     IS 'phrase id that is with the user id part of the prime key for a geo result';
COMMENT ON COLUMN user_results_geo_main.phrase_id_5     IS 'phrase id that is with the user id part of the prime key for a geo result';
COMMENT ON COLUMN user_results_geo_main.phrase_id_6     IS 'phrase id that is with the user id part of the prime key for a geo result';
COMMENT ON COLUMN user_results_geo_main.phrase_id_7     IS 'phrase id that is with the user id part of the prime key for a geo result';
COMMENT ON COLUMN user_results_geo_main.phrase_id_8     IS 'phrase id that is with the user id part of the prime key for a geo result';
COMMENT ON COLUMN user_results_geo_main.source_group_id IS '64-bit reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN user_results_geo_main.user_id         IS 'the id of the user who has requested the change of the geo result';
COMMENT ON COLUMN user_results_geo_main.geo_value       IS 'the user specific geolocation change';
COMMENT ON COLUMN user_results_geo_main.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_results_geo_main.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN user_results_geo_main.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_results_geo_main.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN user_results_geo_main.protect_id      IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure to cache the formula geo results related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS results_geo_big
(
    group_id        text      PRIMARY KEY,
    source_group_id text      DEFAULT NULL,
    geo_value       point         NOT NULL,
    last_update     timestamp DEFAULT NULL,
    formula_id      bigint        NOT NULL,
    user_id         bigint    DEFAULT NULL,
    excluded        smallint  DEFAULT NULL,
    share_type_id   smallint  DEFAULT NULL,
    protect_id      smallint  DEFAULT NULL
);

COMMENT ON TABLE results_geo_big                  IS 'to cache the formula geo results related to more than 16 phrases';
COMMENT ON COLUMN results_geo_big.group_id        IS 'the variable text index to find geo result';
COMMENT ON COLUMN results_geo_big.source_group_id IS 'text reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN results_geo_big.geo_value       IS 'the geolocation given by the user';
COMMENT ON COLUMN results_geo_big.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN results_geo_big.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN results_geo_big.user_id         IS 'the id of the user who has requested the calculation';
COMMENT ON COLUMN results_geo_big.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN results_geo_big.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN results_geo_big.protect_id      IS 'to protect against unwanted changes';

--
-- table structure to store the user specific changes of geo results related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS user_results_geo_big
(
    group_id        text          NOT NULL,
    source_group_id text      DEFAULT NULL,
    user_id         bigint        NOT NULL,
    geo_value       point     DEFAULT NULL,
    last_update     timestamp DEFAULT NULL,
    formula_id      bigint        NOT NULL,
    excluded        smallint  DEFAULT NULL,
    share_type_id   smallint  DEFAULT NULL,
    protect_id      smallint  DEFAULT NULL
);

COMMENT ON TABLE user_results_geo_big                  IS 'to store the user specific changes of geo results related to more than 16 phrases';
COMMENT ON COLUMN user_results_geo_big.group_id        IS 'the text index for more than 16 phrases to find the geo result';
COMMENT ON COLUMN user_results_geo_big.source_group_id IS 'text reference to the sorted phrase list used to calculate this result';
COMMENT ON COLUMN user_results_geo_big.user_id         IS 'the id of the user who has requested the change of the geo result';
COMMENT ON COLUMN user_results_geo_big.geo_value       IS 'the user specific geolocation change';
COMMENT ON COLUMN user_results_geo_big.last_update     IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_results_geo_big.formula_id      IS 'the id of the formula which has been used to calculate this result';
COMMENT ON COLUMN user_results_geo_big.excluded        IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_results_geo_big.share_type_id   IS 'to restrict the access';
COMMENT ON COLUMN user_results_geo_big.protect_id      IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure for table view_type_list
-- TODO generate type
--

CREATE TABLE IF NOT EXISTS view_types
(
    view_type_id BIGSERIAL PRIMARY KEY,
    type_name    varchar(200) NOT NULL,
    description  text         NOT NULL,
    code_id      varchar(100) DEFAULT NULL
);

COMMENT ON TABLE view_types IS 'to group the masks a link a basic format';

-- --------------------------------------------------------

--
-- table structure to store all user interfaces entry points
--

CREATE TABLE IF NOT EXISTS views
(
    view_id       BIGSERIAL PRIMARY KEY,
    user_id       bigint       DEFAULT NULL,
    view_name     varchar(255)     NOT NULL,
    description   text         DEFAULT NULL,
    view_type_id  bigint       DEFAULT NULL,
    code_id       varchar(255) DEFAULT NULL,
    excluded      smallint     DEFAULT NULL,
    share_type_id smallint     DEFAULT NULL,
    protect_id    smallint     DEFAULT NULL
);

COMMENT ON TABLE views IS 'to store all user interfaces entry points';
COMMENT ON COLUMN views.view_id IS 'the internal unique primary index';
COMMENT ON COLUMN views.user_id IS 'the owner / creator of the view';
COMMENT ON COLUMN views.view_name IS 'the name of the view used for searching';
COMMENT ON COLUMN views.description IS 'to explain the view to the user with a mouse over text; to be replaced by a language form entry';
COMMENT ON COLUMN views.view_type_id IS 'to link coded functionality to views e.g. to use a view for the startup page';
COMMENT ON COLUMN views.code_id IS 'to link coded functionality to a specific view e.g. define the internal system views';
COMMENT ON COLUMN views.excluded IS 'true if a user,but not all,have removed it';
COMMENT ON COLUMN views.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN views.protect_id IS 'to protect against unwanted changes';

--
-- table structure to save user specific changes to store all user interfaces entry points
--

CREATE TABLE IF NOT EXISTS user_views
(
    view_id       bigint   NOT NULL,
    user_id       bigint   NOT NULL,
    language_id   bigint   NOT NULL DEFAULT 1,
    view_name     varchar(255)      DEFAULT NULL,
    description   text              DEFAULT NULL,
    view_type_id  bigint            DEFAULT NULL,
    excluded      smallint          DEFAULT NULL,
    share_type_id smallint          DEFAULT NULL,
    protect_id    smallint          DEFAULT NULL
);

COMMENT ON TABLE user_views IS 'to store all user interfaces entry points';
COMMENT ON COLUMN user_views.view_id IS 'with the user_id the internal unique primary index';
COMMENT ON COLUMN user_views.user_id IS 'the changer of the view';
COMMENT ON COLUMN user_views.language_id IS 'the name of the view used for searching';
COMMENT ON COLUMN user_views.view_name IS 'the name of the view used for searching';
COMMENT ON COLUMN user_views.description IS 'to explain the view to the user with a mouse over text; to be replaced by a language form entry';
COMMENT ON COLUMN user_views.view_type_id IS 'to link coded functionality to views e.g. to use a view for the startup page';
COMMENT ON COLUMN user_views.excluded IS 'true if a user,but not all,have removed it';
COMMENT ON COLUMN user_views.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_views.protect_id IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure for table component_link_types
-- TODO generate type
--

CREATE TABLE IF NOT EXISTS component_link_types
(
    component_link_type_id      BIGSERIAL PRIMARY KEY,
    type_name                   varchar(200) NOT NULL,
    code_id                     varchar(50)  NOT NULL
);

--
-- table structure for table component_position_types
-- TODO generate type
--

CREATE TABLE IF NOT EXISTS component_position_types
(
    component_position_type_id      BIGSERIAL PRIMARY KEY,
    type_name                       varchar(100) NOT NULL,
    description                     text         NOT NULL,
    code_id                         varchar(50)  NOT NULL
);

COMMENT ON TABLE component_position_types IS 'sideways or down';

--
-- table structure for table component_types
-- TODO generate type
--

CREATE TABLE IF NOT EXISTS component_types
(
    component_type_id BIGSERIAL PRIMARY KEY,
    type_name              varchar(100) NOT NULL,
    description            text DEFAULT NULL,
    code_id                varchar(100) NOT NULL
);

COMMENT ON TABLE component_types IS 'fixed text, term or formula result';

-- --------------------------------------------------------

--
-- table structure for the single components of a view
--

CREATE TABLE IF NOT EXISTS components
(
    component_id                BIGSERIAL PRIMARY KEY,
    user_id                     bigint       DEFAULT NULL,
    component_name              varchar(255)     NOT NULL,
    description                 text         DEFAULT NULL,
    component_type_id           bigint       DEFAULT NULL,
    word_id_row                 bigint       DEFAULT NULL,
    formula_id                  bigint       DEFAULT NULL,
    word_id_col                 bigint       DEFAULT NULL,
    word_id_col2                bigint       DEFAULT NULL,
    linked_component_id         bigint       DEFAULT NULL,
    component_link_type_id      bigint       DEFAULT NULL,
    link_type_id                bigint       DEFAULT NULL,
    code_id                     varchar(255) DEFAULT NULL,
    ui_msg_code_id              varchar(255) DEFAULT NULL,
    excluded                    smallint     DEFAULT NULL,
    share_type_id               smallint     DEFAULT NULL,
    protect_id                  smallint     DEFAULT NULL
);

COMMENT ON TABLE components IS 'for the single components of a view';
COMMENT ON COLUMN components.component_id IS 'the internal unique primary index';
COMMENT ON COLUMN components.user_id IS 'the owner / creator of the component';
COMMENT ON COLUMN components.component_name IS 'the unique name used to select a component by the user';
COMMENT ON COLUMN components.description IS 'to explain the view component to the user with a mouse over text; to be replaced by a language form entry';
COMMENT ON COLUMN components.component_type_id IS 'to select the predefined functionality';
COMMENT ON COLUMN components.word_id_row IS 'for a tree the related value the start node';
COMMENT ON COLUMN components.formula_id IS 'used for type 6';
COMMENT ON COLUMN components.word_id_col IS 'to define the type for the table columns';
COMMENT ON COLUMN components.word_id_col2 IS 'e.g. "quarter" to show the quarters between the year columns or the second axis of a chart';
COMMENT ON COLUMN components.linked_component_id IS 'to link this component to another component';
COMMENT ON COLUMN components.component_link_type_id IS 'to define how this entry links to the other entry';
COMMENT ON COLUMN components.link_type_id IS 'e.g. for type 4 to select possible terms';
COMMENT ON COLUMN components.code_id IS 'used for system components to select the component by the program code';
COMMENT ON COLUMN components.ui_msg_code_id IS 'used for system components the id to select the language specific user interface message e.g. "add word"';
COMMENT ON COLUMN components.excluded IS 'true if a user,but not all,have removed it';
COMMENT ON COLUMN components.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN components.protect_id IS 'to protect against unwanted changes';

--
-- table structure to save user specific changes for the single components of a view
--

CREATE TABLE IF NOT EXISTS user_components
(
    component_id           bigint           NOT NULL,
    user_id                bigint           NOT NULL,
    component_name         varchar(255) DEFAULT NULL,
    description            text         DEFAULT NULL,
    component_type_id      bigint       DEFAULT NULL,
    word_id_row            bigint       DEFAULT NULL,
    formula_id             bigint       DEFAULT NULL,
    word_id_col            bigint       DEFAULT NULL,
    word_id_col2           bigint       DEFAULT NULL,
    linked_component_id    bigint       DEFAULT NULL,
    component_link_type_id bigint       DEFAULT NULL,
    link_type_id           bigint       DEFAULT NULL,
    excluded               smallint     DEFAULT NULL,
    share_type_id          smallint     DEFAULT NULL,
    protect_id             smallint     DEFAULT NULL
);

COMMENT ON TABLE user_components IS 'for the single components of a view';
COMMENT ON COLUMN user_components.component_id IS 'with the user_id the internal unique primary index';
COMMENT ON COLUMN user_components.user_id IS 'the changer of the component';
COMMENT ON COLUMN user_components.component_name IS 'the unique name used to select a component by the user';
COMMENT ON COLUMN user_components.description IS 'to explain the view component to the user with a mouse over text; to be replaced by a language form entry';
COMMENT ON COLUMN user_components.component_type_id IS 'to select the predefined functionality';
COMMENT ON COLUMN user_components.word_id_row IS 'for a tree the related value the start node';
COMMENT ON COLUMN user_components.formula_id IS 'used for type 6';
COMMENT ON COLUMN user_components.word_id_col IS 'to define the type for the table columns';
COMMENT ON COLUMN user_components.word_id_col2 IS 'e.g. "quarter" to show the quarters between the year columns or the second axis of a chart';
COMMENT ON COLUMN user_components.linked_component_id IS 'to link this component to another component';
COMMENT ON COLUMN user_components.component_link_type_id IS 'to define how this entry links to the other entry';
COMMENT ON COLUMN user_components.link_type_id IS 'e.g. for type 4 to select possible terms';
COMMENT ON COLUMN user_components.excluded IS 'true if a user,but not all,have removed it';
COMMENT ON COLUMN user_components.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_components.protect_id IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure for table component_links
-- TODO generate link
--

CREATE TABLE IF NOT EXISTS component_links
(
    component_link_id      BIGSERIAL PRIMARY KEY,
    user_id                bigint   NOT NULL,
    view_id                bigint   NOT NULL,
    component_id           bigint   NOT NULL,
    order_nbr              bigint   NOT NULL,
    position_type          bigint   NOT NULL DEFAULT '2',
    excluded               smallint          DEFAULT NULL,
    share_type_id          smallint          DEFAULT NULL,
    protect_id             smallint NOT NULL DEFAULT '1'
);

COMMENT ON TABLE component_links IS 'A named mask entry can be used in several masks e.g. the company name';
COMMENT ON COLUMN component_links.position_type IS '1=side, 2 =below';

--
-- table structure for table user_component_links
-- TODO generate link
--

CREATE TABLE IF NOT EXISTS user_component_links
(
    component_link_id bigint   NOT NULL,
    user_id           bigint   NOT NULL,
    order_nbr         bigint            DEFAULT NULL,
    position_type     bigint            DEFAULT NULL,
    excluded          smallint          DEFAULT NULL,
    share_type_id     smallint          DEFAULT NULL,
    protect_id        smallint NOT NULL DEFAULT '1'
);

-- --------------------------------------------------------

--
-- table structure for table view_link_types
-- TODO generate type
--

CREATE TABLE IF NOT EXISTS view_link_types
(
    view_link_type_id BIGSERIAL PRIMARY KEY,
    type_name         varchar(200) NOT NULL,
    comment           text         NOT NULL
);

--
-- table structure for table view_term_links
-- TODO generate link
--

CREATE TABLE IF NOT EXISTS view_term_links
(
    view_term_link_id BIGSERIAL PRIMARY KEY,
    term_id           bigint NOT NULL,
    type_id           bigint NOT NULL   DEFAULT '1',
    link_type_id      bigint            DEFAULT NULL,
    view_id           bigint            DEFAULT NULL,
    user_id           bigint NOT NULL,
    description       text   NOT NULL,
    excluded          smallint          DEFAULT NULL,
    share_type_id     smallint          DEFAULT NULL,
    protect_id        smallint NOT NULL DEFAULT NULL
);

COMMENT ON TABLE view_term_links IS 'used to define the default mask for a term or a term group';
COMMENT ON COLUMN view_term_links.type_id IS '1 = from_term_id is link the terms table; 2=link to the term_links table;3=to term_groups';

--
-- table structure for table user_view_term_links
-- TODO generate link
--

CREATE TABLE IF NOT EXISTS user_view_term_links
(
    view_term_link_id BIGSERIAL PRIMARY KEY,
    type_id           bigint NOT NULL   DEFAULT '1',
    link_type_id      bigint            DEFAULT NULL,
    user_id           bigint NOT NULL,
    description       text   NOT NULL,
    excluded          smallint          DEFAULT NULL,
    share_type_id     smallint          DEFAULT NULL,
    protect_id        smallint NOT NULL DEFAULT NULL
);

-- --------------------------------------------------------

--
-- table structure for future use
--
-- TODO generate check remaining
-- TODO generate test create all

-- --------------------------------------------------------

--
-- table structure for table value_formula_links
-- TODO deprecate becasue already included in the group
--

CREATE TABLE IF NOT EXISTS value_formula_links
(
    value_formula_link_id BIGSERIAL PRIMARY KEY,
    group_id              bigint DEFAULT NULL,
    formula_id            bigint DEFAULT NULL,
    user_id               bigint DEFAULT NULL,
    condition_formula_id  bigint DEFAULT NULL,
    comment               text
);

COMMENT ON TABLE value_formula_links IS 'used to select if a saved value should be used or a calculated value';
COMMENT ON COLUMN value_formula_links.condition_formula_id IS 'if true or 1  to formula is preferred';

-- --------------------------------------------------------

--
-- Structure for view phrase_prime (phrases with an id less than 2^16 so that 4 phrase id fit in a 64 bit db key)
-- TODO generate view
--

CREATE OR REPLACE VIEW prime_phrases AS
SELECT w.word_id   AS phrase_id,
       w.user_id,
       w.word_name AS phrase_name,
       w.description,
       w.values,
       w.phrase_type_id,
       w.excluded,
       w.share_type_id,
       w.protect_id
FROM words AS w
WHERE w.word_id < 32767 -- 2^16 / 2 - 1
UNION
SELECT (t.triple_id * -(1)) AS phrase_id,
       t.user_id,
       CASE WHEN (t.triple_name IS NULL) THEN
            CASE WHEN (t.name_given IS NULL)
                 THEN t.name_generated
                 ELSE t.name_given END
            ELSE t.triple_name END AS phrase_name,
       t.description,
       t.values,
       t.phrase_type_id,
       t.excluded,
       t.share_type_id,
       t.protect_id
FROM triples AS t
WHERE t.triple_id < 32767; -- 2^16 / 2 - 1

--
-- Structure for view phrases
-- TODO generate view
--

CREATE OR REPLACE VIEW phrases AS
SELECT w.word_id   AS phrase_id,
       w.user_id,
       w.word_name AS phrase_name,
       w.description,
       w.values,
       w.phrase_type_id,
       w.excluded,
       w.share_type_id,
       w.protect_id
FROM words AS w
UNION
SELECT (t.triple_id * -(1)) AS phrase_id,
       t.user_id,
       CASE WHEN (t.triple_name IS NULL) THEN
            CASE WHEN (t.name_given IS NULL)
                 THEN t.name_generated
                 ELSE t.name_given END
            ELSE t.triple_name END AS phrase_name,
       t.description,
       t.values,
       t.phrase_type_id,
       t.excluded,
       t.share_type_id,
       t.protect_id
FROM triples AS t;

--
-- Structure for the user_phrases view
-- TODO generate view
--

CREATE OR REPLACE VIEW user_prime_phrases AS
SELECT w.word_id   AS phrase_id,
       w.user_id,
       w.word_name AS phrase_name,
       w.description,
       w.values,
       w.excluded,
       w.share_type_id,
       w.protect_id
FROM user_words AS w
WHERE w.word_id < 32767 -- 2^16 / 2 - 1
UNION
SELECT (t.triple_id * -(1)) AS phrase_id,
       t.user_id,
       CASE WHEN (t.triple_name IS NULL) THEN
            CASE WHEN (t.name_given IS NULL)
                 THEN t.name_generated
                 ELSE t.name_given END
            ELSE t.triple_name END AS phrase_name,
       t.description,
       t.values,
       t.excluded,
       t.share_type_id,
       t.protect_id
FROM user_triples AS t
WHERE t.triple_id < 32767; -- 2^16 / 2 - 1

--
-- Structure for the user_phrases view
-- TODO generate view
--

CREATE OR REPLACE VIEW user_phrases AS
SELECT w.word_id   AS phrase_id,
       w.user_id,
       w.word_name AS phrase_name,
       w.description,
       w.values,
       w.excluded,
       w.share_type_id,
       w.protect_id
  FROM user_words AS w
UNION
SELECT (t.triple_id * -(1)) AS phrase_id,
       t.user_id,
       CASE WHEN (t.triple_name IS NULL) THEN
            CASE WHEN (t.name_given IS NULL)
                 THEN t.name_generated
                 ELSE t.name_given END
            ELSE t.triple_name END AS phrase_name,
       t.description,
       t.values,
       t.excluded,
       t.share_type_id,
       t.protect_id
  FROM user_triples AS t;

--
-- Structure for view terms
-- TODO generate view
--

CREATE OR REPLACE VIEW terms AS
SELECT ((w.word_id * 2) - 1) AS term_id,
       w.user_id,
       w.word_name           AS term_name,
       w.description,
       w.values              AS usage,
       w.phrase_type_id        AS term_type_id,
       w.excluded,
       w.share_type_id,
       w.protect_id,
       ''                    AS formula_text,
       ''                    AS resolved_text
FROM words AS w
WHERE w.phrase_type_id <> 10 OR w.phrase_type_id IS NULL
UNION
SELECT ((t.triple_id * -2) + 1)                                                  AS term_id,
       t.user_id,
       CASE WHEN (t.triple_name IS NULL) THEN
                CASE WHEN (t.name_given IS NULL)
                     THEN t.name_generated
                     ELSE t.name_given END
            ELSE t.triple_name END AS phrase_name,
       t.description,
       t.values                                                                     AS usage,
       t.phrase_type_id,
       t.excluded,
       t.share_type_id,
       t.protect_id,
       ''                    AS formula_text,
       ''                    AS resolved_text
FROM triples AS t
UNION
SELECT (f.formula_id * 2) AS term_id,
       f.user_id,
       f.formula_name     AS term_name,
       f.description,
       f.usage            AS usage,
       f.formula_type_id  AS term_type_id,
       f.excluded,
       f.share_type_id,
       f.protect_id,
       f.formula_text     AS formula_text,
       f.resolved_text    AS resolved_text
FROM formulas AS f
UNION
SELECT (v.verb_id * -2) AS term_id,
       NULL            AS user_id,
       v.verb_name     AS term_name,
       v.description,
       v.words         AS usage,
       NULL            AS term_type_id,
       NULL            AS excluded,
       1               AS share_type_id,
       3               AS protect_id,
       ''              AS formula_text,
       ''              AS resolved_text
FROM verbs AS v
;

--
-- Structure for view user_terms
-- TODO generate view
--

CREATE OR REPLACE VIEW user_terms AS
SELECT ((w.word_id * 2) - 1) AS term_id,
       w.user_id,
       w.word_name           AS term_name,
       w.description,
       w.values              AS usage,
       w.excluded,
       w.share_type_id,
       w.protect_id,
       ''                    AS formula_text,
       ''                    AS resolved_text
FROM user_words AS w
WHERE w.phrase_type_id <> 10
UNION
SELECT ((t.triple_id * -2) + 1)  AS term_id,
       t.user_id,
       CASE WHEN (t.triple_name IS NULL) THEN
                CASE WHEN (t.name_given IS NULL)
                     THEN t.name_generated
                     ELSE t.name_given END
            ELSE t.triple_name END AS phrase_name,
       t.description,
       t.values                  AS usage,
       t.excluded,
       t.share_type_id,
       t.protect_id,
       ''                        AS formula_text,
       ''                        AS resolved_text
FROM user_triples AS t
UNION
SELECT (f.formula_id * 2) AS term_id,
       f.user_id,
       f.formula_name     AS term_name,
       f.description,
       f.usage            AS usage,
       f.excluded,
       f.share_type_id,
       f.protect_id,
       f.formula_text     AS formula_text,
       f.resolved_text    AS resolved_text
FROM user_formulas AS f
UNION
SELECT (v.verb_id * -2) AS term_id,
       NULL            AS user_id,
       v.verb_name     AS term_name,
       v.description,
       v.words         AS usage,
       NULL            AS excluded,
       1               AS share_type_id,
       3               AS protect_id,
       ''              AS formula_text,
       ''              AS resolved_text
FROM verbs AS v
;

--
-- Structure for view change_table_fields
-- TODO generate view
--

CREATE OR REPLACE VIEW change_table_fields AS
SELECT f.change_field_id                              AS change_table_field_id,
       concat(t.change_table_id, f.change_field_name) AS change_table_field_name,
       f.description,
       CASE WHEN (f.code_id IS NULL) THEN concat(t.change_table_id, f.change_field_name) ELSE f.code_id END AS code_id
FROM change_fields AS f,
     change_tables AS t
WHERE f.table_id = t.change_table_id;

-- --------------------------------------------------------

--
-- Indexes for tables
-- remark: no index needed for preloaded tables such as phrase types
--

-- --------------------------------------------------------

--
-- indexes for table config
--

CREATE INDEX config_config_name_idx ON config (config_name);
CREATE INDEX config_code_idx ON config (code_id);

-- --------------------------------------------------------

--
-- indexes for table sys_log_status
--

CREATE INDEX sys_log_status_type_name_idx ON sys_log_status (type_name);

-- --------------------------------------------------------

--
-- indexes for table sys_log_functions
--

CREATE INDEX sys_log_functions_sys_log_function_name_idx ON sys_log_functions (sys_log_function_name);

-- --------------------------------------------------------

--
-- indexes for table sys_log
--

CREATE INDEX sys_log_sys_log_time_idx ON sys_log (sys_log_time);
CREATE INDEX sys_log_sys_log_type_idx ON sys_log (sys_log_type_id);
CREATE INDEX sys_log_sys_log_function_idx ON sys_log (sys_log_function_id);
CREATE INDEX sys_log_user_idx ON sys_log (user_id);
CREATE INDEX sys_log_solver_idx ON sys_log (solver_id);
CREATE INDEX sys_log_sys_log_status_idx ON sys_log (sys_log_status_id);

-- --------------------------------------------------------

--
-- indexes for table job_types
--

CREATE INDEX job_types_type_name_idx ON job_types (type_name);

-- --------------------------------------------------------

--
-- indexes for table job_times
--

CREATE INDEX job_times_schedule_idx ON job_times (schedule);
CREATE INDEX job_times_job_type_idx ON job_times (job_type_id);
CREATE INDEX job_times_user_idx ON job_times (user_id);
CREATE INDEX job_times_parameter_idx ON job_times (parameter);

-- --------------------------------------------------------

--
-- indexes for table jobs
--

CREATE INDEX jobs_user_idx ON jobs (user_id);
CREATE INDEX jobs_job_type_idx ON jobs (job_type_id);
CREATE INDEX jobs_request_time_idx ON jobs (request_time);
CREATE INDEX jobs_start_time_idx ON jobs (start_time);
CREATE INDEX jobs_end_time_idx ON jobs (end_time);
CREATE INDEX jobs_parameter_idx ON jobs (parameter);
CREATE INDEX jobs_change_field_idx ON jobs (change_field_id);
CREATE INDEX jobs_row_idx ON jobs (row_id);

-- --------------------------------------------------------

--
-- indexes for table user_types
--

CREATE INDEX user_types_type_name_idx ON user_types (type_name);

-- --------------------------------------------------------

--
-- indexes for table user_profiles
--

CREATE INDEX user_profiles_type_name_idx ON user_profiles (type_name);

-- --------------------------------------------------------

--
-- indexes for table user_official_types
--

CREATE INDEX user_official_types_type_name_idx ON user_official_types (type_name);

-- --------------------------------------------------------

--
-- indexes for table users
--

CREATE INDEX users_user_name_idx ON users (user_name);
CREATE INDEX users_ip_address_idx ON users (ip_address);
CREATE INDEX users_code_idx ON users (code_id);
CREATE INDEX users_user_profile_idx ON users (user_profile_id);
CREATE INDEX users_user_type_idx ON users (user_type_id);

-- --------------------------------------------------------

--
-- indexes for table ip_ranges
--

CREATE INDEX ip_ranges_ip_from_idx ON ip_ranges (ip_from);
CREATE INDEX ip_ranges_ip_to_idx ON ip_ranges (ip_to);

-- --------------------------------------------------------

--
-- indexes for table sessions
--

CREATE INDEX sessions_uid_idx ON sessions (uid);

-- --------------------------------------------------------

--
-- indexes for table change_actions
--

CREATE INDEX change_actions_change_action_name_idx ON change_actions (change_action_name);

-- --------------------------------------------------------

--
-- indexes for table change_tables
--

CREATE INDEX change_tables_change_table_name_idx ON change_tables (change_table_name);

-- --------------------------------------------------------

--
-- indexes for table change_fields
--

CREATE UNIQUE INDEX change_fields_unique_idx ON change_fields (table_id,change_field_name);
CREATE INDEX change_fields_table_idx ON change_fields (table_id);
CREATE INDEX change_fields_change_field_name_idx ON change_fields (change_field_name);

-- --------------------------------------------------------

--
-- indexes for table changes
--

CREATE INDEX changes_change_idx ON changes (change_id);
CREATE INDEX changes_change_time_idx ON changes (change_time);
CREATE INDEX changes_user_idx ON changes (user_id);

-- --------------------------------------------------------

--
-- indexes for table change_standard_values
--

CREATE INDEX change_standard_values_change_idx ON change_standard_values (change_id);
CREATE INDEX change_standard_values_change_time_idx ON change_standard_values (change_time);
CREATE INDEX change_standard_values_user_idx ON change_standard_values (user_id);

-- --------------------------------------------------------

--
-- indexes for table change_prime_values
--

CREATE INDEX change_prime_values_change_idx ON change_prime_values (change_id);
CREATE INDEX change_prime_values_change_time_idx ON change_prime_values (change_time);
CREATE INDEX change_prime_values_user_idx ON change_prime_values (user_id);

-- --------------------------------------------------------

--
-- indexes for table change_big_values
--

CREATE INDEX change_big_values_change_idx ON change_big_values (change_id);
CREATE INDEX change_big_values_change_time_idx ON change_big_values (change_time);
CREATE INDEX change_big_values_user_idx ON change_big_values (user_id);

-- --------------------------------------------------------

--
-- indexes for table change_links
--

CREATE INDEX change_links_change_link_idx ON change_links (change_link_id);
CREATE INDEX change_links_change_time_idx ON change_links (change_time);
CREATE INDEX change_links_user_idx ON change_links (user_id);

-- --------------------------------------------------------

--
-- indexes for table pod_types
--

CREATE INDEX pod_types_type_name_idx ON pod_types (type_name);

-- --------------------------------------------------------

--
-- indexes for table pod_status
--

CREATE INDEX pod_status_type_name_idx ON pod_status (type_name);

-- --------------------------------------------------------

--
-- indexes for table pods
--

CREATE INDEX pods_type_name_idx ON pods (type_name);
CREATE INDEX pods_pod_type_idx ON pods (pod_type_id);
CREATE INDEX pods_pod_status_idx ON pods (pod_status_id);

-- --------------------------------------------------------

--
-- indexes for table protection_types
--

CREATE INDEX protection_types_type_name_idx ON protection_types (type_name);

-- --------------------------------------------------------

--
-- indexes for table share_types
--

CREATE INDEX share_types_type_name_idx ON share_types (type_name);

-- --------------------------------------------------------

--
-- indexes for table phrase_types
--

CREATE INDEX phrase_types_type_name_idx ON phrase_types (type_name);

-- --------------------------------------------------------

--
-- indexes for table source_types
--

CREATE INDEX source_types_type_name_idx ON source_types (type_name);

-- --------------------------------------------------------

--
-- indexes for table languages
--

CREATE INDEX languages_language_name_idx ON languages (language_name);

-- --------------------------------------------------------

--
-- indexes for table language_forms
--

CREATE INDEX language_forms_language_form_name_idx ON language_forms (language_form_name);
CREATE INDEX language_forms_language_idx ON language_forms (language_id);

-- --------------------------------------------------------

--
-- indexes for table words
--
CREATE INDEX words_user_idx        ON words (user_id);
CREATE INDEX words_word_name_idx   ON words (word_name);
CREATE INDEX words_plural_idx      ON words (plural);
CREATE INDEX words_phrase_type_idx ON words (phrase_type_id);
CREATE INDEX words_view_idx        ON words (view_id);

--
-- indexes for table user_words
--
ALTER TABLE user_words ADD CONSTRAINT user_words_pkey PRIMARY KEY (word_id, user_id, language_id);
CREATE INDEX user_words_word_idx        ON user_words (word_id);
CREATE INDEX user_words_user_idx        ON user_words (user_id);
CREATE INDEX user_words_language_idx    ON user_words (language_id);
CREATE INDEX user_words_word_name_idx   ON user_words (word_name);
CREATE INDEX user_words_plural_idx      ON user_words (plural);
CREATE INDEX user_words_phrase_type_idx ON user_words (phrase_type_id);
CREATE INDEX user_words_view_idx        ON user_words (view_id);

-- --------------------------------------------------------

--
-- indexes for table verbs
--

CREATE INDEX verbs_verb_name_idx ON verbs (verb_name);

-- --------------------------------------------------------

--
-- indexes for table triples
--

CREATE UNIQUE INDEX triples_unique_idx  ON triples (from_phrase_id, verb_id, to_phrase_id);
CREATE INDEX triples_user_idx           ON triples (user_id);
CREATE INDEX triples_from_phrase_idx    ON triples (from_phrase_id);
CREATE INDEX triples_verb_idx           ON triples (verb_id);
CREATE INDEX triples_to_phrase_idx      ON triples (to_phrase_id);
CREATE INDEX triples_triple_name_idx    ON triples (triple_name);
CREATE INDEX triples_name_given_idx     ON triples (name_given);
CREATE INDEX triples_name_generated_idx ON triples (name_generated);
CREATE INDEX triples_phrase_type_idx    ON triples (phrase_type_id);
CREATE INDEX triples_view_idx           ON triples (view_id);

--
-- indexes for table user_triples
--

ALTER TABLE user_triples ADD CONSTRAINT user_triples_pkey PRIMARY KEY (triple_id, user_id, language_id);
CREATE INDEX user_triples_triple_idx         ON user_triples (triple_id);
CREATE INDEX user_triples_user_idx           ON user_triples (user_id);
CREATE INDEX user_triples_language_idx       ON user_triples (language_id);
CREATE INDEX user_triples_triple_name_idx    ON user_triples (triple_name);
CREATE INDEX user_triples_name_given_idx     ON user_triples (name_given);
CREATE INDEX user_triples_name_generated_idx ON user_triples (name_generated);
CREATE INDEX user_triples_phrase_type_idx    ON user_triples (phrase_type_id);
CREATE INDEX user_triples_view_idx           ON user_triples (view_id);

-- --------------------------------------------------------

--
-- indexes for table phrase_table_status
--

CREATE INDEX phrase_table_status_type_name_idx ON phrase_table_status (type_name);

-- --------------------------------------------------------

--
-- indexes for table phrase_tables
--

CREATE INDEX phrase_tables_phrase_idx ON phrase_tables (phrase_id);
CREATE INDEX phrase_tables_pod_idx ON phrase_tables (pod_id);
CREATE INDEX phrase_tables_phrase_table_status_idx ON phrase_tables (phrase_table_status_id);

-- --------------------------------------------------------

--
-- indexes for table groups
--
CREATE INDEX groups_user_idx ON groups (user_id);

--
-- indexes for table user_groups
--
ALTER TABLE user_groups ADD CONSTRAINT user_groups_pkey PRIMARY KEY (group_id, user_id);
CREATE INDEX user_groups_user_idx ON user_groups (user_id);

--
-- indexes for table groups_prime
--
CREATE INDEX groups_prime_user_idx ON groups_prime (user_id);

--
-- indexes for table user_groups_prime
--
ALTER TABLE user_groups_prime ADD CONSTRAINT user_groups_prime_pkey PRIMARY KEY (group_id, user_id);
CREATE INDEX user_groups_prime_user_idx ON user_groups_prime (user_id);

--
-- indexes for table groups_big
--
CREATE INDEX groups_big_user_idx ON groups_big (user_id);

--
-- indexes for table user_groups_big
--
ALTER TABLE user_groups_big ADD CONSTRAINT user_groups_big_pkey PRIMARY KEY (group_id, user_id);
CREATE INDEX user_groups_big_user_idx ON user_groups_big (user_id);

-- --------------------------------------------------------

--
-- indexes for table sources
--
CREATE INDEX sources_user_idx        ON sources (user_id);
CREATE INDEX sources_source_name_idx ON sources (source_name);
CREATE INDEX sources_source_type_idx ON sources (source_type_id);
--
-- indexes for table user_sources
--
ALTER TABLE user_sources ADD CONSTRAINT user_sources_pkey PRIMARY KEY (source_id,user_id);
CREATE INDEX user_sources_source_idx      ON user_sources (source_id);
CREATE INDEX user_sources_user_idx        ON user_sources (user_id);
CREATE INDEX user_sources_source_name_idx ON user_sources (source_name);
CREATE INDEX user_sources_source_type_idx ON user_sources (source_type_id);

--
-- Indexes for table source_values
--
CREATE INDEX source_value_group_idx ON source_values (group_id);
CREATE INDEX source_value_source_idx ON source_values (source_id);
CREATE INDEX source_value_user_idx ON source_values (user_id);

--
-- Indexes for table refs
-- TODO next
--
CREATE UNIQUE INDEX ref_phrase_type_idx ON refs (phrase_id, ref_type_id);
CREATE INDEX ref_type_idx ON refs (ref_type_id);

--
-- Indexes for table user_refs
--
ALTER TABLE user_refs ADD CONSTRAINT user_ref_pkey PRIMARY KEY (ref_id, user_id);
CREATE INDEX user_ref_user_idx ON user_refs (user_id);
CREATE INDEX user_ref_idx ON user_refs (ref_id);

-- --------------------------------------------------------

--
-- indexes for table values_standard_prime
--
CREATE UNIQUE INDEX values_standard_prime_pkey ON values_standard_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4);
CREATE INDEX values_standard_prime_phrase_id_1_idx ON values_standard_prime (phrase_id_1);
CREATE INDEX values_standard_prime_phrase_id_2_idx ON values_standard_prime (phrase_id_2);
CREATE INDEX values_standard_prime_phrase_id_3_idx ON values_standard_prime (phrase_id_3);
CREATE INDEX values_standard_prime_phrase_id_4_idx ON values_standard_prime (phrase_id_4);
CREATE INDEX values_standard_prime_source_idx ON values_standard_prime (source_id);

--
-- indexes for table values_standard
--
CREATE INDEX values_standard_source_idx ON values_standard (source_id);

--
-- indexes for table values
--
CREATE INDEX values_source_idx ON values (source_id);
CREATE INDEX values_user_idx ON values (user_id);

--
-- indexes for table user_values
--
ALTER TABLE user_values ADD CONSTRAINT user_values_pkey PRIMARY KEY (group_id, user_id);
CREATE INDEX user_values_user_idx ON user_values (user_id);
CREATE INDEX user_values_source_idx ON user_values (source_id);

--
-- indexes for table values_prime
--
CREATE UNIQUE INDEX values_prime_pkey ON values_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4);
CREATE INDEX values_prime_phrase_id_1_idx ON values_prime (phrase_id_1);
CREATE INDEX values_prime_phrase_id_2_idx ON values_prime (phrase_id_2);
CREATE INDEX values_prime_phrase_id_3_idx ON values_prime (phrase_id_3);
CREATE INDEX values_prime_phrase_id_4_idx ON values_prime (phrase_id_4);
CREATE INDEX values_prime_source_idx ON values_prime (source_id);
CREATE INDEX values_prime_user_idx ON values_prime (user_id);

--
-- indexes for table user_values_prime
--
CREATE UNIQUE INDEX user_values_prime_pkey ON user_values_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, user_id);
CREATE INDEX user_values_prime_phrase_id_1_idx ON user_values_prime (phrase_id_1);
CREATE INDEX user_values_prime_phrase_id_2_idx ON user_values_prime (phrase_id_2);
CREATE INDEX user_values_prime_phrase_id_3_idx ON user_values_prime (phrase_id_3);
CREATE INDEX user_values_prime_phrase_id_4_idx ON user_values_prime (phrase_id_4);
CREATE INDEX user_values_prime_user_idx ON user_values_prime (user_id);
CREATE INDEX user_values_prime_source_idx ON user_values_prime (source_id);

--
-- indexes for table values_big
--
CREATE INDEX values_big_source_idx ON values_big (source_id);
CREATE INDEX values_big_user_idx ON values_big (user_id);

--
-- indexes for table user_values_big
--
ALTER TABLE user_values_big ADD CONSTRAINT user_values_big_pkey PRIMARY KEY (group_id, user_id);
CREATE INDEX user_values_big_user_idx ON user_values_big (user_id);
CREATE INDEX user_values_big_source_idx ON user_values_big (source_id);

-- --------------------------------------------------------

--
-- indexes for table values_text_standard_prime
--
CREATE UNIQUE INDEX values_text_standard_prime_pkey ON values_text_standard_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4);
CREATE INDEX values_text_standard_prime_phrase_id_1_idx ON values_text_standard_prime (phrase_id_1);
CREATE INDEX values_text_standard_prime_phrase_id_2_idx ON values_text_standard_prime (phrase_id_2);
CREATE INDEX values_text_standard_prime_phrase_id_3_idx ON values_text_standard_prime (phrase_id_3);
CREATE INDEX values_text_standard_prime_phrase_id_4_idx ON values_text_standard_prime (phrase_id_4);
CREATE INDEX values_text_standard_prime_source_idx ON values_text_standard_prime (source_id);

--
-- indexes for table values_text_standard
--
CREATE INDEX values_text_standard_source_idx ON values_text_standard (source_id);

--
-- indexes for table values_text
--
CREATE INDEX values_text_source_idx ON values_text (source_id);
CREATE INDEX values_text_user_idx ON values_text (user_id);

--
-- indexes for table user_values_text
--
ALTER TABLE user_values_text ADD CONSTRAINT user_values_text_pkey PRIMARY KEY (group_id, user_id);
CREATE INDEX user_values_text_user_idx ON user_values_text (user_id);
CREATE INDEX user_values_text_source_idx ON user_values_text (source_id);

--
-- indexes for table values_text_prime
--
CREATE UNIQUE INDEX values_text_prime_pkey ON values_text_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4);
CREATE INDEX values_text_prime_phrase_id_1_idx ON values_text_prime (phrase_id_1);
CREATE INDEX values_text_prime_phrase_id_2_idx ON values_text_prime (phrase_id_2);
CREATE INDEX values_text_prime_phrase_id_3_idx ON values_text_prime (phrase_id_3);
CREATE INDEX values_text_prime_phrase_id_4_idx ON values_text_prime (phrase_id_4);
CREATE INDEX values_text_prime_source_idx ON values_text_prime (source_id);
CREATE INDEX values_text_prime_user_idx ON values_text_prime (user_id);

--
-- indexes for table user_values_text_prime
--
CREATE UNIQUE INDEX user_values_text_prime_pkey ON user_values_text_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, user_id);
CREATE INDEX user_values_text_prime_phrase_id_1_idx ON user_values_text_prime (phrase_id_1);
CREATE INDEX user_values_text_prime_phrase_id_2_idx ON user_values_text_prime (phrase_id_2);
CREATE INDEX user_values_text_prime_phrase_id_3_idx ON user_values_text_prime (phrase_id_3);
CREATE INDEX user_values_text_prime_phrase_id_4_idx ON user_values_text_prime (phrase_id_4);
CREATE INDEX user_values_text_prime_user_idx ON user_values_text_prime (user_id);
CREATE INDEX user_values_text_prime_source_idx ON user_values_text_prime (source_id);

--
-- indexes for table values_text_big
--
CREATE INDEX values_text_big_source_idx ON values_text_big (source_id);
CREATE INDEX values_text_big_user_idx ON values_text_big (user_id);

--
-- indexes for table user_values_text_big
--
ALTER TABLE user_values_text_big ADD CONSTRAINT user_values_text_big_pkey PRIMARY KEY (group_id, user_id);
CREATE INDEX user_values_text_big_user_idx ON user_values_text_big (user_id);
CREATE INDEX user_values_text_big_source_idx ON user_values_text_big (source_id);

-- --------------------------------------------------------

--
-- indexes for table values_time_standard_prime
--
CREATE UNIQUE INDEX values_time_standard_prime_pkey ON values_time_standard_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4);
CREATE INDEX values_time_standard_prime_phrase_id_1_idx ON values_time_standard_prime (phrase_id_1);
CREATE INDEX values_time_standard_prime_phrase_id_2_idx ON values_time_standard_prime (phrase_id_2);
CREATE INDEX values_time_standard_prime_phrase_id_3_idx ON values_time_standard_prime (phrase_id_3);
CREATE INDEX values_time_standard_prime_phrase_id_4_idx ON values_time_standard_prime (phrase_id_4);
CREATE INDEX values_time_standard_prime_source_idx ON values_time_standard_prime (source_id);

--
-- indexes for table values_time_standard
--
CREATE INDEX values_time_standard_source_idx ON values_time_standard (source_id);

--
-- indexes for table values_time
--
CREATE INDEX values_time_source_idx ON values_time (source_id);
CREATE INDEX values_time_user_idx ON values_time (user_id);

--
-- indexes for table user_values_time
--
ALTER TABLE user_values_time ADD CONSTRAINT user_values_time_pkey PRIMARY KEY (group_id, user_id);
CREATE INDEX user_values_time_user_idx ON user_values_time (user_id);
CREATE INDEX user_values_time_source_idx ON user_values_time (source_id);

--
-- indexes for table values_time_prime
--
CREATE UNIQUE INDEX values_time_prime_pkey ON values_time_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4);
CREATE INDEX values_time_prime_phrase_id_1_idx ON values_time_prime (phrase_id_1);
CREATE INDEX values_time_prime_phrase_id_2_idx ON values_time_prime (phrase_id_2);
CREATE INDEX values_time_prime_phrase_id_3_idx ON values_time_prime (phrase_id_3);
CREATE INDEX values_time_prime_phrase_id_4_idx ON values_time_prime (phrase_id_4);
CREATE INDEX values_time_prime_source_idx ON values_time_prime (source_id);
CREATE INDEX values_time_prime_user_idx ON values_time_prime (user_id);

--
-- indexes for table user_values_time_prime
--
CREATE UNIQUE INDEX user_values_time_prime_pkey ON user_values_time_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, user_id);
CREATE INDEX user_values_time_prime_phrase_id_1_idx ON user_values_time_prime (phrase_id_1);
CREATE INDEX user_values_time_prime_phrase_id_2_idx ON user_values_time_prime (phrase_id_2);
CREATE INDEX user_values_time_prime_phrase_id_3_idx ON user_values_time_prime (phrase_id_3);
CREATE INDEX user_values_time_prime_phrase_id_4_idx ON user_values_time_prime (phrase_id_4);
CREATE INDEX user_values_time_prime_user_idx ON user_values_time_prime (user_id);
CREATE INDEX user_values_time_prime_source_idx ON user_values_time_prime (source_id);

--
-- indexes for table values_time_big
--
CREATE INDEX values_time_big_source_idx ON values_time_big (source_id);
CREATE INDEX values_time_big_user_idx ON values_time_big (user_id);

--
-- indexes for table user_values_time_big
--
ALTER TABLE user_values_time_big ADD CONSTRAINT user_values_time_big_pkey PRIMARY KEY (group_id, user_id);
CREATE INDEX user_values_time_big_user_idx ON user_values_time_big (user_id);
CREATE INDEX user_values_time_big_source_idx ON user_values_time_big (source_id);

-- --------------------------------------------------------

--
-- indexes for table values_geo_standard_prime
--
CREATE UNIQUE INDEX values_geo_standard_prime_pkey ON values_geo_standard_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4);
CREATE INDEX values_geo_standard_prime_phrase_id_1_idx ON values_geo_standard_prime (phrase_id_1);
CREATE INDEX values_geo_standard_prime_phrase_id_2_idx ON values_geo_standard_prime (phrase_id_2);
CREATE INDEX values_geo_standard_prime_phrase_id_3_idx ON values_geo_standard_prime (phrase_id_3);
CREATE INDEX values_geo_standard_prime_phrase_id_4_idx ON values_geo_standard_prime (phrase_id_4);
CREATE INDEX values_geo_standard_prime_source_idx ON values_geo_standard_prime (source_id);

--
-- indexes for table values_geo_standard
--
CREATE INDEX values_geo_standard_source_idx ON values_geo_standard (source_id);

--
-- indexes for table values_geo
--
CREATE INDEX values_geo_source_idx ON values_geo (source_id);
CREATE INDEX values_geo_user_idx ON values_geo (user_id);

--
-- indexes for table user_values_geo
--
ALTER TABLE user_values_geo ADD CONSTRAINT user_values_geo_pkey PRIMARY KEY (group_id, user_id);
CREATE INDEX user_values_geo_user_idx ON user_values_geo (user_id);
CREATE INDEX user_values_geo_source_idx ON user_values_geo (source_id);

--
-- indexes for table values_geo_prime
--
CREATE UNIQUE INDEX values_geo_prime_pkey ON values_geo_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4);
CREATE INDEX values_geo_prime_phrase_id_1_idx ON values_geo_prime (phrase_id_1);
CREATE INDEX values_geo_prime_phrase_id_2_idx ON values_geo_prime (phrase_id_2);
CREATE INDEX values_geo_prime_phrase_id_3_idx ON values_geo_prime (phrase_id_3);
CREATE INDEX values_geo_prime_phrase_id_4_idx ON values_geo_prime (phrase_id_4);
CREATE INDEX values_geo_prime_source_idx ON values_geo_prime (source_id);
CREATE INDEX values_geo_prime_user_idx ON values_geo_prime (user_id);

--
-- indexes for table user_values_geo_prime
--
CREATE UNIQUE INDEX user_values_geo_prime_pkey ON user_values_geo_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, user_id);
CREATE INDEX user_values_geo_prime_phrase_id_1_idx ON user_values_geo_prime (phrase_id_1);
CREATE INDEX user_values_geo_prime_phrase_id_2_idx ON user_values_geo_prime (phrase_id_2);
CREATE INDEX user_values_geo_prime_phrase_id_3_idx ON user_values_geo_prime (phrase_id_3);
CREATE INDEX user_values_geo_prime_phrase_id_4_idx ON user_values_geo_prime (phrase_id_4);
CREATE INDEX user_values_geo_prime_user_idx ON user_values_geo_prime (user_id);
CREATE INDEX user_values_geo_prime_source_idx ON user_values_geo_prime (source_id);

--
-- indexes for table values_geo_big
--
CREATE INDEX values_geo_big_source_idx ON values_geo_big (source_id);
CREATE INDEX values_geo_big_user_idx ON values_geo_big (user_id);

--
-- indexes for table user_values_geo_big
--
ALTER TABLE user_values_geo_big ADD CONSTRAINT user_values_geo_big_pkey PRIMARY KEY (group_id, user_id);
CREATE INDEX user_values_geo_big_user_idx ON user_values_geo_big (user_id);
CREATE INDEX user_values_geo_big_source_idx ON user_values_geo_big (source_id);

-- --------------------------------------------------------

--
-- Indexes for table value_ts_data
--
CREATE UNIQUE INDEX value_time_series_idx ON value_ts_data (value_time_series_id, val_time);

-- --------------------------------------------------------

--
-- Indexes for table formula_elements
--
CREATE INDEX formula_element_idx ON formula_elements (formula_id);
CREATE INDEX formula_element_type_idx ON formula_elements (formula_element_type_id);

-- --------------------------------------------------------

--
-- indexes for table formula_types
--

CREATE INDEX formula_types_type_name_idx ON formula_types (type_name);

-- --------------------------------------------------------

--
-- indexes for table formulas
--
CREATE INDEX formulas_user_idx ON formulas (user_id);
CREATE UNIQUE INDEX formulas_formula_name_idx ON formulas (formula_name);
CREATE INDEX formulas_formula_type_idx ON formulas (formula_type_id);
CREATE INDEX formulas_view_idx ON formulas (view_id);

--
-- indexes for table user_formulas
--
ALTER TABLE user_formulas ADD CONSTRAINT user_formulas_pkey PRIMARY KEY (formula_id,user_id);
CREATE INDEX user_formulas_formula_idx ON user_formulas (formula_id);
CREATE INDEX user_formulas_user_idx ON user_formulas (user_id);
CREATE INDEX user_formulas_formula_name_idx ON user_formulas (formula_name);
CREATE INDEX user_formulas_formula_type_idx ON user_formulas (formula_type_id);
CREATE INDEX user_formulas_view_idx ON user_formulas (view_id);

--
-- Indexes for table formula_links
--
CREATE INDEX formula_link_user_idx ON formula_links (user_id);
CREATE INDEX formula_link_idx ON formula_links (formula_id);
CREATE INDEX formula_link_type_idx ON formula_links (link_type_id);

--
-- Indexes for table user_formula_links
--
CREATE UNIQUE INDEX user_formula_link_unique_idx ON user_formula_links (formula_link_id, user_id);
CREATE INDEX user_formula_link_idx ON user_formula_links (formula_link_id);
CREATE INDEX user_formula_link_user_idx ON user_formula_links (user_id);
CREATE INDEX user_formula_link_type_idx ON user_formula_links (link_type_id);

-- --------------------------------------------------------

--
-- indexes for table results_standard_prime
--
CREATE UNIQUE INDEX results_standard_prime_pkey ON results_standard_prime (formula_id, phrase_id_1, phrase_id_2, phrase_id_3);
CREATE INDEX results_standard_prime_formula_idx ON results_standard_prime (formula_id);
CREATE INDEX results_standard_prime_phrase_id_1_idx ON results_standard_prime (phrase_id_1);
CREATE INDEX results_standard_prime_phrase_id_2_idx ON results_standard_prime (phrase_id_2);
CREATE INDEX results_standard_prime_phrase_id_3_idx ON results_standard_prime (phrase_id_3);

--
-- indexes for table results_standard_main
--
CREATE UNIQUE INDEX results_standard_main_pkey ON results_standard_main (formula_id, phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, phrase_id_5, phrase_id_6, phrase_id_7);
CREATE INDEX results_standard_main_formula_idx ON results_standard_main (formula_id);
CREATE INDEX results_standard_main_phrase_id_1_idx ON results_standard_main (phrase_id_1);
CREATE INDEX results_standard_main_phrase_id_2_idx ON results_standard_main (phrase_id_2);
CREATE INDEX results_standard_main_phrase_id_3_idx ON results_standard_main (phrase_id_3);
CREATE INDEX results_standard_main_phrase_id_4_idx ON results_standard_main (phrase_id_4);
CREATE INDEX results_standard_main_phrase_id_5_idx ON results_standard_main (phrase_id_5);
CREATE INDEX results_standard_main_phrase_id_6_idx ON results_standard_main (phrase_id_6);
CREATE INDEX results_standard_main_phrase_id_7_idx ON results_standard_main (phrase_id_7);

--
-- indexes for table results_standard
--

--
-- indexes for table results
--
CREATE INDEX results_source_group_idx ON results (source_group_id);
CREATE INDEX results_formula_idx ON results (formula_id);
CREATE INDEX results_user_idx ON results (user_id);

--
-- indexes for table user_results
--
ALTER TABLE user_results ADD CONSTRAINT user_results_pkey PRIMARY KEY (group_id, user_id);
CREATE INDEX user_results_source_group_idx ON user_results (source_group_id);
CREATE INDEX user_results_user_idx ON user_results (user_id);
CREATE INDEX user_results_formula_idx ON user_results (formula_id);

--
-- indexes for table results_prime
--
CREATE UNIQUE INDEX results_prime_pkey ON results_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4);
CREATE INDEX results_prime_phrase_id_1_idx ON results_prime (phrase_id_1);
CREATE INDEX results_prime_phrase_id_2_idx ON results_prime (phrase_id_2);
CREATE INDEX results_prime_phrase_id_3_idx ON results_prime (phrase_id_3);
CREATE INDEX results_prime_phrase_id_4_idx ON results_prime (phrase_id_4);
CREATE INDEX results_prime_source_group_idx ON results_prime (source_group_id);
CREATE INDEX results_prime_formula_idx ON results_prime (formula_id);
CREATE INDEX results_prime_user_idx ON results_prime (user_id);

--
-- indexes for table user_results_prime
--
CREATE UNIQUE INDEX user_results_prime_pkey ON user_results_prime (phrase_id_1,phrase_id_2,phrase_id_3,phrase_id_4, user_id);
CREATE INDEX user_results_prime_phrase_id_1_idx ON user_results_prime (phrase_id_1);
CREATE INDEX user_results_prime_phrase_id_2_idx ON user_results_prime (phrase_id_2);
CREATE INDEX user_results_prime_phrase_id_3_idx ON user_results_prime (phrase_id_3);
CREATE INDEX user_results_prime_phrase_id_4_idx ON user_results_prime (phrase_id_4);
CREATE INDEX user_results_prime_source_group_idx ON user_results_prime (source_group_id);
CREATE INDEX user_results_prime_user_idx ON user_results_prime (user_id);
CREATE INDEX user_results_prime_formula_idx ON user_results_prime (formula_id);

--
-- indexes for table results_main
--
CREATE UNIQUE INDEX results_main_pkey ON results_main (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, phrase_id_5, phrase_id_6, phrase_id_7, phrase_id_8);
CREATE INDEX results_main_phrase_id_1_idx ON results_main (phrase_id_1);
CREATE INDEX results_main_phrase_id_2_idx ON results_main (phrase_id_2);
CREATE INDEX results_main_phrase_id_3_idx ON results_main (phrase_id_3);
CREATE INDEX results_main_phrase_id_4_idx ON results_main (phrase_id_4);
CREATE INDEX results_main_phrase_id_5_idx ON results_main (phrase_id_5);
CREATE INDEX results_main_phrase_id_6_idx ON results_main (phrase_id_6);
CREATE INDEX results_main_phrase_id_7_idx ON results_main (phrase_id_7);
CREATE INDEX results_main_phrase_id_8_idx ON results_main (phrase_id_8);
CREATE INDEX results_main_source_group_idx ON results_main (source_group_id);
CREATE INDEX results_main_formula_idx ON results_main (formula_id);
CREATE INDEX results_main_user_idx ON results_main (user_id);

--
-- indexes for table user_results_main
--
CREATE UNIQUE INDEX user_results_main_pkey ON user_results_main (phrase_id_1,phrase_id_2,phrase_id_3,phrase_id_4, phrase_id_5, phrase_id_6, phrase_id_7, phrase_id_8, user_id);
CREATE INDEX user_results_main_phrase_id_1_idx ON user_results_main (phrase_id_1);
CREATE INDEX user_results_main_phrase_id_2_idx ON user_results_main (phrase_id_2);
CREATE INDEX user_results_main_phrase_id_3_idx ON user_results_main (phrase_id_3);
CREATE INDEX user_results_main_phrase_id_4_idx ON user_results_main (phrase_id_4);
CREATE INDEX user_results_main_phrase_id_5_idx ON user_results_main (phrase_id_5);
CREATE INDEX user_results_main_phrase_id_6_idx ON user_results_main (phrase_id_6);
CREATE INDEX user_results_main_phrase_id_7_idx ON user_results_main (phrase_id_7);
CREATE INDEX user_results_main_phrase_id_8_idx ON user_results_main (phrase_id_8);
CREATE INDEX user_results_main_source_group_idx ON user_results_main (source_group_id);
CREATE INDEX user_results_main_user_idx ON user_results_main (user_id);
CREATE INDEX user_results_main_formula_idx ON user_results_main (formula_id);

--
-- indexes for table results_big
--
CREATE INDEX results_big_source_group_idx ON results_big (source_group_id);
CREATE INDEX results_big_formula_idx ON results_big (formula_id);
CREATE INDEX results_big_user_idx ON results_big (user_id);

--
-- indexes for table user_results_big
--
ALTER TABLE user_results_big ADD CONSTRAINT user_results_big_pkey PRIMARY KEY (group_id, user_id);
CREATE INDEX user_results_big_source_group_idx ON user_results_big (source_group_id);
CREATE INDEX user_results_big_user_idx ON user_results_big (user_id);
CREATE INDEX user_results_big_formula_idx ON user_results_big (formula_id);

-- --------------------------------------------------------

--
-- indexes for table results_text_standard_prime
--
CREATE UNIQUE INDEX results_text_standard_prime_pkey ON results_text_standard_prime (formula_id, phrase_id_1, phrase_id_2, phrase_id_3);
CREATE INDEX results_text_standard_prime_formula_idx ON results_text_standard_prime (formula_id);
CREATE INDEX results_text_standard_prime_phrase_id_1_idx ON results_text_standard_prime (phrase_id_1);
CREATE INDEX results_text_standard_prime_phrase_id_2_idx ON results_text_standard_prime (phrase_id_2);
CREATE INDEX results_text_standard_prime_phrase_id_3_idx ON results_text_standard_prime (phrase_id_3);

--
-- indexes for table results_text_standard_main
--
CREATE UNIQUE INDEX results_text_standard_main_pkey ON results_text_standard_main (formula_id, phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, phrase_id_5, phrase_id_6, phrase_id_7);
CREATE INDEX results_text_standard_main_formula_idx ON results_text_standard_main (formula_id);
CREATE INDEX results_text_standard_main_phrase_id_1_idx ON results_text_standard_main (phrase_id_1);
CREATE INDEX results_text_standard_main_phrase_id_2_idx ON results_text_standard_main (phrase_id_2);
CREATE INDEX results_text_standard_main_phrase_id_3_idx ON results_text_standard_main (phrase_id_3);
CREATE INDEX results_text_standard_main_phrase_id_4_idx ON results_text_standard_main (phrase_id_4);
CREATE INDEX results_text_standard_main_phrase_id_5_idx ON results_text_standard_main (phrase_id_5);
CREATE INDEX results_text_standard_main_phrase_id_6_idx ON results_text_standard_main (phrase_id_6);
CREATE INDEX results_text_standard_main_phrase_id_7_idx ON results_text_standard_main (phrase_id_7);

--
-- indexes for table results_text_standard
--

--
-- indexes for table results_text
--
CREATE INDEX results_text_source_group_idx ON results_text (source_group_id);
CREATE INDEX results_text_formula_idx ON results_text (formula_id);
CREATE INDEX results_text_user_idx ON results_text (user_id);

--
-- indexes for table user_results_text
--
ALTER TABLE user_results_text ADD CONSTRAINT user_results_text_pkey PRIMARY KEY (group_id, user_id);
CREATE INDEX user_results_text_source_group_idx ON user_results_text (source_group_id);
CREATE INDEX user_results_text_user_idx ON user_results_text (user_id);
CREATE INDEX user_results_text_formula_idx ON user_results_text (formula_id);

--
-- indexes for table results_text_prime
--
CREATE UNIQUE INDEX results_text_prime_pkey ON results_text_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4);
CREATE INDEX results_text_prime_phrase_id_1_idx ON results_text_prime (phrase_id_1);
CREATE INDEX results_text_prime_phrase_id_2_idx ON results_text_prime (phrase_id_2);
CREATE INDEX results_text_prime_phrase_id_3_idx ON results_text_prime (phrase_id_3);
CREATE INDEX results_text_prime_phrase_id_4_idx ON results_text_prime (phrase_id_4);
CREATE INDEX results_text_prime_source_group_idx ON results_text_prime (source_group_id);
CREATE INDEX results_text_prime_formula_idx ON results_text_prime (formula_id);
CREATE INDEX results_text_prime_user_idx ON results_text_prime (user_id);

--
-- indexes for table user_results_text_prime
--
CREATE UNIQUE INDEX user_results_text_prime_pkey ON user_results_text_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, user_id);
CREATE INDEX user_results_text_prime_phrase_id_1_idx ON user_results_text_prime (phrase_id_1);
CREATE INDEX user_results_text_prime_phrase_id_2_idx ON user_results_text_prime (phrase_id_2);
CREATE INDEX user_results_text_prime_phrase_id_3_idx ON user_results_text_prime (phrase_id_3);
CREATE INDEX user_results_text_prime_phrase_id_4_idx ON user_results_text_prime (phrase_id_4);
CREATE INDEX user_results_text_prime_source_group_idx ON user_results_text_prime (source_group_id);
CREATE INDEX user_results_text_prime_user_idx ON user_results_text_prime (user_id);
CREATE INDEX user_results_text_prime_formula_idx ON user_results_text_prime (formula_id);

--
-- indexes for table results_text_main
--
CREATE UNIQUE INDEX results_text_main_pkey ON results_text_main (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, phrase_id_5, phrase_id_6, phrase_id_7, phrase_id_8);
CREATE INDEX results_text_main_phrase_id_1_idx ON results_text_main (phrase_id_1);
CREATE INDEX results_text_main_phrase_id_2_idx ON results_text_main (phrase_id_2);
CREATE INDEX results_text_main_phrase_id_3_idx ON results_text_main (phrase_id_3);
CREATE INDEX results_text_main_phrase_id_4_idx ON results_text_main (phrase_id_4);
CREATE INDEX results_text_main_phrase_id_5_idx ON results_text_main (phrase_id_5);
CREATE INDEX results_text_main_phrase_id_6_idx ON results_text_main (phrase_id_6);
CREATE INDEX results_text_main_phrase_id_7_idx ON results_text_main (phrase_id_7);
CREATE INDEX results_text_main_phrase_id_8_idx ON results_text_main (phrase_id_8);
CREATE INDEX results_text_main_source_group_idx ON results_text_main (source_group_id);
CREATE INDEX results_text_main_formula_idx ON results_text_main (formula_id);
CREATE INDEX results_text_main_user_idx ON results_text_main (user_id);

--
-- indexes for table user_results_text_main
--
CREATE UNIQUE INDEX user_results_text_main_pkey ON user_results_text_main (phrase_id_1,phrase_id_2,phrase_id_3,phrase_id_4, phrase_id_5, phrase_id_6, phrase_id_7, phrase_id_8, user_id);
CREATE INDEX user_results_text_main_phrase_id_1_idx ON user_results_text_main (phrase_id_1);
CREATE INDEX user_results_text_main_phrase_id_2_idx ON user_results_text_main (phrase_id_2);
CREATE INDEX user_results_text_main_phrase_id_3_idx ON user_results_text_main (phrase_id_3);
CREATE INDEX user_results_text_main_phrase_id_4_idx ON user_results_text_main (phrase_id_4);
CREATE INDEX user_results_text_main_phrase_id_5_idx ON user_results_text_main (phrase_id_5);
CREATE INDEX user_results_text_main_phrase_id_6_idx ON user_results_text_main (phrase_id_6);
CREATE INDEX user_results_text_main_phrase_id_7_idx ON user_results_text_main (phrase_id_7);
CREATE INDEX user_results_text_main_phrase_id_8_idx ON user_results_text_main (phrase_id_8);
CREATE INDEX user_results_text_main_source_group_idx ON user_results_text_main (source_group_id);
CREATE INDEX user_results_text_main_user_idx ON user_results_text_main (user_id);
CREATE INDEX user_results_text_main_formula_idx ON user_results_text_main (formula_id);

--
-- indexes for table results_text_big
--
CREATE INDEX results_text_big_source_group_idx ON results_text_big (source_group_id);
CREATE INDEX results_text_big_formula_idx ON results_text_big (formula_id);
CREATE INDEX results_text_big_user_idx ON results_text_big (user_id);

--
-- indexes for table user_results_text_big
--
ALTER TABLE user_results_text_big ADD CONSTRAINT user_results_text_big_pkey PRIMARY KEY (group_id, user_id);
CREATE INDEX user_results_text_big_source_group_idx ON user_results_text_big (source_group_id);
CREATE INDEX user_results_text_big_user_idx ON user_results_text_big (user_id);
CREATE INDEX user_results_text_big_formula_idx ON user_results_text_big (formula_id);

-- --------------------------------------------------------

--
-- indexes for table results_time_standard_prime
--
CREATE UNIQUE INDEX results_time_standard_prime_pkey ON results_time_standard_prime (formula_id, phrase_id_1, phrase_id_2, phrase_id_3);
CREATE INDEX results_time_standard_prime_formula_idx ON results_time_standard_prime (formula_id);
CREATE INDEX results_time_standard_prime_phrase_id_1_idx ON results_time_standard_prime (phrase_id_1);
CREATE INDEX results_time_standard_prime_phrase_id_2_idx ON results_time_standard_prime (phrase_id_2);
CREATE INDEX results_time_standard_prime_phrase_id_3_idx ON results_time_standard_prime (phrase_id_3);

--
-- indexes for table results_time_standard_main
--
CREATE UNIQUE INDEX results_time_standard_main_pkey ON results_time_standard_main (formula_id, phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, phrase_id_5, phrase_id_6, phrase_id_7);
CREATE INDEX results_time_standard_main_formula_idx ON results_time_standard_main (formula_id);
CREATE INDEX results_time_standard_main_phrase_id_1_idx ON results_time_standard_main (phrase_id_1);
CREATE INDEX results_time_standard_main_phrase_id_2_idx ON results_time_standard_main (phrase_id_2);
CREATE INDEX results_time_standard_main_phrase_id_3_idx ON results_time_standard_main (phrase_id_3);
CREATE INDEX results_time_standard_main_phrase_id_4_idx ON results_time_standard_main (phrase_id_4);
CREATE INDEX results_time_standard_main_phrase_id_5_idx ON results_time_standard_main (phrase_id_5);
CREATE INDEX results_time_standard_main_phrase_id_6_idx ON results_time_standard_main (phrase_id_6);
CREATE INDEX results_time_standard_main_phrase_id_7_idx ON results_time_standard_main (phrase_id_7);

--
-- indexes for table results_time_standard
--

--
-- indexes for table results_time
--
CREATE INDEX results_time_source_group_idx ON results_time (source_group_id);
CREATE INDEX results_time_formula_idx ON results_time (formula_id);
CREATE INDEX results_time_user_idx ON results_time (user_id);

--
-- indexes for table user_results_time
--
ALTER TABLE user_results_time ADD CONSTRAINT user_results_time_pkey PRIMARY KEY (group_id, user_id);
CREATE INDEX user_results_time_source_group_idx ON user_results_time (source_group_id);
CREATE INDEX user_results_time_user_idx ON user_results_time (user_id);
CREATE INDEX user_results_time_formula_idx ON user_results_time (formula_id);

--
-- indexes for table results_time_prime
--
CREATE UNIQUE INDEX results_time_prime_pkey ON results_time_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4);
CREATE INDEX results_time_prime_phrase_id_1_idx ON results_time_prime (phrase_id_1);
CREATE INDEX results_time_prime_phrase_id_2_idx ON results_time_prime (phrase_id_2);
CREATE INDEX results_time_prime_phrase_id_3_idx ON results_time_prime (phrase_id_3);
CREATE INDEX results_time_prime_phrase_id_4_idx ON results_time_prime (phrase_id_4);
CREATE INDEX results_time_prime_source_group_idx ON results_time_prime (source_group_id);
CREATE INDEX results_time_prime_formula_idx ON results_time_prime (formula_id);
CREATE INDEX results_time_prime_user_idx ON results_time_prime (user_id);

--
-- indexes for table user_results_time_prime
--
CREATE UNIQUE INDEX user_results_time_prime_pkey ON user_results_time_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, user_id);
CREATE INDEX user_results_time_prime_phrase_id_1_idx ON user_results_time_prime (phrase_id_1);
CREATE INDEX user_results_time_prime_phrase_id_2_idx ON user_results_time_prime (phrase_id_2);
CREATE INDEX user_results_time_prime_phrase_id_3_idx ON user_results_time_prime (phrase_id_3);
CREATE INDEX user_results_time_prime_phrase_id_4_idx ON user_results_time_prime (phrase_id_4);
CREATE INDEX user_results_time_prime_source_group_idx ON user_results_time_prime (source_group_id);
CREATE INDEX user_results_time_prime_user_idx ON user_results_time_prime (user_id);
CREATE INDEX user_results_time_prime_formula_idx ON user_results_time_prime (formula_id);

--
-- indexes for table results_time_main
--
CREATE UNIQUE INDEX results_time_main_pkey ON results_time_main (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, phrase_id_5, phrase_id_6, phrase_id_7, phrase_id_8);
CREATE INDEX results_time_main_phrase_id_1_idx ON results_time_main (phrase_id_1);
CREATE INDEX results_time_main_phrase_id_2_idx ON results_time_main (phrase_id_2);
CREATE INDEX results_time_main_phrase_id_3_idx ON results_time_main (phrase_id_3);
CREATE INDEX results_time_main_phrase_id_4_idx ON results_time_main (phrase_id_4);
CREATE INDEX results_time_main_phrase_id_5_idx ON results_time_main (phrase_id_5);
CREATE INDEX results_time_main_phrase_id_6_idx ON results_time_main (phrase_id_6);
CREATE INDEX results_time_main_phrase_id_7_idx ON results_time_main (phrase_id_7);
CREATE INDEX results_time_main_phrase_id_8_idx ON results_time_main (phrase_id_8);
CREATE INDEX results_time_main_source_group_idx ON results_time_main (source_group_id);
CREATE INDEX results_time_main_formula_idx ON results_time_main (formula_id);
CREATE INDEX results_time_main_user_idx ON results_time_main (user_id);

--
-- indexes for table user_results_time_main
--
CREATE UNIQUE INDEX user_results_time_main_pkey ON user_results_time_main (phrase_id_1,phrase_id_2,phrase_id_3,phrase_id_4, phrase_id_5, phrase_id_6, phrase_id_7, phrase_id_8, user_id);
CREATE INDEX user_results_time_main_phrase_id_1_idx ON user_results_time_main (phrase_id_1);
CREATE INDEX user_results_time_main_phrase_id_2_idx ON user_results_time_main (phrase_id_2);
CREATE INDEX user_results_time_main_phrase_id_3_idx ON user_results_time_main (phrase_id_3);
CREATE INDEX user_results_time_main_phrase_id_4_idx ON user_results_time_main (phrase_id_4);
CREATE INDEX user_results_time_main_phrase_id_5_idx ON user_results_time_main (phrase_id_5);
CREATE INDEX user_results_time_main_phrase_id_6_idx ON user_results_time_main (phrase_id_6);
CREATE INDEX user_results_time_main_phrase_id_7_idx ON user_results_time_main (phrase_id_7);
CREATE INDEX user_results_time_main_phrase_id_8_idx ON user_results_time_main (phrase_id_8);
CREATE INDEX user_results_time_main_source_group_idx ON user_results_time_main (source_group_id);
CREATE INDEX user_results_time_main_user_idx ON user_results_time_main (user_id);
CREATE INDEX user_results_time_main_formula_idx ON user_results_time_main (formula_id);

--
-- indexes for table results_time_big
--
CREATE INDEX results_time_big_source_group_idx ON results_time_big (source_group_id);
CREATE INDEX results_time_big_formula_idx ON results_time_big (formula_id);
CREATE INDEX results_time_big_user_idx ON results_time_big (user_id);

--
-- indexes for table user_results_time_big
--
ALTER TABLE user_results_time_big ADD CONSTRAINT user_results_time_big_pkey PRIMARY KEY (group_id, user_id);
CREATE INDEX user_results_time_big_source_group_idx ON user_results_time_big (source_group_id);
CREATE INDEX user_results_time_big_user_idx ON user_results_time_big (user_id);
CREATE INDEX user_results_time_big_formula_idx ON user_results_time_big (formula_id);

-- --------------------------------------------------------

--
-- indexes for table results_geo_standard_prime
--
CREATE UNIQUE INDEX results_geo_standard_prime_pkey ON results_geo_standard_prime (formula_id, phrase_id_1, phrase_id_2, phrase_id_3);
CREATE INDEX results_geo_standard_prime_formula_idx ON results_geo_standard_prime (formula_id);
CREATE INDEX results_geo_standard_prime_phrase_id_1_idx ON results_geo_standard_prime (phrase_id_1);
CREATE INDEX results_geo_standard_prime_phrase_id_2_idx ON results_geo_standard_prime (phrase_id_2);
CREATE INDEX results_geo_standard_prime_phrase_id_3_idx ON results_geo_standard_prime (phrase_id_3);

--
-- indexes for table results_geo_standard_main
--
CREATE UNIQUE INDEX results_geo_standard_main_pkey ON results_geo_standard_main (formula_id, phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, phrase_id_5, phrase_id_6, phrase_id_7);
CREATE INDEX results_geo_standard_main_formula_idx ON results_geo_standard_main (formula_id);
CREATE INDEX results_geo_standard_main_phrase_id_1_idx ON results_geo_standard_main (phrase_id_1);
CREATE INDEX results_geo_standard_main_phrase_id_2_idx ON results_geo_standard_main (phrase_id_2);
CREATE INDEX results_geo_standard_main_phrase_id_3_idx ON results_geo_standard_main (phrase_id_3);
CREATE INDEX results_geo_standard_main_phrase_id_4_idx ON results_geo_standard_main (phrase_id_4);
CREATE INDEX results_geo_standard_main_phrase_id_5_idx ON results_geo_standard_main (phrase_id_5);
CREATE INDEX results_geo_standard_main_phrase_id_6_idx ON results_geo_standard_main (phrase_id_6);
CREATE INDEX results_geo_standard_main_phrase_id_7_idx ON results_geo_standard_main (phrase_id_7);

--
-- indexes for table results_geo_standard
--

--
-- indexes for table results_geo
--
CREATE INDEX results_geo_source_group_idx ON results_geo (source_group_id);
CREATE INDEX results_geo_formula_idx ON results_geo (formula_id);
CREATE INDEX results_geo_user_idx ON results_geo (user_id);

--
-- indexes for table user_results_geo
--
ALTER TABLE user_results_geo ADD CONSTRAINT user_results_geo_pkey PRIMARY KEY (group_id, user_id);
CREATE INDEX user_results_geo_source_group_idx ON user_results_geo (source_group_id);
CREATE INDEX user_results_geo_user_idx ON user_results_geo (user_id);
CREATE INDEX user_results_geo_formula_idx ON user_results_geo (formula_id);

--
-- indexes for table results_geo_prime
--
CREATE UNIQUE INDEX results_geo_prime_pkey ON results_geo_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4);
CREATE INDEX results_geo_prime_phrase_id_1_idx ON results_geo_prime (phrase_id_1);
CREATE INDEX results_geo_prime_phrase_id_2_idx ON results_geo_prime (phrase_id_2);
CREATE INDEX results_geo_prime_phrase_id_3_idx ON results_geo_prime (phrase_id_3);
CREATE INDEX results_geo_prime_phrase_id_4_idx ON results_geo_prime (phrase_id_4);
CREATE INDEX results_geo_prime_source_group_idx ON results_geo_prime (source_group_id);
CREATE INDEX results_geo_prime_formula_idx ON results_geo_prime (formula_id);
CREATE INDEX results_geo_prime_user_idx ON results_geo_prime (user_id);

--
-- indexes for table user_results_geo_prime
--
CREATE UNIQUE INDEX user_results_geo_prime_pkey ON user_results_geo_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, user_id);
CREATE INDEX user_results_geo_prime_phrase_id_1_idx ON user_results_geo_prime (phrase_id_1);
CREATE INDEX user_results_geo_prime_phrase_id_2_idx ON user_results_geo_prime (phrase_id_2);
CREATE INDEX user_results_geo_prime_phrase_id_3_idx ON user_results_geo_prime (phrase_id_3);
CREATE INDEX user_results_geo_prime_phrase_id_4_idx ON user_results_geo_prime (phrase_id_4);
CREATE INDEX user_results_geo_prime_source_group_idx ON user_results_geo_prime (source_group_id);
CREATE INDEX user_results_geo_prime_user_idx ON user_results_geo_prime (user_id);
CREATE INDEX user_results_geo_prime_formula_idx ON user_results_geo_prime (formula_id);

--
-- indexes for table results_geo_main
--
CREATE UNIQUE INDEX results_geo_main_pkey ON results_geo_main (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, phrase_id_5, phrase_id_6, phrase_id_7, phrase_id_8);
CREATE INDEX results_geo_main_phrase_id_1_idx ON results_geo_main (phrase_id_1);
CREATE INDEX results_geo_main_phrase_id_2_idx ON results_geo_main (phrase_id_2);
CREATE INDEX results_geo_main_phrase_id_3_idx ON results_geo_main (phrase_id_3);
CREATE INDEX results_geo_main_phrase_id_4_idx ON results_geo_main (phrase_id_4);
CREATE INDEX results_geo_main_phrase_id_5_idx ON results_geo_main (phrase_id_5);
CREATE INDEX results_geo_main_phrase_id_6_idx ON results_geo_main (phrase_id_6);
CREATE INDEX results_geo_main_phrase_id_7_idx ON results_geo_main (phrase_id_7);
CREATE INDEX results_geo_main_phrase_id_8_idx ON results_geo_main (phrase_id_8);
CREATE INDEX results_geo_main_source_group_idx ON results_geo_main (source_group_id);
CREATE INDEX results_geo_main_formula_idx ON results_geo_main (formula_id);
CREATE INDEX results_geo_main_user_idx ON results_geo_main (user_id);

--
-- indexes for table user_results_geo_main
--
CREATE UNIQUE INDEX user_results_geo_main_pkey ON user_results_geo_main (phrase_id_1,phrase_id_2,phrase_id_3,phrase_id_4, phrase_id_5, phrase_id_6, phrase_id_7, phrase_id_8, user_id);
CREATE INDEX user_results_geo_main_phrase_id_1_idx ON user_results_geo_main (phrase_id_1);
CREATE INDEX user_results_geo_main_phrase_id_2_idx ON user_results_geo_main (phrase_id_2);
CREATE INDEX user_results_geo_main_phrase_id_3_idx ON user_results_geo_main (phrase_id_3);
CREATE INDEX user_results_geo_main_phrase_id_4_idx ON user_results_geo_main (phrase_id_4);
CREATE INDEX user_results_geo_main_phrase_id_5_idx ON user_results_geo_main (phrase_id_5);
CREATE INDEX user_results_geo_main_phrase_id_6_idx ON user_results_geo_main (phrase_id_6);
CREATE INDEX user_results_geo_main_phrase_id_7_idx ON user_results_geo_main (phrase_id_7);
CREATE INDEX user_results_geo_main_phrase_id_8_idx ON user_results_geo_main (phrase_id_8);
CREATE INDEX user_results_geo_main_source_group_idx ON user_results_geo_main (source_group_id);
CREATE INDEX user_results_geo_main_user_idx ON user_results_geo_main (user_id);
CREATE INDEX user_results_geo_main_formula_idx ON user_results_geo_main (formula_id);

--
-- indexes for table results_geo_big
--
CREATE INDEX results_geo_big_source_group_idx ON results_geo_big (source_group_id);
CREATE INDEX results_geo_big_formula_idx ON results_geo_big (formula_id);
CREATE INDEX results_geo_big_user_idx ON results_geo_big (user_id);

--
-- indexes for table user_results_geo_big
--
ALTER TABLE user_results_geo_big ADD CONSTRAINT user_results_geo_big_pkey PRIMARY KEY (group_id, user_id);
CREATE INDEX user_results_geo_big_source_group_idx ON user_results_geo_big (source_group_id);
CREATE INDEX user_results_geo_big_user_idx ON user_results_geo_big (user_id);
CREATE INDEX user_results_geo_big_formula_idx ON user_results_geo_big (formula_id);

-- --------------------------------------------------------

--
-- indexes for table views
--

CREATE INDEX views_user_idx ON views (user_id);
CREATE INDEX views_view_name_idx ON views (view_name);
CREATE INDEX views_view_type_idx ON views (view_type_id);

--
-- indexes for table user_views
--

ALTER TABLE user_views
    ADD CONSTRAINT user_views_pkey PRIMARY KEY (view_id,user_id,language_id);
CREATE INDEX user_views_view_idx ON user_views (view_id);
CREATE INDEX user_views_user_idx ON user_views (user_id);
CREATE INDEX user_views_language_idx ON user_views (language_id);
CREATE INDEX user_views_view_name_idx ON user_views (view_name);
CREATE INDEX user_views_view_type_idx ON user_views (view_type_id);

-- --------------------------------------------------------

--
-- indexes for table components
--

CREATE INDEX components_user_idx ON components (user_id);
CREATE INDEX components_component_name_idx ON components (component_name);
CREATE INDEX components_component_type_idx ON components (component_type_id);
CREATE INDEX components_word_id_row_idx ON components (word_id_row);
CREATE INDEX components_formula_idx ON components (formula_id);
CREATE INDEX components_word_id_col_idx ON components (word_id_col);
CREATE INDEX components_word_id_col2_idx ON components (word_id_col2);
CREATE INDEX components_linked_component_idx ON components (linked_component_id);
CREATE INDEX components_component_link_type_idx ON components (component_link_type_id);
CREATE INDEX components_link_type_idx ON components (link_type_id);

--
-- indexes for table user_components
--

ALTER TABLE user_components ADD CONSTRAINT user_components_pkey PRIMARY KEY (component_id,user_id);
CREATE INDEX user_components_component_idx ON user_components (component_id);
CREATE INDEX user_components_user_idx ON user_components (user_id);
CREATE INDEX user_components_component_name_idx ON user_components (component_name);
CREATE INDEX user_components_component_type_idx ON user_components (component_type_id);
CREATE INDEX user_components_word_id_row_idx ON user_components (word_id_row);
CREATE INDEX user_components_formula_idx ON user_components (formula_id);
CREATE INDEX user_components_word_id_col_idx ON user_components (word_id_col);
CREATE INDEX user_components_word_id_col2_idx ON user_components (word_id_col2);
CREATE INDEX user_components_linked_component_idx ON user_components (linked_component_id);
CREATE INDEX user_components_component_link_type_idx ON user_components (component_link_type_id);
CREATE INDEX user_components_link_type_idx ON user_components (link_type_id);

--
-- Indexes for table component_links
--
CREATE INDEX component_link_idx ON component_links (view_id);
CREATE INDEX component_link_component_idx ON component_links (component_id);
CREATE INDEX component_link_position__idx ON component_links (position_type);

--
-- Indexes for table user_component_links
--
ALTER TABLE user_component_links ADD CONSTRAINT user_component_link_pkey PRIMARY KEY (component_link_id, user_id);
CREATE INDEX user_component_link_user_idx ON user_component_links (user_id);
CREATE INDEX user_component_link_position_idx ON user_component_links (position_type);
CREATE INDEX user_component_link_view_idx ON user_component_links (component_link_id);

-- --------------------------------------------------------

--
-- foreign key constraints and auto_increment for tables
--

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
    ADD CONSTRAINT jobs_job_type_fk FOREIGN KEY (job_type_id) REFERENCES job_types (job_type_id);

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
-- constraints for table change_standard_values
--

ALTER TABLE change_standard_values
    ADD CONSTRAINT change_standard_values_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT change_standard_values_change_action_fk FOREIGN KEY (change_action_id) REFERENCES change_actions (change_action_id),
    ADD CONSTRAINT change_standard_values_change_field_fk FOREIGN KEY (change_field_id) REFERENCES change_fields (change_field_id);

-- --------------------------------------------------------

--
-- constraints for table change_prime_values
--

ALTER TABLE change_prime_values
    ADD CONSTRAINT change_prime_values_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT change_prime_values_change_action_fk FOREIGN KEY (change_action_id) REFERENCES change_actions (change_action_id),
    ADD CONSTRAINT change_prime_values_change_field_fk FOREIGN KEY (change_field_id) REFERENCES change_fields (change_field_id);

-- --------------------------------------------------------

--
-- constraints for table change_big_values
--

ALTER TABLE change_big_values
    ADD CONSTRAINT change_big_values_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT change_big_values_change_action_fk FOREIGN KEY (change_action_id) REFERENCES change_actions (change_action_id),
    ADD CONSTRAINT change_big_values_change_field_fk FOREIGN KEY (change_field_id) REFERENCES change_fields (change_field_id);

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
    ADD CONSTRAINT triples_user_fk        FOREIGN KEY (user_id)        REFERENCES users (user_id),
    ADD CONSTRAINT triples_verb_fk        FOREIGN KEY (verb_id)        REFERENCES verbs (verb_id),
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
ALTER TABLE groups
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


--
-- constraints for table refs
-- TODO foreign
--
ALTER TABLE refs
    ADD CONSTRAINT refs_fk_1 FOREIGN KEY (ref_type_id) REFERENCES ref_types (ref_type_id);

--
-- constraints for table user_refs
--
ALTER TABLE user_refs
    ADD CONSTRAINT user_refs_fk_1 FOREIGN KEY (ref_id) REFERENCES refs (ref_id),
    ADD CONSTRAINT user_refs_fk_2 FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table source_values
--
ALTER TABLE source_values
    ADD CONSTRAINT source_values_fk_1 FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT source_values_fk_2 FOREIGN KEY (source_id) REFERENCES sources (source_id);

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
ALTER TABLE values
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

--
-- constraints for table formula_elements
--
ALTER TABLE formula_elements
    ADD CONSTRAINT formula_elements_fk_1 FOREIGN KEY (formula_element_type_id) REFERENCES formula_element_types (formula_element_type_id),
    ADD CONSTRAINT formula_elements_fk_2 FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

--
-- constraints for table formula_links
--
ALTER TABLE formula_links
    ADD CONSTRAINT formula_links_fk_1 FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_formula_links
--
ALTER TABLE user_formula_links
    ADD CONSTRAINT user_formula_links_fk_1 FOREIGN KEY (formula_link_id) REFERENCES formula_links (formula_link_id),
    ADD CONSTRAINT user_formula_links_fk_2 FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_formula_links_fk_3 FOREIGN KEY (link_type_id) REFERENCES formula_link_types (formula_link_type_id);

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
-- constraints for table views
--

ALTER TABLE views
    ADD CONSTRAINT views_view_name_uk UNIQUE (view_name),
    ADD CONSTRAINT views_code_id_uk UNIQUE (code_id),
    ADD CONSTRAINT views_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT views_view_type_fk FOREIGN KEY (view_type_id) REFERENCES view_types (view_type_id);

--
-- constraints for table user_views
--

ALTER TABLE user_views
    ADD CONSTRAINT user_views_view_fk FOREIGN KEY (view_id) REFERENCES views (view_id),
    ADD CONSTRAINT user_views_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_views_language_fk FOREIGN KEY (language_id) REFERENCES languages (language_id),
    ADD CONSTRAINT user_views_view_type_fk FOREIGN KEY (view_type_id) REFERENCES view_types (view_type_id);

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
    ADD CONSTRAINT components_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

--
-- constraints for table user_components
--

ALTER TABLE user_components
    ADD CONSTRAINT user_components_component_fk FOREIGN KEY (component_id) REFERENCES components (component_id),
    ADD CONSTRAINT user_components_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_components_component_type_fk FOREIGN KEY (component_type_id) REFERENCES component_types (component_type_id),
    ADD CONSTRAINT user_components_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

--
-- constraints for table component_links
--
ALTER TABLE component_links
    ADD CONSTRAINT component_links_fk_1 FOREIGN KEY (view_id) REFERENCES views (view_id),
    ADD CONSTRAINT component_links_fk_2 FOREIGN KEY (position_type) REFERENCES component_position_types (component_position_type_id),
    ADD CONSTRAINT component_links_fk_3 FOREIGN KEY (component_id) REFERENCES components (component_id);

--
-- constraints for table user_component_links
--
ALTER TABLE user_component_links
    ADD CONSTRAINT user_component_links_fk_1 FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_component_links_fk_2 FOREIGN KEY (component_link_id) REFERENCES component_links (component_link_id),
    ADD CONSTRAINT user_component_links_fk_3 FOREIGN KEY (position_type) REFERENCES component_position_types (component_position_type_id);


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
