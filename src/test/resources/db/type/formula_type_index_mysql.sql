-- --------------------------------------------------------

--
-- indexes for table formula_types
--

ALTER TABLE formula_types
    ADD PRIMARY KEY (formula_type_id),
    ADD KEY formula_types_type_name_idx (type_name);
