-- --------------------------------------------------------

--
-- table structure for table languages
--

CREATE TABLE IF NOT EXISTS languages
(
    language_id    bigint           NOT NULL COMMENT 'the internal unique primary index',
    language_name  varchar(255)     NOT NULL,
    code_id        varchar(100) DEFAULT NULL,
    description    text         DEFAULT NULL,
    wikimedia_code varchar(100) DEFAULT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for table languages';
