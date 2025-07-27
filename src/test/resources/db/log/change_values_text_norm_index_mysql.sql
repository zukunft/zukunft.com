-- --------------------------------------------------------

--
-- indexes for table change_values_text_norm
--

ALTER TABLE change_values_text_norm
    ADD PRIMARY KEY (change_id),
    ADD KEY change_values_text_norm_change_idx (change_id),
    ADD KEY change_values_text_norm_change_time_idx (change_time),
    ADD KEY change_values_text_norm_user_idx (user_id);
