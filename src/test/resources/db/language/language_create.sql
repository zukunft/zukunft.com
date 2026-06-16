-- --------------------------------------------------------

--
-- table structure for table languages
--

CREATE TABLE IF NOT EXISTS languages
(
    language_id    SERIAL PRIMARY KEY,
    language_name  varchar(255)     NOT NULL,
    code_id        varchar(100) DEFAULT NULL,
    description    text         DEFAULT NULL,
    wikimedia_code varchar(100) DEFAULT NULL,
    local_name     varchar(255) DEFAULT NULL,
    usage          bigint       DEFAULT NULL
);

COMMENT ON TABLE languages IS 'for table languages';
COMMENT ON COLUMN languages.language_id IS 'the internal unique primary index';
COMMENT ON COLUMN languages.language_name IS 'the name of the language in the system language, which is English';
COMMENT ON COLUMN languages.code_id IS 'the ISO 639-1 language code plus BCP 47 plus additional language codes requested by zukunft.com users';
COMMENT ON COLUMN languages.wikimedia_code IS 'wikimedia language code e.g. no instead of nb (Norwegian Bokmål in ISO) for a full link to wikipedia';
COMMENT ON COLUMN languages.local_name IS 'the name of the language in the language';
COMMENT ON COLUMN languages.usage IS 'the number of speakers worldwide';
