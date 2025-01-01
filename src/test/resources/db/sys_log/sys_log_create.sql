-- --------------------------------------------------------

--
-- table structure for system error tracking and to measure execution times
--

CREATE TABLE IF NOT EXISTS sys_log
(
    sys_log_id          BIGSERIAL PRIMARY KEY,
    sys_log_time        timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP,
    sys_log_type_id     smallint   NOT NULL,
    sys_log_function_id smallint   NOT NULL,
    sys_log_text        text   DEFAULT NULL,
    sys_log_description text   DEFAULT NULL,
    sys_log_trace       text   DEFAULT NULL,
    user_id             bigint DEFAULT NULL,
    solver_id           bigint DEFAULT NULL,
    sys_log_status_id   bigint     NOT NULL DEFAULT 1
);

COMMENT ON TABLE sys_log IS 'for system error tracking and to measure execution times';
COMMENT ON COLUMN sys_log.sys_log_id IS 'the internal unique primary index';
COMMENT ON COLUMN sys_log.sys_log_time IS 'timestamp of the creation';
COMMENT ON COLUMN sys_log.sys_log_type_id IS 'the level e.g. debug,info,warning,error or fatal';
COMMENT ON COLUMN sys_log.sys_log_function_id IS 'the function or function group for the entry e.g. db_write to measure the db write times';
COMMENT ON COLUMN sys_log.sys_log_text IS 'the short text of the log entry to identify the error and to reduce the number of double entries';
COMMENT ON COLUMN sys_log.sys_log_description IS 'the long description with all details of the log entry to solve ti issue';
COMMENT ON COLUMN sys_log.sys_log_trace IS 'the generated code trace to local the path to the error cause';
COMMENT ON COLUMN sys_log.user_id IS 'the id of the user who has caused the log entry';
COMMENT ON COLUMN sys_log.solver_id IS 'user id of the user that is trying to solve the problem';
