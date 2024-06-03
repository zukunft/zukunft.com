-- --------------------------------------------------------

--
-- indexes for table formula_links
--

ALTER TABLE formula_links
    ADD PRIMARY KEY (formula_link_id),
    ADD KEY formula_links_user_idx (user_id),
    ADD KEY formula_links_formula_link_type_idx (formula_link_type_id),
    ADD KEY formula_links_formula_idx (formula_id),
    ADD KEY formula_links_phrase_idx (phrase_id);

--
-- indexes for table user_formula_links
--

ALTER TABLE user_formula_links
    ADD PRIMARY KEY (formula_link_id,user_id),
    ADD KEY user_formula_links_formula_link_idx (formula_link_id),
    ADD KEY user_formula_links_user_idx (user_id),
    ADD KEY user_formula_links_formula_link_type_idx (formula_link_type_id);
