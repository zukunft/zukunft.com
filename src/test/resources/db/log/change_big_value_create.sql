-- --------------------------------------------------------

--
-- table structure to log all changes done by any user on values with a big group id
--

CREATE TABLE IF NOT EXISTS change_big_values
(
    change_id        BIGSERIAL PRIMARY KEY,
    change_time      timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id          bigint    NOT NULL,
    change_action_id smallint  NOT NULL,
    group_id         text      NOT NULL,
    change_field_id  smallint  NOT NULL,
    old_value        double precision DEFAULT NULL,
    new_value        double precision DEFAULT NULL
);

COMMENT ON TABLE change_big_values IS 'to log all changes done by any user on values with a big group id';
COMMENT ON COLUMN change_big_values.change_id IS 'the prime key to identify the change change_big_value';
COMMENT ON COLUMN change_big_values.change_time IS 'time when the user has confirmed the change';
COMMENT ON COLUMN change_big_values.user_id IS 'reference to the user who has done the change';
COMMENT ON COLUMN change_big_values.change_action_id IS 'the curl action';
