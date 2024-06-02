PREPARE ref_by_id (bigint,bigint) AS
    SELECT s.ref_id,
           u.ref_id AS user_ref_id,
           s.user_id,
           s.phrase_id,
           s.ref_type_id,
           CASE WHEN (u.external_key <> '' IS NOT TRUE) THEN s.external_key ELSE u.external_key END AS external_key,
           CASE WHEN (u.url          <> '' IS NOT TRUE) THEN s.url          ELSE u.url          END AS url,
           CASE WHEN (u.description  <> '' IS NOT TRUE) THEN s.description  ELSE u.description  END AS description,
           CASE WHEN (u.source_id          IS     NULL) THEN s.source_id    ELSE u.source_id    END AS source_id,
           CASE WHEN (u.excluded           IS     NULL) THEN s.excluded     ELSE u.excluded     END AS excluded
      FROM refs s
 LEFT JOIN user_refs u ON s.ref_id = u.ref_id AND u.user_id = $1
     WHERE s.ref_id = $2;