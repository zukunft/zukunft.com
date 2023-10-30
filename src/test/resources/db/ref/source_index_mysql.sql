-- --------------------------------------------------------

--
-- indexes for table sources
--
ALTER TABLE sources
    ADD PRIMARY KEY (source_id),
    ADD KEY sources_user_idx (user_id),
    ADD KEY sources_source_name_idx (source_name),
    ADD KEY sources_source_type_idx (source_type_id);

--
-- indexes for table user_sources
--
ALTER TABLE user_sources
    ADD PRIMARY KEY (source_id, user_id),
    ADD KEY user_sources_source_idx (source_id),
    ADD KEY user_sources_user_idx (user_id),
    ADD KEY user_sources_source_name_idx (source_name),
    ADD KEY user_sources_source_type_idx (source_type_id);
