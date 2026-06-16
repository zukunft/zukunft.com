-- --------------------------------------------------------

--
-- indexes for table db_caches
--

CREATE INDEX db_caches_type_idx        ON db_caches (type_id);
CREATE INDEX db_caches_user_idx        ON db_caches (user_id);
CREATE INDEX db_caches_status_idx      ON db_caches (status_id);
CREATE INDEX db_caches_last_update_idx ON db_caches (last_update);