-- --------------------------------------------------------

--
-- table structure to link predefined behaviour to a source
--

CREATE TABLE IF NOT EXISTS source_types
(
    source_type_id    SERIAL       PRIMARY KEY,
    type_name         varchar(255)     NOT NULL,
    code_id           varchar(255) DEFAULT NULL,
    description       text         DEFAULT NULL,
    group_msg_code_id varchar(255) DEFAULT NULL,
    wikipedia         text         DEFAULT NULL
);

COMMENT ON TABLE source_types                    IS 'to link predefined behaviour to a source';
COMMENT ON COLUMN source_types.source_type_id    IS 'the internal unique primary index';
COMMENT ON COLUMN source_types.type_name         IS 'the unique type name as shown to the user and used for the selection';
COMMENT ON COLUMN source_types.code_id           IS 'this id text is unique for all code links, is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration';
COMMENT ON COLUMN source_types.description       IS 'text to explain the type to the user as a tooltip; to be replaced by a language form entry';
COMMENT ON COLUMN source_types.group_msg_code_id IS 'the message id of the translatable name of the group e.g. structured data formats used to group the source types in a selector';
COMMENT ON COLUMN source_types.wikipedia         IS 'the url of the english wikipedia page that explains the format';
