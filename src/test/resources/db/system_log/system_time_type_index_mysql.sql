-- --------------------------------------------------------

--
-- indexes for table system_time_types
--

ALTER TABLE system_time_types
    ADD PRIMARY KEY (system_time_type_id),
    ADD KEY system_time_types_type_name_idx (type_name);
