-- --------------------------------------------------------

--
-- constraints for table groups
--
ALTER TABLE groups
    ADD CONSTRAINT groups_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_groups
--
ALTER TABLE user_groups
    ADD CONSTRAINT user_groups_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table groups_prime
--
ALTER TABLE groups_prime
    ADD CONSTRAINT groups_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_groups_prime
--
ALTER TABLE user_groups_prime
    ADD CONSTRAINT user_groups_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table groups_big
--
ALTER TABLE groups_big
    ADD CONSTRAINT groups_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_groups_big
--
ALTER TABLE user_groups_big
    ADD CONSTRAINT user_groups_big_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);