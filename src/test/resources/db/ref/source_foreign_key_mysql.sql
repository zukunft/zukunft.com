-- --------------------------------------------------------

--
-- constraints for table sources
--
ALTER TABLE sources
    ADD CONSTRAINT source_name_uk UNIQUE (source_name),
    ADD CONSTRAINT sources_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_sources
--
ALTER TABLE user_sources
    ADD CONSTRAINT user_sources_source_fk FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT user_sources_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);
