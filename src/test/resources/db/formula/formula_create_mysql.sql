-- --------------------------------------------------------
--
-- table structure the mathematical expression to calculate results based on values and results
--

CREATE TABLE IF NOT EXISTS formulas
(
    formula_id        bigint        NOT NULL COMMENT 'the internal unique primary index',
    user_id           bigint    DEFAULT NULL COMMENT 'the owner / creator of the formula',
    formula_name      varchar(255)  NOT NULL COMMENT 'the text used to search for formulas that must also be unique for all terms (words, triples, verbs and formulas)',
    formula_text      text          NOT NULL COMMENT 'the internal formula expression with the database references e.g. {f1} for formula with id 1',
    resolved_text     text          NOT NULL COMMENT 'the formula expression in user readable format as shown to the user which can include formatting for better readability',
    description       text      DEFAULT NULL COMMENT 'text to be shown to the user for mouse over; to be replaced by a language form entry',
    formula_type_id   bigint    DEFAULT NULL COMMENT 'the id of the formula type',
    all_values_needed smallint  DEFAULT NULL COMMENT 'the "calculate only if all values used in the formula exist" flag should be converted to "all needed for calculation" instead of just displaying "1"',
    last_update       timestamp DEFAULT NULL COMMENT 'time of the last calculation relevant update',
    view_id           bigint    DEFAULT NULL COMMENT 'the default mask for this formula',
    `usage`           bigint    DEFAULT NULL COMMENT 'number of results linked to this formula',
    excluded          smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id     smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id        smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'the mathematical expression to calculate results based on values and results';

--
-- table structure to save user specific changes the mathematical expression to calculate results based on values and results
--

CREATE TABLE IF NOT EXISTS user_formulas
(
    formula_id        bigint           NOT NULL COMMENT 'with the user_id the internal unique primary index',
    user_id           bigint           NOT NULL COMMENT 'the changer of the formula',
    formula_name      varchar(255) DEFAULT NULL COMMENT 'the text used to search for formulas that must also be unique for all terms (words, triples, verbs and formulas)',
    formula_text      text         DEFAULT NULL COMMENT 'the internal formula expression with the database references e.g. {f1} for formula with id 1',
    resolved_text     text         DEFAULT NULL COMMENT 'the formula expression in user readable format as shown to the user which can include formatting for better readability',
    description       text         DEFAULT NULL COMMENT 'text to be shown to the user for mouse over; to be replaced by a language form entry',
    formula_type_id   bigint       DEFAULT NULL COMMENT 'the id of the formula type',
    all_values_needed smallint     DEFAULT NULL COMMENT 'the "calculate only if all values used in the formula exist" flag should be converted to "all needed for calculation" instead of just displaying "1"',
    last_update       timestamp    DEFAULT NULL COMMENT 'time of the last calculation relevant update',
    view_id           bigint       DEFAULT NULL COMMENT 'the default mask for this formula',
    `usage`           bigint       DEFAULT NULL COMMENT 'number of results linked to this formula',
    excluded          smallint     DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id     smallint     DEFAULT NULL COMMENT 'to restrict the access',
    protect_id        smallint     DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'the mathematical expression to calculate results based on values and results';