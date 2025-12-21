-- --------------------------------------------------------

--
-- indexes for table sys_log_types
--

ALTER TABLE sys_log_types
    ADD KEY sys_log_types_type_name_idx (type_name);
