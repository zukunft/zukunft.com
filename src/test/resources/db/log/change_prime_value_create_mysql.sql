-- --------------------------------------------------------

--
-- table structure to log all changes done by any user on values with a prime group id
--

CREATE TABLE IF NOT EXISTS change_prime_values
(
    change_id        bigint     NOT NULL COMMENT 'the prime key to identify the change change_prime_value',
    change_time      timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'time when the user has confirmed the change',
    user_id          bigint     NOT NULL COMMENT 'reference to the user who has done the change',
    change_action_id smallint   NOT NULL COMMENT 'the curl action',
    group_id         bigint     NOT NULL,
    change_field_id  bigint     NOT NULL,
    old_value        double DEFAULT NULL,
    new_value        double DEFAULT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to log all changes done by any user on values with a prime group id';
