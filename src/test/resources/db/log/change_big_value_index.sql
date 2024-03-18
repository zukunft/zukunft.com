-- --------------------------------------------------------

--
-- indexes for table change_big_values
--

CREATE INDEX change_big_values_change_idx ON change_big_values (change_id);
CREATE INDEX change_big_values_change_time_idx ON change_big_values (change_time);
CREATE INDEX change_big_values_user_idx ON change_big_values (user_id);
