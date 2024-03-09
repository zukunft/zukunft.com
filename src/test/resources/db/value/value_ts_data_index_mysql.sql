-- --------------------------------------------------------

--
-- indexes for table value_ts_data
--

ALTER TABLE value_ts_data
    ADD KEY value_ts_data_value_time_series_idx (value_time_series_id);
