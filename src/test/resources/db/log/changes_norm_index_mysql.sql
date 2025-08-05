-- --------------------------------------------------------

--
-- indexes for table changes_norm
--

ALTER TABLE changes_norm
    ADD KEY changes_norm_change_idx (change_id),
    ADD KEY changes_norm_change_time_idx (change_time),
    ADD KEY changes_norm_user_idx (user_id);
