-- --------------------------------------------------------

--
-- table structure to log all changes done by any user on all tables except value and link changes
--

CREATE TABLE IF NOT EXISTS changes
(
    change_id        bigint     NOT NULL COMMENT 'the prime key to identify the change change',
    change_time      timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'time when the user has confirmed the change',
    user_id          bigint     NOT NULL COMMENT 'reference to the user who has done the change',
    change_action_id smallint   NOT NULL COMMENT 'the curl action',
    row_id           bigint DEFAULT NULL COMMENT 'the prime id in the table with the change',
    change_field_id  bigint     NOT NULL,
    old_value        text   DEFAULT NULL,
    new_value        text   DEFAULT NULL,
    old_id           bigint DEFAULT NULL COMMENT 'old value id',
    new_id           bigint DEFAULT NULL COMMENT 'new value id'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to log all changes done by any user on all tables except value and link changes';
