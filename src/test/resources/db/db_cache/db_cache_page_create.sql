-- --------------------------------------------------------

--
-- table structure cached html pages of view-only requests keyed by the url for faster response times
--

CREATE TABLE IF NOT EXISTS db_cache_pages
(
    db_cache_page_id BIGSERIAL PRIMARY KEY,
    url              text          NOT NULL,
    html_page        text      DEFAULT NULL,
    last_update      timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON TABLE db_cache_pages                   IS 'cached html pages of view-only requests keyed by the url for faster response times';
COMMENT ON COLUMN db_cache_pages.db_cache_page_id IS 'the internal unique primary index';
COMMENT ON COLUMN db_cache_pages.url              IS 'the request url that the cached html page belongs to';
COMMENT ON COLUMN db_cache_pages.html_page        IS 'the pre-rendered html page returned for the url';
COMMENT ON COLUMN db_cache_pages.last_update      IS 'timestamp of the last rendering of the cached html page';
