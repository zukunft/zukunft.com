-- --------------------------------------------------------

--
-- indexes for table component_links
--

CREATE INDEX component_links_view_idx ON component_links (view_id);
CREATE INDEX component_links_component_idx ON component_links (component_id);
CREATE INDEX component_links_user_idx ON component_links (user_id);
CREATE INDEX component_links_component_link_type_idx ON component_links (component_link_type_id);
CREATE INDEX component_links_position_type_idx ON component_links (position_type_id);
CREATE INDEX component_links_view_style_idx ON component_links (view_style_id);

--
-- indexes for table user_component_links
--

ALTER TABLE user_component_links
    ADD CONSTRAINT user_component_links_pkey PRIMARY KEY (component_link_id,user_id);
CREATE INDEX user_component_links_component_link_idx ON user_component_links (component_link_id);
CREATE INDEX user_component_links_user_idx ON user_component_links (user_id);
CREATE INDEX user_component_links_component_link_type_idx ON user_component_links (component_link_type_id);
CREATE INDEX user_component_links_position_type_idx ON user_component_links (position_type_id);
CREATE INDEX user_component_links_view_style_idx ON user_component_links (view_style_id);
