--
-- constraints for table users
--

ALTER TABLE users
    ADD CONSTRAINT users_user_profile_fk FOREIGN KEY (user_profile_id) REFERENCES user_profiles (user_profile_id),
    ADD CONSTRAINT users_user_type_fk FOREIGN KEY (user_type_id) REFERENCES user_types (user_type_id),
    ADD CONSTRAINT users_triple_fk FOREIGN KEY (name_triple_id) REFERENCES triples (triple_id),
    ADD CONSTRAINT users_triple2_fk FOREIGN KEY (geo_triple_id) REFERENCES triples (triple_id),
    ADD CONSTRAINT users_view_fk FOREIGN KEY (view_id) REFERENCES views (view_id),
    ADD CONSTRAINT users_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);
