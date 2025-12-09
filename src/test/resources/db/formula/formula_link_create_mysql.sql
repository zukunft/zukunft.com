-- --------------------------------------------------------

--
-- table structure for the link of a formula to phrases e.g. if the term pattern of a value matches this term pattern
--

CREATE TABLE IF NOT EXISTS formula_links
(
    formula_link_id      bigint       NOT NULL COMMENT 'the internal unique primary index',
    user_id              bigint   DEFAULT NULL COMMENT 'the owner / creator of the formula_link',
    formula_link_type_id smallint DEFAULT NULL,
    order_nbr            bigint   DEFAULT NULL,
    formula_id           bigint       NOT NULL,
    phrase_id            bigint       NOT NULL,
    excluded             smallint DEFAULT NULL COMMENT 'true if a user,but not all,have removed it',
    share_type_id        smallint DEFAULT NULL COMMENT 'to restrict the access',
    protect_id           smallint DEFAULT NULL COMMENT 'to protect against unwanted changes',
    PRIMARY KEY (formula_link_id)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for the link of a formula to phrases e.g. if the term pattern of a value matches this term pattern';

--
-- AUTO_INCREMENT for table formula_links
--
ALTER TABLE formula_links
    MODIFY formula_link_id bigint NOT NULL AUTO_INCREMENT;

--
-- table structure to save user specific changes for the link of a formula to phrases e.g. if the term pattern of a value matches this term pattern
--

CREATE TABLE IF NOT EXISTS user_formula_links
(
    formula_link_id      bigint       NOT NULL COMMENT 'with the user_id the internal unique primary index',
    user_id              bigint       NOT NULL COMMENT 'the changer of the formula_link',
    formula_link_type_id smallint DEFAULT NULL,
    order_nbr            bigint   DEFAULT NULL,
    excluded             smallint DEFAULT NULL COMMENT 'true if a user,but not all,have removed it',
    share_type_id        smallint DEFAULT NULL COMMENT 'to restrict the access',
    protect_id           smallint DEFAULT NULL COMMENT 'to protect against unwanted changes',
    PRIMARY KEY (formula_link_id,user_id)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for the link of a formula to phrases e.g. if the term pattern of a value matches this term pattern';
