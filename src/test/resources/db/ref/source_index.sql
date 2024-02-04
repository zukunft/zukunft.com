-- --------------------------------------------------------

--
-- indexes for table sources
--
CREATE INDEX sources_user_idx        ON sources (user_id);
CREATE INDEX sources_source_name_idx ON sources (source_name);
CREATE INDEX sources_source_type_idx ON sources (source_type_id);
--
-- indexes for table user_sources
--
ALTER TABLE user_sources ADD CONSTRAINT user_sources_pkey PRIMARY KEY (source_id,user_id);
CREATE INDEX user_sources_source_idx      ON user_sources (source_id);
CREATE INDEX user_sources_user_idx        ON user_sources (user_id);
CREATE INDEX user_sources_source_name_idx ON user_sources (source_name);
CREATE INDEX user_sources_source_type_idx ON user_sources (source_type_id);


