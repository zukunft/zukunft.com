-- --------------------------------------------------------

--
-- table structure precollected data for faster response times in the json format
--

CREATE TABLE IF NOT EXISTS db_caches
(
    db_cache_id bigint        NOT NULL COMMENT 'the internal unique primary index',
    type_id     smallint      NOT NULL COMMENT 'to separate the system, user and frontend configuration',
    data        text      DEFAULT NULL COMMENT 'the cached data as text',
    user_id     bigint        NOT NULL COMMENT 'to link coded functionality to words e.g. to exclude measure words from a percent result',
    status_id   smallint      NOT NULL DEFAULT 1 COMMENT 'clean, dirty, updating, outdated or unused',
    last_update timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp of the last update of the cache',
    PRIMARY KEY (db_cache_id)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'precollected data for faster response times in the json format';

--
-- AUTO_INCREMENT for table db_caches
--
ALTER TABLE db_caches
    MODIFY db_cache_id bigint NOT NULL AUTO_INCREMENT;