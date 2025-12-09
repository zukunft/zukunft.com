-- --------------------------------------------------------

--
-- table structure to define the relation between two views to another view e.g. to extend the change word view with the word usage and log components shared with the exclude word view
--

CREATE TABLE IF NOT EXISTS view_relations
(
    view_relation_id      bigint       NOT NULL COMMENT 'the internal unique primary index',
    parent_view_id        bigint       NOT NULL COMMENT 'the parent view that should be modified by the child view for the used view',
    child_view_id         bigint       NOT NULL COMMENT 'the child view that should modify the parent view for the used view',
    user_id               bigint   DEFAULT NULL COMMENT 'the owner / creator of the view_relation',
    view_relation_type_id smallint     NOT NULL DEFAULT 1 COMMENT '1 = add components,2 = remove components as defined in view_relation_type',
    start_pos             bigint   DEFAULT NULL,
    description           text     DEFAULT NULL,
    excluded              smallint DEFAULT NULL COMMENT 'true if a user,but not all,have removed it',
    share_type_id         smallint DEFAULT NULL COMMENT 'to restrict the access',
    protect_id            smallint DEFAULT NULL COMMENT 'to protect against unwanted changes',
    PRIMARY KEY (view_relation_id)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to define the relation between two views to another view e.g. to extend the change word view with the word usage and log components shared with the exclude word view';

--
-- AUTO_INCREMENT for table view_relations
--
ALTER TABLE view_relations
    MODIFY view_relation_id bigint NOT NULL AUTO_INCREMENT;

--
-- table structure to save user specific changes to define the relation between two views to another view e.g. to extend the change word view with the word usage and log components shared with the exclude word view
--

CREATE TABLE IF NOT EXISTS user_view_relations
(
    view_relation_id bigint       NOT NULL COMMENT 'with the user_id the internal unique primary index',
    user_id          bigint       NOT NULL COMMENT 'the changer of the view_relation',
    start_pos        bigint   DEFAULT NULL,
    description      text     DEFAULT NULL,
    excluded         smallint DEFAULT NULL COMMENT 'true if a user,but not all,have removed it',
    share_type_id    smallint DEFAULT NULL COMMENT 'to restrict the access',
    protect_id       smallint DEFAULT NULL COMMENT 'to protect against unwanted changes',
    PRIMARY KEY (view_relation_id, user_id)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to define the relation between two views to another view e.g. to extend the change word view with the word usage and log components shared with the exclude word view';
