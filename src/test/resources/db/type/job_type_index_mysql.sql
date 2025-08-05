-- --------------------------------------------------------

--
-- indexes for table job_types
--

ALTER TABLE job_types
    ADD KEY job_types_type_name_idx (type_name);
