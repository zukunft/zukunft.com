-- --------------------------------------------------------

--
-- table structure for add,change,delete,undo and redo actions
--

CREATE TABLE IF NOT EXISTS change_actions
(
    change_action_id   smallint     NOT NULL COMMENT 'the internal unique primary index',
    change_action_name varchar(255) NOT NULL,
    code_id            varchar(255) NOT NULL,
    description        text     DEFAULT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for add,change,delete,undo and redo actions';

--
-- AUTO_INCREMENT for table change_actions
--
ALTER TABLE change_actions
    MODIFY change_action_id int(11) NOT NULL AUTO_INCREMENT;
