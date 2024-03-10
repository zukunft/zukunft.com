-- --------------------------------------------------------

--
-- indexes for table formula_link_types
--

ALTER TABLE formula_link_types
    ADD PRIMARY KEY (formula_link_type_id),
    ADD KEY formula_link_types_type_name_idx (type_name);
