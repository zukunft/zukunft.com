-- --------------------------------------------------------

--
-- indexes for table refs
--

ALTER TABLE refs
    ADD PRIMARY KEY (ref_id),
    ADD KEY refs_user_idx (user_id),
    ADD KEY refs_external_key_idx (external_key),
    ADD KEY refs_source_idx (source_id),
    ADD KEY refs_phrase_idx (phrase_id),
    ADD KEY refs_ref_type_idx (ref_type_id);

--
-- indexes for table user_refs
--

ALTER TABLE user_refs
    ADD PRIMARY KEY (ref_id,user_id),
    ADD KEY user_refs_ref_idx (ref_id),
    ADD KEY user_refs_user_idx (user_id),
    ADD KEY user_refs_external_key_idx (external_key),
    ADD KEY user_refs_source_idx (source_id);
