-- --------------------------------------------------------

--
-- indexes for table position_types
--

ALTER TABLE position_types
    ADD PRIMARY KEY (position_type_id),
    ADD KEY position_types_type_name_idx (type_name);
