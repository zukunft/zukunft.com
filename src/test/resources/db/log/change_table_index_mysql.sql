-- --------------------------------------------------------

--
-- indexes for table change_tables
--

ALTER TABLE change_tables
    ADD PRIMARY KEY (change_table_id),
    ADD KEY change_tables_change_table_name_idx (change_table_name);
