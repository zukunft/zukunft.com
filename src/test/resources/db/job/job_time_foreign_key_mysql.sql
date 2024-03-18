--
-- constraints for table job_times
--

ALTER TABLE job_times
    ADD CONSTRAINT job_times_job_type_fk FOREIGN KEY (job_type_id) REFERENCES job_types (job_type_id),
    ADD CONSTRAINT job_times_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);