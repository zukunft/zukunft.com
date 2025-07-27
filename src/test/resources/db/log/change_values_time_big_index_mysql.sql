-- --------------------------------------------------------

--
-- indexes for table change_values_time_big
--

ALTER TABLE change_values_time_big
    ADD PRIMARY KEY (change_id),
    ADD KEY change_values_time_big_change_idx (change_id),
    ADD KEY change_values_time_big_change_time_idx (change_time),
    ADD KEY change_values_time_big_user_idx (user_id);
