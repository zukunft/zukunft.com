-- --------------------------------------------------------

--
-- constraints for table change_values_text_norm
--

ALTER TABLE change_values_text_norm
    ADD CONSTRAINT change_values_text_norm_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT change_values_text_norm_change_action_fk FOREIGN KEY (change_action_id) REFERENCES change_actions (change_action_id),
    ADD CONSTRAINT change_values_text_norm_change_field_fk FOREIGN KEY (change_field_id) REFERENCES change_fields (change_field_id);
