-- --------------------------------------------------------

--
-- indexes for table change_fields
--

ALTER TABLE change_fields
    ADD PRIMARY KEY (change_field_id),
    ADD KEY change_fields_change_field_name_idx (change_field_name),
    ADD KEY change_fields_table_idx (table_id);
