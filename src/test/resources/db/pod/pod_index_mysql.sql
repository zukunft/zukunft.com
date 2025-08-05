-- --------------------------------------------------------
--
-- indexes for table pods
--

ALTER TABLE pods
    ADD KEY pods_type_name_idx (type_name),
    ADD KEY pods_pod_type_idx (pod_type_id),
    ADD KEY pods_pod_status_idx (pod_status_id);
