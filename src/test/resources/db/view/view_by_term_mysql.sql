PREPARE view_by_term FROM
   'SELECT
                s.view_id,
                u.view_id AS user_view_id,
                s.user_id,
                s.code_id,
                l.term_id,
                l.type_id,
                l.view_id,
                IF(u.view_name     IS NULL, s.view_name,     u.view_name)     AS view_name,
                IF(u.description   IS NULL, s.description,   u.description)   AS description,
                IF(u.view_type_id  IS NULL, s.view_type_id,  u.view_type_id)  AS view_type_id,
                IF(u.excluded      IS NULL, s.excluded,      u.excluded)      AS excluded,
                IF(u.share_type_id IS NULL, s.share_type_id, u.share_type_id) AS share_type_id,
                IF(u.protect_id    IS NULL, s.protect_id,    u.protect_id)    AS protect_id
           FROM views s
      LEFT JOIN user_views u      ON s.view_id = u.view_id AND u.user_id = ?
      LEFT JOIN view_term_links l ON s.view_id = l.view_id
          WHERE l.term_id = ?';