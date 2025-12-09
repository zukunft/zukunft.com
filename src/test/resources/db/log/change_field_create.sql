-- --------------------------------------------------------

--
-- table structure to keep the original field name even if a table name has changed
--

CREATE TABLE IF NOT EXISTS change_fields
(
    change_field_id   SERIAL PRIMARY KEY,
    table_id          smallint         NOT NULL,
    change_field_name varchar(255)     NOT NULL,
    code_id           varchar(255) DEFAULT NULL,
    description       text         DEFAULT NULL
);

COMMENT ON TABLE change_fields IS 'to keep the original field name even if a table name has changed';
COMMENT ON COLUMN change_fields.change_field_id IS 'the internal unique primary index';
COMMENT ON COLUMN change_fields.table_id IS 'because every field must only be unique within a table';
COMMENT ON COLUMN change_fields.change_field_name IS 'the real name';
COMMENT ON COLUMN change_fields.code_id IS 'to display the change with some linked information';
