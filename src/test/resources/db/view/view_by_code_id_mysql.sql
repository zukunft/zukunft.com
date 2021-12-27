SELECT
            s.view_id,
            u.view_id AS user_view_id,
            s.user_id,
            s.code_id,
            IF(u.view_name    IS NULL, s.view_name,    u.view_name)    AS view_name,
            IF(u.comment      IS NULL, s.comment,      u.comment)      AS comment,
            IF(u.view_type_id IS NULL, s.view_type_id, u.view_type_id) AS view_type_id,
            IF(u.excluded     IS NULL, s.excluded,     u.excluded)     AS excluded
       FROM views s
  LEFT JOIN user_views u ON s.view_id = u.view_id
        AND u.user_id = 1
      WHERE s.code_id = 'word';