-- --------------------------------------------------------

--
-- indexes for table changes_big
--

ALTER TABLE changes_big
    ADD KEY changes_big_change_idx (change_id),
    ADD KEY changes_big_change_time_idx (change_time),
    ADD KEY changes_big_user_idx (user_id);
