-- --------------------------------------------------------
--
-- constraints for table views
--

ALTER TABLE views
    ADD CONSTRAINT views_view_name_uk UNIQUE (view_name),
    ADD CONSTRAINT views_code_id_uk UNIQUE (code_id),
    ADD CONSTRAINT views_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT views_view_type_fk FOREIGN KEY (view_type_id) REFERENCES view_types (view_type_id),
    ADD CONSTRAINT views_view_style_fk FOREIGN KEY (view_style_id) REFERENCES view_styles (view_style_id);

--
-- constraints for table user_views
--

ALTER TABLE user_views
    ADD CONSTRAINT user_views_view_fk FOREIGN KEY (view_id) REFERENCES views (view_id),
    ADD CONSTRAINT user_views_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_views_language_fk FOREIGN KEY (language_id) REFERENCES languages (language_id),
    ADD CONSTRAINT user_views_view_type_fk FOREIGN KEY (view_type_id) REFERENCES view_types (view_type_id),
    ADD CONSTRAINT user_views_view_style_fk FOREIGN KEY (view_style_id) REFERENCES view_styles (view_style_id);