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
    sys_log_status_id   smallint   NOT NULL DEFAULT 1,
    PRIMARY KEY (sys_log_id)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for system error tracking and to measure execution times';

--
-- AUTO_INCREMENT for table sys_log
--
ALTER TABLE sys_log
    MODIFY sys_log_id bigint NOT NULL AUTO_INCREMENT;
