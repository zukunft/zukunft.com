-- --------------------------------------------------------

--
-- table structure to link components to views with an n:m relation
--

CREATE TABLE IF NOT EXISTS component_links
(
    component_link_id BIGSERIAL PRIMARY KEY,
    view_id                bigint   NOT NULL,
    component_id           bigint   NOT NULL,
    user_id                bigint            DEFAULT NULL,
    order_nbr              bigint   NOT NULL DEFAULT 1,
    component_link_type_id smallint NOT NULL DEFAULT 1,
    position_type_id       smallint NOT NULL DEFAULT 2,
    excluded               smallint          DEFAULT NULL,
    share_type_id          smallint          DEFAULT NULL,
    protect_id             smallint          DEFAULT NULL
);

COMMENT ON TABLE component_links IS 'to link components to views with an n:m relation';
COMMENT ON COLUMN component_links.component_link_id IS 'the internal unique primary index';
COMMENT ON COLUMN component_links.user_id IS 'the owner / creator of the component_link';
COMMENT ON COLUMN component_links.position_type_id IS 'the position of the component e.g. right or below';
COMMENT ON COLUMN component_links.excluded IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN component_links.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN component_links.protect_id IS 'to protect against unwanted changes';

--
-- table structure to save user specific changes to link components to views with an n:m relation
--

CREATE TABLE IF NOT EXISTS user_component_links
(
    component_link_id      bigint        NOT NULL,
    user_id                bigint       NOT NULL,
    order_nbr              bigint   DEFAULT NULL,
    component_link_type_id smallint DEFAULT NULL,
    position_type_id       smallint DEFAULT NULL,
    excluded               smallint DEFAULT NULL,
    share_type_id          smallint DEFAULT NULL,
    protect_id             smallint DEFAULT NULL
);

COMMENT ON TABLE user_component_links IS 'to link components to views with an n:m relation';
COMMENT ON COLUMN user_component_links.component_link_id IS 'with the user_id the internal unique primary index';
COMMENT ON COLUMN user_component_links.user_id IS 'the changer of the component_link';
COMMENT ON COLUMN user_component_links.position_type_id IS 'the position of the component e.g. right or below';
COMMENT ON COLUMN user_component_links.excluded IS 'true if a user,but not all,have removed it';
COMMENT ON COLUMN user_component_links.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_component_links.protect_id IS 'to protect against unwanted changes';
