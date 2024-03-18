-- --------------------------------------------------------

--
-- indexes for table component_link_types
--

ALTER TABLE component_link_types
    ADD PRIMARY KEY (component_link_type_id),
    ADD KEY component_link_types_type_name_idx (type_name);
