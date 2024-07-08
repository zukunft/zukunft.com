-- --------------------------------------------------------

--
-- table structure to log all changes done by any user on the group name for values with up to 16 phrases
--

CREATE TABLE IF NOT EXISTS changes_norm
(
    change_id        bigint        NOT NULL COMMENT 'the prime key to identify the change changes_norm',
    change_time      timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'time when the user has confirmed the change',
    user_id          bigint        NOT NULL COMMENT 'reference to the user who has done the change',
    change_action_id smallint      NOT NULL COMMENT 'the curl action',
    row_id           char(112) DEFAULT NULL COMMENT 'the prime id in the table with the change',
    change_field_id  smallint      NOT NULL,
    old_value        text      DEFAULT NULL,
    new_value        text      DEFAULT NULL,
    old_id           char(112) DEFAULT NULL COMMENT 'old value id',
    new_id           char(112) DEFAULT NULL COMMENT 'new value id'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to log all changes done by any user on the group name for values with up to 16 phrases';

--
-- AUTO_INCREMENT for table changes_norm
--
ALTER TABLE changes_norm
    MODIFY change_id int(11) NOT NULL AUTO_INCREMENT;
