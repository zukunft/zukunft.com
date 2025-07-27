-- --------------------------------------------------------

--
-- table structure to log all geo value changes done by any user on values with a standard group id
--

CREATE TABLE IF NOT EXISTS change_values_geo_norm
(
    change_id        bigint     NOT NULL COMMENT 'the prime key to identify the change change_values_geo_norm',
    change_time      timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'time when the user has confirmed the change',
    user_id          bigint     NOT NULL COMMENT 'reference to the user who has done the change',
    change_action_id smallint   NOT NULL COMMENT 'the curl action',
    group_id         char(112)  NOT NULL,
    change_field_id  smallint   NOT NULL,
    old_value        point  DEFAULT NULL,
    new_value        point  DEFAULT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to log all geo value changes done by any user on values with a standard group id';

--
-- AUTO_INCREMENT for table change_values_geo_norm
--
ALTER TABLE change_values_geo_norm
    MODIFY change_id int(11) NOT NULL AUTO_INCREMENT;
