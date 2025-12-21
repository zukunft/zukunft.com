-- --------------------------------------------------------

--
-- indexes for table sys_log_status
--

ALTER TABLE sys_log_status
    ADD KEY sys_log_status_type_name_idx (type_name);
