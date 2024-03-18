-- --------------------------------------------------------

--
-- constraints for table refs
--

ALTER TABLE refs
    ADD CONSTRAINT refs_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT refs_ref_type_fk FOREIGN KEY (ref_type_id) REFERENCES ref_types (ref_type_id),
    ADD CONSTRAINT refs_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id);

--
-- constraints for table user_refs
--

ALTER TABLE user_refs
    ADD CONSTRAINT user_refs_ref_fk FOREIGN KEY (ref_id) REFERENCES refs (ref_id),
    ADD CONSTRAINT user_refs_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);
