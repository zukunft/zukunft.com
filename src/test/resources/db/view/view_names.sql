PREPARE view_names (bigint, bigint, bigint) AS
    SELECT
                s.view_id,
                u.view_id AS user_view_id,
                s.user_id,
                CASE WHEN (u.view_name <> '' IS NOT TRUE) THEN s.view_name ELSE u.view_name END AS view_name
           FROM views s
      LEFT JOIN user_views u ON s.view_id = u.view_id
            AND u.user_id = $1
          WHERE ( s.view_type_id NOT IN (7) OR s.view_type_id IS NULL )
       ORDER BY s.view_name
          LIMIT $2
         OFFSET $3;
