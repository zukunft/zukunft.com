-- --------------------------------------------------------

--
-- indexes for table view_link_types
--

ALTER TABLE view_link_types
    ADD PRIMARY KEY (view_link_type_id),
    ADD KEY view_link_types_type_name_idx (type_name);
