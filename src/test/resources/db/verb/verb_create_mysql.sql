-- --------------------------------------------------------

--
-- table structure for verbs / triple predicates to use predefined behavior
--

CREATE TABLE IF NOT EXISTS verbs
(
    verb_id             smallint         NOT NULL COMMENT 'the internal unique primary index',
    verb_name           varchar(255)     NOT NULL COMMENT 'the unique type name as shown to the user and used for the selection',
    code_id             varchar(255) DEFAULT NULL COMMENT 'id text to link coded functionality to a specific verb',
    description         text         DEFAULT NULL COMMENT 'text to explain the type to the user as a tooltip; to be replaced by a language form entry',
    condition_type      bigint       DEFAULT NULL,
    formula_name        varchar(255) DEFAULT NULL COMMENT 'naming used in formulas',
    name_plural_reverse varchar(255) DEFAULT NULL COMMENT 'english description for the reverse list, e.g. Companies are ... TODO move to language forms',
    name_plural         varchar(255) DEFAULT NULL,
    name_reverse        varchar(255) DEFAULT NULL,
    words               bigint       DEFAULT NULL COMMENT 'used for how many phrases or formulas',
    PRIMARY KEY (verb_id)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for verbs / triple predicates to use predefined behavior';

--
-- AUTO_INCREMENT for table verbs
--
ALTER TABLE verbs
    MODIFY verb_id smallint NOT NULL AUTO_INCREMENT;
