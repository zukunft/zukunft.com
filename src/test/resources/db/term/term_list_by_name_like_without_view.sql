PREPARE term_list_by_name_like (bigint,text) AS
    SELECT s.term_id,
           s.term_name,
           s.description,
           s.usage,
           s.excluded,
           s.share_type_id,
           s.protect_id
      FROM (

        SELECT ((s.word_id * 2) - 1) AS term_id,
               s.values            AS usage,
               CASE WHEN (u.word_name   <> '' IS NOT TRUE) THEN s.word_name     ELSE u.word_name     END AS term_name,
               CASE WHEN (u.description <> '' IS NOT TRUE) THEN s.description   ELSE u.description   END AS description,
               CASE WHEN (u.excluded              IS NULL) THEN s.excluded      ELSE u.excluded      END AS excluded,
               CASE WHEN (u.share_type_id         IS NULL) THEN s.share_type_id ELSE u.share_type_id END AS share_type_id,
               CASE WHEN (u.protect_id            IS NULL) THEN s.protect_id    ELSE u.protect_id    END AS protect_id
          FROM words s
     LEFT JOIN user_words u ON s.word_id = u.word_id AND u.user_id = $1

  UNION SELECT ((s.triple_id * -2) + 1) AS term_id,
               s.values                    AS usage,
               CASE WHEN (u.name_given     <> '' IS NOT TRUE) THEN
               CASE WHEN (s.name_given     <> '' IS NOT TRUE) THEN
               CASE WHEN (u.name_generated <> '' IS NOT TRUE) THEN s.name_generated
                                                              ELSE u.name_generated END
                                                              ELSE s.name_given     END
                                                              ELSE u.name_given     END AS term_name,
               CASE WHEN (u.description    <> '' IS NOT TRUE) THEN s.description    ELSE u.description   END AS description,
               CASE WHEN (u.excluded                 IS NULL) THEN s.excluded       ELSE u.excluded      END AS excluded,
               CASE WHEN (u.share_type_id            IS NULL) THEN s.share_type_id  ELSE u.share_type_id END AS share_type_id,
               CASE WHEN (u.protect_id               IS NULL) THEN s.protect_id     ELSE u.protect_id    END AS protect_id
          FROM triples s
     LEFT JOIN user_triples u ON s.triple_id = u.triple_id AND u.user_id = $1

  UNION SELECT (s.formula_id * 2) AS term_id,
               s.usage            AS usage,
               CASE WHEN (u.formula_name <> '' IS NOT TRUE) THEN s.formula_name  ELSE u.formula_name  END AS term_name,
               CASE WHEN (u.description  <> '' IS NOT TRUE) THEN s.description   ELSE u.description   END AS description,
               CASE WHEN (u.excluded               IS NULL) THEN s.excluded      ELSE u.excluded      END AS excluded,
               CASE WHEN (u.share_type_id          IS NULL) THEN s.share_type_id ELSE u.share_type_id END AS share_type_id,
               CASE WHEN (u.protect_id             IS NULL) THEN s.protect_id    ELSE u.protect_id    END AS protect_id
          FROM formulas s
     LEFT JOIN user_formulas u ON s.formula_id = u.formula_id AND u.user_id = $1

  UNION SELECT (s.verb_id * -2) AS term_id,
               s.words          AS usage,
               s.verb_name      AS term_name,
               s.description    AS description,
               NULL             AS excluded,
               1                AS share_type_id,
               3                AS protect_id
        FROM verbs s

          ) AS s
         WHERE s.term_name LIKE $2;