-- --------------------------------------------------------

--
-- table structure precollected data for faster response times in the json format
--

CREATE TABLE IF NOT EXISTS db_caches
(
    db_cache_id BIGSERIAL PRIMARY KEY,
    type_id     smallint NOT NULL,
    data        text DEFAULT NULL,
    user_id     bigint NOT NULL,
    status_id   smallint NOT NULL DEFAULT 1,
    last_update timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP);

COMMENT ON TABLE db_caches IS 'precollected data for faster response times in the json format';
COMMENT ON COLUMN db_caches.db_cache_id IS 'the internal unique primary index';
COMMENT ON COLUMN db_caches.type_id IS 'to separate the system,user and frontend configuration';
COMMENT ON COLUMN db_caches.data IS 'the cached data as text';
COMMENT ON COLUMN db_caches.user_id IS 'to link coded functionality to words e.g. to exclude measure words from a percent result';
COMMENT ON COLUMN db_caches.status_id IS 'clean,dirty,updating,outdated or unused';
COMMENT ON COLUMN db_caches.last_update IS 'timestamp of the last update of the cache';