-- --------------------------------------------------------

--
-- indexes for table components
--

CREATE INDEX components_user_idx ON components (user_id);
CREATE INDEX components_component_name_idx ON components (component_name);
CREATE INDEX components_component_type_idx ON components (component_type_id);
CREATE INDEX components_view_style_idx ON components (view_style_id);
CREATE INDEX components_word_id_row_idx ON components (word_id_row);
CREATE INDEX components_formula_idx ON components (formula_id);
CREATE INDEX components_word_id_col_idx ON components (word_id_col);
CREATE INDEX components_word_id_col2_idx ON components (word_id_col2);
CREATE INDEX components_linked_component_idx ON components (linked_component_id);
CREATE INDEX components_component_link_type_idx ON components (component_link_type_id);
CREATE INDEX components_link_type_idx ON components (link_type_id);

--
-- indexes for table user_components
--

ALTER TABLE user_components ADD CONSTRAINT user_components_pkey PRIMARY KEY (component_id,user_id);
CREATE INDEX user_components_component_idx ON user_components (component_id);
CREATE INDEX user_components_user_idx ON user_components (user_id);
CREATE INDEX user_components_component_name_idx ON user_components (component_name);
CREATE INDEX user_components_component_type_idx ON user_components (component_type_id);
CREATE INDEX user_components_view_style_idx ON user_components (view_style_id);
CREATE INDEX user_components_word_id_row_idx ON user_components (word_id_row);
CREATE INDEX user_components_formula_idx ON user_components (formula_id);
CREATE INDEX user_components_word_id_col_idx ON user_components (word_id_col);
CREATE INDEX user_components_word_id_col2_idx ON user_components (word_id_col2);
CREATE INDEX user_components_linked_component_idx ON user_components (linked_component_id);
CREATE INDEX user_components_component_link_type_idx ON user_components (component_link_type_id);
CREATE INDEX user_components_link_type_idx ON user_components (link_type_id);