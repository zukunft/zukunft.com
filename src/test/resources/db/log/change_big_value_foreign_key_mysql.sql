-- --------------------------------------------------------

--
-- constraints for table change_big_values
--

ALTER TABLE change_big_values
    ADD CONSTRAINT change_big_values_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT change_big_values_change_action_fk FOREIGN KEY (change_action_id) REFERENCES change_actions (change_action_id),
    ADD CONSTRAINT change_big_values_change_field_fk FOREIGN KEY (change_field_id) REFERENCES change_fields (change_field_id);
