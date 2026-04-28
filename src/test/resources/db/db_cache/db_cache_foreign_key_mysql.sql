--
-- constraints for table db_caches
--

ALTER TABLE db_caches
    ADD CONSTRAINT db_caches_db_cache_type_fk   FOREIGN KEY (type_id)   REFERENCES db_cache_types   (type_id),
    ADD CONSTRAINT db_caches_user_fk            FOREIGN KEY (user_id)   REFERENCES users            (user_id),
    ADD CONSTRAINT db_caches_db_cache_status_fk FOREIGN KEY (status_id) REFERENCES db_cache_statuum (status_id);
