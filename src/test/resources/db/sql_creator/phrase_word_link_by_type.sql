SELECT from_phrase_id FROM (
       SELECT DISTINCT
              l.from_phrase_id,
              CASE WHEN (u.excluded         IS     NULL) THEN COALESCE(l.excluded,0)  ELSE COALESCE(u.excluded,0) END AS excluded
         FROM triples l
         LEFT JOIN user_triples u ON u.triple_id = l.triple_id
                                 AND u.user_id = 3
        WHERE l.to_phrase_id = 2
          AND l.verb_id = 2 ) AS a
        WHERE (excluded <> 1 OR excluded is NULL);