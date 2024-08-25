-- --------------------------------------------------------

--
-- constraints for table view_term_links
--

ALTER TABLE view_term_links
    ADD CONSTRAINT view_term_links_view_fk FOREIGN KEY (view_id) REFERENCES views (view_id),
    ADD CONSTRAINT view_term_links_view_link_type_fk FOREIGN KEY (view_link_type_id) REFERENCES view_link_types (view_link_type_id),
    ADD CONSTRAINT view_term_links_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_view_term_links
--

ALTER TABLE user_view_term_links
    ADD CONSTRAINT user_view_term_links_view_term_link_fk FOREIGN KEY (view_term_link_id) REFERENCES view_term_links (view_term_link_id),
    ADD CONSTRAINT user_view_term_links_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_view_term_links_view_link_type_fk FOREIGN KEY (view_link_type_id) REFERENCES view_link_types (view_link_type_id);
