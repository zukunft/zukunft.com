-- --------------------------------------------------------

--
-- table structure the mathematical expression to calculate results based on values and results
--

CREATE TABLE IF NOT EXISTS formulas
(
    formula_id BIGSERIAL PRIMARY KEY,
    user_id           bigint   DEFAULT NULL,
    formula_name      varchar(255) NOT NULL,
    formula_text      text     DEFAULT NULL,
    resolved_text     text     DEFAULT NULL,
    description       text     DEFAULT NULL,
    formula_type_id   bigint   DEFAULT NULL,
    all_values_needed smallint DEFAULT NULL,
    last_update       timestamp DEFAULT NULL,
    view_id           bigint   DEFAULT NULL,
    usage             bigint   DEFAULT NULL,
    excluded          smallint DEFAULT NULL,
    share_type_id     smallint DEFAULT NULL,
    protect_id        smallint DEFAULT NULL
);

COMMENT ON TABLE formulas IS 'the mathematical expression to calculate results based on values and results';
COMMENT ON COLUMN formulas.formula_id IS 'the internal unique primary index';
COMMENT ON COLUMN formulas.user_id IS 'the owner / creator of the formula';
COMMENT ON COLUMN formulas.formula_name IS 'the text used to search for formulas that must also be unique for all terms (words,triples,verbs and formulas)';
COMMENT ON COLUMN formulas.formula_text IS 'the internal formula expression with the database references e.g. {f1} for formula with id 1';
COMMENT ON COLUMN formulas.resolved_text IS 'the formula expression in user readable format as shown to the user which can include formatting for better readability';
COMMENT ON COLUMN formulas.description IS 'text to be shown to the user for mouse over; to be replaced by a language form entry';
COMMENT ON COLUMN formulas.formula_type_id IS 'the id of the formula type';
COMMENT ON COLUMN formulas.all_values_needed IS 'the "calculate only if all values used in the formula exist" flag should be converted to "all needed for calculation" instead of just displaying "1"';
COMMENT ON COLUMN formulas.last_update IS 'time of the last calculation relevant update';
COMMENT ON COLUMN formulas.view_id IS 'the default mask for this formula';
COMMENT ON COLUMN formulas.usage IS 'number of results linked to this formula';
COMMENT ON COLUMN formulas.excluded IS 'true if a user,but not all,have removed it';
COMMENT ON COLUMN formulas.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN formulas.protect_id IS 'to protect against unwanted changes';

--
-- table structure to save user specific changes the mathematical expression to calculate results based on values and results
--

CREATE TABLE IF NOT EXISTS user_formulas
(
    formula_id        bigint           NOT NULL,
    user_id           bigint           NOT NULL,
    formula_name      varchar(255) DEFAULT NULL,
    formula_text      text         DEFAULT NULL,
    resolved_text     text         DEFAULT NULL,
    description       text         DEFAULT NULL,
    formula_type_id   bigint       DEFAULT NULL,
    all_values_needed smallint     DEFAULT NULL,
    last_update       timestamp    DEFAULT NULL,
    view_id           bigint       DEFAULT NULL,
    usage             bigint       DEFAULT NULL,
    excluded          smallint     DEFAULT NULL,
    share_type_id     smallint     DEFAULT NULL,
    protect_id        smallint     DEFAULT NULL
);

COMMENT ON TABLE user_formulas IS 'the mathematical expression to calculate results based on values and results';
COMMENT ON COLUMN user_formulas.formula_id IS 'with the user_id the internal unique primary index';
COMMENT ON COLUMN user_formulas.user_id IS 'the changer of the formula';
COMMENT ON COLUMN user_formulas.formula_name IS 'the text used to search for formulas that must also be unique for all terms (words,triples,verbs and formulas)';
COMMENT ON COLUMN user_formulas.formula_text IS 'the internal formula expression with the database references e.g. {f1} for formula with id 1';
COMMENT ON COLUMN user_formulas.resolved_text IS 'the formula expression in user readable format as shown to the user which can include formatting for better readability';
COMMENT ON COLUMN user_formulas.description IS 'text to be shown to the user for mouse over; to be replaced by a language form entry';
COMMENT ON COLUMN user_formulas.formula_type_id IS 'the id of the formula type';
COMMENT ON COLUMN user_formulas.all_values_needed IS 'the "calculate only if all values used in the formula exist" flag should be converted to "all needed for calculation" instead of just displaying "1"';
COMMENT ON COLUMN user_formulas.last_update IS 'time of the last calculation relevant update';
COMMENT ON COLUMN user_formulas.view_id IS 'the default mask for this formula';
COMMENT ON COLUMN user_formulas.usage IS 'number of results linked to this formula';
COMMENT ON COLUMN user_formulas.excluded IS 'true if a user,but not all,have removed it';
COMMENT ON COLUMN user_formulas.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_formulas.protect_id IS 'to protect against unwanted changes';