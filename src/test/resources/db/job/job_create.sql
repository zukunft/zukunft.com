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