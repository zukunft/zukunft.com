-- --------------------------------------------------------

--
-- indexes for table change_values_big
--

CREATE INDEX change_values_big_change_idx ON change_values_big (change_id);
CREATE INDEX change_values_big_change_time_idx ON change_values_big (change_time);
CREATE INDEX change_values_big_user_idx ON change_values_big (user_id);
