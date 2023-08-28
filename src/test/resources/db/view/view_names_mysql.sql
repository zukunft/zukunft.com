PREPARE view_names FROM
   'SELECT
                s.view_id,
                u.view_id AS user_view_id,
                s.user_id,
                IF(u.view_name IS NULL, s.view_name, u.view_name) AS view_name
           FROM views s
      LEFT JOIN user_views u ON s.view_id = u.view_id
            AND u.user_id = ?
          WHERE ( s.view_type_id NOT IN (7) OR s.view_type_id IS NULL )
       ORDER BY s.view_name
          LIMIT ?
         OFFSET ?';