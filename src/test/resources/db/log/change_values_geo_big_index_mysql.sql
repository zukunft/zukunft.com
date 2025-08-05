-- --------------------------------------------------------

--
-- indexes for table change_values_geo_big
--

ALTER TABLE change_values_geo_big
    ADD KEY change_values_geo_big_change_idx (change_id),
    ADD KEY change_values_geo_big_change_time_idx (change_time),
    ADD KEY change_values_geo_big_user_idx (user_id);
