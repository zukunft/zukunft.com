PREPARE view_term_link_by_id (bigint,bigint) AS
    SELECT
        s.view_term_link_id,
        u.view_term_link_id AS user_view_term_link_id,
        s.user_id,
        s.term_id,
        s.type_id,
        s.view_id,
        CASE WHEN (u.description <> '' IS NOT TRUE) THEN s.description   ELSE u.description   END AS description,
        CASE WHEN (u.excluded          IS     NULL) THEN s.excluded      ELSE u.excluded      END AS excluded,
        CASE WHEN (u.share_type_id     IS     NULL) THEN s.share_type_id ELSE u.share_type_id END AS share_type_id,
        CASE WHEN (u.protect_id        IS     NULL) THEN s.protect_id    ELSE u.protect_id    END AS protect_id
    FROM
        view_term_links s
            LEFT JOIN user_view_term_links u
                ON s.view_term_link_id = u.view_term_link_id AND u.user_id = $1
    WHERE s.view_term_link_id = $2;