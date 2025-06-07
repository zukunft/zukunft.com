-- --------------------------------------------------------

--
-- constraints for table term_views
--

ALTER TABLE term_views
    ADD CONSTRAINT term_views_view_fk FOREIGN KEY (view_id) REFERENCES views (view_id),
    ADD CONSTRAINT term_views_view_link_type_fk FOREIGN KEY (view_link_type_id) REFERENCES view_link_types (view_link_type_id),
    ADD CONSTRAINT term_views_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id);

--
-- constraints for table user_term_views
--

ALTER TABLE user_term_views
    ADD CONSTRAINT user_term_views_term_view_fk FOREIGN KEY (term_view_id) REFERENCES term_views (term_view_id),
    ADD CONSTRAINT user_term_views_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_term_views_view_link_type_fk FOREIGN KEY (view_link_type_id) REFERENCES view_link_types (view_link_type_id);
