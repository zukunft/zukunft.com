-- --------------------------------------------------------

--
-- indexes for table user_log
--

CREATE INDEX user_log_user_idx ON user_log (user_id);
CREATE INDEX user_log_session_start_idx ON user_log (session_start);
CREATE INDEX user_log_session_end_idx ON user_log (session_end);
