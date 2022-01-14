PREPARE phrase_list_by_3ids_triple_part (int, int, int, int) AS
    SELECT s.word_link_id,
           u.word_link_id AS user_word_link_id,
           s.user_id,
           s.from_phrase_id,
           s.to_phrase_id,
           s.verb_id,
           s.word_type_id,
           s.word_link_condition_id,
           s.word_link_condition_type_id,
           CASE WHEN (u.name           <> '' IS NOT TRUE) THEN s.name           ELSE u.name           END AS name,
           CASE WHEN (u.name_generated <> '' IS NOT TRUE) THEN s.name_generated ELSE u.name_generated END AS name_generated,
           CASE WHEN (u.description    <> '' IS NOT TRUE) THEN s.description    ELSE u.description    END AS description,
           CASE WHEN (u.values               IS     NULL) THEN s.values         ELSE u.values         END AS values,
           CASE WHEN (u.excluded             IS     NULL) THEN s.excluded       ELSE u.excluded       END AS excluded,
           CASE WHEN (u.share_type_id        IS     NULL) THEN s.share_type_id  ELSE u.share_type_id  END AS share_type_id,
           CASE WHEN (u.protect_id           IS     NULL) THEN s.protect_id     ELSE u.protect_id     END AS protect_id
      FROM word_links s
 LEFT JOIN user_word_links u ON s.word_link_id = u.word_link_id AND u.user_id = $1
     WHERE s.word_link_id IN ($2,$3,$4);