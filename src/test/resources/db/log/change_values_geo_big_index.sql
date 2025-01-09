-- --------------------------------------------------------

--
-- indexes for table change_values_geo_big
--

CREATE INDEX change_values_geo_big_change_idx ON change_values_geo_big (change_id);
CREATE INDEX change_values_geo_big_change_time_idx ON change_values_geo_big (change_time);
CREATE INDEX change_values_geo_big_user_idx ON change_values_geo_big (user_id);
