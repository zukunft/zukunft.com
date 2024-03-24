-- --------------------------------------------------------

--
-- indexes for table change_norm_values
--

CREATE INDEX change_norm_values_change_idx ON change_norm_values (change_id);
CREATE INDEX change_norm_values_change_time_idx ON change_norm_values (change_time);
CREATE INDEX change_norm_values_user_idx ON change_norm_values (user_id);
