-- --------------------------------------------------------

--
-- indexes for table user_types
--

ALTER TABLE user_types
    ADD PRIMARY KEY (user_type_id),
    ADD KEY user_types_type_name_idx (type_name);
