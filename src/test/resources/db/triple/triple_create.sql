-- --------------------------------------------------------

--
-- table structure to link one word or triple with a verb to another word or triple
--

CREATE TABLE IF NOT EXISTS triples
(
    triple_id           BIGSERIAL PRIMARY KEY,
    from_phrase_id      bigint            DEFAULT NULL,
    verb_id             bigint                NOT NULL,
    to_phrase_id        bigint                NOT NULL,
    user_id             bigint            DEFAULT NULL,
    triple_name         varchar(255)      DEFAULT NULL,
    name_given          varchar(255)      DEFAULT NULL,
    name_generated      varchar(255)      DEFAULT NULL,
    description         text              DEFAULT NULL,
    triple_condition_id bigint            DEFAULT NULL,
    phrase_type_id      smallint          DEFAULT NULL,
    view_id             bigint            DEFAULT NULL,
    values              bigint            DEFAULT NULL,
    inactive            smallint          DEFAULT NULL,
    code_id             varchar(255)      DEFAULT NULL,
    excluded            smallint          DEFAULT NULL,
    share_type_id       smallint          DEFAULT NULL,
    protect_id          smallint          DEFAULT NULL
);

COMMENT ON TABLE triples IS 'to link one word or triple with a verb to another word or triple';
COMMENT ON COLUMN triples.triple_id IS 'the internal unique primary index';
COMMENT ON COLUMN triples.from_phrase_id IS 'the phrase_id that is linked which can be null e.g. if a symbol is assigned to a triple (m/s is symbol for meter per second)';
COMMENT ON COLUMN triples.verb_id IS 'the verb_id that defines how the phrases are linked';
COMMENT ON COLUMN triples.to_phrase_id IS 'the phrase_id to which the first phrase is linked';
COMMENT ON COLUMN triples.user_id IS 'the owner / creator of the triple';
COMMENT ON COLUMN triples.triple_name IS 'the name used which must be unique within the terms of the user';
COMMENT ON COLUMN triples.name_given IS 'the unique name manually set by the user,which can be null if the generated name should be used';
COMMENT ON COLUMN triples.name_generated IS 'the generated name is saved in the database for database base unique check based on the phrases and verb,which can be overwritten by the given name';
COMMENT ON COLUMN triples.description IS 'text that should be shown to the user in case of mouseover on the triple name';
COMMENT ON COLUMN triples.triple_condition_id IS 'formula_id of a formula with a boolean result; the term is only added if formula result is true';
COMMENT ON COLUMN triples.phrase_type_id IS 'to link coded functionality to words e.g. to exclude measure words from a percent result';
COMMENT ON COLUMN triples.view_id IS 'the default mask for this triple';
COMMENT ON COLUMN triples.values IS 'number of values linked to the word,which gives an indication of the importance';
COMMENT ON COLUMN triples.inactive IS 'true if the word is not yet active e.g. because it is moved to the prime words with a 16 bit id';
COMMENT ON COLUMN triples.code_id IS 'to link coded functionality to a specific triple e.g. to get the values of the system configuration';
COMMENT ON COLUMN triples.excluded IS 'true if a user,but not all,have removed it';
COMMENT ON COLUMN triples.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN triples.protect_id IS 'to protect against unwanted changes';

--
-- table structure to save user specific changes to link one word or triple with a verb to another word or triple
--

CREATE TABLE IF NOT EXISTS user_triples
(
    triple_id           bigint   NOT NULL,
    user_id             bigint   NOT NULL,
    language_id         bigint   NOT NULL DEFAULT 1,
    triple_name         varchar(255)      DEFAULT NULL,
    name_given          varchar(255)      DEFAULT NULL,
    name_generated      varchar(255)      DEFAULT NULL,
    description         text              DEFAULT NULL,
    triple_condition_id bigint            DEFAULT NULL,
    phrase_type_id      smallint          DEFAULT NULL,
    view_id             bigint            DEFAULT NULL,
    values              bigint            DEFAULT NULL,
    excluded            smallint          DEFAULT NULL,
    share_type_id       smallint          DEFAULT NULL,
    protect_id          smallint          DEFAULT NULL
);

COMMENT ON TABLE user_triples IS 'to link one word or triple with a verb to another word or triple';
COMMENT ON COLUMN user_triples.triple_id IS 'with the user_id the internal unique primary index';
COMMENT ON COLUMN user_triples.user_id IS 'the changer of the triple';
COMMENT ON COLUMN user_triples.language_id IS 'the name used which must be unique within the terms of the user';
COMMENT ON COLUMN user_triples.triple_name IS 'the name used which must be unique within the terms of the user';
COMMENT ON COLUMN user_triples.name_given IS 'the unique name manually set by the user,which can be null if the generated name should be used';
COMMENT ON COLUMN user_triples.name_generated IS 'the generated name is saved in the database for database base unique check based on the phrases and verb,which can be overwritten by the given name';
COMMENT ON COLUMN user_triples.description IS 'text that should be shown to the user in case of mouseover on the triple name';
COMMENT ON COLUMN user_triples.triple_condition_id IS 'formula_id of a formula with a boolean result; the term is only added if formula result is true';
COMMENT ON COLUMN user_triples.phrase_type_id IS 'to link coded functionality to words e.g. to exclude measure words from a percent result';
COMMENT ON COLUMN user_triples.view_id IS 'the default mask for this triple';
COMMENT ON COLUMN user_triples.values IS 'number of values linked to the word,which gives an indication of the importance';
COMMENT ON COLUMN user_triples.excluded IS 'true if a user,but not all,have removed it';
COMMENT ON COLUMN user_triples.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_triples.protect_id IS 'to protect against unwanted changes';