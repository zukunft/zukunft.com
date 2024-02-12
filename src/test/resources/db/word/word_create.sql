-- --------------------------------------------------------

--
-- table structure for a short text, that can be used to search for values or results with a 64 bit database key because humans will never be able to use more than a few million words
--

CREATE TABLE IF NOT EXISTS words
(
    word_id        BIGSERIAL PRIMARY KEY,
    user_id        bigint                DEFAULT NULL,
    word_name      varchar(255) NOT NULL,
    plural         varchar(255)          DEFAULT NULL,
    description    text                  DEFAULT NULL,
    phrase_type_id bigint                DEFAULT NULL,
    view_id        bigint                DEFAULT NULL,
    values         bigint                DEFAULT NULL,
    inactive       smallint              DEFAULT NULL,
    excluded       smallint              DEFAULT NULL,
    share_type_id  smallint              DEFAULT NULL,
    protect_id     smallint              DEFAULT NULL
);

COMMENT ON TABLE words IS 'for a short text, that can be used to search for values or results with a 64 bit database key because humans will never be able to use more than a few million words';
COMMENT ON COLUMN words.word_id IS 'the internal unique primary index ';
COMMENT ON COLUMN words.user_id IS 'the owner / creator of the value';
COMMENT ON COLUMN words.word_name IS 'the text used for searching';
COMMENT ON COLUMN words.plural IS 'to be replaced by a language form entry; TODO to be move to language forms';
COMMENT ON COLUMN words.description IS 'to be replaced by a language form entry';
COMMENT ON COLUMN words.phrase_type_id IS 'to link coded functionality to a word e.g. to exclude measure words from a percent result';
COMMENT ON COLUMN words.view_id IS 'the default mask for this word';
COMMENT ON COLUMN words.values IS 'number of values linked to the word, which gives an indication of the importance';
COMMENT ON COLUMN words.inactive IS 'true if the word is not yet active e.g. because it is moved to the prime words with a 16 bit id';
COMMENT ON COLUMN words.excluded IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN words.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN words.protect_id IS 'to protect against unwanted changes';

--
-- table structure to save user specific changes for a short text, that can be used to search for values or results with a 64 bit database key because humans will never be able to use more than a few million words
--

CREATE TABLE IF NOT EXISTS user_words
(
    word_id        bigint   NOT NULL,
    user_id        bigint   NOT NULL,
    language_id    bigint   NOT NULL DEFAULT 1,
    word_name      varchar(255)      DEFAULT NULL,
    plural         varchar(255)      DEFAULT NULL,
    description    text              DEFAULT NULL,
    phrase_type_id bigint            DEFAULT NULL,
    view_id        bigint            DEFAULT NULL,
    values         bigint            DEFAULT NULL,
    excluded       smallint          DEFAULT NULL,
    share_type_id  smallint          DEFAULT NULL,
    protect_id     smallint          DEFAULT NULL
);

COMMENT ON TABLE user_words IS 'for a short text, that can be used to search for values or results with a 64 bit database key because humans will never be able to use more than a few million words';
COMMENT ON COLUMN user_words.word_id IS 'with the user_id the internal unique primary index ';
COMMENT ON COLUMN user_words.user_id IS 'the changer of the ';
COMMENT ON COLUMN user_words.language_id IS 'the text used for searching';
COMMENT ON COLUMN user_words.word_name IS 'the text used for searching';
COMMENT ON COLUMN user_words.plural IS 'to be replaced by a language form entry; TODO to be move to language forms';
COMMENT ON COLUMN user_words.description IS 'to be replaced by a language form entry';
COMMENT ON COLUMN user_words.phrase_type_id IS 'to link coded functionality to a word e.g. to exclude measure words from a percent result';
COMMENT ON COLUMN user_words.view_id IS 'the default mask for this word';
COMMENT ON COLUMN user_words.values IS 'number of values linked to the word,which gives an indication of the importance';
COMMENT ON COLUMN user_words.excluded IS 'true if a user,but not all,have removed it';
COMMENT ON COLUMN user_words.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_words.protect_id IS 'to protect against unwanted changes';

