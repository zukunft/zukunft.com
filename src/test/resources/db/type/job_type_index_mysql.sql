-- --------------------------------------------------------

--
-- indexes for table job_types
--

ALTER TABLE job_types
    ADD PRIMARY KEY (job_type_id),
    ADD KEY job_types_type_name_idx (type_name);
