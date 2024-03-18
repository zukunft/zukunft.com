-- --------------------------------------------------------

--
-- indexes for table pods
--

CREATE INDEX pods_type_name_idx ON pods (type_name);
CREATE INDEX pods_pod_type_idx ON pods (pod_type_id);
CREATE INDEX pods_pod_status_idx ON pods (pod_status_id);
