-- --------------------------------------------------------

--
-- table structure to log all time value changes done by any user on values with a standard group id
--

CREATE TABLE IF NOT EXISTS change_values_time_norm
(
    change_id        BIGSERIAL PRIMARY KEY,
    change_time      timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id          bigint    NOT NULL,
    change_action_id smallint  NOT NULL,
    group_id         char(112) NOT NULL,
    change_field_id  smallint  NOT NULL,
    old_value        timestamp DEFAULT NULL,
    new_value        timestamp DEFAULT NULL
);

COMMENT ON TABLE change_values_time_norm IS 'to log all time value changes done by any user on values with a standard group id';
COMMENT ON COLUMN change_values_time_norm.change_id IS 'the prime key to identify the change change_values_time_norm';
COMMENT ON COLUMN change_values_time_norm.change_time IS 'time when the user has confirmed the change';
COMMENT ON COLUMN change_values_time_norm.user_id IS 'reference to the user who has done the change';
COMMENT ON COLUMN change_values_time_norm.change_action_id IS 'the curl action';