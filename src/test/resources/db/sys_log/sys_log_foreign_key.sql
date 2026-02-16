--
-- constraints for table sys_log
--

ALTER TABLE sys_log
    ADD CONSTRAINT sys_log_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT sys_log_sys_log_function_fk FOREIGN KEY (sys_log_function_id) REFERENCES sys_log_functions (sys_log_function_id),
    ADD CONSTRAINT sys_log_sys_log_level_fk FOREIGN KEY (sys_log_level_id) REFERENCES sys_log_levels (sys_log_level_id),
    ADD CONSTRAINT sys_log_user2_fk FOREIGN KEY (solver_id) REFERENCES users (user_id),
    ADD CONSTRAINT sys_log_sys_log_status_fk FOREIGN KEY (sys_log_status_id) REFERENCES sys_log_statuus (sys_log_status_id);
