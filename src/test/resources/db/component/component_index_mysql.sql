-- --------------------------------------------------------

--
-- indexes for table components
--

ALTER TABLE components
    ADD PRIMARY KEY (component_id),
    ADD KEY components_user_idx (user_id),
    ADD KEY components_component_name_idx (component_name),
    ADD KEY components_component_type_idx (component_type_id),
    ADD KEY components_word_id_row_idx (word_id_row),
    ADD KEY components_formula_idx (formula_id),
    ADD KEY components_word_id_col_idx (word_id_col),
    ADD KEY components_word_id_col2_idx (word_id_col2),
    ADD KEY components_linked_component_idx (linked_component_id),
    ADD KEY components_component_link_type_idx (component_link_type_id),
    ADD KEY components_link_type_idx (link_type_id);

--
-- indexes for table user_components
--

ALTER TABLE user_components
    ADD PRIMARY KEY (component_id,user_id),
    ADD KEY user_components_component_idx (component_id),
    ADD KEY user_components_user_idx (user_id),
    ADD KEY user_components_component_name_idx (component_name),
    ADD KEY user_components_component_type_idx (component_type_id),
    ADD KEY user_components_word_id_row_idx (word_id_row),
    ADD KEY user_components_formula_idx (formula_id),
    ADD KEY user_components_word_id_col_idx (word_id_col),
    ADD KEY user_components_word_id_col2_idx (word_id_col2),
    ADD KEY user_components_linked_component_idx (linked_component_id),
    ADD KEY user_components_component_link_type_idx (component_link_type_id),
    ADD KEY user_components_link_type_idx (link_type_id);