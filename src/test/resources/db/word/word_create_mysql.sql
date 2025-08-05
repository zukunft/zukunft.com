-- --------------------------------------------------------

--
-- table structure for a short text, that can be used to search for values or results with a 64 bit database key because humans will never be able to use more than a few million words
--

CREATE TABLE IF NOT EXISTS words
(
    word_id        bigint       NOT     NULL COMMENT 'the internal unique primary index',
    user_id        bigint       DEFAULT NULL COMMENT 'the owner / creator of the word',
    word_name      varchar(255) NOT     NULL COMMENT 'the text used for searching',
    plural         varchar(255) DEFAULT NULL COMMENT 'to be replaced by a language form entry; TODO to be move to language forms',
    description    text         DEFAULT NULL COMMENT 'to be replaced by a language form entry',
    phrase_type_id smallint     DEFAULT NULL COMMENT 'to link coded functionality to words e.g. to exclude measure words from a percent result',
    view_id        bigint       DEFAULT NULL COMMENT 'the default mask for this word',
    `values`       bigint       DEFAULT NULL COMMENT 'number of values linked to the word, which gives an indication of the importance',
    inactive       smallint     DEFAULT NULL COMMENT 'true if the word is not yet active e.g. because it is moved to the prime words with a 16 bit id',
    code_id        varchar(255) DEFAULT NULL COMMENT 'to link coded functionality to a specific word e.g. to get the values of the system configuration',
    excluded       smallint     DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id  smallint     DEFAULT NULL COMMENT 'to restrict the access',
    protect_id     smallint     DEFAULT NULL COMMENT 'to protect against unwanted changes',
    PRIMARY KEY (word_id)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for a short text, that can be used to search for values or results with a 64 bit database key because humans will never be able to use more than a few million words';

--
-- AUTO_INCREMENT for table words
--
ALTER TABLE words
    MODIFY word_id bigint NOT NULL AUTO_INCREMENT;

--
-- table structure to save user specific changes for a short text, that can be used to search for values or results with a 64 bit database key because humans will never be able to use more than a few million words
--

CREATE TABLE IF NOT EXISTS user_words
(
    word_id        bigint       NOT NULL              COMMENT 'with the user_id the internal unique primary index',
    user_id        bigint       NOT NULL              COMMENT 'the changer of the word',
    language_id    smallint     NOT NULL DEFAULT 1    COMMENT 'the text used for searching',
    word_name      varchar(255)          DEFAULT NULL COMMENT 'the text used for searching',
    plural         varchar(255)          DEFAULT NULL COMMENT 'to be replaced by a language form entry; TODO to be move to language forms',
    description    text                  DEFAULT NULL COMMENT 'to be replaced by a language form entry',
    phrase_type_id smallint              DEFAULT NULL COMMENT 'to link coded functionality to words e.g. to exclude measure words from a percent result',
    view_id        bigint                DEFAULT NULL COMMENT 'the default mask for this word',
    `values`       bigint                DEFAULT NULL COMMENT 'number of values linked to the word, which gives an indication of the importance',
    excluded       smallint              DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id  smallint              DEFAULT NULL COMMENT 'to restrict the access',
    protect_id     smallint              DEFAULT NULL COMMENT 'to protect against unwanted changes',
    PRIMARY KEY (word_id,user_id,language_id)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for a short text, that can be used to search for values or results with a 64 bit database key because humans will never be able to use more than a few million words';
