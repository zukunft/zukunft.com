-- --------------------------------------------------------

--
-- indexes for table languages
--

ALTER TABLE languages
    ADD KEY languages_language_name_idx (language_name),
    ADD KEY languages_local_name_idx (local_name);
