-- --------------------------------------------------------

--
-- indexes for table change_big_values
--

ALTER TABLE change_big_values
    ADD PRIMARY KEY (change_id),
    ADD KEY change_big_values_change_idx (change_id),
    ADD KEY change_big_values_change_time_idx (change_time),
    ADD KEY change_big_values_user_idx (user_id);
