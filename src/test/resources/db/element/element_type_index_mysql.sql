-- --------------------------------------------------------

--
-- indexes for table element_types
--

ALTER TABLE element_types
    ADD PRIMARY KEY (element_type_id),
    ADD KEY element_types_type_name_idx (type_name);
