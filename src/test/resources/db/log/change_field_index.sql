-- --------------------------------------------------------

--
-- indexes for table change_fields
--

CREATE INDEX change_fields_change_field_name_idx ON change_fields (change_field_name);
CREATE INDEX change_fields_table_idx ON change_fields (table_id);
