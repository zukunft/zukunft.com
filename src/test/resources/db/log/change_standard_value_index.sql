-- --------------------------------------------------------

--
-- indexes for table change_standard_values
--

CREATE INDEX change_standard_values_change_idx ON change_standard_values (change_id);
CREATE INDEX change_standard_values_change_time_idx ON change_standard_values (change_time);
CREATE INDEX change_standard_values_user_idx ON change_standard_values (user_id);
