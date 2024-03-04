-- --------------------------------------------------------

--
-- indexes for table pod_types
--

ALTER TABLE pod_types
    ADD PRIMARY KEY (pod_type_id),
    ADD KEY pod_types_type_name_idx (type_name);
