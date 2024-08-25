-- --------------------------------------------------------

--
-- indexes for table changes_big
--

CREATE INDEX changes_big_change_idx ON changes_big (change_id);
CREATE INDEX changes_big_change_time_idx ON changes_big (change_time);
CREATE INDEX changes_big_user_idx ON changes_big (user_id);
