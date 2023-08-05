PREPARE view_names_like (int, text, int, int) AS
         SELECT s.view_id,
                u.view_id AS user_view_id,
                s.user_id,
                CASE WHEN (u.view_name <> '' IS NOT TRUE) THEN s.view_name ELSE u.view_name END AS view_name
           FROM views s
      LEFT JOIN user_views u ON s.view_id = u.view_id
            AND u.user_id = $1
          WHERE s.view_name like $2
            AND s.code_id IS NULL
       ORDER BY s.view_name
          LIMIT $3
         OFFSET $4;
