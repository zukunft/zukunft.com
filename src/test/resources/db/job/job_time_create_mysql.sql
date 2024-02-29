-- --------------------------------------------------------

--
-- table structure to schedule jobs with predefined parameters
--

CREATE TABLE IF NOT EXISTS job_times
(
    job_time_id bigint          NOT NULL COMMENT 'the internal unique primary index',
    schedule    varchar(20) DEFAULT NULL COMMENT 'the crontab for the job schedule',
    job_type_id bigint          NOT NULL COMMENT 'the id of the job type that should be started',
    user_id     bigint          NOT NULL COMMENT 'the id of the user who edit the scheduler the last time',
    start       timestamp   DEFAULT NULL COMMENT 'the last start of the job',
    parameter   bigint      DEFAULT NULL COMMENT 'the phrase id that contains all parameters for the next job start'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to schedule jobs with predefined parameters';