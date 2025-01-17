-- --------------------------------------------------------

--
-- constraints for table components
--

ALTER TABLE components
    ADD CONSTRAINT components_component_name_uk UNIQUE (component_name),
    ADD CONSTRAINT components_code_id_uk UNIQUE (code_id),
    ADD CONSTRAINT components_ui_msg_code_id_uk UNIQUE (ui_msg_code_id),
    ADD CONSTRAINT components_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT components_component_type_fk FOREIGN KEY (component_type_id) REFERENCES component_types (component_type_id),
    ADD CONSTRAINT components_view_style_fk FOREIGN KEY (view_style_id) REFERENCES view_styles (view_style_id),
    ADD CONSTRAINT components_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

--
-- constraints for table user_components
--

ALTER TABLE user_components
    ADD CONSTRAINT user_components_component_fk FOREIGN KEY (component_id) REFERENCES components (component_id),
    ADD CONSTRAINT user_components_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_components_component_type_fk FOREIGN KEY (component_type_id) REFERENCES component_types (component_type_id),
    ADD CONSTRAINT user_components_view_style_fk FOREIGN KEY (view_style_id) REFERENCES view_styles (view_style_id),
    ADD CONSTRAINT user_components_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);