-- --------------------------------------------------------

--
-- table structure for the single components of a view
--

CREATE TABLE IF NOT EXISTS components
(
    component_id           bigint           NOT NULL COMMENT 'the internal unique primary index',
    user_id                bigint       DEFAULT NULL COMMENT 'the owner / creator of the component',
    component_name         varchar(255)     NOT NULL COMMENT 'the unique name used to select a component by the user',
    description            text         DEFAULT NULL COMMENT 'to explain the view component to the user with a mouse over text; to be replaced by a language form entry',
    component_type_id      bigint       DEFAULT NULL COMMENT 'to select the predefined functionality',
    word_id_row            bigint       DEFAULT NULL COMMENT 'for a tree the related value the start node',
    formula_id             bigint       DEFAULT NULL COMMENT 'used for type 6',
    word_id_col            bigint       DEFAULT NULL COMMENT 'to define the type for the table columns',
    word_id_col2           bigint       DEFAULT NULL COMMENT 'e.g. "quarter" to show the quarters between the year columns or the second axis of a chart',
    linked_component_id    bigint       DEFAULT NULL COMMENT 'to link this component to another component',
    component_link_type_id bigint       DEFAULT NULL COMMENT 'to define how this entry links to the other entry',
    link_type_id           bigint       DEFAULT NULL COMMENT 'e.g. for type 4 to select possible terms',
    code_id                varchar(255) DEFAULT NULL COMMENT 'used for system components to select the component by the program code',
    ui_msg_code_id         varchar(255) DEFAULT NULL COMMENT 'used for system components the id to select the language specific user interface message e.g. "add word"',
    excluded               smallint     DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id          smallint     DEFAULT NULL COMMENT 'to restrict the access',
    protect_id             smallint     DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for the single components of a view';

--
-- table structure to save user specific changes for the single components of a view
--

CREATE TABLE IF NOT EXISTS user_components
(
    component_id           bigint       NOT     NULL COMMENT 'with the user_id the internal unique primary index',
    user_id                bigint       NOT     NULL COMMENT 'the changer of the component',
    component_name         varchar(255) DEFAULT NULL COMMENT 'the unique name used to select a component by the user',
    description            text         DEFAULT NULL COMMENT 'to explain the view component to the user with a mouse over text; to be replaced by a language form entry',
    component_type_id      bigint       DEFAULT NULL COMMENT 'to select the predefined functionality',
    word_id_row            bigint       DEFAULT NULL COMMENT 'for a tree the related value the start node',
    formula_id             bigint       DEFAULT NULL COMMENT 'used for type 6',
    word_id_col            bigint       DEFAULT NULL COMMENT 'to define the type for the table columns',
    word_id_col2           bigint       DEFAULT NULL COMMENT 'e.g. "quarter" to show the quarters between the year columns or the second axis of a chart',
    linked_component_id    bigint       DEFAULT NULL COMMENT 'to link this component to another component',
    component_link_type_id bigint       DEFAULT NULL COMMENT 'to define how this entry links to the other entry',
    link_type_id           bigint       DEFAULT NULL COMMENT 'e.g. for type 4 to select possible terms',
    excluded               smallint     DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id          smallint     DEFAULT NULL COMMENT 'to restrict the access',
    protect_id             smallint     DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for the single components of a view';