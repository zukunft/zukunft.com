-- --------------------------------------------------------

--
-- table structure for the read access control
--

CREATE TABLE IF NOT EXISTS share_types
(
    share_type_id SERIAL PRIMARY KEY,
    type_name     varchar(255) NOT NULL,
    code_id       varchar(255) DEFAULT NULL,
    description   text         DEFAULT NULL
);

COMMENT ON TABLE share_types IS 'for the read access control';
COMMENT ON COLUMN share_types.share_type_id IS 'the internal unique primary index';
COMMENT ON COLUMN share_types.type_name IS 'the unique type name as shown to the user and used for the selection';
COMMENT ON COLUMN share_types.code_id IS 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration';
COMMENT ON COLUMN share_types.description IS 'text to explain the type to the user as a tooltip; to be replaced by a language form entry';
