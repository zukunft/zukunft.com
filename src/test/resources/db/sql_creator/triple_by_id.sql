SELECT s.triple_id,
       u.triple_id AS user_triple_id,
       s.user_id,
       s.from_phrase_id,
       s.to_phrase_id,
       s.verb_id,
       s.phrase_type_id,
       CASE WHEN (u.name_given  <> '' IS NOT TRUE) THEN s.name_given  ELSE u.name_given  END AS name_given,
       CASE WHEN (u.description <> '' IS NOT TRUE) THEN s.description ELSE u.description END AS description,
       CASE WHEN (u.excluded          IS     NULL) THEN s.excluded    ELSE u.excluded    END AS excluded
  FROM triples s
  LEFT JOIN user_triples u ON s.triple_id = u.triple_id
                          AND u.user_id = 3
 WHERE s.triple_id = 1;