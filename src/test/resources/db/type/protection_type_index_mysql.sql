-- --------------------------------------------------------

--
-- indexes for table protection_types
--

ALTER TABLE protection_types
    ADD PRIMARY KEY (protection_type_id),
    ADD KEY protection_types_type_name_idx (type_name);
