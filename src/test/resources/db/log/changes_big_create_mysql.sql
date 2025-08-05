-- --------------------------------------------------------

--
-- table structure to log all changes done by any user on the group name for values with more than 16 phrases
--

CREATE TABLE IF NOT EXISTS changes_big
(
    change_id        bigint     NOT NULL COMMENT 'the prime key to identify the change changes_big',
    change_time      timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'time when the user has confirmed the change',
    user_id          bigint     NOT NULL COMMENT 'reference to the user who has done the change',
    change_action_id smallint   NOT NULL COMMENT 'the curl action',
    row_id           text   DEFAULT NULL COMMENT 'the prime id in the table with the change',
    change_field_id  smallint   NOT NULL,
    old_value        text   DEFAULT NULL,
    new_value        text   DEFAULT NULL,
    old_id           text   DEFAULT NULL COMMENT 'old value id',
    new_id           text   DEFAULT NULL COMMENT 'new value id',
    PRIMARY KEY (change_id)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to log all changes done by any user on the group name for values with more than 16 phrases';

--
-- AUTO_INCREMENT for table changes_big
--
ALTER TABLE changes_big
    MODIFY change_id bigint NOT NULL AUTO_INCREMENT;
