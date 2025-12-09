-- --------------------------------------------------------

--
-- table structure to define the relation between two views to another view e.g. to extend the change word view with the word usage and log components shared with the exclude word view
--

CREATE TABLE IF NOT EXISTS view_relations
(
    view_relation_id BIGSERIAL PRIMARY KEY,
    parent_view_id        bigint                NOT NULL,
    child_view_id         bigint                NOT NULL,
    user_id               bigint            DEFAULT NULL,
    view_relation_type_id smallint NOT NULL DEFAULT 1,
    start_pos             bigint            DEFAULT NULL,
    description           text              DEFAULT NULL,
    excluded              smallint          DEFAULT NULL,
    share_type_id         smallint          DEFAULT NULL,
    protect_id            smallint          DEFAULT NULL
);

COMMENT ON TABLE view_relations IS 'to define the relation between two views to another view e.g. to extend the change word view with the word usage and log components shared with the exclude word view';
COMMENT ON COLUMN view_relations.view_relation_id IS 'the internal unique primary index';
COMMENT ON COLUMN view_relations.parent_view_id IS 'the parent view that should be modified by the child view for the used view'; COMMENT ON COLUMN view_relations.child_view_id IS 'the child view that should modify the parent view for the used view';
COMMENT ON COLUMN view_relations.user_id IS 'the owner / creator of the view_relation';
COMMENT ON COLUMN view_relations.view_relation_type_id IS '1 = add components,2 = remove components as defined in view_relation_type';
COMMENT ON COLUMN view_relations.excluded IS 'true if a user,but not all,have removed it';
COMMENT ON COLUMN view_relations.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN view_relations.protect_id IS 'to protect against unwanted changes';

--
-- table structure to save user specific changes to define the relation between two views to another view e.g. to extend the change word view with the word usage and log components shared with the exclude word view
--

CREATE TABLE IF NOT EXISTS user_view_relations
(
    view_relation_id      bigint       NOT NULL,
    user_id               bigint       NOT NULL,
    start_pos             bigint   DEFAULT NULL,
    description           text     DEFAULT NULL,
    excluded              smallint DEFAULT NULL,
    share_type_id         smallint DEFAULT NULL,
    protect_id            smallint DEFAULT NULL
);

COMMENT ON TABLE user_view_relations IS 'to define the relation between two views to another view e.g. to extend the change word view with the word usage and log components shared with the exclude word view';
COMMENT ON COLUMN user_view_relations.view_relation_id IS 'with the user_id the internal unique primary index';
COMMENT ON COLUMN user_view_relations.user_id IS 'the changer of the view_relation';
COMMENT ON COLUMN user_view_relations.excluded IS 'true if a user,but not all,have removed it';
COMMENT ON COLUMN user_view_relations.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_view_relations.protect_id IS 'to protect against unwanted changes';