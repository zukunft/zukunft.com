-- --------------------------------------------------------

--
-- constraints for table component_links
--

ALTER TABLE component_links
    ADD CONSTRAINT component_links_view_fk FOREIGN KEY (view_id) REFERENCES views (view_id),
    ADD CONSTRAINT component_links_component_fk FOREIGN KEY (component_id) REFERENCES components (component_id),
    ADD CONSTRAINT component_links_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT component_links_component_link_type_fk FOREIGN KEY (component_link_type_id) REFERENCES component_link_types (component_link_type_id),
    ADD CONSTRAINT component_links_position_type_fk FOREIGN KEY (position_type_id) REFERENCES position_types (position_type_id),
    ADD CONSTRAINT component_links_view_style_fk FOREIGN KEY (view_style_id) REFERENCES view_styles (view_style_id);

--
-- constraints for table user_component_links
--

ALTER TABLE user_component_links
    ADD CONSTRAINT user_component_links_component_link_fk FOREIGN KEY (component_link_id) REFERENCES component_links (component_link_id),
    ADD CONSTRAINT user_component_links_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_component_links_component_link_type_fk FOREIGN KEY (component_link_type_id) REFERENCES component_link_types (component_link_type_id),
    ADD CONSTRAINT user_component_links_position_type_fk FOREIGN KEY (position_type_id) REFERENCES position_types (position_type_id),
    ADD CONSTRAINT user_component_links_view_style_fk FOREIGN KEY (view_style_id) REFERENCES view_styles (view_style_id);
