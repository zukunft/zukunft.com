-- --------------------------------------------------------

--
-- indexes for table user_profiles
--

ALTER TABLE user_profiles
    ADD PRIMARY KEY (user_profile_id),
    ADD KEY user_profiles_type_name_idx (type_name);
