-- --------------------------------------------------------
--
-- indexes for table formulas
--

ALTER TABLE formulas
    ADD PRIMARY KEY (formula_id),
    ADD KEY formulas_user_idx (user_id),
    ADD KEY formulas_formula_name_idx (formula_name),
    ADD KEY formulas_formula_type_idx (formula_type_id),
    ADD KEY formulas_view_idx (view_id);

--
-- indexes for table user_formulas
--

ALTER TABLE user_formulas
    ADD PRIMARY KEY (formula_id,user_id),
    ADD KEY user_formulas_formula_idx (formula_id),
    ADD KEY user_formulas_user_idx (user_id),
    ADD KEY user_formulas_formula_name_idx (formula_name),
    ADD KEY user_formulas_formula_type_idx (formula_type_id),
    ADD KEY user_formulas_view_idx (view_id);
