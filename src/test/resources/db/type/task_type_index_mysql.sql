-- --------------------------------------------------------

--
-- indexes for table task_types
--

ALTER TABLE task_types
    ADD PRIMARY KEY (task_type_id),
    ADD KEY task_types_type_name_idx (type_name);
