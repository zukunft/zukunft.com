SELECT DISTINCT
       id, phrase_name
  FROM ( SELECT DISTINCT
                w.word_id AS id,
                CASE WHEN (u.word_name <> '' IS NOT TRUE) THEN w.word_name            ELSE u.word_name            END AS phrase_name,
                CASE WHEN (u.excluded        IS     NULL) THEN COALESCE(w.excluded,0) ELSE COALESCE(u.excluded,0) END AS excluded
           FROM words w
           LEFT JOIN user_words u ON u.word_id = w.word_id
                                 AND u.user_id = 3
    UNION SELECT DISTINCT
                l.triple_id * -1 AS id,
                CASE WHEN (u.name   <> '' IS NOT TRUE) THEN l.name       ELSE u.name       END AS phrase_name,
                CASE WHEN (u.excluded               IS     NULL) THEN COALESCE(l.excluded,0) ELSE COALESCE(u.excluded,0) END AS excluded
           FROM triples l
           LEFT JOIN user_triples u ON u.triple_id = l.triple_id
                                   AND u.user_id = 3
       ) AS p
 WHERE excluded = 0
 ORDER BY p.phrase_name;