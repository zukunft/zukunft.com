-- --------------------------------------------------------

--
-- table structure to link view to a word,triple,verb or formula with an n:m relation
--

CREATE TABLE IF NOT EXISTS view_term_links
(
    view_term_link_id bigint       NOT NULL COMMENT 'the internal unique primary index',
    term_id           bigint       NOT NULL,
    view_id           bigint       NOT NULL,
    view_link_type_id smallint     NOT NULL DEFAULT 1 COMMENT '1 = from_term_id is link the terms table; 2=link to the term_links table;3=to term_groups',
    user_id           bigint   DEFAULT NULL COMMENT 'the owner / creator of the view_term_link',
    description       text     DEFAULT NULL,
    excluded          smallint DEFAULT NULL COMMENT 'true if a user,but not all,have removed it',
    share_type_id     smallint DEFAULT NULL COMMENT 'to restrict the access',
    protect_id        smallint DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to link view to a word,triple,verb or formula with an n:m relation';

--
-- AUTO_INCREMENT for table view_term_links
--
ALTER TABLE view_term_links
    MODIFY view_term_link_id int(11) NOT NULL AUTO_INCREMENT;

--
-- table structure to save user specific changes to link view to a word,triple,verb or formula with an n:m relation
--

CREATE TABLE IF NOT EXISTS user_view_term_links
(
    view_term_link_id bigint       NOT NULL COMMENT 'with the user_id the internal unique primary index',
    user_id           bigint       NOT NULL COMMENT 'the changer of the view_term_link',
    view_link_type_id smallint DEFAULT NULL,
    description       text     DEFAULT NULL,
    excluded          smallint DEFAULT NULL COMMENT 'true if a user,but not all,have removed it',
    share_type_id     smallint DEFAULT NULL COMMENT 'to restrict the access',
    protect_id        smallint DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to link view to a word,triple,verb or formula with an n:m relation';
