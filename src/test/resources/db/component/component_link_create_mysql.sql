-- --------------------------------------------------------

--
-- table structure to link components to views with an n:m relation
--

CREATE TABLE IF NOT EXISTS component_links
(
    component_link_id      bigint   NOT NULL COMMENT 'the internal unique primary index',
    view_id                bigint   NOT NULL,
    component_id           bigint   NOT NULL,
    user_id                bigint            DEFAULT NULL COMMENT 'the owner / creator of the component_link',
    order_nbr              bigint   NOT NULL DEFAULT 1,
    component_link_type_id smallint NOT NULL DEFAULT 1,
    position_type_id       smallint NOT NULL DEFAULT 1 COMMENT 'the position of the component e.g. right or below',
    view_style_id          smallint          DEFAULT NULL COMMENT 'the display style for this component link',
    excluded               smallint          DEFAULT NULL COMMENT 'true if a user,but not all,have removed it',
    share_type_id          smallint          DEFAULT NULL COMMENT 'to restrict the access',
    protect_id             smallint          DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to link components to views with an n:m relation';

--
-- AUTO_INCREMENT for table component_links
--
ALTER TABLE component_links
    MODIFY component_link_id int(11) NOT NULL AUTO_INCREMENT;

--
-- table structure to save user specific changes to link components to views with an n:m relation
--

CREATE TABLE IF NOT EXISTS user_component_links
(
    component_link_id      bigint       NOT NULL COMMENT 'with the user_id the internal unique primary index',
    user_id                bigint       NOT NULL COMMENT 'the changer of the component_link',
    order_nbr              bigint   DEFAULT NULL,
    component_link_type_id smallint DEFAULT NULL,
    position_type_id       smallint DEFAULT NULL COMMENT 'the position of the component e.g. right or below',
    view_style_id          smallint DEFAULT NULL COMMENT 'the display style for this component link',
    excluded               smallint DEFAULT NULL COMMENT 'true if a user,but not all,have removed it',
    share_type_id          smallint DEFAULT NULL COMMENT 'to restrict the access',
    protect_id             smallint DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to link components to views with an n:m relation';
