-- --------------------------------------------------------

--
-- indexes for table db_cache_pages
--

ALTER TABLE db_cache_pages
    ADD KEY db_cache_pages_url_idx (url),
    ADD KEY db_cache_pages_last_update_idx (last_update);
