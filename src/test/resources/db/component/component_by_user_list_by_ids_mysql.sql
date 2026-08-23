PREPARE component_by_user_list_by_ids FROM
   'SELECT     s.component_id,
               s.component_name,
               l.user_id,
               l.user_name
          FROM user_components s
     LEFT JOIN users l ON s.user_id = l.user_id
         WHERE s.component_id IN (?) AND ( s.excluded <> ? OR s.excluded IS NULL )';