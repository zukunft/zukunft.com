-- --------------------------------------------------------

--
-- indexes for table words
--
ALTER TABLE words
    ADD PRIMARY KEY (word_id),
    ADD KEY words_user_idx (user_id),
    ADD KEY words_word_name_idx (word_name),
    ADD KEY words_plural_idx (plural),
    ADD KEY words_phrase_type_idx (phrase_type_id),
    ADD KEY words_view_idx (view_id);

--
-- indexes for table user_words
--
ALTER TABLE user_words
    ADD PRIMARY KEY (word_id, user_id, language_id),
    ADD KEY user_words_word_idx (word_id),
    ADD KEY user_words_user_idx (user_id),
    ADD KEY user_words_language_idx (language_id),
    ADD KEY user_words_word_name_idx (word_name),
    ADD KEY user_words_plural_idx (plural),
    ADD KEY user_words_phrase_type_idx (phrase_type_id),
    ADD KEY user_words_view_idx (view_id);
