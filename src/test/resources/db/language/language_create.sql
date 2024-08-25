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
    wikimedia_code varchar(100) DEFAULT NULL
);

COMMENT ON TABLE languages IS 'for table languages';
COMMENT ON COLUMN languages.language_id IS 'the internal unique primary index';
