-- --------------------------------------------------------

--
-- table structure to log all geo value changes done by any user on values with a big group id
--

CREATE TABLE IF NOT EXISTS change_values_geo_big
(
    change_id        BIGSERIAL PRIMARY KEY,
    change_time      timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id          bigint    NOT NULL,
    change_action_id smallint  NOT NULL,
    group_id         text      NOT NULL,
    change_field_id  smallint  NOT NULL,
    old_value        point DEFAULT NULL,
    new_value        point DEFAULT NULL
);

COMMENT ON TABLE change_values_geo_big IS 'to log all geo value changes done by any user on values with a big group id';
COMMENT ON COLUMN change_values_geo_big.change_id IS 'the prime key to identify the change change_values_geo_big';
COMMENT ON COLUMN change_values_geo_big.change_time IS 'time when the user has confirmed the change';
COMMENT ON COLUMN change_values_geo_big.user_id IS 'reference to the user who has done the change';
COMMENT ON COLUMN change_values_geo_big.change_action_id IS 'the curl action';
