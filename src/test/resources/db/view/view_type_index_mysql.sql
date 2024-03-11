-- --------------------------------------------------------

--
-- indexes for table view_types
--

ALTER TABLE view_types
    ADD PRIMARY KEY (view_type_id),
    ADD KEY view_types_type_name_idx (type_name);
