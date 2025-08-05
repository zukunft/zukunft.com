-- --------------------------------------------------------

--
-- indexes for table views
--

ALTER TABLE views
    ADD KEY views_user_idx (user_id),
    ADD KEY views_view_name_idx (view_name),
    ADD KEY views_view_type_idx (view_type_id),
    ADD KEY views_view_style_idx (view_style_id);

--
-- indexes for table user_views
--

ALTER TABLE user_views
    ADD KEY user_views_view_idx (view_id),
    ADD KEY user_views_user_idx (user_id),
    ADD KEY user_views_language_idx (language_id),
    ADD KEY user_views_view_name_idx (view_name),
    ADD KEY user_views_view_type_idx (view_type_id),
    ADD KEY user_views_view_style_idx (view_style_id);

