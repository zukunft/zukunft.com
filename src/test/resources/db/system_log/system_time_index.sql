-- --------------------------------------------------------

--
-- indexes for table system_times
--

CREATE INDEX system_times_start_time_idx ON system_times (start_time);
CREATE INDEX system_times_end_time_idx ON system_times (end_time);
CREATE INDEX system_times_system_time_type_idx ON system_times (system_time_type_id);
