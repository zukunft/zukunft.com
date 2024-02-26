-- --------------------------------------------------------

--
-- table structure for the predefined behaviour of e.g. a word, triple, ...
--

CREATE TABLE IF NOT EXISTS phrase_types
(
    phrase_type_id BIGSERIAL PRIMARY KEY,
    type_name      varchar(255) NOT NULL,
    description    text         DEFAULT NULL,
    code_id        varchar(255) DEFAULT NULL,
    scaling_factor bigint       DEFAULT NULL,
    word_symbol    varchar(255) DEFAULT NULL
);

COMMENT ON TABLE phrase_types IS 'for the predefined behaviour of e.g. a word,triple,...';
COMMENT ON COLUMN phrase_types.phrase_type_id IS 'the internal unique primary index';
COMMENT ON COLUMN phrase_types.type_name IS 'the unique name to select the type by the user';
COMMENT ON COLUMN phrase_types.description IS 'text that should be shown to the user on mouse over; to be replaced by a language form entry ';
COMMENT ON COLUMN phrase_types.code_id IS 'to link coded functionality to a specific word e.g. to get the values of the system configuration';
COMMENT ON COLUMN phrase_types.scaling_factor IS 'e.g. for percent the scaling factor is 100';
COMMENT ON COLUMN phrase_types.word_symbol IS 'e.g. for percent the symbol is %';
