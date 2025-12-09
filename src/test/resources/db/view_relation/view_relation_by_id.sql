PREPARE view_relation_by_id (bigint,bigint) AS
    SELECT
        s.view_relation_id,
        u.view_relation_id AS user_view_relation_id,
        s.user_id,
        s.parent_view_id,
        s.view_relation_type_id,
        s.child_view_id,
        CASE WHEN (u.description <> '' IS NOT TRUE) THEN s.description   ELSE u.description   END AS description,
        CASE WHEN (u.start_pos         IS     NULL) THEN s.start_pos     ELSE u.start_pos     END AS start_pos,
        CASE WHEN (u.excluded          IS     NULL) THEN s.excluded      ELSE u.excluded      END AS excluded,
        CASE WHEN (u.share_type_id     IS     NULL) THEN s.share_type_id ELSE u.share_type_id END AS share_type_id,
        CASE WHEN (u.protect_id        IS     NULL) THEN s.protect_id    ELSE u.protect_id    END AS protect_id
    FROM
        view_relations s
            LEFT JOIN user_view_relations u
                ON s.view_relation_id = u.view_relation_id AND u.user_id = $1
    WHERE s.view_relation_id = $2;