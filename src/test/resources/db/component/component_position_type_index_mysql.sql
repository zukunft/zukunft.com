-- --------------------------------------------------------

--
-- indexes for table component_position_types
--

ALTER TABLE component_position_types
    ADD PRIMARY KEY (component_position_type_id),
    ADD KEY component_position_types_type_name_idx (type_name);
