-- --------------------------------------------------------

--
-- table structure to define the status of internal errors
--

CREATE TABLE IF NOT EXISTS sys_log_statuum
(
    sys_log_status_id SERIAL PRIMARY KEY,
    status_name       varchar(255)     NOT NULL,
    code_id           varchar(255) DEFAULT NULL,
    description       text         DEFAULT NULL,
    action            varchar(255) DEFAULT NULL
);

COMMENT ON TABLE sys_log_statuum IS 'to define the status of internal errors';
COMMENT ON COLUMN sys_log_statuum.sys_log_status_id IS 'the internal unique primary index';
COMMENT ON COLUMN sys_log_statuum.status_name IS 'the unique type name as shown to the user and used for the selection';
COMMENT ON COLUMN sys_log_statuum.code_id IS 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration';
COMMENT ON COLUMN sys_log_statuum.description IS 'text to explain the type to the user as a tooltip; to be replaced by a language form entry';
COMMENT ON COLUMN sys_log_statuum.action IS 'description of the action to get to this status';