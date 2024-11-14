-- --------------------------------------------------------

--
-- indexes for table component_links
--

ALTER TABLE component_links
    ADD PRIMARY KEY (component_link_id),
    ADD KEY component_links_view_idx (view_id),
    ADD KEY component_links_component_idx (component_id),
    ADD KEY component_links_user_idx (user_id),
    ADD KEY component_links_component_link_type_idx (component_link_type_id),
    ADD KEY component_links_position_type_idx (position_type_id),
    ADD KEY component_links_view_style_idx (view_style_id);

--
-- indexes for table user_component_links
--

ALTER TABLE user_component_links
    ADD PRIMARY KEY (component_link_id,user_id),
    ADD KEY user_component_links_component_link_idx (component_link_id),
    ADD KEY user_component_links_user_idx (user_id),
    ADD KEY user_component_links_component_link_type_idx (component_link_type_id),
    ADD KEY user_component_links_position_type_idx (position_type_id),
    ADD KEY user_component_links_view_style_idx (view_style_id);
