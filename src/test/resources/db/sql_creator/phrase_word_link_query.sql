SELECT l.triple_id * -1 AS id,
       CASE WHEN (u.name  <> '' IS NOT TRUE) THEN          l.name ELSE          u.name   END AS name,
       CASE WHEN (u.excluded              IS     NULL) THEN COALESCE(l.excluded,0)    ELSE COALESCE(u.excluded,0) END AS excluded
  FROM triples l
  LEFT JOIN user_triples u ON u.triple_id = l.triple_id
                          AND u.user_id = 3
 GROUP BY l.triple_id, l.name ;