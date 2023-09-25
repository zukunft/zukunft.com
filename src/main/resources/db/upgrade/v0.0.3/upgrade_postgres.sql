--
-- upgrade the zukunft.com Postgres database to Version 0.0.3
--

-- --------------------------------------------------------

--
-- Table structure for table user_refs
--

CREATE TABLE IF NOT EXISTS user_refs
(
    ref_id         bigint NOT NULL,
    user_id        bigint NOT NULL,
    url            text         DEFAULT NULL,
    description    text         DEFAULT NULL,
    excluded       smallint     DEFAULT NULL
);

--
-- Indexes for table user_refs
--
ALTER TABLE user_refs
    ADD CONSTRAINT user_ref_pkey PRIMARY KEY (ref_id, user_id);
CREATE INDEX user_ref_user_idx ON user_refs (user_id);
CREATE INDEX user_ref_idx ON user_refs (ref_id);

--
-- Constraints for table user_refs
--
ALTER TABLE user_refs
    ADD CONSTRAINT user_refs_fk_1 FOREIGN KEY (ref_id) REFERENCES refs (ref_id),
    ADD CONSTRAINT user_refs_fk_2 FOREIGN KEY (user_id) REFERENCES users (user_id);

-- --------------------------------------------------------

--
-- Table structure for table user_view_term_links
--

CREATE TABLE IF NOT EXISTS user_view_term_links
(
    view_term_link_id BIGSERIAL PRIMARY KEY,
    type_id           bigint NOT NULL   DEFAULT '1',
    link_type_id      bigint            DEFAULT NULL,
    user_id           bigint NOT NULL,
    description       text   NOT NULL,
    excluded          smallint          DEFAULT NULL,
    share_type_id     smallint          DEFAULT NULL,
    protect_id        smallint NOT NULL DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure for the phrases view
--

CREATE OR REPLACE VIEW phrases AS
SELECT w.word_id   AS phrase_id,
       w.user_id,
       w.word_name AS phrase_name,
       w.description,
       w.values,
       w.phrase_type_id,
       w.excluded,
       w.share_type_id,
       w.protect_id
FROM words AS w
UNION
SELECT (l.triple_id * -(1))                                                    AS phrase_id,
       l.user_id,
       CASE WHEN (l.triple_name IS NULL) THEN
              CASE WHEN (l.name_given IS NULL)
                     THEN l.name_generated
                     ELSE l.name_given END
           ELSE l.triple_name END AS phrase_name,
       l.description,
       l.values,
       l.phrase_type_id,
       l.excluded,
       l.share_type_id,
       l.protect_id
FROM triples AS l;

--
-- Structure for the user_phrases view
--

CREATE OR REPLACE VIEW user_phrases AS
SELECT w.word_id   AS phrase_id,
       w.user_id,
       w.word_name AS phrase_name,
       w.description,
       w.values,
       w.excluded,
       w.share_type_id,
       w.protect_id
FROM user_words AS w
UNION
SELECT (l.triple_id * -(1))                                                    AS phrase_id,
       l.user_id,
       CASE WHEN (l.triple_name IS NULL) THEN
                   CASE WHEN (l.name_given IS NULL)
                               THEN l.name_generated
                        ELSE l.name_given END
            ELSE l.triple_name END AS phrase_name,
       l.description,
       l.values,
       l.excluded,
       l.share_type_id,
       l.protect_id
FROM user_triples AS l;

--
-- Structure for view terms
--

CREATE OR REPLACE VIEW terms AS
SELECT ((w.word_id * 2) - 1) AS term_id,
       w.user_id,
       w.word_name           AS term_name,
       w.description,
       w.values              AS usage,
       w.phrase_type_id      AS term_type_id,
       w.excluded,
       w.share_type_id,
       w.protect_id,
       ''                    AS formula_text,
       ''                    AS resolved_text
FROM words AS w
WHERE w.phrase_type_id <> 10 OR w.phrase_type_id IS NULL
UNION
SELECT ((l.triple_id * -2) + 1)                                                  AS term_id,
       l.user_id,
       CASE WHEN (l.triple_name IS NULL) THEN
                   CASE WHEN (l.name_given IS NULL)
                               THEN l.name_generated
                        ELSE l.name_given END
            ELSE l.triple_name END AS phrase_name,
       l.description,
       l.values                                                                     AS usage,
       l.phrase_type_id,
       l.excluded,
       l.share_type_id,
       l.protect_id,
       ''                    AS formula_text,
       ''                    AS resolved_text
FROM triples AS l
UNION
SELECT (f.formula_id * 2) AS term_id,
       f.user_id,
       f.formula_name     AS term_name,
       f.description,
       f.usage            AS usage,
       f.formula_type_id  AS term_type_id,
       f.excluded,
       f.share_type_id,
       f.protect_id,
       f.formula_text     AS formula_text,
       f.resolved_text    AS resolved_text
FROM formulas AS f
UNION
SELECT (v.verb_id * -2) AS term_id,
       NULL            AS user_id,
       v.verb_name     AS term_name,
       v.description,
       v.words         AS usage,
       NULL            AS term_type_id,
       NULL            AS excluded,
       1               AS share_type_id,
       3               AS protect_id,
       ''              AS formula_text,
       ''              AS resolved_text
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
       w.protect_id,
       ''                    AS formula_text,
       ''                    AS resolved_text
FROM user_words AS w
WHERE w.phrase_type_id <> 10
UNION
SELECT ((l.triple_id * -2) + 1)  AS term_id,
       l.user_id,
       CASE WHEN (l.triple_name IS NULL) THEN
                   CASE WHEN (l.name_given IS NULL)
                               THEN l.name_generated
                        ELSE l.name_given END
            ELSE l.triple_name END AS phrase_name,
       l.description,
       l.values                  AS usage,
       l.excluded,
       l.share_type_id,
       l.protect_id,
       ''                        AS formula_text,
       ''                        AS resolved_text
FROM user_triples AS l
UNION
SELECT (f.formula_id * 2) AS term_id,
       f.user_id,
       f.formula_name     AS term_name,
       f.description,
       f.usage            AS usage,
       f.excluded,
       f.share_type_id,
       f.protect_id,
       f.formula_text     AS formula_text,
       f.resolved_text    AS resolved_text
FROM user_formulas AS f
UNION
SELECT (v.verb_id * -2) AS term_id,
       NULL            AS user_id,
       v.verb_name     AS term_name,
       v.description,
       v.words         AS usage,
       NULL            AS excluded,
       1               AS share_type_id,
       3               AS protect_id,
       ''              AS formula_text,
       ''              AS resolved_text
FROM verbs AS v
;

--
-- Structure for view change_table_fields
--

CREATE OR REPLACE VIEW change_table_fields AS
SELECT f.change_field_id                              AS change_table_field_id,
       concat(t.change_table_id, f.change_field_name) AS change_table_field_name,
       f.description,
       CASE WHEN (f.code_id IS NULL) THEN concat(t.change_table_id, f.change_field_name) ELSE f.code_id END AS code_id
FROM change_fields AS f,
     change_tables AS t
WHERE f.table_id = t.change_table_id;

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

-- --------------------------------------------------------
-- September 2023 changes
-- --------------------------------------------------------

--
-- Table structure for public values that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS value_standard
(
    group_id      char(112) PRIMARY KEY,
    numeric_value double precision NOT NULL,
    source_id     bigint DEFAULT NULL
);

COMMENT ON TABLE value_standard is 'for public unprotected values that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN value_standard.group_id is 'the prime index to find the value';

-- --------------------------------------------------------

--
-- Table structure for the most often requested values related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS value_prime
(
    group_id        BIGSERIAL PRIMARY KEY,
    user_id         bigint                    DEFAULT NULL,
    numeric_value   double precision NOT NULL,
    source_id       bigint                    DEFAULT NULL,
    last_update     timestamp        NULL     DEFAULT NULL,
    description     text,
    excluded        smallint                  DEFAULT NULL,
    share_type_id   smallint                  DEFAULT NULL,
    protect_id      bigint           NOT NULL DEFAULT '1'
);

COMMENT ON TABLE value_prime is 'for the most often used values';
COMMENT ON COLUMN value_prime.group_id is 'temp field to increase speed created by the value term links';
COMMENT ON COLUMN value_prime.user_id is 'the owner / creator of the value';
COMMENT ON COLUMN value_prime.last_update is 'for fast recalculation';
COMMENT ON COLUMN value_prime.description is 'temp field used during dev phase for easy value to trm assigns';
COMMENT ON COLUMN value_prime.excluded is 'the default exclude setting for most users';

--
-- Table structure to store the user specific changes for the most often requested values related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS user_value_prime
(
    group_id        BIGSERIAL NOT NULL,
    user_id         bigint                    DEFAULT NULL,
    numeric_value   double precision NOT NULL,
    source_id       bigint                    DEFAULT NULL,
    last_update     timestamp        NULL     DEFAULT NULL,
    description     text,
    excluded        smallint                  DEFAULT NULL,
    share_type_id   smallint                  DEFAULT NULL,
    protect_id      bigint           NOT NULL DEFAULT '1'
);

COMMENT ON TABLE user_value_prime is 'the user specific changes of the most often used values';
COMMENT ON COLUMN user_value_prime.group_id is 'temp field to increase speed created by the value term links';
COMMENT ON COLUMN user_value_prime.user_id is 'the owner / creator of the value';
COMMENT ON COLUMN user_value_prime.last_update is 'for fast recalculation';
COMMENT ON COLUMN user_value_prime.description is 'temp field used during dev phase for easy value to trm assigns';
COMMENT ON COLUMN user_value_prime.excluded is 'the default exclude setting for most users';

-- --------------------------------------------------------

--
-- Table structure for values related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS value_big
(
    group_id        TEXT NOT NULL,
    user_id         bigint                    DEFAULT NULL,
    numeric_value   double precision NOT NULL,
    source_id       bigint                    DEFAULT NULL,
    last_update     timestamp        NULL     DEFAULT NULL,
    description     text,
    excluded        smallint                  DEFAULT NULL,
    share_type_id   smallint                  DEFAULT NULL,
    protect_id      bigint           NOT NULL DEFAULT '1'
);

COMMENT ON TABLE value_big is 'for numeric values related to more than 16 phrases';
COMMENT ON COLUMN value_big.group_id is 'temp field to increase speed created by the value term links';
COMMENT ON COLUMN value_big.user_id is 'the owner / creator of the value';
COMMENT ON COLUMN value_big.last_update is 'for fast recalculation';
COMMENT ON COLUMN value_big.description is 'temp field used during dev phase for easy value to trm assigns';
COMMENT ON COLUMN value_big.excluded is 'the default exclude setting for most users';

--
-- Table structure to store the user specific changes of values related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS user_value_big
(
    group_id        TEXT NOT NULL,
    user_id         bigint                    DEFAULT NULL,
    numeric_value   double precision NOT NULL,
    source_id       bigint                    DEFAULT NULL,
    last_update     timestamp        NULL     DEFAULT NULL,
    description     text,
    excluded        smallint                  DEFAULT NULL,
    share_type_id   smallint                  DEFAULT NULL,
    protect_id      bigint           NOT NULL DEFAULT '1'
);

COMMENT ON TABLE user_value_big is 'the user specific changes of numeric values related to more than 16 phrases';
COMMENT ON COLUMN user_value_big.group_id is 'temp field to increase speed created by the value term links';
COMMENT ON COLUMN user_value_big.user_id is 'the owner / creator of the value';
COMMENT ON COLUMN user_value_big.last_update is 'for fast recalculation';
COMMENT ON COLUMN user_value_big.description is 'temp field used during dev phase for easy value to trm assigns';
COMMENT ON COLUMN user_value_big.excluded is 'the default exclude setting for most users';

-- --------------------------------------------------------

--
-- Table structure for text values where the text might be long and where the text is expected to be never user in a search
--

CREATE TABLE IF NOT EXISTS value_text
(
    group_id        char(112) PRIMARY KEY,
    text_value      text NOT NULL,
    user_id         bigint                    DEFAULT NULL,
    source_id       bigint                    DEFAULT NULL,
    description     text,
    excluded        smallint                  DEFAULT NULL,
    last_update     timestamp        NULL     DEFAULT NULL,
    share_type_id   smallint                  DEFAULT NULL,
    protect_id      bigint           NOT NULL DEFAULT '1'
);

COMMENT ON TABLE value_text is 'for the most often used text values';
COMMENT ON COLUMN value_text.group_id is 'the prime index to find the values';
COMMENT ON COLUMN value_text.user_id is 'the owner / creator of the value';
COMMENT ON COLUMN value_text.last_update is 'for fast recalculation';
COMMENT ON COLUMN value_text.description is 'temp field used during dev phase for easy value to trm assigns';
COMMENT ON COLUMN value_text.excluded is 'the default exclude setting for most users';

--
-- Table structure to store the user specific changes of text values where the text might be long and where the text is expected to be never user in a search
--

CREATE TABLE IF NOT EXISTS user_value_text
(
    group_id        char(112) NOT NULL,
    user_id         bigint                    DEFAULT NULL,
    text_value      text NOT NULL,
    source_id       bigint                    DEFAULT NULL,
    description     text,
    excluded        smallint                  DEFAULT NULL,
    last_update     timestamp        NULL     DEFAULT NULL,
    share_type_id   smallint                  DEFAULT NULL,
    protect_id      bigint           NOT NULL DEFAULT '1'
);

COMMENT ON TABLE user_value_text is 'to store the user specific changes of the most often used text values';
COMMENT ON COLUMN user_value_text.group_id is 'the prime index to find the values';
COMMENT ON COLUMN user_value_text.user_id is 'the owner / creator of the value';
COMMENT ON COLUMN user_value_text.last_update is 'for fast recalculation';
COMMENT ON COLUMN user_value_text.description is 'temp field used during dev phase for easy value to trm assigns';
COMMENT ON COLUMN user_value_text.excluded is 'the default exclude setting for most users';

-- --------------------------------------------------------

--
-- Table structure for time values where the time is expected to be never user in a search
--

CREATE TABLE IF NOT EXISTS value_time
(
    group_id        char(112) PRIMARY KEY,
    time_value      timestamp NOT NULL,
    user_id         bigint                    DEFAULT NULL,
    source_id       bigint                    DEFAULT NULL,
    description     text,
    excluded        smallint                  DEFAULT NULL,
    last_update     timestamp        NULL     DEFAULT NULL,
    share_type_id   smallint                  DEFAULT NULL,
    protect_id      bigint           NOT NULL DEFAULT '1'
);

COMMENT ON TABLE value_time is 'for the most often used time values';
COMMENT ON COLUMN value_time.group_id is 'the prime index to find the values';
COMMENT ON COLUMN value_time.user_id is 'the owner / creator of the value';
COMMENT ON COLUMN value_time.last_update is 'for fast recalculation';
COMMENT ON COLUMN value_time.description is 'temp field used during dev phase for easy value to trm assigns';
COMMENT ON COLUMN value_time.excluded is 'the default exclude setting for most users';

--
-- Table structure to store the user specific changes of time values where the time is expected to be never user in a search
--

CREATE TABLE IF NOT EXISTS user_value_time
(
    group_id        char(112) NOT NULL,
    user_id         bigint                    DEFAULT NULL,
    time_value      timestamp NOT NULL,
    source_id       bigint                    DEFAULT NULL,
    description     text,
    excluded        smallint                  DEFAULT NULL,
    last_update     timestamp        NULL     DEFAULT NULL,
    share_type_id   smallint                  DEFAULT NULL,
    protect_id      bigint           NOT NULL DEFAULT '1'
);

COMMENT ON TABLE user_value_time is 'to store the user specific changes of the most often used time values';
COMMENT ON COLUMN user_value_time.group_id is 'the prime index to find the values';
COMMENT ON COLUMN user_value_time.user_id is 'the owner / creator of the value';
COMMENT ON COLUMN user_value_time.last_update is 'for fast recalculation';
COMMENT ON COLUMN user_value_time.description is 'temp field used during dev phase for easy value to trm assigns';
COMMENT ON COLUMN user_value_time.excluded is 'the default exclude setting for most users';

-- --------------------------------------------------------

--
-- Table structure for geo location values
--

CREATE TABLE IF NOT EXISTS value_geo
(
    group_id        char(112) PRIMARY KEY,
    geo_value       point NOT NULL,
    user_id         bigint                    DEFAULT NULL,
    source_id       bigint                    DEFAULT NULL,
    description     text,
    excluded        smallint                  DEFAULT NULL,
    last_update     timestamp        NULL     DEFAULT NULL,
    share_type_id   smallint                  DEFAULT NULL,
    protect_id      bigint           NOT NULL DEFAULT '1'
);

COMMENT ON TABLE value_geo is 'for the most often used geo location values';
COMMENT ON COLUMN value_geo.group_id is 'the prime index to find the values';
COMMENT ON COLUMN value_geo.user_id is 'the owner / creator of the value';
COMMENT ON COLUMN value_geo.last_update is 'for fast recalculation';
COMMENT ON COLUMN value_geo.description is 'temp field used during dev phase for easy value to trm assigns';
COMMENT ON COLUMN value_geo.excluded is 'the default exclude setting for most users';

--
-- Table structure to store the user specific changes of geo location values
--

CREATE TABLE IF NOT EXISTS user_value_geo
(
    group_id        char(112) NOT NULL,
    user_id         bigint                    DEFAULT NULL,
    geo_value       point NOT NULL,
    source_id       bigint                    DEFAULT NULL,
    description     text,
    excluded        smallint                  DEFAULT NULL,
    last_update     timestamp        NULL     DEFAULT NULL,
    share_type_id   smallint                  DEFAULT NULL,
    protect_id      bigint           NOT NULL DEFAULT '1'
);

COMMENT ON TABLE user_value_geo is 'to store the user specific changes of the most often used geo location values';
COMMENT ON COLUMN user_value_geo.group_id is 'the prime index to find the values';
COMMENT ON COLUMN user_value_geo.user_id is 'the owner / creator of the value';
COMMENT ON COLUMN user_value_geo.last_update is 'for fast recalculation';
COMMENT ON COLUMN user_value_geo.description is 'temp field used during dev phase for easy value to trm assigns';
COMMENT ON COLUMN user_value_geo.excluded is 'the default exclude setting for most users';

-- --------------------------------------------------------
