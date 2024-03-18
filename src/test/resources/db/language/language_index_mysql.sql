-- --------------------------------------------------------

--
-- indexes for table languages
--

ALTER TABLE languages
    ADD PRIMARY KEY (language_id),
    ADD KEY languages_language_name_idx (language_name);
