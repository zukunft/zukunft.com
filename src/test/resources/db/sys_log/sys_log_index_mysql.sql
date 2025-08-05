-- --------------------------------------------------------

--
-- indexes for table sys_log
--

ALTER TABLE sys_log
    ADD KEY sys_log_sys_log_time_idx (sys_log_time),
    ADD KEY sys_log_sys_log_type_idx (sys_log_type_id),
    ADD KEY sys_log_sys_log_function_idx (sys_log_function_id),
    ADD KEY sys_log_user_idx (user_id),
    ADD KEY sys_log_solver_idx (solver_id),
    ADD KEY sys_log_sys_log_status_idx (sys_log_status_id);
