-- --------------------------------------------------------

--
-- indexes for table user_log
--

ALTER TABLE user_log
    ADD KEY user_log_user_idx (user_id),
    ADD KEY user_log_session_start_idx (session_start),
    ADD KEY user_log_session_end_idx (session_end);
