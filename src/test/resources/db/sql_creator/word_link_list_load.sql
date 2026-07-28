SELECT l.triple_id,
       l.from_phrase_id,
       l.verb_id,
       l.to_phrase_id,
       l.description,
       l.name,
       v.verb_id,
       v.code_id,
       v.verb_name,
       v.name_plural,
       v.name_reverse,
       v.name_plural_reverse,
       v.formula_name,
       v.description,
       CASE WHEN (ul.excluded          IS     NULL) THEN l.excluded      ELSE ul.excluded     END AS excluded,
       t2.word_id AS word_id2,
       t2.user_id AS user_id2,
       CASE WHEN (u2.word_name   <> '' IS NOT TRUE) THEN t2.word_name    ELSE u2.word_name    END AS word_name,
       CASE WHEN (u2.plural      <> '' IS NOT TRUE) THEN t2.plural       ELSE u2.plural       END AS plural,
       CASE WHEN (u2.description <> '' IS NOT TRUE) THEN t2.description  ELSE u2.description  END AS description,
       CASE WHEN (u2.phrase_type_id      IS     NULL) THEN t2.phrase_type_id ELSE u2.phrase_type_id END AS phrase_type_id,
       CASE WHEN (u2.excluded          IS     NULL) THEN t2.excluded     ELSE u2.excluded     END AS excluded,
       t2.values AS values2
  FROM triples l
  LEFT JOIN user_triples ul ON ul.triple_id = l.triple_id
                           AND ul.user_id = 1,
       verbs v,
       words t2 LEFT JOIN user_words u2 ON u2.word_id = t2.word_id
                                       AND u2.user_id = 1
 WHERE l.verb_id = v.verb_id
   AND l.from_phrase_id = t2.word_id
   AND l.to_phrase_id   = 3
   AND l.verb_id = 2
 GROUP BY t2.word_id, l.verb_id
 ORDER BY l.verb_id, word_name;