-- --------------------------------------------------------

--
-- indexes for table phrase_tables
--

ALTER TABLE phrase_tables
    ADD KEY phrase_tables_phrase_idx (phrase_id),
    ADD KEY phrase_tables_pod_idx (pod_id),
    ADD KEY phrase_tables_phrase_table_status_idx (phrase_table_status_id);
