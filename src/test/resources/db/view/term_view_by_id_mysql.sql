PREPARE term_view_by_id FROM
    'SELECT
        s.term_view_id,
        u.term_view_id AS user_term_view_id,
        s.user_id,
        s.term_id,
        s.view_link_type_id,
        s.view_id,
        IF(u.description   IS NULL, s.description,   u.description  ) AS description,
        IF(u.excluded      IS NULL, s.excluded,      u.excluded     ) AS excluded,
        IF(u.share_type_id IS NULL, s.share_type_id, u.share_type_id) AS share_type_id,
        IF(u.protect_id    IS NULL, s.protect_id,    u.protect_id   ) AS protect_id
    FROM
        term_views s
            LEFT JOIN user_term_views u
                      ON s.term_view_id = u.term_view_id AND u.user_id = ?
    WHERE s.term_view_id = ?';
