-- --------------------------------------------------------

--
-- indexes for table change_values_time_norm
--

ALTER TABLE change_values_time_norm
    ADD KEY change_values_time_norm_change_idx (change_id),
    ADD KEY change_values_time_norm_change_time_idx (change_time),
    ADD KEY change_values_time_norm_user_idx (user_id);
