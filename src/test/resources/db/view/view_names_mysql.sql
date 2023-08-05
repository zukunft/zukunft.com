PREPARE view_names FROM
   'SELECT
                s.view_id,
                u.view_id AS user_view_id,
                s.user_id,
                IF(u.view_name IS NULL, s.view_name, u.view_name) AS view_name
           FROM views s
      LEFT JOIN user_views u ON s.view_id = u.view_id
            AND u.user_id = ?
       ORDER BY s.view_name
          LIMIT ?
         OFFSET ?';