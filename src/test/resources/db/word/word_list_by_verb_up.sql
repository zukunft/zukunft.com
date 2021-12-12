SELECT s.word_id,
       s.user_id,
       CASE WHEN (u.word_name <> ''   IS NOT TRUE) THEN s.word_name    ELSE u.word_name    END AS word_name,
       CASE WHEN (u.plural <> ''      IS NOT TRUE) THEN s.plural       ELSE u.plural       END AS plural,
       CASE WHEN (u.description <> '' IS NOT TRUE) THEN s.description  ELSE u.description  END AS description,
       CASE WHEN (u.word_type_id      IS     NULL) THEN s.word_type_id ELSE u.word_type_id END AS word_type_id,
       CASE WHEN (u.excluded          IS     NULL) THEN s.excluded     ELSE u.excluded     END AS excluded,
       l.verb_id,
       s.values
FROM word_links l,
     words s
         LEFT JOIN user_words u ON s.word_id = u.word_id
         AND u.user_id = 1
WHERE l.to_phrase_id = s.word_id
  AND l.from_phrase_id IN (7)
  AND l.verb_id = 2
ORDER BY s.values DESC, s.word_name;