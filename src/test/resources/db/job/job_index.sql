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
CREATE INDEX jobs_source_idx ON jobs (source_id);
CREATE INDEX jobs_ref_idx ON jobs (ref_id);
