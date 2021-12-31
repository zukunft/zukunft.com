PREPARE source_by_name (int, text) AS
    SELECT s.source_id,
           u.source_id AS user_source_id,
           s.user_id,
           s.source_name,
           s.code_id,
           CASE WHEN (u.source_name <> '' IS NOT TRUE) THEN s.source_name    ELSE u.source_name    END AS source_name,
           CASE WHEN (u.url         <> '' IS NOT TRUE) THEN s.url            ELSE u.url            END AS url,
           CASE WHEN (u.comment     <> '' IS NOT TRUE) THEN s.comment        ELSE u.comment        END AS comment,
           CASE WHEN (u.source_type_id    IS     NULL) THEN s.source_type_id ELSE u.source_type_id END AS source_type_id,
           CASE WHEN (u.excluded          IS     NULL) THEN s.excluded       ELSE u.excluded       END AS excluded
      FROM sources s
 LEFT JOIN user_sources u ON s.source_id = u.source_id AND u.user_id = $1
     WHERE s.source_name = $2;