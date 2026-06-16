-- --------------------------------------------------------

--
-- indexes for table db_caches
--

ALTER TABLE db_caches
    ADD KEY db_caches_type_idx (type_id),
    ADD KEY db_caches_user_idx (user_id),
    ADD KEY db_caches_status_idx (status_id),
    ADD KEY db_caches_last_update_idx (last_update);