-- --------------------------------------------------------

--
-- indexes for table user_official_types
--

ALTER TABLE user_official_types
    ADD PRIMARY KEY (user_official_type_id),
    ADD KEY user_official_types_type_name_idx (type_name);
