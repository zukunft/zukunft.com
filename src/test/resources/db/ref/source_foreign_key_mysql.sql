-- --------------------------------------------------------

--
-- constraints for table sources
--
ALTER TABLE sources
    ADD CONSTRAINT sources_source_name_uk UNIQUE (source_name),
    ADD CONSTRAINT sources_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT sources_source_type_fk FOREIGN KEY (source_type_id) REFERENCES source_types (source_type_id);

--
-- constraints for table user_sources
--
ALTER TABLE user_sources
    ADD CONSTRAINT user_sources_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT user_sources_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_sources_source_type_fk FOREIGN KEY (source_type_id) REFERENCES source_types (source_type_id);
