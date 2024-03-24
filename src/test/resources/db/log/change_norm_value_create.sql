-- --------------------------------------------------------

--
-- table structure to log all changes done by any user on values with a standard group id
--

CREATE TABLE IF NOT EXISTS change_norm_values
(
    change_id        BIGSERIAL PRIMARY KEY,
    change_time      timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id          bigint    NOT NULL,
    change_action_id smallint  NOT NULL,
    group_id         char(112) NOT NULL,
    change_field_id  bigint    NOT NULL,
    old_value        double precision DEFAULT NULL,
    new_value        double precision DEFAULT NULL
);

COMMENT ON TABLE change_norm_values IS 'to log all changes done by any user on values with a standard group id';
COMMENT ON COLUMN change_norm_values.change_id IS 'the prime key to identify the change change_norm_value';
COMMENT ON COLUMN change_norm_values.change_time IS 'time when the user has confirmed the change';
COMMENT ON COLUMN change_norm_values.user_id IS 'reference to the user who has done the change';
COMMENT ON COLUMN change_norm_values.change_action_id IS 'the curl action';
