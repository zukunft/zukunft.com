-- --------------------------------------------------------

--
-- indexes for table jobs
--

ALTER TABLE jobs
    ADD PRIMARY KEY (job_id),
    ADD KEY jobs_user_idx (user_id),
    ADD KEY jobs_job_type_idx (job_type_id),
    ADD KEY jobs_request_time_idx (request_time),
    ADD KEY jobs_start_time_idx (start_time),
    ADD KEY jobs_end_time_idx (end_time),
    ADD KEY jobs_parameter_idx (parameter),
    ADD KEY jobs_change_field_idx (change_field_id),
    ADD KEY jobs_row_idx (row_id),
    ADD KEY jobs_source_idx (source_id),
    ADD KEY jobs_ref_idx (ref_id);
