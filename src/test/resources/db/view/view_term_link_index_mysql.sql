-- --------------------------------------------------------

--
-- indexes for table view_term_links
--

ALTER TABLE view_term_links
    ADD PRIMARY KEY (view_term_link_id),
    ADD KEY view_term_links_term_idx (term_id),
    ADD KEY view_term_links_view_idx (view_id),
    ADD KEY view_term_links_type_idx (type_id),
    ADD KEY view_term_links_user_idx (user_id),
    ADD KEY view_term_links_view_link_type_idx (view_link_type_id);

--
-- indexes for table user_view_term_links
--

ALTER TABLE user_view_term_links
    ADD PRIMARY KEY (view_term_link_id,user_id),
    ADD KEY user_view_term_links_view_term_link_idx (view_term_link_id),
    ADD KEY user_view_term_links_user_idx (user_id),
    ADD KEY user_view_term_links_view_link_type_idx (view_link_type_id);
