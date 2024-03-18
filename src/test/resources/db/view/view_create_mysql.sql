-- --------------------------------------------------------

--
-- table structure to store all user interfaces entry points
--

CREATE TABLE IF NOT EXISTS views
(
    view_id       bigint           NOT NULL COMMENT 'the internal unique primary index',
    user_id       bigint       DEFAULT NULL COMMENT 'the owner / creator of the view',
    view_name     varchar(255)     NOT NULL COMMENT 'the name of the view used for searching',
    description   text         DEFAULT NULL COMMENT 'to explain the view to the user with a mouse over text; to be replaced by a language form entry',
    view_type_id  bigint       DEFAULT NULL COMMENT 'to link coded functionality to views e.g. to use a view for the startup page',
    code_id       varchar(255) DEFAULT NULL COMMENT 'to link coded functionality to a specific view e.g. define the internal system views',
    excluded      smallint     DEFAULT NULL COMMENT 'true if a user,but not all,have removed it',
    share_type_id smallint     DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint     DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to store all user interfaces entry points';

--
-- AUTO_INCREMENT for table views
--
ALTER TABLE views
    MODIFY view_id int(11) NOT NULL AUTO_INCREMENT;

--
-- table structure to save user specific changes to store all user interfaces entry points
--

CREATE TABLE IF NOT EXISTS user_views
(
    view_id       bigint NOT NULL              COMMENT 'with the user_id the internal unique primary index',
    user_id       bigint NOT NULL              COMMENT 'the changer of the view',
    language_id   bigint NOT NULL DEFAULT 1    COMMENT 'the name of the view used for searching',
    view_name     varchar(255)    DEFAULT NULL COMMENT 'the name of the view used for searching',
    description   text            DEFAULT NULL COMMENT 'to explain the view to the user with a mouse over text; to be replaced by a language form entry',
    view_type_id  bigint          DEFAULT NULL COMMENT 'to link coded functionality to views e.g. to use a view for the startup page',
    excluded      smallint        DEFAULT NULL COMMENT 'true if a user,but not all,have removed it',
    share_type_id smallint        DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint        DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to store all user interfaces entry points';
