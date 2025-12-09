-- --------------------------------------------------------

--
-- indexes for table protection_types
--

ALTER TABLE protection_types
    ADD KEY protection_types_type_name_idx (type_name);
