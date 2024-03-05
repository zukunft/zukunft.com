-- --------------------------------------------------------

--
-- table structure to log the link changes done by the users
--

CREATE TABLE IF NOT EXISTS change_links
(
    change_link_id   bigint     NOT NULL COMMENT 'the prime key to identify the change change_link',
    change_time      timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'time when the user has confirmed the change',
    user_id          bigint     NOT NULL COMMENT 'reference to the user who has done the change',
    change_action_id smallint   NOT NULL COMMENT 'the curl action',
    row_id           bigint DEFAULT NULL COMMENT 'the prime id in the table with the change',
    change_table_id  bigint     NOT NULL,
    old_from_id      bigint DEFAULT NULL,
    old_link_id      bigint DEFAULT NULL,
    old_to_id        bigint DEFAULT NULL,
    old_text_from    text   DEFAULT NULL,
    old_text_link    text   DEFAULT NULL,
    old_text_to      text   DEFAULT NULL,
    new_from_id      bigint DEFAULT NULL,
    new_link_id      bigint DEFAULT NULL,
    new_to_id        bigint DEFAULT NULL COMMENT 'either internal row id or the ref type id of the external system e.g. 2 for wikidata',
    new_text_from    text   DEFAULT NULL,
    new_text_link    text   DEFAULT NULL,
    new_text_to      text   DEFAULT NULL COMMENT 'the fixed text to display to the user or the external reference id e.g. Q1 (for universe) in case of wikidata'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to log the link changes done by the users';
