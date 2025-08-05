-- --------------------------------------------------------

--
-- table structure for language forms like plural
--

CREATE TABLE IF NOT EXISTS language_forms
(
    language_form_id   smallint         NOT NULL COMMENT 'the internal unique primary index',
    language_form_name varchar(255) DEFAULT NULL COMMENT 'type of adjustment of a term in a language e.g. plural',
    code_id            varchar(100) DEFAULT NULL,
    description        text         DEFAULT NULL,
    language_id        smallint     DEFAULT NULL,
    PRIMARY KEY (language_form_id)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for language forms like plural';

--
-- AUTO_INCREMENT for table language_forms
--
ALTER TABLE language_forms
    MODIFY language_form_id smallint NOT NULL AUTO_INCREMENT;
