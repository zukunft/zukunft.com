-- --------------------------------------------------------

--
-- table structure for the phrase type to set the predefined behaviour of a word or triple
--

CREATE TABLE IF NOT EXISTS phrase_types
(
    phrase_type_id BIGSERIAL PRIMARY KEY,
    type_name      varchar(255) NOT NULL,
    code_id        varchar(255) DEFAULT NULL,
    description    text         DEFAULT NULL,
    scaling_factor bigint       DEFAULT NULL,
    word_symbol    varchar(255) DEFAULT NULL
);

COMMENT ON TABLE phrase_types IS 'for the phrase type to set the predefined behaviour of a word or triple';
COMMENT ON COLUMN phrase_types.phrase_type_id IS 'the internal unique primary index';
COMMENT ON COLUMN phrase_types.type_name IS 'the unique type name as shown to the user and used for the selection';
COMMENT ON COLUMN phrase_types.code_id IS 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration';
COMMENT ON COLUMN phrase_types.description IS 'text to explain the type to the user as a tooltip; to be replaced by a language form entry';
COMMENT ON COLUMN phrase_types.scaling_factor IS 'e.g. for percent the scaling factor is 100';
COMMENT ON COLUMN phrase_types.word_symbol IS 'e.g. for percent the symbol is %';
