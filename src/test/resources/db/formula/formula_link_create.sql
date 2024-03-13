-- --------------------------------------------------------

--
-- table structure for the link of a formula to phrases e.g. if the term pattern of a value matches this term pattern
--

CREATE TABLE IF NOT EXISTS formula_links
(
    formula_link_id BIGSERIAL PRIMARY KEY,
    user_id         bigint   DEFAULT NULL,
    link_type_id    bigint   DEFAULT NULL,
    order_nbr       bigint   DEFAULT NULL,
    formula_id      bigint       NOT NULL,
    phrase_id       bigint       NOT NULL,
    excluded        smallint DEFAULT NULL,
    share_type_id   smallint DEFAULT NULL,
    protect_id      smallint DEFAULT NULL
);

COMMENT ON TABLE formula_links IS 'for the link of a formula to phrases e.g. if the term pattern of a value matches this term pattern';
COMMENT ON COLUMN formula_links.formula_link_id IS 'the internal unique primary index';
COMMENT ON COLUMN formula_links.user_id IS 'the owner / creator of the formula_link';
COMMENT ON COLUMN formula_links.excluded IS 'true if a user,but not all,have removed it';
COMMENT ON COLUMN formula_links.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN formula_links.protect_id IS 'to protect against unwanted changes';

--
-- table structure to save user specific changes for the link of a formula to phrases e.g. if the term pattern of a value matches this term pattern
--

CREATE TABLE IF NOT EXISTS user_formula_links
(
    formula_link_id bigint       NOT NULL,
    user_id         bigint       NOT NULL,
    link_type_id    bigint   DEFAULT NULL,
    order_nbr       bigint   DEFAULT NULL,
    excluded        smallint DEFAULT NULL,
    share_type_id   smallint DEFAULT NULL,
    protect_id      smallint DEFAULT NULL

);

COMMENT ON TABLE user_formula_links IS 'for the link of a formula to phrases e.g. if the term pattern of a value matches this term pattern';
COMMENT ON COLUMN user_formula_links.formula_link_id IS 'with the user_id the internal unique primary index';
COMMENT ON COLUMN user_formula_links.user_id IS 'the changer of the formula_link';
COMMENT ON COLUMN user_formula_links.excluded IS 'true if a user,but not all,have removed it';
COMMENT ON COLUMN user_formula_links.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_formula_links.protect_id IS 'to protect against unwanted changes';
