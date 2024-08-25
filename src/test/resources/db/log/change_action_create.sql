-- --------------------------------------------------------

--
-- table structure for add,change,delete,undo and redo actions
--

CREATE TABLE IF NOT EXISTS change_actions
(
    change_action_id   SERIAL PRIMARY KEY,
    change_action_name varchar(255) NOT NULL,
    code_id            varchar(255) NOT NULL,
    description        text     DEFAULT NULL
);

COMMENT ON TABLE change_actions IS 'for add,change,delete,undo and redo actions';
COMMENT ON COLUMN change_actions.change_action_id IS 'the internal unique primary index';
