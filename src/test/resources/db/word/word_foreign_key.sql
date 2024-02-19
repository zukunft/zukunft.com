-- --------------------------------------------------------

--
-- constraints for table words
--
ALTER TABLE words
    ADD CONSTRAINT word_name_uk UNIQUE (word_name),
    ADD CONSTRAINT code_id_uk UNIQUE (code_id),
    ADD CONSTRAINT words_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT words_phrase_type_fk FOREIGN KEY (phrase_type_id) REFERENCES phrase_types (phrase_type_id),
    ADD CONSTRAINT words_view_fk FOREIGN KEY (view_id) REFERENCES views (view_id);

--
-- constraints for table user_words
--
ALTER TABLE user_words
    ADD CONSTRAINT user_words_word_fk FOREIGN KEY (word_id) REFERENCES words (word_id),
    ADD CONSTRAINT user_words_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_words_language_fk FOREIGN KEY (language_id) REFERENCES languages (language_id),
    ADD CONSTRAINT user_words_phrase_type_fk FOREIGN KEY (phrase_type_id) REFERENCES phrase_types (phrase_type_id),
    ADD CONSTRAINT user_words_view_fk FOREIGN KEY (view_id) REFERENCES views (view_id);
