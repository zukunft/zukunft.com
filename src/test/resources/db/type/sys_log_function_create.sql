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
