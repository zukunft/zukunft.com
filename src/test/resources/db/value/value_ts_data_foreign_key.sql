--
-- constraints for table value_ts_data
--

ALTER TABLE value_ts_data
    ADD CONSTRAINT value_ts_data_value_time_series_fk FOREIGN KEY (value_time_series_id) REFERENCES value_time_series (value_time_series_id);
