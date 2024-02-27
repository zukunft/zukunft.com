-- --------------------------------------------------------

--
-- indexes for table sys_log_status
--

ALTER TABLE sys_log_status
    ADD PRIMARY KEY (sys_log_status_id),
    ADD KEY sys_log_status_type_name_idx (type_name);
