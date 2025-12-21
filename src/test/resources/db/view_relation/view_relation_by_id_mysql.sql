PREPARE view_relation_by_id FROM
    'SELECT
        s.view_relation_id,
        u.view_relation_id AS user_view_relation_id,
        s.user_id,
        s.parent_view_id,
        s.view_relation_type_id,
        s.child_view_id,
        IF(u.description   IS NULL, s.description,   u.description  ) AS description,
        IF(u.start_pos     IS NULL, s.start_pos,     u.start_pos    ) AS start_pos,
        IF(u.excluded      IS NULL, s.excluded,      u.excluded     ) AS excluded,
        IF(u.share_type_id IS NULL, s.share_type_id, u.share_type_id) AS share_type_id,
        IF(u.protect_id    IS NULL, s.protect_id,    u.protect_id   ) AS protect_id
    FROM
        view_relations s
            LEFT JOIN user_view_relations u
                      ON s.view_relation_id = u.view_relation_id AND u.user_id = ?
    WHERE s.view_relation_id = ?';
