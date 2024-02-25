-- --------------------------------------------------------

--
-- table structure for the predefined behaviour of e.g. a word, triple, ...
--

CREATE TABLE IF NOT EXISTS phrase_types
(
    phrase_type_id BIGSERIAL PRIMARY KEY,
    type_name      varchar(255) NOT NULL,
    description    text,
    code_id        varchar(255) DEFAULT NULL,
    scaling_factor bigint       DEFAULT NULL,
    word_symbol    varchar(5)   DEFAULT NULL
);

COMMENT ON TABLE phrase_types IS 'for the predefined behaviour of e.g. a word,triple,...';
COMMENT ON COLUMN phrase_types.phrase_type_id IS 'the internal unique primary index';
COMMENT ON COLUMN phrase_types.scaling_factor IS 'e.g. for percent the scaling factor is 100';
COMMENT ON COLUMN phrase_types.word_symbol IS 'e.g. for percent the symbol is %';
