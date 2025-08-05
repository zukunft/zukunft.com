-- --------------------------------------------------------

--
-- indexes for table elements
--

ALTER TABLE elements
    ADD KEY elements_formula_idx (formula_id),
    ADD KEY elements_element_type_idx (element_type_id);
