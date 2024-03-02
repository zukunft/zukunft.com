-- --------------------------------------------------------

--
-- table structure for language forms like plural
--

CREATE TABLE IF NOT EXISTS language_forms
(
    language_form_id   BIGSERIAL PRIMARY KEY,
    language_form_name varchar(255) DEFAULT NULL,
    code_id            varchar(100) DEFAULT NULL,
    description        text         DEFAULT NULL,
    language_id        bigint       DEFAULT NULL
);

COMMENT ON TABLE language_forms IS 'for language forms like plural';
COMMENT ON COLUMN language_forms.language_form_id IS 'the internal unique primary index';
COMMENT ON COLUMN language_forms.language_form_name IS 'type of adjustment of a term in a language e.g. plural';
