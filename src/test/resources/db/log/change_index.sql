-- --------------------------------------------------------

--
-- indexes for table changes
--

CREATE INDEX changes_change_idx ON changes (change_id);
CREATE INDEX changes_change_time_idx ON changes (change_time);
CREATE INDEX changes_user_idx ON changes (user_id);
