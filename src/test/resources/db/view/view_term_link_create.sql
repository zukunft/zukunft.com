-- --------------------------------------------------------

--
-- table structure to link view to a word, triple, verb or formula with an n:m relation
--

CREATE TABLE IF NOT EXISTS view_term_links
(
    view_term_link_id BIGSERIAL PRIMARY KEY,
    term_id           bigint             NOT NULL,
    view_id           bigint             NOT NULL,
    view_link_type_id smallint NOT NULL DEFAULT 1,
    user_id           bigint         DEFAULT NULL,
    description       text           DEFAULT NULL,
    excluded          smallint       DEFAULT NULL,
    share_type_id     smallint       DEFAULT NULL,
    protect_id        smallint       DEFAULT NULL
);

COMMENT ON TABLE view_term_links IS 'to link view to a word, triple, verb or formula with an n:m relation';
COMMENT ON COLUMN view_term_links.view_term_link_id IS 'the internal unique primary index';
COMMENT ON COLUMN view_term_links.view_link_type_id IS '1 = from_term_id is link the terms table; 2=link to the term_links table;3=to term_groups';
COMMENT ON COLUMN view_term_links.user_id IS 'the owner / creator of the view_term_link';
COMMENT ON COLUMN view_term_links.excluded IS 'true if a user,but not all,have removed it';
COMMENT ON COLUMN view_term_links.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN view_term_links.protect_id IS 'to protect against unwanted changes';

--
-- table structure to save user specific changes to link view to a word, triple, verb or formula with an n:m relation
--

CREATE TABLE IF NOT EXISTS user_view_term_links
(
    view_term_link_id bigint       NOT NULL,
    user_id           bigint       NOT NULL,
    view_link_type_id smallint DEFAULT NULL,
    description       text     DEFAULT NULL,
    excluded          smallint DEFAULT NULL,
    share_type_id     smallint DEFAULT NULL,
    protect_id        smallint DEFAULT NULL
);

COMMENT ON TABLE user_view_term_links IS 'to link view to a word,triple,verb or formula with an n:m relation';
COMMENT ON COLUMN user_view_term_links.view_term_link_id IS 'with the user_id the internal unique primary index';
COMMENT ON COLUMN user_view_term_links.user_id IS 'the changer of the view_term_link';
COMMENT ON COLUMN user_view_term_links.excluded IS 'true if a user,but not all,have removed it';
COMMENT ON COLUMN user_view_term_links.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_view_term_links.protect_id IS 'to protect against unwanted changes';