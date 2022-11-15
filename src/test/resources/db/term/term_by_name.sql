PREPARE term_by_name (int,text) AS
    SELECT s.term_id,
           u.term_id AS user_term_id,
           s.user_id,
           CASE WHEN (u.term_name   <> '' IS NOT TRUE) THEN s.term_name     ELSE u.term_name     END AS term_name,
           CASE WHEN (u.description <> '' IS NOT TRUE) THEN s.description   ELSE u.description   END AS description,
           CASE WHEN (u.usage             IS     NULL) THEN s.usage         ELSE u.usage         END AS usage,
           CASE WHEN (u.excluded          IS     NULL) THEN s.excluded      ELSE u.excluded      END AS excluded,
           CASE WHEN (u.share_type_id     IS     NULL) THEN s.share_type_id ELSE u.share_type_id END AS share_type_id,
           CASE WHEN (u.protect_id        IS     NULL) THEN s.protect_id    ELSE u.protect_id    END AS protect_id
      FROM terms s
 LEFT JOIN user_terms u ON s.term_id = u.term_id AND u.user_id = $1
     WHERE s.term_name = $2;


