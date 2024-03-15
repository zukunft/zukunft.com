-- --------------------------------------------------------

--
-- structure for view prime_phrases (phrases with an id less than 2^16 so that 4 phrase id fit in a 64 bit db key)
--

CREATE OR REPLACE VIEW prime_phrases AS
    SELECT w.word_id AS phrase_id,
           w.user_id,
           w.word_name AS phrase_name,
           w.description,
           w.`values`,
           w.phrase_type_id,
           w.excluded,
           w.share_type_id,
           w.protect_id
      FROM words AS w
     WHERE w.word_id < 32767
UNION
    SELECT t.triple_id * -1 AS phrase_id,
           t.user_id,
           IF(t.triple_name IS NULL,
              IF(t.name_given IS NULL,
                 t.name_generated,
                 t.name_given),
              t.triple_name) AS phrase_name,
           t.description,
           t.`values`,
           t.phrase_type_id,
           t.excluded,
           t.share_type_id,
           t.protect_id
      FROM triples AS t
     WHERE t.triple_id < 32767;

--
-- structure for view phrases (phrases with an id that is not prime)
--

CREATE OR REPLACE VIEW phrases AS
    SELECT w.word_id AS phrase_id,
           w.user_id,
           w.word_name AS phrase_name,
           w.description,
           w.`values`,
           w.phrase_type_id,
           w.excluded,
           w.share_type_id,
           w.protect_id
      FROM words AS w
UNION
    SELECT t.triple_id * -1 AS phrase_id,
           t.user_id,
           IF(t.triple_name IS NULL,
              IF(t.name_given IS NULL,
                 t.name_generated,
                 t.name_given),
              t.triple_name) AS phrase_name,
           t.description,
           t.`values`,
           t.phrase_type_id,
           t.excluded,
           t.share_type_id,
           t.protect_id
      FROM triples AS t;

--
-- structure for view user_prime_phrases (phrases with an id less than 2^16 so that 4 phrase id fit in a 64 bit db key)
--

CREATE OR REPLACE VIEW user_prime_phrases AS
    SELECT w.word_id AS phrase_id,
           w.user_id,
           w.word_name AS phrase_name,
           w.description,
           w.`values`,
           w.phrase_type_id,
           w.excluded,
           w.share_type_id,
           w.protect_id
      FROM user_words AS w
     WHERE w.word_id < 32767
UNION
    SELECT t.triple_id * -1 AS phrase_id,
           t.user_id,
           IF(t.triple_name IS NULL,
              IF(t.name_given IS NULL,
                 t.name_generated,
                 t.name_given),
              t.triple_name) AS phrase_name,
           t.description,
           t.`values`,
           t.phrase_type_id,
           t.excluded,
           t.share_type_id,
           t.protect_id
      FROM user_triples AS t
     WHERE t.triple_id < 32767;

--
-- structure for view user_phrases (phrases with an id that is not prime)
--

CREATE OR REPLACE VIEW user_phrases AS
    SELECT w.word_id AS phrase_id,
           w.user_id,
           w.word_name AS phrase_name,
           w.description,
           w.`values`,
           w.phrase_type_id,
           w.excluded,
           w.share_type_id,
           w.protect_id
      FROM user_words AS w
UNION
    SELECT t.triple_id * -1 AS phrase_id,
           t.user_id,
           IF(t.triple_name IS NULL,
              IF(t.name_given IS NULL,
                 t.name_generated,
                 t.name_given),
              t.triple_name) AS phrase_name,
           t.description,
           t.`values`,
           t.phrase_type_id,
           t.excluded,
           t.share_type_id,
           t.protect_id
      FROM user_triples AS t;
