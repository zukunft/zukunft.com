--
-- constraints for table change_fields
--

ALTER TABLE change_fields
    ADD CONSTRAINT change_fields_code_id_uk UNIQUE (code_id),
    ADD CONSTRAINT change_fields_change_table_fk FOREIGN KEY (table_id) REFERENCES change_tables (change_table_id);
