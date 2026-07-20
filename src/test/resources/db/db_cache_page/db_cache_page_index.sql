-- --------------------------------------------------------

--
-- indexes for table db_cache_pages
--

CREATE INDEX db_cache_pages_url_idx ON db_cache_pages (url);
CREATE INDEX db_cache_pages_last_update_idx ON db_cache_pages (last_update);
