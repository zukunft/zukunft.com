-- --------------------------------------------------------

--
-- table structure to log all changes done by any user on values with a big group id
--

CREATE TABLE IF NOT EXISTS change_big_values
(
    change_id        bigint     NOT NULL COMMENT 'the prime key to identify the change change_big_value',
    change_time      timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'time when the user has confirmed the change',
    user_id          bigint     NOT NULL COMMENT 'reference to the user who has done the change',
    change_action_id smallint   NOT NULL COMMENT 'the curl action',
    group_id         text       NOT NULL,
    change_field_id  smallint   NOT NULL,
    old_value        double DEFAULT NULL,
    new_value        double DEFAULT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to log all changes done by any user on values with a big group id';

--
-- AUTO_INCREMENT for table change_big_values
--
ALTER TABLE change_big_values
    MODIFY change_id int(11) NOT NULL AUTO_INCREMENT;
