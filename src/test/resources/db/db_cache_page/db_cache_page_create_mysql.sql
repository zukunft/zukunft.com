-- --------------------------------------------------------

--
-- table structure cached html pages of view-only requests keyed by the url for faster response times
--

CREATE TABLE IF NOT EXISTS db_cache_pages
(
    db_cache_page_id bigint        NOT NULL COMMENT 'the internal unique primary index',
    `url`            text          NOT NULL COMMENT 'the request url that the cached html page belongs to',
    html_page        text      DEFAULT NULL COMMENT 'the pre-rendered html page returned for the url',
    last_update      timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp of the last rendering of the cached html page',
    PRIMARY KEY (db_cache_page_id)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'cached html pages of view-only requests keyed by the url for faster response times';

--
-- AUTO_INCREMENT for table db_cache_pages
--
ALTER TABLE db_cache_pages
    MODIFY db_cache_page_id bigint NOT NULL AUTO_INCREMENT;
