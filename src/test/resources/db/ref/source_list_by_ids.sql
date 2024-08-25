PREPARE source_list_by_ids (bigint, bigint[]) AS
    SELECT s.source_id,
           u.source_id                                                                                 AS user_source_id,
           s.user_id,
           s.source_name,
           s.code_id,
           CASE WHEN (u.source_name <> '' IS NOT TRUE) THEN s.source_name    ELSE u.source_name    END AS source_name,
           CASE WHEN (u.url         <> '' IS NOT TRUE) THEN s.url            ELSE u.url            END AS url,
           CASE WHEN (u.description <> '' IS NOT TRUE) THEN s.description    ELSE u.description    END AS description,
           CASE WHEN (u.source_type_id    IS     NULL) THEN s.source_type_id ELSE u.source_type_id END AS source_type_id,
           CASE WHEN (u.excluded          IS     NULL) THEN s.excluded       ELSE u.excluded       END AS excluded,
           CASE WHEN (u.share_type_id     IS     NULL) THEN s.share_type_id  ELSE u.share_type_id  END AS share_type_id,
           CASE WHEN (u.protect_id        IS     NULL) THEN s.protect_id     ELSE u.protect_id     END AS protect_id
      FROM sources s
 LEFT JOIN user_sources u ON s.source_id = u.source_id AND u.user_id = $1
     WHERE s.source_id = ANY ($2)
  ORDER BY s.source_id;
