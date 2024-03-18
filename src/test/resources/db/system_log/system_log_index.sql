-- --------------------------------------------------------

--
-- indexes for table sys_log
--

CREATE INDEX sys_log_sys_log_time_idx ON sys_log (sys_log_time);
CREATE INDEX sys_log_sys_log_type_idx ON sys_log (sys_log_type_id);
CREATE INDEX sys_log_sys_log_function_idx ON sys_log (sys_log_function_id);
CREATE INDEX sys_log_user_idx ON sys_log (user_id);
CREATE INDEX sys_log_solver_idx ON sys_log (solver_id);
CREATE INDEX sys_log_sys_log_status_idx ON sys_log (sys_log_status_id);
