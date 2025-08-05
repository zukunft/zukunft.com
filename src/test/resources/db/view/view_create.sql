-- --------------------------------------------------------

--
-- table structure to store all user interfaces entry points
--

CREATE TABLE IF NOT EXISTS views
(
    view_id       BIGSERIAL PRIMARY KEY,
    user_id       bigint       DEFAULT NULL,
    view_name     varchar(255)     NOT NULL,
    description   text         DEFAULT NULL,
    view_type_id  smallint     DEFAULT NULL,
    view_style_id smallint     DEFAULT NULL,
    code_id       varchar(255) DEFAULT NULL,
    excluded      smallint     DEFAULT NULL,
    share_type_id smallint     DEFAULT NULL,
    protect_id    smallint     DEFAULT NULL
);

COMMENT ON TABLE views IS 'to store all user interfaces entry points';
COMMENT ON COLUMN views.view_id IS 'the internal unique primary index';
COMMENT ON COLUMN views.user_id IS 'the owner / creator of the view';
COMMENT ON COLUMN views.view_name IS 'the name of the view used for searching';
COMMENT ON COLUMN views.description IS 'to explain the view to the user with a mouse over text; to be replaced by a language form entry';
COMMENT ON COLUMN views.view_type_id IS 'to link coded functionality to views e.g. to use a view for the startup page';
COMMENT ON COLUMN views.view_style_id IS 'the default display style for this view';
COMMENT ON COLUMN views.code_id IS 'to link coded functionality to a specific view e.g. define the internal system views';
COMMENT ON COLUMN views.excluded IS 'true if a user,but not all,have removed it';
COMMENT ON COLUMN views.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN views.protect_id IS 'to protect against unwanted changes';

--
-- table structure to save user specific changes to store all user interfaces entry points
--

CREATE TABLE IF NOT EXISTS user_views
(
    view_id       bigint   NOT NULL,
    user_id       bigint   NOT NULL,
    language_id   smallint NOT NULL DEFAULT 1,
    view_name     varchar(255)      DEFAULT NULL,
    description   text              DEFAULT NULL,
    view_type_id  smallint          DEFAULT NULL,
    view_style_id smallint          DEFAULT NULL,
    excluded      smallint          DEFAULT NULL,
    share_type_id smallint          DEFAULT NULL,
    protect_id    smallint          DEFAULT NULL
);

COMMENT ON TABLE user_views IS 'to store all user interfaces entry points';
COMMENT ON COLUMN user_views.view_id IS 'with the user_id the internal unique primary index';
COMMENT ON COLUMN user_views.user_id IS 'the changer of the view';
COMMENT ON COLUMN user_views.language_id IS 'the name of the view used for searching';
COMMENT ON COLUMN user_views.view_name IS 'the name of the view used for searching';
COMMENT ON COLUMN user_views.description IS 'to explain the view to the user with a mouse over text; to be replaced by a language form entry';
COMMENT ON COLUMN user_views.view_type_id IS 'to link coded functionality to views e.g. to use a view for the startup page';
COMMENT ON COLUMN user_views.view_style_id IS 'the default display style for this view';
COMMENT ON COLUMN user_views.excluded IS 'true if a user,but not all,have removed it';
COMMENT ON COLUMN user_views.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_views.protect_id IS 'to protect against unwanted changes';
