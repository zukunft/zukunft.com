-- --------------------------------------------------------

--
-- indexes for table changes_norm
--

CREATE INDEX changes_norm_change_idx ON changes_norm (change_id);
CREATE INDEX changes_norm_change_time_idx ON changes_norm (change_time);
CREATE INDEX changes_norm_user_idx ON changes_norm (user_id);
