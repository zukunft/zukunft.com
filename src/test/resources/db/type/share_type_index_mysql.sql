-- --------------------------------------------------------

--
-- indexes for table share_types
--

ALTER TABLE share_types
    ADD PRIMARY KEY (share_type_id),
    ADD KEY share_types_type_name_idx (type_name);
