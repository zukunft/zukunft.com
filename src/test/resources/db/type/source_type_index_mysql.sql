-- --------------------------------------------------------

--
-- indexes for table source_types
--

ALTER TABLE source_types
    ADD PRIMARY KEY (source_type_id),
    ADD KEY source_types_type_name_idx (type_name);
