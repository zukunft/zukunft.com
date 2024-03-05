-- --------------------------------------------------------

--
-- indexes for table change_fields
--

CREATE UNIQUE INDEX change_fields_unique_idx ON change_fields (table_id,change_field_name);
CREATE INDEX change_fields_table_idx ON change_fields (table_id);
CREATE INDEX change_fields_change_field_name_idx ON change_fields (change_field_name);
