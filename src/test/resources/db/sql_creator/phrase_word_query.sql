SELECT w.word_id AS id,
       CASE WHEN (u.word_name  <> '' IS NOT TRUE) THEN          w.word_name    ELSE          u.word_name   END AS word_name,
       CASE WHEN (u.excluded         IS     NULL) THEN COALESCE(w.excluded,0)  ELSE COALESCE(u.excluded,0) END AS excluded
  FROM words w
  LEFT JOIN user_words u ON u.word_id = w.word_id
                        AND u.user_id = 3
 GROUP BY w.word_id, w.word_name ;