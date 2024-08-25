-- --------------------------------------------------------

--
-- table structure for verbs / triple predicates to use predefined behavior
--

CREATE TABLE IF NOT EXISTS verbs
(
    verb_id             SERIAL PRIMARY KEY,
    verb_name           varchar(255)     NOT NULL,
    code_id             varchar(255) DEFAULT NULL,
    description         text         DEFAULT NULL,
    condition_type      bigint       DEFAULT NULL,
    formula_name        varchar(255) DEFAULT NULL,
    name_plural_reverse varchar(255) DEFAULT NULL,
    name_plural         varchar(255) DEFAULT NULL,
    name_reverse        varchar(255) DEFAULT NULL,
    words               bigint       DEFAULT NULL
);

COMMENT ON TABLE verbs IS 'for verbs / triple predicates to use predefined behavior';
COMMENT ON COLUMN verbs.verb_id IS 'the internal unique primary index';
COMMENT ON COLUMN verbs.verb_name IS 'the unique type name as shown to the user and used for the selection';
COMMENT ON COLUMN verbs.code_id IS 'id text to link coded functionality to a specific verb';
COMMENT ON COLUMN verbs.description IS 'text to explain the type to the user as a tooltip; to be replaced by a language form entry';
COMMENT ON COLUMN verbs.formula_name IS 'naming used in formulas';
COMMENT ON COLUMN verbs.name_plural_reverse IS 'english description for the reverse list, e.g. Companies are ... TODO move to language forms';
COMMENT ON COLUMN verbs.words IS 'used for how many phrases or formulas';
