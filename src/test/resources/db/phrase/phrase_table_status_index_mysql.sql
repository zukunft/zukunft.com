-- --------------------------------------------------------

--
-- indexes for table phrase_table_status
--

ALTER TABLE phrase_table_status
    ADD KEY phrase_table_status_type_name_idx (type_name);
