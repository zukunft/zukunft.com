-- --------------------------------------------------------

--
-- indexes for table users
--

ALTER TABLE users
    ADD PRIMARY KEY (user_id),
    ADD KEY users_user_name_idx (user_name),
    ADD KEY users_ip_address_idx (ip_address),
    ADD KEY users_code_idx (code_id),
    ADD KEY users_user_profile_idx (user_profile_id),
    ADD KEY users_user_type_idx (user_type_id);
