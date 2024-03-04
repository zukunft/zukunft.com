-- --------------------------------------------------------

--
-- indexes for table phrase_tables
--

CREATE INDEX phrase_tables_phrase_idx ON phrase_tables (phrase_id);
CREATE INDEX phrase_tables_pod_idx ON phrase_tables (pod_id);
CREATE INDEX phrase_tables_phrase_table_status_idx ON phrase_tables (phrase_table_status_id);
