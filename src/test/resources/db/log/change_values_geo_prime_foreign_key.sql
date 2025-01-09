-- --------------------------------------------------------

--
-- constraints for table change_values_geo_prime
--

ALTER TABLE change_values_geo_prime
    ADD CONSTRAINT change_values_geo_prime_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT change_values_geo_prime_change_action_fk FOREIGN KEY (change_action_id) REFERENCES change_actions (change_action_id),
    ADD CONSTRAINT change_values_geo_prime_change_field_fk FOREIGN KEY (change_field_id) REFERENCES change_fields (change_field_id);
