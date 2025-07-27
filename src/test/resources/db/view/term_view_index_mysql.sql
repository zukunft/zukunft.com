-- --------------------------------------------------------

--
-- indexes for table term_views
--

ALTER TABLE term_views
    ADD PRIMARY KEY (term_view_id),
    ADD KEY term_views_term_idx (term_id),
    ADD KEY term_views_view_idx (view_id),
    ADD KEY term_views_view_link_type_idx (view_link_type_id),
    ADD KEY term_views_user_idx (user_id);

--
-- indexes for table user_term_views
--

ALTER TABLE user_term_views
    ADD PRIMARY KEY (term_view_id,user_id),
    ADD KEY user_term_views_term_view_idx (term_view_id),
    ADD KEY user_term_views_user_idx (user_id),
    ADD KEY user_term_views_view_link_type_idx (view_link_type_id);
