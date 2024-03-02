-- --------------------------------------------------------

--
-- table structure to log the link changes done by the users
--

CREATE TABLE IF NOT EXISTS change_links
(
    change_id BIGSERIAL PRIMARY KEY,
    change_time      timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id          bigint     NOT NULL,
    change_action_id smallint   NOT NULL,
    row_id           bigint DEFAULT NULL,
    change_table_id  bigint     NOT NULL,
    old_from_id      bigint DEFAULT NULL,
    old_link_id      bigint DEFAULT NULL,
    old_to_id        bigint DEFAULT NULL,
    old_text_from    text   DEFAULT NULL,
    old_text_link    text   DEFAULT NULL,
    old_text_to      text   DEFAULT NULL,
    new_from_id      bigint DEFAULT NULL,
    new_link_id      bigint DEFAULT NULL,
    new_to_id        bigint DEFAULT NULL,
    new_text_from    text   DEFAULT NULL,
    new_text_link    text   DEFAULT NULL,
    new_text_to      text   DEFAULT NULL
);

COMMENT ON TABLE change_links IS 'to log the link changes done by the users';
COMMENT ON COLUMN change_links.change_id IS 'the prime key to identify the change change_link';
COMMENT ON COLUMN change_links.change_time IS 'time when the user has confirmed the change';
COMMENT ON COLUMN change_links.user_id IS 'reference to the user who has done the change';
COMMENT ON COLUMN change_links.change_action_id IS 'the curl action';
COMMENT ON COLUMN change_links.row_id IS 'the prime id in the table with the change';
COMMENT ON COLUMN change_links.new_to_id IS 'either internal row id or the ref type id of the external system e.g. 2 for wikidata';
COMMENT ON COLUMN change_links.new_text_to IS 'the fixed text to display to the user or the external reference id e.g. Q1 (for universe) in case of wikidata';
