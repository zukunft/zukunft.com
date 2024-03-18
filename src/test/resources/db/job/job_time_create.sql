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