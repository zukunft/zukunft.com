-- --------------------------------------------------------

--
-- indexes for table system_times
--

ALTER TABLE system_times
    ADD KEY system_times_start_time_idx (start_time),
    ADD KEY system_times_end_time_idx (end_time),
    ADD KEY system_times_system_time_type_idx (system_time_type_id);
