--
-- constraints for table user_log
--

ALTER TABLE user_log
    ADD CONSTRAINT user_log_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);
