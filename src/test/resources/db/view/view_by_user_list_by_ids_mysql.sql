PREPARE view_by_user_list_by_ids FROM
   'SELECT     s.view_id,
               s.view_name,
               l.user_id,
               l.user_name
          FROM user_views s
     LEFT JOIN users l ON s.user_id = l.user_id
         WHERE s.view_id IN (?)
           AND ( s.excluded <> ? OR s.excluded IS NULL )';