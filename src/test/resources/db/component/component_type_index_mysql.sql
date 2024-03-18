-- --------------------------------------------------------

--
-- indexes for table component_types
--

ALTER TABLE component_types
    ADD PRIMARY KEY (component_type_id),
    ADD KEY component_types_type_name_idx (type_name);
