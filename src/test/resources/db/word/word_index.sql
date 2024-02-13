-- --------------------------------------------------------

--
-- indexes for table words
--
CREATE INDEX words_user_idx        ON words (user_id);
CREATE INDEX words_word_name_idx   ON words (word_name);
CREATE INDEX words_plural_idx      ON words (plural);
CREATE INDEX words_phrase_type_idx ON words (phrase_type_id);
CREATE INDEX words_view_idx        ON words (view_id);

--
-- indexes for table user_words
--
ALTER TABLE user_words ADD CONSTRAINT user_words_pkey PRIMARY KEY (word_id, user_id, language_id);
CREATE INDEX user_words_word_idx        ON user_words (word_id);
CREATE INDEX user_words_user_idx        ON user_words (user_id);
CREATE INDEX user_words_language_idx    ON user_words (language_id);
CREATE INDEX user_words_word_name_idx   ON user_words (word_name);
CREATE INDEX user_words_plural_idx      ON user_words (plural);
CREATE INDEX user_words_phrase_type_idx ON user_words (phrase_type_id);
CREATE INDEX user_words_view_idx        ON user_words (view_id);


