-- --------------------------------------------------------

--
-- indexes for table change_links
--

ALTER TABLE change_links
    ADD PRIMARY KEY (change_id),
    ADD KEY change_links_change_idx (change_id),
    ADD KEY change_links_change_time_idx (change_time),
    ADD KEY change_links_user_idx (user_id);
