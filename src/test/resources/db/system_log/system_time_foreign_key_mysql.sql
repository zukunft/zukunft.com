--
-- constraints for table system_times
--

ALTER TABLE system_times
    ADD CONSTRAINT system_times_system_time_type_fk FOREIGN KEY (system_time_type_id) REFERENCES system_time_types (system_time_type_id);
