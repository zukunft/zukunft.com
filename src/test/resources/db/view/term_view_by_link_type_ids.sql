PREPARE term_view_by_link_type_ids
    (bigint, bigint, bigint, bigint) AS
        SELECT
                s.term_view_id,
                u.term_view_id AS user_term_view_id,
                s.user_id,
                s.term_id,
                s.view_link_type_id,
                s.view_id,
                CASE WHEN (u.description <> '' IS NOT TRUE) THEN s.description   ELSE u.description   END AS description,
                CASE WHEN (u.excluded          IS NULL)     THEN s.excluded      ELSE u.excluded      END AS excluded,
                CASE WHEN (u.share_type_id     IS NULL)     THEN s.share_type_id ELSE u.share_type_id END AS share_type_id,
                CASE WHEN (u.protect_id        IS NULL)     THEN s.protect_id    ELSE u.protect_id    END AS protect_id
           FROM term_views s
      LEFT JOIN user_term_views u ON s.term_view_id = u.term_view_id
                                      AND u.user_id = $1
          WHERE s.view_id = $2
            AND s.view_link_type_id = $3
            AND s.term_id = $4;