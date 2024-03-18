-- --------------------------------------------------------

--
-- indexes for table phrase_types
--

ALTER TABLE phrase_types
    ADD PRIMARY KEY (phrase_type_id),
    ADD KEY phrase_types_type_name_idx (type_name);
