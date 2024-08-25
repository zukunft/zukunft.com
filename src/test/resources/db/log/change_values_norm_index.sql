-- --------------------------------------------------------

--
-- indexes for table change_values_norm
--

CREATE INDEX change_values_norm_change_idx ON change_values_norm (change_id);
CREATE INDEX change_values_norm_change_time_idx ON change_values_norm (change_time);
CREATE INDEX change_values_norm_user_idx ON change_values_norm (user_id);
