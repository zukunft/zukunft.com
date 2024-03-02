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
-- Table structure to log the value changes done by the users
--

CREATE TABLE IF NOT EXISTS change_values
(
    change_id        BIGSERIAL PRIMARY KEY,
    change_time      timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id          bigint    NOT NULL,
    change_action_id bigint    NOT NULL,
    change_field_id  bigint    NOT NULL,
    group_id         char(112) NOT NULL,
    old_value        double precision DEFAULT NULL,
    new_value        double precision DEFAULT NULL
);

COMMENT ON TABLE change_values is 'to log all changes';
COMMENT ON COLUMN change_values.change_time is 'time when the value has been changed';

--
-- Table structure to log the value changes done by the users
--

CREATE TABLE IF NOT EXISTS change_values_prime
(
    change_id        BIGSERIAL PRIMARY KEY,
    change_time      timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id          bigint    NOT NULL,
    change_action_id bigint    NOT NULL,
    change_field_id  bigint    NOT NULL,
    group_id         bigint    NOT NULL,
    old_value        double precision DEFAULT NULL,
    new_value        double precision DEFAULT NULL
);

COMMENT ON TABLE change_values_prime is 'to log changes of prime value';
COMMENT ON COLUMN change_values_prime.change_time is 'time when the value has been changed';

--
-- Table structure to log the value changes done by the users
--

CREATE TABLE IF NOT EXISTS change_values_big
(
    change_id        BIGSERIAL PRIMARY KEY,
    change_time      timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id          bigint    NOT NULL,
    change_action_id bigint    NOT NULL,
    change_field_id  bigint    NOT NULL,
    group_id         TEXT      NOT NULL,
    old_value        double precision DEFAULT NULL,
    new_value        double precision DEFAULT NULL
);

COMMENT ON TABLE change_values_big is 'to log all changes';
COMMENT ON COLUMN change_values_big.change_time is 'time when the value has been changed';

-- --------------------------------------------------------

--
-- Table structure for phrase group names
--

CREATE TABLE IF NOT EXISTS groups
(
    group_id    char(112) PRIMARY KEY,
    group_name  varchar(1000) DEFAULT NULL,
    description varchar(4000) DEFAULT NULL
);

COMMENT ON TABLE groups is 'to add a user given name using a 512 bit group id index for up to 16 16 bit phrase ids including the order';
COMMENT ON COLUMN groups.group_name is 'the name given by a user to display the group (does not need to be unique))';
COMMENT ON COLUMN groups.description is 'the description of the group given by a user';

--
-- Table structure for saving a user specific group name
--

CREATE TABLE IF NOT EXISTS user_groups
(
    group_id    char(112) PRIMARY KEY,
    user_id     bigint NOT NULL,
    group_name  varchar(1000) DEFAULT NULL,
    description varchar(4000) DEFAULT NULL
);

COMMENT ON TABLE user_groups is 'to link the user specific name to the standard group';
COMMENT ON COLUMN user_groups.group_name is 'the user specific group name which can contain the phrase names in a different order';
COMMENT ON COLUMN user_groups.description is 'the user specific description for mouse over helps';

--
-- Table structure for phrase group names of up to four prime phrases
--

CREATE TABLE IF NOT EXISTS groups_prime
(
    group_id    BIGSERIAL PRIMARY KEY,
    group_name  varchar(1000) DEFAULT NULL,
    description varchar(4000) DEFAULT NULL
);

COMMENT ON TABLE groups_prime is 'to add a user given name using a 64 bit bigint group id index for up to four 16 bit phrase ids including the order';
COMMENT ON COLUMN groups_prime.group_name is 'the name given by a user to display the group (does not need to be unique))';
COMMENT ON COLUMN groups_prime.description is 'the description of the group given by a user';

--
-- Table structure for saving a user specific group name for up to four prime phrases
--

CREATE TABLE IF NOT EXISTS user_groups_prime
(
    group_id    BIGSERIAL PRIMARY KEY,
    user_id     bigint NOT NULL,
    group_name  varchar(1000) DEFAULT NULL,
    description varchar(4000) DEFAULT NULL
);

COMMENT ON TABLE user_groups_prime is 'to link the user specific name to the group';
COMMENT ON COLUMN user_groups_prime.group_name is 'the user specific group name which can contain the phrase names in a different order';
COMMENT ON COLUMN user_groups_prime.description is 'the user specific description for mouse over helps';

--
-- Table structure for phrase group names of more than 16 phrases
--

CREATE TABLE IF NOT EXISTS groups_big
(
    group_id    text PRIMARY KEY,
    group_name  varchar(1000) DEFAULT NULL,
    description varchar(4000) DEFAULT NULL
);

COMMENT ON TABLE groups_big is 'to add a user given name using text group id index for an almost unlimited number of phrase ids including the order';
COMMENT ON COLUMN groups_big.group_name is 'the name given by a user to display the group (does not need to be unique))';
COMMENT ON COLUMN groups_big.description is 'the description of the group given by a user';

--
-- Table structure for saving a user specific group name for more than 16 phrases
--

CREATE TABLE IF NOT EXISTS user_groups_big
(
    group_id    text PRIMARY KEY,
    user_id     bigint NOT NULL,
    group_name  varchar(1000) DEFAULT NULL,
    description varchar(4000) DEFAULT NULL
);

COMMENT ON TABLE user_groups_big is 'to link the user specific name to the group';
COMMENT ON COLUMN user_groups_big.group_name is 'the user specific group name which can contain the phrase names in a different order';
COMMENT ON COLUMN user_groups_big.description is 'the user specific description for mouse over helps';

--
-- Table structure to link phrases to a group
-- TODO add prime index
--

--
-- Table structure to link phrases to a group
-- TODO add prime index
--

CREATE TABLE IF NOT EXISTS group_link
(
    group_id  char(112) NOT NULL,
    phrase_id bigint NOT NULL
);

COMMENT ON TABLE group_link is 'link phrases to a phrase group for database based selections';

--
-- Table structure to store user specific ex- or includes of single link of phrases to groups
--

CREATE TABLE IF NOT EXISTS user_group_link
(
    group_id  char(112) NOT NULL,
    phrase_id bigint    NOT NULL,
    user_id   bigint    DEFAULT NULL,
    excluded  smallint  DEFAULT NULL
);

COMMENT ON TABLE user_group_link is 'to ex- or include user specific link to the standard group';

--
-- Table structure to link up to four prime phrases to a group
--

CREATE TABLE IF NOT EXISTS groups_prime_link
(
    group_id  BIGSERIAL,
    phrase_id bigint NOT NULL
);

COMMENT ON TABLE groups_prime_link is 'link phrases to a short phrase group for database based selections';

--
-- Table structure for user specific links of up to four prime phrases per group
--

CREATE TABLE IF NOT EXISTS user_groups_prime_link
(
    group_id  BIGSERIAL,
    phrase_id bigint    NOT NULL,
    user_id   bigint    DEFAULT NULL,
    excluded  smallint  DEFAULT NULL
);

COMMENT ON TABLE user_groups_prime_link is 'user specific link to groups with up to four prime phrase';

--
-- Table structure to link up more than 16 phrases to a group
--

CREATE TABLE IF NOT EXISTS groups_big_link
(
    group_id  text,
    phrase_id bigint NOT NULL
);

COMMENT ON TABLE groups_big_link is 'link phrases to a long phrase group for database based selections';

--
-- Table structure for user specific links for more than 16 phrases per group
--

CREATE TABLE IF NOT EXISTS user_groups_big_link
(
    group_id  text,
    phrase_id bigint   NOT NULL,
    user_id   bigint   DEFAULT NULL,
    excluded  smallint DEFAULT NULL
);

COMMENT ON TABLE user_groups_big_link is 'to ex- or include user specific link to the standard group';

-- --------------------------------------------------------

--
-- Table structure for public values related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS value_standard_prime
(
    group_id      BIGSERIAL NOT NULL,
    numeric_value double precision NOT NULL,
    source_id     int DEFAULT NULL
);

COMMENT ON TABLE value_standard_prime is 'for public unprotected values related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN value_standard_prime.group_id is 'the prime index to find the value';
COMMENT ON COLUMN value_standard_prime.source_id is 'the prime source';

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
-- Table structure for values
--

CREATE TABLE IF NOT EXISTS values
(
    group_id        char(112) PRIMARY KEY,
    numeric_value   double precision NOT NULL,
    user_id         bigint                    DEFAULT NULL,
    source_id       bigint                    DEFAULT NULL,
    description     text,
    excluded        smallint                  DEFAULT NULL,
    last_update     timestamp        NULL     DEFAULT NULL,
    share_type_id   smallint                  DEFAULT NULL,
    protect_id      bigint           NOT NULL DEFAULT '1'
);

COMMENT ON TABLE values is 'for numeric values related to up to 16 phrases';
COMMENT ON COLUMN values.group_id is 'the prime index to find the values';
COMMENT ON COLUMN values.user_id is 'the owner / creator of the value';
COMMENT ON COLUMN values.last_update is 'for fast recalculation';
COMMENT ON COLUMN values.description is 'temp field used during dev phase for easy value to trm assigns';
COMMENT ON COLUMN values.excluded is 'the default exclude setting for most users';

--
-- Table structure for table user_values
--

CREATE TABLE IF NOT EXISTS user_values
(
    group_id      char(112) NOT NULL,
    user_id       bigint    NOT NULL,
    numeric_value double precision DEFAULT NULL,
    source_id     bigint           DEFAULT NULL,
    excluded      smallint         DEFAULT NULL,
    last_update   timestamp NULL   DEFAULT NULL,
    share_type_id bigint           DEFAULT NULL,
    protect_id    bigint           DEFAULT NULL
);

COMMENT ON TABLE user_values is 'for quick access to the user specific values';
COMMENT ON COLUMN user_values.last_update is 'for fast calculation of the updates';

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

--
-- Table structure for public text values related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS value_text_standard_prime
(
    group_id   BIGSERIAL NOT NULL,
    text_value text NOT NULL,
    source_id  int DEFAULT NULL
);

COMMENT ON TABLE value_text_standard_prime is 'for public unprotected text values related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN value_text_standard_prime.group_id is 'the prime index to find the value';

--
-- Table structure for public text values that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS value_text_standard
(
    group_id   char(112) PRIMARY KEY,
    text_value text NOT NULL,
    source_id  bigint DEFAULT NULL
);

COMMENT ON TABLE value_text_standard is 'for public unprotected text values that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN value_text_standard.group_id is 'the prime index to find the value';

--
-- Table structure for the most often requested text values related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS value_text_prime
(
    group_id        BIGSERIAL PRIMARY KEY,
    user_id         bigint                    DEFAULT NULL,
    text_value      text NOT NULL,
    source_id       bigint                    DEFAULT NULL,
    description     text,
    excluded        smallint                  DEFAULT NULL,
    share_type_id   smallint                  DEFAULT NULL,
    protect_id      bigint           NOT NULL DEFAULT '1'
);

COMMENT ON TABLE value_text_prime is 'for the most often used values';
COMMENT ON COLUMN value_text_prime.group_id is 'temp field to increase speed created by the value term links';
COMMENT ON COLUMN value_text_prime.user_id is 'the owner / creator of the value';
COMMENT ON COLUMN value_text_prime.description is 'temp field used during dev phase for easy value to trm assigns';
COMMENT ON COLUMN value_text_prime.excluded is 'the default exclude setting for most users';

--
-- Table structure to store the user specific changes for the most often requested values related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS user_value_text_prime
(
    group_id        BIGSERIAL NOT NULL,
    user_id         bigint                    DEFAULT NULL,
    text_value      text NOT NULL,
    source_id       bigint                    DEFAULT NULL,
    description     text,
    excluded        smallint                  DEFAULT NULL,
    share_type_id   smallint                  DEFAULT NULL,
    protect_id      bigint           NOT NULL DEFAULT '1'
);

COMMENT ON TABLE user_value_text_prime is 'the user specific changes of the most often used values';
COMMENT ON COLUMN user_value_text_prime.group_id is 'temp field to increase speed created by the value term links';
COMMENT ON COLUMN user_value_text_prime.user_id is 'the owner / creator of the value';
COMMENT ON COLUMN user_value_text_prime.description is 'temp field used during dev phase for easy value to trm assigns';
COMMENT ON COLUMN user_value_text_prime.excluded is 'the default exclude setting for most users';

--
-- Table structure for the most often requested text values related up to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS value_text_big
(
    group_id        TEXT NOT NULL,
    user_id         bigint                    DEFAULT NULL,
    text_value      text NOT NULL,
    source_id       bigint                    DEFAULT NULL,
    description     text,
    excluded        smallint                  DEFAULT NULL,
    share_type_id   smallint                  DEFAULT NULL,
    protect_id      bigint           NOT NULL DEFAULT '1'
);

COMMENT ON TABLE value_text_big is 'for the most often used values';
COMMENT ON COLUMN value_text_big.group_id is 'the prime index to find the values';
COMMENT ON COLUMN value_text_big.user_id is 'the owner / creator of the value';
COMMENT ON COLUMN value_text_big.description is 'temp field used during dev phase for easy value to trm assigns';
COMMENT ON COLUMN value_text_big.excluded is 'the default exclude setting for most users';

--
-- Table structure to store the user specific changes for the most often requested values related up to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS user_value_text_big
(
    group_id        TEXT NOT NULL,
    user_id         bigint                    DEFAULT NULL,
    text_value      text NOT NULL,
    source_id       bigint                    DEFAULT NULL,
    description     text,
    excluded        smallint                  DEFAULT NULL,
    share_type_id   smallint                  DEFAULT NULL,
    protect_id      bigint           NOT NULL DEFAULT '1'
);

COMMENT ON TABLE user_value_text_big is 'the user specific changes of the most often used values';
COMMENT ON COLUMN user_value_text_big.group_id is 'the prime index to find the values';
COMMENT ON COLUMN user_value_text_big.user_id is 'the owner / creator of the value';
COMMENT ON COLUMN user_value_text_big.description is 'temp field used during dev phase for easy value to trm assigns';
COMMENT ON COLUMN user_value_text_big.excluded is 'the default exclude setting for most users';

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

--
-- Table structure for public time values related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS value_time_standard_prime
(
    group_id   BIGSERIAL NOT NULL,
    time_value timestamp NOT NULL,
    source_id  int DEFAULT NULL
);

COMMENT ON TABLE value_time_standard_prime is 'for public unprotected time values related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN value_time_standard_prime.group_id is 'the prime index to find the value';

--
-- Table structure for public time values that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS value_time_standard
(
    group_id   char(112) PRIMARY KEY,
    time_value timestamp NOT NULL,
    source_id  bigint DEFAULT NULL
);

COMMENT ON TABLE value_time_standard is 'for public unprotected time values that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN value_time_standard.group_id is 'the prime index to find the value';

--
-- Table structure for time values where the time is expected to be never user in a search related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS value_time_prime
(
    group_id        BIGSERIAL PRIMARY KEY,
    time_value      timestamp NOT NULL,
    user_id         bigint                    DEFAULT NULL,
    source_id       bigint                    DEFAULT NULL,
    description     text,
    excluded        smallint                  DEFAULT NULL,
    last_update     timestamp        NULL     DEFAULT NULL,
    share_type_id   smallint                  DEFAULT NULL,
    protect_id      bigint           NOT NULL DEFAULT '1'
);

COMMENT ON TABLE value_time_prime is 'for the most often used time values';
COMMENT ON COLUMN value_time_prime.group_id is 'the prime index to find the values';
COMMENT ON COLUMN value_time_prime.user_id is 'the owner / creator of the value';
COMMENT ON COLUMN value_time_prime.last_update is 'for fast recalculation';
COMMENT ON COLUMN value_time_prime.description is 'temp field used during dev phase for easy value to trm assigns';
COMMENT ON COLUMN value_time_prime.excluded is 'the default exclude setting for most users';

--
-- Table structure to store the user specific changes of time values where the time is expected to be never user in a search related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS user_value_time_prime
(
    group_id        BIGSERIAL PRIMARY KEY,
    user_id         bigint                    DEFAULT NULL,
    time_value      timestamp NOT NULL,
    source_id       bigint                    DEFAULT NULL,
    description     text,
    excluded        smallint                  DEFAULT NULL,
    last_update     timestamp        NULL     DEFAULT NULL,
    share_type_id   smallint                  DEFAULT NULL,
    protect_id      bigint           NOT NULL DEFAULT '1'
);

COMMENT ON TABLE user_value_time_prime is 'to store the user specific changes of the most often used time values';
COMMENT ON COLUMN user_value_time_prime.group_id is 'the prime index to find the values';
COMMENT ON COLUMN user_value_time_prime.user_id is 'the owner / creator of the value';
COMMENT ON COLUMN user_value_time_prime.last_update is 'for fast recalculation';
COMMENT ON COLUMN user_value_time_prime.description is 'temp field used during dev phase for easy value to trm assigns';
COMMENT ON COLUMN user_value_time_prime.excluded is 'the default exclude setting for most users';

--
-- Table structure for time values where the time is expected to be never user in a search related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS value_time_big
(
    group_id        TEXT NOT NULL,
    time_value      timestamp NOT NULL,
    user_id         bigint                    DEFAULT NULL,
    source_id       bigint                    DEFAULT NULL,
    description     text,
    excluded        smallint                  DEFAULT NULL,
    last_update     timestamp        NULL     DEFAULT NULL,
    share_type_id   smallint                  DEFAULT NULL,
    protect_id      bigint           NOT NULL DEFAULT '1'
);

COMMENT ON TABLE value_time_big is 'for the most often used time values';
COMMENT ON COLUMN value_time_big.group_id is 'the prime index to find the values';
COMMENT ON COLUMN value_time_big.user_id is 'the owner / creator of the value';
COMMENT ON COLUMN value_time_big.last_update is 'for fast recalculation';
COMMENT ON COLUMN value_time_big.description is 'temp field used during dev phase for easy value to trm assigns';
COMMENT ON COLUMN value_time_big.excluded is 'the default exclude setting for most users';

--
-- Table structure to store the user specific changes of time values where the time is expected to be never user in a search related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS user_value_time_big
(
    group_id        TEXT NOT NULL,
    user_id         bigint                    DEFAULT NULL,
    time_value      timestamp NOT NULL,
    source_id       bigint                    DEFAULT NULL,
    description     text,
    excluded        smallint                  DEFAULT NULL,
    last_update     timestamp        NULL     DEFAULT NULL,
    share_type_id   smallint                  DEFAULT NULL,
    protect_id      bigint           NOT NULL DEFAULT '1'
);

COMMENT ON TABLE user_value_time_big is 'to store the user specific changes of the most often used time values';
COMMENT ON COLUMN user_value_time_big.group_id is 'the prime index to find the values';
COMMENT ON COLUMN user_value_time_big.user_id is 'the owner / creator of the value';
COMMENT ON COLUMN user_value_time_big.last_update is 'for fast recalculation';
COMMENT ON COLUMN user_value_time_big.description is 'temp field used during dev phase for easy value to trm assigns';
COMMENT ON COLUMN user_value_time_big.excluded is 'the default exclude setting for most users';

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
-- Table structure to store the user specific changes of geo locations
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

--
-- Table structure for public geo location values related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS value_geo_standard_prime
(
    group_id  BIGSERIAL NOT NULL,
    geo_value point NOT NULL,
    source_id  int DEFAULT NULL
);

COMMENT ON TABLE value_geo_standard_prime is 'for public unprotected geo locations related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN value_geo_standard_prime.group_id is 'the prime index to find the geo location';

--
-- Table structure for public geo location values that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS value_geo_standard
(
    group_id  char(112) PRIMARY KEY,
    geo_value point NOT NULL,
    source_id  bigint DEFAULT NULL
);

COMMENT ON TABLE value_geo_standard is 'for public unprotected geo locations that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN value_geo_standard.group_id is 'the prime index to find the geo location';

--
-- Table structure for geo location values related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS value_geo_prime
(
    group_id        BIGSERIAL NOT NULL,
    geo_value       point NOT NULL,
    user_id         bigint                    DEFAULT NULL,
    source_id       bigint                    DEFAULT NULL,
    description     text,
    excluded        smallint                  DEFAULT NULL,
    last_update     timestamp        NULL     DEFAULT NULL,
    share_type_id   smallint                  DEFAULT NULL,
    protect_id      bigint           NOT NULL DEFAULT '1'
);

COMMENT ON TABLE value_geo_prime is 'for the most often used geo location values';
COMMENT ON COLUMN value_geo_prime.group_id is 'the prime index to find the geo locations';
COMMENT ON COLUMN value_geo_prime.user_id is 'the owner / creator of the value';
COMMENT ON COLUMN value_geo_prime.last_update is 'for fast recalculation';
COMMENT ON COLUMN value_geo_prime.description is 'temp field used during dev phase for easy value to trm assigns';
COMMENT ON COLUMN value_geo_prime.excluded is 'the default exclude setting for most users';

--
-- Table structure to store the user specific changes of geo locations related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS user_value_geo_prime
(
    group_id        BIGSERIAL NOT NULL,
    user_id         bigint                    DEFAULT NULL,
    geo_value       point NOT NULL,
    source_id       bigint                    DEFAULT NULL,
    description     text,
    excluded        smallint                  DEFAULT NULL,
    last_update     timestamp        NULL     DEFAULT NULL,
    share_type_id   smallint                  DEFAULT NULL,
    protect_id      bigint           NOT NULL DEFAULT '1'
);

COMMENT ON TABLE user_value_geo_prime is 'to store the user specific changes of the most often used geo location values';
COMMENT ON COLUMN user_value_geo_prime.group_id is 'the prime index to find the geo locations';
COMMENT ON COLUMN user_value_geo_prime.user_id is 'the owner / creator of the value';
COMMENT ON COLUMN user_value_geo_prime.last_update is 'for fast recalculation';
COMMENT ON COLUMN user_value_geo_prime.description is 'temp field used during dev phase for easy value to trm assigns';
COMMENT ON COLUMN user_value_geo_prime.excluded is 'the default exclude setting for most users';

--
-- Table structure for geo location values related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS value_geo_big
(
    group_id        BIGSERIAL NOT NULL,
    geo_value       point NOT NULL,
    user_id         bigint                    DEFAULT NULL,
    source_id       bigint                    DEFAULT NULL,
    description     text,
    excluded        smallint                  DEFAULT NULL,
    last_update     timestamp        NULL     DEFAULT NULL,
    share_type_id   smallint                  DEFAULT NULL,
    protect_id      bigint           NOT NULL DEFAULT '1'
);

COMMENT ON TABLE value_geo_big is 'for the most often used geo location values';
COMMENT ON COLUMN value_geo_big.group_id is 'the prime index to find the geo locations';
COMMENT ON COLUMN value_geo_big.user_id is 'the owner / creator of the value';
COMMENT ON COLUMN value_geo_big.last_update is 'for fast recalculation';
COMMENT ON COLUMN value_geo_big.description is 'temp field used during dev phase for easy value to trm assigns';
COMMENT ON COLUMN value_geo_big.excluded is 'the default exclude setting for most users';

--
-- Table structure to store the user specific changes of geo locations related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS user_value_geo_big
(
    group_id        BIGSERIAL NOT NULL,
    user_id         bigint                    DEFAULT NULL,
    geo_value       point NOT NULL,
    source_id       bigint                    DEFAULT NULL,
    description     text,
    excluded        smallint                  DEFAULT NULL,
    last_update     timestamp        NULL     DEFAULT NULL,
    share_type_id   smallint                  DEFAULT NULL,
    protect_id      bigint           NOT NULL DEFAULT '1'
);

COMMENT ON TABLE user_value_geo_big is 'to store the user specific changes of the most often used geo location values';
COMMENT ON COLUMN user_value_geo_big.group_id is 'the prime index to find the geo locations';
COMMENT ON COLUMN user_value_geo_big.user_id is 'the owner / creator of the value';
COMMENT ON COLUMN user_value_geo_big.last_update is 'for fast recalculation';
COMMENT ON COLUMN user_value_geo_big.description is 'temp field used during dev phase for easy value to trm assigns';
COMMENT ON COLUMN user_value_geo_big.excluded is 'the default exclude setting for most users';

-- --------------------------------------------------------

alter table value_time_series
    rename column phrase_group_id to group_id;

alter table value_time_series
    alter column group_id type char(112) using group_id::char(112);

-- --------------------------------------------------------

--
-- Table structure to remember which phrases are store in which table and pod
--

CREATE TABLE IF NOT EXISTS phrase_tables
(
    table_id   BIGSERIAL PRIMARY KEY,
    phrase_id  bigint   NOT NULL,
    pod_url    text     NOT NULL,
    active     smallint DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Table structure for standard results
--

CREATE TABLE IF NOT EXISTS result_standard_prime
(
    group_id BIGSERIAL PRIMARY KEY,
    result   double precision
);

COMMENT ON TABLE result_standard is 'table to cache the pure formula results related up to four prime phrase without any related information';
COMMENT ON COLUMN result_standard_prime.group_id is 'the prime index to find the results';

--
-- Table structure for standard results
--

CREATE TABLE IF NOT EXISTS result_standard
(
    group_id char(112) PRIMARY KEY,
    result   double precision
);

COMMENT ON TABLE result_standard is 'table to cache the pure formula results without any related information';
COMMENT ON COLUMN result_standard.group_id is 'the prime index to find the results';

--
-- Table structure for the most often requested results related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS result_prime
(
    group_id        BIGSERIAL PRIMARY KEY,
    result          double precision,
    formula_id      bigint         NOT NULL,
    source_group_id bigint         DEFAULT NULL,
    user_id         bigint         DEFAULT NULL,
    last_update     timestamp NULL DEFAULT NULL
);

COMMENT ON TABLE result_prime is 'table to cache the formula results related up to four prime phrases';
COMMENT ON COLUMN result_prime.group_id is 'the prime index to find the results';
COMMENT ON COLUMN result_prime.formula_id is 'the id of the formula which has been used to calculate the result number';
COMMENT ON COLUMN result_prime.source_group_id is 'the sorted phrase list used to calculate the result number';
COMMENT ON COLUMN result_prime.user_id is 'the id of the user who has requested the calculation';
COMMENT ON COLUMN result_prime.last_update is 'time of last value update mainly used for recovery in case of inconsistencies, empty in case this value is dirty and needs to be updated';

--
-- Table structure for results related more than 16 phrases
--

CREATE TABLE IF NOT EXISTS result_big
(
    group_id        TEXT PRIMARY KEY,
    result          double precision,
    formula_id      bigint         NOT NULL,
    source_group_id TEXT           DEFAULT NULL,
    user_id         bigint         DEFAULT NULL,
    last_update     timestamp NULL DEFAULT NULL
);

COMMENT ON TABLE result_big is 'table to cache the formula results related up to four prime phrases';
COMMENT ON COLUMN result_big.group_id is 'the prime index to find the results';
COMMENT ON COLUMN result_big.formula_id is 'the id of the formula which has been used to calculate the result number';
COMMENT ON COLUMN result_big.source_group_id is 'the sorted phrase list used to calculate the result number';
COMMENT ON COLUMN result_big.user_id is 'the id of the user who has requested the calculation';
COMMENT ON COLUMN result_big.last_update is 'time of last value update mainly used for recovery in case of inconsistencies, empty in case this value is dirty and needs to be updated';

-- --------------------------------------------------------

alter table results
    drop column group_id;

alter table results
    rename column source_phrase_group_id to source_group_id;

alter table results
    alter column source_group_id type char(112) using source_group_id::char(112);

alter table results
    rename column phrase_group_id to group_id;

alter table results
    alter column group_id type char(112) using group_id::char(112);

alter table results
    drop column dirty;

drop index formula_group_idx;

alter table results
    drop column source_time_id;

create unique index formula_group_idx
    on results (formula_id, user_id, phrase_group_id, source_phrase_group_id, source_time_id);

-- --------------------------------------------------------

--
-- Indexes for table groups
--
CREATE UNIQUE INDEX group_name_idx ON groups (group_name);

--
-- Indexes for table user_groups
--
CREATE UNIQUE INDEX user_group_name_idx ON user_groups (group_name, user_id);
CREATE INDEX user_group_idx ON user_groups (group_id);
CREATE INDEX user_group_user_idx ON user_groups (user_id);

--
-- Indexes for table prime groups
--
CREATE UNIQUE INDEX groups_prime_name_idx ON groups_prime (group_name);

--
-- Indexes for table user_groups
--
CREATE UNIQUE INDEX user_groups_prime_name_idx ON user_groups_prime (group_name, user_id);
CREATE INDEX user_groups_prime_idx ON user_groups (group_id);
CREATE INDEX user_groups_prime_user_idx ON user_groups (user_id);

--
-- Indexes for table big groups
--
CREATE UNIQUE INDEX groups_big_name_idx ON groups_big (group_name);

--
-- Indexes for table user_groups
--
CREATE UNIQUE INDEX user_groups_big_name_idx ON user_groups_big (group_name, user_id);
CREATE INDEX user_groups_big_idx ON user_groups (group_id);
CREATE INDEX user_groups_big_user_idx ON user_groups (user_id);

--
-- Indexes for table group_links
--
CREATE UNIQUE INDEX group_link_idx ON group_link (group_id, phrase_id);
CREATE INDEX group_link_phrase_idx ON group_link (phrase_id);

--
-- Indexes for table user_group_links
--
CREATE UNIQUE INDEX user_group_link_idx ON user_group_link (group_id, phrase_id, user_id);
CREATE INDEX user_group_link_phrase_idx ON user_group_link (phrase_id, user_id);

--
-- Indexes for table prime group links
--
CREATE UNIQUE INDEX groups_prime_link_idx ON groups_prime_link (group_id, phrase_id);
CREATE INDEX groups_prime_link_phrase_idx ON groups_prime_link (phrase_id);

--
-- Indexes for table prime user group links
--
CREATE UNIQUE INDEX user_groups_prime_link_idx ON user_groups_prime_link (group_id, phrase_id, user_id);
CREATE INDEX user_groups_prime_link_phrase_idx ON user_groups_prime_link (phrase_id, user_id);

--
-- Indexes for table big group links
--
CREATE UNIQUE INDEX groups_big_link_idx ON groups_big_link (group_id, phrase_id);
CREATE INDEX groups_big_link_phrase_idx ON groups_big_link (phrase_id);

--
-- Indexes for table big user group links
--
CREATE UNIQUE INDEX user_groups_big_link_idx ON user_groups_big_link (group_id, phrase_id, user_id);
CREATE INDEX user_groups_big_link_phrase_idx ON user_groups_big_link (phrase_id, user_id);

-- --------------------------------------------------------

drop view phrase_group_phrase_links;

drop view user_phrase_group_phrase_links;

-- --------------------------------------------------------

drop table phrase_group_triple_links cascade;;

drop table phrase_group_word_links cascade;;

drop table phrase_groups cascade;;

drop table user_phrase_group_triple_links cascade;;

drop table user_phrase_group_word_links cascade;;

drop table user_phrase_groups;

-- --------------------------------------------------------


