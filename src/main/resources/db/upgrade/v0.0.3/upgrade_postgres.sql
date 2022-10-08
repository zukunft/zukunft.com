--
-- upgrade the zukunft.com PostgreSQL database to Version 0.0.3
--

-- --------------------------------------------------------

--
-- Structure for the phrases view
--

CREATE OR REPLACE VIEW phrases AS
SELECT w.word_id   AS phrase_id,
       w.user_id,
       w.word_name AS name_used,
       w.description,
       w.values,
       w.word_type_id,
       w.excluded,
       w.share_type_id,
       w.protect_id
FROM words AS w
UNION
SELECT (l.word_link_id * -(1))                                                    AS phrase_id,
       l.user_id,
       CASE WHEN (l.name_given IS NULL) THEN l.name_generated ELSE l.name_given END AS name_used,
       l.description,
       l.values,
       l.word_type_id,
       l.excluded,
       l.share_type_id,
       l.protect_id
FROM word_links AS l;

--
-- Structure for the user_phrases view
--

CREATE OR REPLACE VIEW user_phrases AS
SELECT w.word_id   AS phrase_id,
       w.user_id,
       w.word_name AS name_used,
       w.description,
       w.values,
       w.excluded,
       w.share_type_id,
       w.protect_id
FROM user_words AS w
UNION
SELECT (l.word_link_id * -(1))                                                    AS phrase_id,
       l.user_id,
       CASE WHEN (l.name_given IS NULL) THEN l.name_generated ELSE l.name_given END AS name_used,
       l.description,
       l.values,
       l.excluded,
       l.share_type_id,
       l.protect_id
FROM user_word_links AS l;

--
-- Structure for view terms
--

CREATE OR REPLACE VIEW terms AS
SELECT ((w.word_id * 2) - 1) AS term_id,
       w.user_id,
       w.word_name           AS term_name,
       w.description,
       w.values              AS usage,
       w.excluded,
       w.share_type_id,
       w.protect_id
FROM words AS w
UNION
SELECT ((l.word_link_id * -2) + 1)                                                  AS term_id,
       l.user_id,
       CASE WHEN (l.name_given IS NULL) THEN l.name_generated ELSE l.name_given END AS term_name,
       l.description,
       l.values                                                                     AS usage,
       l.excluded,
       l.share_type_id,
       l.protect_id
FROM word_links AS l
UNION
SELECT (f.formula_id * 2) AS term_id,
       f.user_id,
       f.formula_name     AS term_name,
       f.description,
       f.usage            AS usage,
       f.excluded,
       f.share_type_id,
       f.protect_id
FROM formulas AS f
UNION
SELECT (v.verb_id * -2) AS term_id,
       NULL            AS user_id,
       v.verb_name     AS term_name,
       v.description,
       v.words         AS usage,
       NULL            AS excluded,
       1               AS share_type_id,
       3               AS protect_id
FROM verbs AS v
;

--
-- Structure for view user_terms
--

CREATE OR REPLACE VIEW user_terms AS
SELECT ((w.word_id * 2) - 1) AS term_id,
       w.user_id,
       w.word_name           AS term_name,
       w.description,
       w.values              AS usage,
       w.excluded,
       w.share_type_id,
       w.protect_id
FROM user_words AS w
UNION
SELECT ((l.word_link_id * -2) + 1)                                                  AS term_id,
       l.user_id,
       CASE WHEN (l.name_given IS NULL) THEN l.name_generated ELSE l.name_given END AS term_name,
       l.description,
       l.values                                                                     AS usage,
       l.excluded,
       l.share_type_id,
       l.protect_id
FROM user_word_links AS l
UNION
SELECT (f.formula_id * 2) AS term_id,
       f.user_id,
       f.formula_name     AS term_name,
       f.description,
       f.usage            AS usage,
       f.excluded,
       f.share_type_id,
       f.protect_id
FROM user_formulas AS f
UNION
SELECT (v.verb_id * -2) AS term_id,
       NULL            AS user_id,
       v.verb_name     AS term_name,
       v.description,
       v.words         AS usage,
       NULL            AS excluded,
       1               AS share_type_id,
       3               AS protect_id
FROM verbs AS v
;

-- --------------------------------------------------------

--
-- Table structure for table value_time_series
--

CREATE TABLE IF NOT EXISTS user_value_time_series
(
    value_time_series_id BIGSERIAL PRIMARY KEY,
    user_id              bigint    NOT NULL,
    source_id            bigint         DEFAULT NULL,
    excluded             smallint       DEFAULT NULL,
    share_type_id        bigint         DEFAULT NULL,
    protect_id           bigint    NOT NULL,
    last_update          timestamp NULL DEFAULT NULL
);

COMMENT ON TABLE user_value_time_series is 'common parameters for a user specific list of intraday values';

--
-- Indexes for table user_values
--
ALTER TABLE user_value_time_series
    ADD CONSTRAINT user_value_time_series_pkey PRIMARY KEY (value_time_series_id, user_id);
CREATE INDEX user_value_time_series_user_idx ON user_value_time_series (user_id);
CREATE INDEX user_value_time_series_source_idx ON user_value_time_series (source_id);
CREATE INDEX user_value_time_series_share_idx ON user_value_time_series (share_type_id);
CREATE INDEX user_value_time_series_protection_idx ON user_value_time_series (protect_id);

--
-- Constraints for table user_value_time_series
--
ALTER TABLE user_value_time_series
    ADD CONSTRAINT user_value_time_series_fk_1 FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_value_time_series_fk_2 FOREIGN KEY (source_id) REFERENCES sources (source_id),
    ADD CONSTRAINT user_value_time_series_fk_3 FOREIGN KEY (share_type_id) REFERENCES share_types (share_type_id),
    ADD CONSTRAINT user_value_time_series_fk_4 FOREIGN KEY (protect_id) REFERENCES protection_types (protection_type_id);

--
-- database corrections
--
--
-- Constraints for table user_values
--
ALTER TABLE user_values
    ADD CONSTRAINT user_values_fk_4 FOREIGN KEY (protect_id) REFERENCES protection_types (protection_type_id);
