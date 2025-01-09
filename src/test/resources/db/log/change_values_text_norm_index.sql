-- --------------------------------------------------------

--
-- indexes for table change_values_text_norm
--

CREATE INDEX change_values_text_norm_change_idx ON change_values_text_norm (change_id);
CREATE INDEX change_values_text_norm_change_time_idx ON change_values_text_norm (change_time);
CREATE INDEX change_values_text_norm_user_idx ON change_values_text_norm (user_id);
