PREPARE term_view_by_user_list_by_ids FROM
   'SELECT     s.term_view_id,
               l.user_id,
               l.user_name
          FROM user_term_views s
     LEFT JOIN users l ON s.user_id = l.user_id
         WHERE s.term_view_id IN (?) AND ( s.excluded <> ? OR s.excluded IS NULL )';