-- --------------------------------------------------------

--
-- indexes for table pod_status
--

ALTER TABLE pod_status
    ADD PRIMARY KEY (pod_status_id),
    ADD KEY pod_status_type_name_idx (type_name);
