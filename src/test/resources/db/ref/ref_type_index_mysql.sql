-- --------------------------------------------------------

--
-- indexes for table ref_types
--

ALTER TABLE ref_types
    ADD PRIMARY KEY (ref_type_id),
    ADD KEY ref_types_type_name_idx (type_name);
