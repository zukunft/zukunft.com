-- --------------------------------------------------------

--
-- table structure for a single time series value data entry and efficient saving of daily or intra-day values
--

CREATE TABLE IF NOT EXISTS value_ts_data
(
    value_time_series_id bigint     NOT NULL COMMENT 'link to the value time series',
    val_time             timestamp  NOT NULL COMMENT 'short name of the configuration entry to be shown to the admin',
    number               double DEFAULT NULL COMMENT 'the configuration value as a string'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for a single time series value data entry and efficient saving of daily or intra-day values';
