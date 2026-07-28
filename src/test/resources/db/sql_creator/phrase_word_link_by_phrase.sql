SELECT id, name, excluded FROM (
       SELECT DISTINCT
              w.word_id AS id,
              CASE WHEN (u.word_name  <> '' IS NOT TRUE) THEN          w.word_name ELSE          u.word_name   END AS name,
              CASE WHEN (u.excluded         IS     NULL) THEN COALESCE(w.excluded,0)  ELSE COALESCE(u.excluded,0) END AS excluded
         FROM ( SELECT from_phrase_id AS id FROM (
                    SELECT DISTINCT
                           l.from_phrase_id,
                           CASE WHEN (u.excluded              IS     NULL) THEN COALESCE(l.excluded,0)    ELSE COALESCE(u.excluded,0) END AS excluded
                      FROM triples l LEFT JOIN user_triples u ON u.triple_id = l.triple_id
                                                            AND u.user_id = 3
                     WHERE l.to_phrase_id = 1
                       AND l.verb_id = 2 ) AS a
       WHERE (excluded <> 1 OR excluded is NULL)  ) a, words w LEFT JOIN user_words u ON u.word_id = w.word_id
                                                 AND u.user_id = 3
        WHERE w.word_id NOT IN ( SELECT from_phrase_id FROM (
                    SELECT DISTINCT
                           l.from_phrase_id,
                           CASE WHEN (u.excluded         IS     NULL) THEN COALESCE(l.excluded,0)  ELSE COALESCE(u.excluded,0) END AS excluded
                      FROM triples l LEFT JOIN user_triples u ON u.triple_id = l.triple_id
                                                                   AND u.user_id = 3
                     WHERE l.to_phrase_id <> 1
                       AND l.verb_id = 2
                       AND l.from_phrase_id IN ( SELECT from_phrase_id AS id FROM (
                                 SELECT DISTINCT
                                        l.from_phrase_id,
                                        CASE WHEN (u.excluded              IS     NULL) THEN COALESCE(l.excluded,0)    ELSE COALESCE(u.excluded,0) END AS excluded
                                   FROM triples l LEFT JOIN user_triples u ON u.triple_id = l.triple_id
                                                                                AND u.user_id = 3
                                  WHERE l.to_phrase_id = 1
                                    AND l.verb_id = 2 ) AS a
                          WHERE (excluded <> 1 OR excluded is NULL)
                       )
                  GROUP BY l.from_phrase_id ) AS o
                     WHERE (excluded <> 1 OR excluded is NULL)
              )
         AND w.word_id = a.id
    GROUP BY name ) AS w
 WHERE (excluded <> 1 OR excluded is NULL);