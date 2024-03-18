-- --------------------------------------------------------

--
-- structure for view prime_terms (terms with an id less than 2^16 so that 4 term id fit in a 64 bit db key)
--

CREATE OR REPLACE VIEW prime_terms AS
    SELECT w.word_id * 2 - 1 AS term_id,
           w.user_id,
           w.word_name       AS term_name,
           w.description,
           w.values          AS usage,
           w.phrase_type_id  AS term_type_id,
           w.excluded,
           w.share_type_id,
           w.protect_id,
           ''                AS formula_text,
           ''                AS resolved_text
      FROM words AS w
     WHERE (w.phrase_type_id <> 10 OR w.phrase_type_id IS NULL)
       AND w.word_id < 32767
UNION
    SELECT t.triple_id * -2 + 1       AS term_id,
           t.user_id,
           CASE WHEN (t.triple_name IS NULL) THEN
               CASE WHEN (t.name_given IS NULL) THEN
                   t.name_generated
               ELSE t.name_given END
           ELSE t.triple_name END AS term_name,
           t.description,
           t.values                   AS usage,
           t.phrase_type_id           AS term_type_id,
           t.excluded,
           t.share_type_id,
           t.protect_id,
           ''                         AS formula_text,
           ''                         AS resolved_text
      FROM triples AS t
     WHERE t.triple_id < 32767
UNION
    SELECT f.formula_id * 2  AS term_id,
           f.user_id,
           f.formula_name    AS term_name,
           f.description,
           f.usage           AS usage,
           f.formula_type_id AS term_type_id,
           f.excluded,
           f.share_type_id,
           f.protect_id,
           f.formula_text,
           f.resolved_text
      FROM formulas AS f
     WHERE f.formula_id < 32767
UNION
    SELECT v.verb_id * -2 AS term_id,
           NULL           AS user_id,
           v.verb_name    AS term_name,
           v.description,
           v.words        AS usage,
           NULL           AS term_type_id,
           NULL           AS excluded,
           1              AS share_type_id,
           3              AS protect_id,
           ''             AS formula_text,
           ''             AS resolved_text
      FROM verbs AS v
     WHERE v.verb_id < 32767;

--
-- structure for view terms (terms with an id that is not prime)
--

CREATE OR REPLACE VIEW terms AS
    SELECT w.word_id * 2 - 1 AS term_id,
           w.user_id,
           w.word_name       AS term_name,
           w.description,
           w.values          AS usage,
           w.phrase_type_id  AS term_type_id,
           w.excluded,
           w.share_type_id,
           w.protect_id,
           ''                AS formula_text,
           ''                AS resolved_text
      FROM words AS w
     WHERE (w.phrase_type_id <> 10 OR w.phrase_type_id IS NULL)
UNION
    SELECT t.triple_id * -2 + 1       AS term_id,
           t.user_id,
           CASE WHEN (t.triple_name IS NULL) THEN
               CASE WHEN (t.name_given IS NULL) THEN
                   t.name_generated
               ELSE t.name_given END
           ELSE t.triple_name END AS term_name,
           t.description,
           t.values                   AS usage,
           t.phrase_type_id           AS term_type_id,
           t.excluded,
           t.share_type_id,
           t.protect_id,
           ''                         AS formula_text,
           ''                         AS resolved_text
      FROM triples AS t
UNION
    SELECT f.formula_id * 2  AS term_id,
           f.user_id,
           f.formula_name    AS term_name,
           f.description,
           f.usage           AS usage,
           f.formula_type_id AS term_type_id,
           f.excluded,
           f.share_type_id,
           f.protect_id,
           f.formula_text,
           f.resolved_text
      FROM formulas AS f
UNION
    SELECT v.verb_id * -2 AS term_id,
           NULL           AS user_id,
           v.verb_name    AS term_name,
           v.description,
           v.words        AS usage,
           NULL           AS term_type_id,
           NULL           AS excluded,
           1              AS share_type_id,
           3              AS protect_id,
           ''             AS formula_text,
           ''             AS resolved_text
      FROM verbs AS v;

--
-- structure for view user_prime_terms (terms with an id less than 2^16 so that 4 term id fit in a 64 bit db key)
--

CREATE OR REPLACE VIEW user_prime_terms AS
    SELECT w.word_id * 2 - 1 AS term_id,
           w.user_id,
           w.word_name       AS term_name,
           w.description,
           w.values          AS usage,
           w.phrase_type_id  AS term_type_id,
           w.excluded,
           w.share_type_id,
           w.protect_id,
           ''                AS formula_text,
           ''                AS resolved_text
      FROM user_words AS w
     WHERE (w.phrase_type_id <> 10 OR w.phrase_type_id IS NULL)
       AND w.word_id < 32767
UNION
    SELECT t.triple_id * -2 + 1       AS term_id,
           t.user_id,
           CASE WHEN (t.triple_name IS NULL) THEN
               CASE WHEN (t.name_given IS NULL) THEN
                    t.name_generated
               ELSE t.name_given END
           ELSE t.triple_name END AS term_name,
           t.description,
           t.values                   AS usage,
           t.phrase_type_id           AS term_type_id,
           t.excluded,
           t.share_type_id,
           t.protect_id,
           ''                         AS formula_text,
           ''                         AS resolved_text
      FROM user_triples AS t
     WHERE t.triple_id < 32767
UNION
    SELECT f.formula_id * 2  AS term_id,
           f.user_id,
           f.formula_name    AS term_name,
           f.description,
           f.usage           AS usage,
           f.formula_type_id AS term_type_id,
           f.excluded,
           f.share_type_id,
           f.protect_id,
           f.formula_text,
           f.resolved_text
      FROM user_formulas AS f
     WHERE f.formula_id < 32767
UNION
    SELECT v.verb_id * -2 AS term_id,
           NULL           AS user_id,
           v.verb_name    AS term_name,
           v.description,
           v.words        AS usage,
           NULL           AS term_type_id,
           NULL           AS excluded,
           1              AS share_type_id,
           3              AS protect_id,
           ''             AS formula_text,
           ''             AS resolved_text
      FROM verbs AS v
     WHERE v.verb_id < 32767;

--
-- structure for view user_terms (terms with an id that is not prime)
--

CREATE OR REPLACE VIEW user_terms AS
    SELECT w.word_id * 2 - 1 AS term_id,
           w.user_id,
           w.word_name       AS term_name,
           w.description,
           w.values          AS usage,
           w.phrase_type_id  AS term_type_id,
           w.excluded,
           w.share_type_id,
           w.protect_id,
           ''                AS formula_text,
           ''                AS resolved_text
      FROM user_words AS w
     WHERE (w.phrase_type_id <> 10 OR w.phrase_type_id IS NULL)
UNION
    SELECT t.triple_id * -2 + 1       AS term_id,
           t.user_id,
           CASE WHEN (t.triple_name IS NULL) THEN
               CASE WHEN (t.name_given IS NULL) THEN
                   t.name_generated
               ELSE t.name_given END
           ELSE t.triple_name END AS term_name,
           t.description,
           t.values                   AS usage,
           t.phrase_type_id           AS term_type_id,
           t.excluded,
           t.share_type_id,
           t.protect_id,
           ''                         AS formula_text,
           ''                         AS resolved_text
      FROM user_triples AS t
UNION
    SELECT f.formula_id * 2  AS term_id,
           f.user_id,
           f.formula_name    AS term_name,
           f.description,
           f.usage           AS usage,
           f.formula_type_id AS term_type_id,
           f.excluded,
           f.share_type_id,
           f.protect_id,
           f.formula_text,
           f.resolved_text
      FROM user_formulas AS f
UNION
    SELECT v.verb_id * -2 AS term_id,
           NULL           AS user_id,
           v.verb_name    AS term_name,
           v.description,
           v.words        AS usage,
           NULL           AS term_type_id,
           NULL           AS excluded,
           1              AS share_type_id,
           3              AS protect_id,
           ''             AS formula_text,
           ''             AS resolved_text
      FROM verbs AS v;
