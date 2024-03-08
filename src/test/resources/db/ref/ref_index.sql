-- --------------------------------------------------------

--
-- indexes for table refs
--

CREATE INDEX refs_user_idx ON refs (user_id);
CREATE INDEX refs_phrase_idx ON refs (phrase_id);
CREATE INDEX refs_external_key_idx ON refs (external_key);
CREATE INDEX refs_ref_type_idx ON refs (ref_type_id);
CREATE INDEX refs_source_idx ON refs (source_id);

--
-- indexes for table user_refs
--

ALTER TABLE user_refs
    ADD CONSTRAINT user_refs_pkey PRIMARY KEY (ref_id,user_id);
CREATE INDEX user_refs_ref_idx ON user_refs (ref_id);
CREATE INDEX user_refs_user_idx ON user_refs (user_id);
