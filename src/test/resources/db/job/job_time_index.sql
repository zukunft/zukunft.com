-- --------------------------------------------------------

--
-- indexes for table job_times
--

CREATE INDEX job_times_schedule_idx ON job_times (schedule);
CREATE INDEX job_times_job_type_idx ON job_times (job_type_id);
CREATE INDEX job_times_user_idx ON job_times (user_id);
CREATE INDEX job_times_parameter_idx ON job_times (parameter);
