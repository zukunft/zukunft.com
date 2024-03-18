-- --------------------------------------------------------

--
-- indexes for table job_times
--

ALTER TABLE job_times
    ADD PRIMARY KEY (job_time_id),
    ADD KEY job_times_schedule_idx (schedule),
    ADD KEY job_times_job_type_idx (job_type_id),
    ADD KEY job_times_user_idx (user_id),
    ADD KEY job_times_parameter_idx (parameter);
