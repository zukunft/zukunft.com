-- --------------------------------------------------------

--
-- table structure to log all changes done by any user on the group name for values with more than 16 phrases
--

CREATE TABLE IF NOT EXISTS changes_big
(
    change_id        BIGSERIAL PRIMARY KEY,
    change_time      timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id          bigint     NOT NULL,
    change_action_id smallint   NOT NULL,
    row_id           bigint DEFAULT NULL,
    change_field_id  smallint   NOT NULL,
    old_value        text   DEFAULT NULL,
    new_value        text   DEFAULT NULL,
    old_id           text   DEFAULT NULL,
    new_id           text   DEFAULT NULL
);

COMMENT ON TABLE changes_big IS 'to log all changes done by any user on the group name for values with more than 16 phrases';
COMMENT ON COLUMN changes_big.change_id IS 'the prime key to identify the change changes_big';
COMMENT ON COLUMN changes_big.change_time IS 'time when the user has confirmed the change';
COMMENT ON COLUMN changes_big.user_id IS 'reference to the user who has done the change';
COMMENT ON COLUMN changes_big.change_action_id IS 'the curl action';
COMMENT ON COLUMN changes_big.row_id IS 'the prime id in the table with the change';
COMMENT ON COLUMN changes_big.old_id IS 'old value id';
COMMENT ON COLUMN changes_big.new_id IS 'new value id';
