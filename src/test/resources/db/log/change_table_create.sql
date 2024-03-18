-- --------------------------------------------------------

--
-- table structure to keep the original table name even if a table name has changed and to avoid log changes in case a table is renamed
--

CREATE TABLE IF NOT EXISTS change_tables
(
    change_table_id   BIGSERIAL PRIMARY KEY,
    change_table_name varchar(255)     NOT NULL,
    code_id           varchar(255) DEFAULT NULL,
    description       text         DEFAULT NULL
);

COMMENT ON TABLE change_tables IS 'to keep the original table name even if a table name has changed and to avoid log changes in case a table is renamed';
COMMENT ON COLUMN change_tables.change_table_id IS 'the internal unique primary index';
COMMENT ON COLUMN change_tables.change_table_name IS 'the real name';
COMMENT ON COLUMN change_tables.code_id IS 'with this field tables can be combined in case of renaming';
COMMENT ON COLUMN change_tables.description IS 'the user readable name';
