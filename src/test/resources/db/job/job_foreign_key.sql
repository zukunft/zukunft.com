--
-- constraints for table jobs
--

ALTER TABLE jobs
    ADD CONSTRAINT jobs_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT jobs_job_type_fk FOREIGN KEY (job_type_id) REFERENCES job_types (job_type_id);
