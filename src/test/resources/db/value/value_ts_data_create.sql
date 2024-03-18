-- --------------------------------------------------------

--
-- table structure for a single time series value data entry and efficient saving of daily or intra-day values
--

CREATE TABLE IF NOT EXISTS value_ts_data
(
    value_time_series_id bigint NOT NULL,
    val_time             timestamp NOT NULL,
    number               double precision DEFAULT NULL
);

COMMENT ON TABLE value_ts_data IS 'for a single time series value data entry and efficient saving of daily or intra-day values';
COMMENT ON COLUMN value_ts_data.value_time_series_id IS 'link to the value time series';
COMMENT ON COLUMN value_ts_data.val_time IS 'short name of the configuration entry to be shown to the admin';
COMMENT ON COLUMN value_ts_data.number IS 'the configuration value as a string';
