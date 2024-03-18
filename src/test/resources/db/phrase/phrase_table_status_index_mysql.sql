-- --------------------------------------------------------

--
-- indexes for table phrase_table_status
--

ALTER TABLE phrase_table_status
    ADD PRIMARY KEY (phrase_table_status_id),
    ADD KEY phrase_table_status_type_name_idx (type_name);
