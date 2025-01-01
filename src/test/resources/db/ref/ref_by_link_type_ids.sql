PREPARE ref_by_link_type_ids (bigint, bigint, bigint, text) AS
    SELECT s.ref_id,
           u.ref_id AS user_ref_id,
           s.user_id,
           s.phrase_id,
           s.ref_type_id,
           CASE WHEN (u.external_key <> '' IS NOT TRUE) THEN s.external_key   ELSE u.external_key   END AS external_key,
           CASE WHEN (u.url          <> '' IS NOT TRUE) THEN s.url            ELSE u.url            END AS url,
           CASE WHEN (u.description  <> '' IS NOT TRUE) THEN s.description    ELSE u.description    END AS description,
           CASE WHEN (u.source_id          IS     NULL) THEN s.source_id      ELSE u.source_id      END AS source_id,
           CASE WHEN (u.excluded           IS     NULL) THEN s.excluded       ELSE u.excluded       END AS excluded,
           CASE WHEN (u.share_type_id      IS     NULL) THEN s.share_type_id  ELSE u.share_type_id  END AS share_type_id,
           CASE WHEN (u.protect_id         IS     NULL) THEN s.protect_id     ELSE u.protect_id     END AS protect_id
      FROM refs s
 LEFT JOIN user_refs u ON s.ref_id = u.ref_id AND u.user_id = $1
     WHERE s.phrase_id = $2
       AND s.ref_type_id = $3
       AND s.external_key = $4;