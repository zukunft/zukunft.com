--
-- constraints for table phrase_tables
--

ALTER TABLE phrase_tables
    ADD CONSTRAINT phrase_tables_pod_fk FOREIGN KEY (pod_id) REFERENCES pods (pod_id),
    ADD CONSTRAINT phrase_tables_phrase_table_status_fk FOREIGN KEY (phrase_table_status_id) REFERENCES phrase_table_status (phrase_table_status_id);
