-- --------------------------------------------------------

--
-- indexes for table job_statuum
--

ALTER TABLE job_statuum
    ADD KEY job_statuum_status_name_idx (status_name);
