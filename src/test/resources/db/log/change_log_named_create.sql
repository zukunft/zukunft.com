
-- --------------------------------------------------------

--
-- table structure to log all changes done by any user
--

CREATE TABLE IF NOT EXISTS changes
(
    change_id        BIGSERIAL PRIMARY KEY,
    change_time      timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id          bigint    NOT NULL,
    change_action_id smallint  NOT NULL,
    change_field_id  smallint  NOT NULL,
    row_id           bigint             DEFAULT NULL,
    old_value        varchar(300)       DEFAULT NULL,
    new_value        varchar(300)       DEFAULT NULL,
    old_id           bigint             DEFAULT NULL,
    new_id           bigint             DEFAULT NULL
);

COMMENT ON TABLE changes IS 'to log all changes';
COMMENT ON COLUMN changes.change_time IS 'time when the value has been changed';
COMMENT ON COLUMN changes.old_id IS 'old value id';
COMMENT ON COLUMN changes.new_id IS 'new value id';

