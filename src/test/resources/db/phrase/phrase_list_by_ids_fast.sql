PREPARE phrase_list_by_ids_fast (int, int[]) AS
    SELECT s.phrase_id,
           u.phrase_id AS user_phrase_id,
           s.user_id,
           s.word_type_id,
           CASE WHEN (u.phrase_name    <> '' IS NOT TRUE) THEN s.phrase_name    ELSE u.phrase_name    END AS phrase_name,
           CASE WHEN (u.description    <> '' IS NOT TRUE) THEN s.description    ELSE u.description    END AS description,
           CASE WHEN (u.values               IS     NULL) THEN s.values         ELSE u.values         END AS values,
           CASE WHEN (u.excluded             IS     NULL) THEN s.excluded       ELSE u.excluded       END AS excluded,
           CASE WHEN (u.share_type_id        IS     NULL) THEN s.share_type_id  ELSE u.share_type_id  END AS share_type_id,
           CASE WHEN (u.protect_id           IS     NULL) THEN s.protect_id     ELSE u.protect_id     END AS protect_id
      FROM phrases s
 LEFT JOIN user_phrases u ON s.phrase_id = u.phrase_id AND u.user_id = $1
     WHERE s.phrase_id = ANY ($2)
  ORDER BY s.values DESC, phrase_name;
