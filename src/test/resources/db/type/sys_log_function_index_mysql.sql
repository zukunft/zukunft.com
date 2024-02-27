-- --------------------------------------------------------

--
-- indexes for table sys_log_functions
--

ALTER TABLE sys_log_functions
    ADD PRIMARY KEY (sys_log_function_id),
    ADD KEY sys_log_functions_sys_log_function_name_idx (sys_log_function_name);
