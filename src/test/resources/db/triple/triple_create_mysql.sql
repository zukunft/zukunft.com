-- --------------------------------------------------------

--
-- table structure to link one word or triple with a verb to another word or triple
--

CREATE TABLE IF NOT EXISTS triples
(
    triple_id           bigint           NOT NULL COMMENT 'the internal unique primary index',
    user_id             bigint       DEFAULT NULL COMMENT 'the owner / creator of the triple',
    from_phrase_id      bigint           NOT NULL COMMENT 'the phrase_id that is linked',
    verb_id             bigint           NOT NULL COMMENT 'the verb_id that defines how the phrases are linked',
    to_phrase_id        bigint           NOT NULL COMMENT 'the phrase_id to which the first phrase is linked',
    triple_name         varchar(255) DEFAULT NULL COMMENT 'the name used which must be unique within the terms of the user',
    name_given          varchar(255) DEFAULT NULL COMMENT 'the unique name manually set by the user,which can be null if the generated name should be used',
    name_generated      varchar(255) DEFAULT NULL COMMENT 'the generated name is saved in the database for database base unique check based on the phrases and verb,which can be overwritten by the given name',
    description         text         DEFAULT NULL COMMENT 'text that should be shown to the user in case of mouseover on the triple name',
    triple_condition_id bigint       DEFAULT NULL COMMENT 'formula_id of a formula with a boolean result; the term is only added if formula result is true',
    phrase_type_id      bigint       DEFAULT NULL COMMENT 'to link coded functionality to words e.g. to exclude measure words from a percent result',
    view_id             bigint       DEFAULT NULL COMMENT 'the default mask for this triple',
    `values`            bigint       DEFAULT NULL COMMENT 'number of values linked to the word,which gives an indication of the importance',
    inactive            smallint     DEFAULT NULL COMMENT 'true if the word is not yet active e.g. because it is moved to the prime words with a 16 bit id',
    code_id             varchar(255) DEFAULT NULL COMMENT 'to link coded functionality to a specific triple e.g. to get the values of the system configuration',
    excluded            smallint     DEFAULT NULL COMMENT 'true if a user,but not all,have removed it',
    share_type_id       smallint     DEFAULT NULL COMMENT 'to restrict the access',
    protect_id          smallint     DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to link one word or triple with a verb to another word or triple';

--
-- table structure to save user specific changes to link one word or triple with a verb to another word or triple
--

CREATE TABLE IF NOT EXISTS user_triples
(
    triple_id           bigint           NOT NULL COMMENT 'with the user_id the internal unique primary index',
    user_id             bigint           NOT NULL COMMENT 'the changer of the triple',
    language_id         bigint NOT NULL DEFAULT 1 COMMENT 'the name used which must be unique within the terms of the user',
    triple_name         varchar(255) DEFAULT NULL COMMENT 'the name used which must be unique within the terms of the user',
    name_given          varchar(255) DEFAULT NULL COMMENT 'the unique name manually set by the user,which can be null if the generated name should be used',
    name_generated      varchar(255) DEFAULT NULL COMMENT 'the generated name is saved in the database for database base unique check based on the phrases and verb,which can be overwritten by the given name',
    description         text         DEFAULT NULL COMMENT 'text that should be shown to the user in case of mouseover on the triple name',
    triple_condition_id bigint       DEFAULT NULL COMMENT 'formula_id of a formula with a boolean result; the term is only added if formula result is true',
    phrase_type_id      bigint       DEFAULT NULL COMMENT 'to link coded functionality to words e.g. to exclude measure words from a percent result',
    view_id             bigint       DEFAULT NULL COMMENT 'the default mask for this triple',
    `values`            bigint       DEFAULT NULL COMMENT 'number of values linked to the word,which gives an indication of the importance',
    excluded            smallint     DEFAULT NULL COMMENT 'true if a user,but not all,have removed it',
    share_type_id       smallint     DEFAULT NULL COMMENT 'to restrict the access',
    protect_id          smallint     DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to link one word or triple with a verb to another word or triple';

