-- --------------------------------------------------------

--
-- table structure for the single components of a view
--

CREATE TABLE IF NOT EXISTS components
(
    component_id                BIGSERIAL PRIMARY KEY,
    user_id                     bigint       DEFAULT NULL,
    component_name              varchar(255)     NOT NULL,
    description                 text         DEFAULT NULL,
    component_type_id           smallint     DEFAULT NULL,
    view_style_id               smallint     DEFAULT NULL,
    word_id_row                 bigint       DEFAULT NULL,
    formula_id                  bigint       DEFAULT NULL,
    word_id_col                 bigint       DEFAULT NULL,
    word_id_col2                bigint       DEFAULT NULL,
    linked_component_id         bigint       DEFAULT NULL,
    component_link_type_id      smallint     DEFAULT NULL,
    link_type_id                smallint     DEFAULT NULL,
    code_id                     varchar(255) DEFAULT NULL,
    ui_msg_code_id              varchar(255) DEFAULT NULL,
    excluded                    smallint     DEFAULT NULL,
    share_type_id               smallint     DEFAULT NULL,
    protect_id                  smallint     DEFAULT NULL
);

COMMENT ON TABLE components IS 'for the single components of a view';
COMMENT ON COLUMN components.component_id IS 'the internal unique primary index';
COMMENT ON COLUMN components.user_id IS 'the owner / creator of the component';
COMMENT ON COLUMN components.component_name IS 'the unique name used to select a component by the user';
COMMENT ON COLUMN components.description IS 'to explain the view component to the user with a mouse over text; to be replaced by a language form entry';
COMMENT ON COLUMN components.component_type_id IS 'to select the predefined functionality';
COMMENT ON COLUMN components.view_style_id IS 'the default display style for this component';
COMMENT ON COLUMN components.word_id_row IS 'for a tree the related value the start node';
COMMENT ON COLUMN components.formula_id IS 'used for type 6';
COMMENT ON COLUMN components.word_id_col IS 'to define the type for the table columns';
COMMENT ON COLUMN components.word_id_col2 IS 'e.g. "quarter" to show the quarters between the year columns or the second axis of a chart';
COMMENT ON COLUMN components.linked_component_id IS 'to link this component to another component';
COMMENT ON COLUMN components.component_link_type_id IS 'to define how this entry links to the other entry';
COMMENT ON COLUMN components.link_type_id IS 'e.g. for type 4 to select possible terms';
COMMENT ON COLUMN components.code_id IS 'used for system components to select the component by the program code';
COMMENT ON COLUMN components.ui_msg_code_id IS 'used for system components the id to select the language specific user interface message e.g. "add word"';
COMMENT ON COLUMN components.excluded IS 'true if a user,but not all,have removed it';
COMMENT ON COLUMN components.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN components.protect_id IS 'to protect against unwanted changes';

--
-- table structure to save user specific changes for the single components of a view
--

CREATE TABLE IF NOT EXISTS user_components
(
    component_id           bigint           NOT NULL,
    user_id                bigint           NOT NULL,
    component_name         varchar(255) DEFAULT NULL,
    description            text         DEFAULT NULL,
    component_type_id      smallint     DEFAULT NULL,
    view_style_id          smallint     DEFAULT NULL,
    word_id_row            bigint       DEFAULT NULL,
    formula_id             bigint       DEFAULT NULL,
    word_id_col            bigint       DEFAULT NULL,
    word_id_col2           bigint       DEFAULT NULL,
    linked_component_id    bigint       DEFAULT NULL,
    component_link_type_id smallint     DEFAULT NULL,
    link_type_id           smallint     DEFAULT NULL,
    excluded               smallint     DEFAULT NULL,
    share_type_id          smallint     DEFAULT NULL,
    protect_id             smallint     DEFAULT NULL
);

COMMENT ON TABLE user_components IS 'for the single components of a view';
COMMENT ON COLUMN user_components.component_id IS 'with the user_id the internal unique primary index';
COMMENT ON COLUMN user_components.user_id IS 'the changer of the component';
COMMENT ON COLUMN user_components.component_name IS 'the unique name used to select a component by the user';
COMMENT ON COLUMN user_components.description IS 'to explain the view component to the user with a mouse over text; to be replaced by a language form entry';
COMMENT ON COLUMN user_components.component_type_id IS 'to select the predefined functionality';
COMMENT ON COLUMN user_components.view_style_id IS 'the default display style for this component';
COMMENT ON COLUMN user_components.word_id_row IS 'for a tree the related value the start node';
COMMENT ON COLUMN user_components.formula_id IS 'used for type 6';
COMMENT ON COLUMN user_components.word_id_col IS 'to define the type for the table columns';
COMMENT ON COLUMN user_components.word_id_col2 IS 'e.g. "quarter" to show the quarters between the year columns or the second axis of a chart';
COMMENT ON COLUMN user_components.linked_component_id IS 'to link this component to another component';
COMMENT ON COLUMN user_components.component_link_type_id IS 'to define how this entry links to the other entry';
COMMENT ON COLUMN user_components.link_type_id IS 'e.g. for type 4 to select possible terms';
COMMENT ON COLUMN user_components.excluded IS 'true if a user,but not all,have removed it';
COMMENT ON COLUMN user_components.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_components.protect_id IS 'to protect against unwanted changes';