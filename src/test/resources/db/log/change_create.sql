-- --------------------------------------------------------

--
-- table structure to log all changes done by any user on all tables except value and link changes
--

CREATE TABLE IF NOT EXISTS changes
(
    change_id        BIGSERIAL PRIMARY KEY,
    change_time      timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id          bigint     NOT NULL,
    change_action_id smallint   NOT NULL,
    row_id           bigint DEFAULT NULL,
    change_field_id  bigint     NOT NULL,
    old_value        text   DEFAULT NULL,
    new_value        text   DEFAULT NULL,
    old_id           bigint DEFAULT NULL,
    new_id           bigint DEFAULT NULL
);

COMMENT ON TABLE changes IS 'to log all changes done by any user on all tables except value and link changes';
COMMENT ON COLUMN changes.change_id IS 'the prime key to identify the change change';
COMMENT ON COLUMN changes.change_time IS 'time when the user has confirmed the change';
COMMENT ON COLUMN changes.user_id IS 'reference to the user who has done the change';
COMMENT ON COLUMN changes.change_action_id IS 'the curl action';
COMMENT ON COLUMN changes.row_id IS 'the prime id in the table with the change';
COMMENT ON COLUMN changes.old_id IS 'old value id';
COMMENT ON COLUMN changes.new_id IS 'new value id';
