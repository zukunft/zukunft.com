-- --------------------------------------------------------

--
-- table structure for the actual status of tables for a phrase
--

CREATE TABLE IF NOT EXISTS phrase_table_status
(
    phrase_table_status_id BIGSERIAL PRIMARY KEY,
    type_name     varchar(255)     NOT NULL,
    code_id       varchar(255) DEFAULT NULL,
    description   text         DEFAULT NULL
);

COMMENT ON TABLE phrase_table_status IS 'for the actual status of tables for a phrase';
COMMENT ON COLUMN phrase_table_status.phrase_table_status_id IS 'the internal unique primary index';
COMMENT ON COLUMN phrase_table_status.type_name IS 'the unique type name as shown to the user and used for the selection';
COMMENT ON COLUMN phrase_table_status.code_id IS 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration';
COMMENT ON COLUMN phrase_table_status.description IS 'text to explain the type to the user as a tooltip; to be replaced by a language form entry';
