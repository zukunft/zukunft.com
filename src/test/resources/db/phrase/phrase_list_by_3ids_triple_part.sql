PREPARE phrase_list_by_3ids_triple_part (int, int, int, int) AS
    SELECT s.triple_id,
           u.triple_id AS user_triple_id,
           s.user_id,
           s.from_phrase_id,
           s.to_phrase_id,
           s.verb_id,
           s.word_type_id,
           s.triple_condition_id,
           s.triple_condition_type_id,
           CASE WHEN (u.name_given     <> '' IS NOT TRUE) THEN s.name_given     ELSE u.name_given     END AS name_given,
           CASE WHEN (u.name_generated <> '' IS NOT TRUE) THEN s.name_generated ELSE u.name_generated END AS name_generated,
           CASE WHEN (u.description    <> '' IS NOT TRUE) THEN s.description    ELSE u.description    END AS description,
           CASE WHEN (u.values               IS     NULL) THEN s.values         ELSE u.values         END AS values,
           CASE WHEN (u.excluded             IS     NULL) THEN s.excluded       ELSE u.excluded       END AS excluded,
           CASE WHEN (u.share_type_id        IS     NULL) THEN s.share_type_id  ELSE u.share_type_id  END AS share_type_id,
           CASE WHEN (u.protect_id           IS     NULL) THEN s.protect_id     ELSE u.protect_id     END AS protect_id
      FROM triples s
 LEFT JOIN user_triples u ON s.triple_id = u.triple_id AND u.user_id = $1
     WHERE s.triple_id IN ($2,$3,$4);