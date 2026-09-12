SELECT id, name
  FROM ( SELECT w.word_id AS id,
                CASE WHEN (u.word_name  <> '' IS NOT TRUE) THEN          w.word_name ELSE          u.word_name   END AS name,
                CASE WHEN (u.excluded         IS     NULL) THEN COALESCE(w.excluded,0)  ELSE COALESCE(u.excluded,0) END AS excluded
           FROM triples l, words w
           LEFT JOIN user_words u ON u.word_id = w.word_id
                                 AND u.user_id = 3
          WHERE w.phrase_type_id = 2
            AND w.word_id = l.from_phrase_id
            AND l.verb_id = 2
            AND l.to_phrase_id = 14
       GROUP BY name) AS s
 WHERE (excluded <> 1 OR excluded is NULL)
 ORDER BY name;