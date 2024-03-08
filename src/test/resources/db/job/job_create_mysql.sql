-- --------------------------------------------------------

--
-- table structure for each concrete job run
--

CREATE TABLE IF NOT EXISTS jobs
(
    job_id          bigint        NOT NULL COMMENT 'the internal unique primary index',
    user_id         bigint        NOT NULL COMMENT 'the id of the user who has requested the job by editing the scheduler the last time',
    job_type_id     bigint        NOT NULL COMMENT 'the id of the job type that should be started',
    request_time    timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp of the request for the job execution',
    start_time      timestamp DEFAULT NULL COMMENT 'timestamp when the system has started the execution',
    end_time        timestamp DEFAULT NULL COMMENT 'timestamp when the job has been completed or canceled',
    parameter       bigint    DEFAULT NULL COMMENT 'id of the phrase with the snaped parameter set for this job start',
    change_field_id bigint    DEFAULT NULL COMMENT 'e.g. for undo jobs the id of the field that should be changed',
    row_id          bigint    DEFAULT NULL COMMENT 'e.g. for undo jobs the id of the row that should be changed',
    source_id       bigint    DEFAULT NULL COMMENT 'used for import to link the source',
    ref_id          bigint    DEFAULT NULL COMMENT 'used for import to link the reference'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for each concrete job run';