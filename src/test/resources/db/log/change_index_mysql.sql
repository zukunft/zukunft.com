-- --------------------------------------------------------

--
-- indexes for table changes
--

ALTER TABLE changes
    ADD KEY changes_change_idx (change_id),
    ADD KEY changes_change_time_idx (change_time),
    ADD KEY changes_user_idx (user_id);
