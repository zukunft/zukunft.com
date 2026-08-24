PREPARE view_relation_by_user_list_by_ids FROM
   'SELECT     s.view_relation_id,
               l.user_id,
               l.user_name
          FROM user_view_relations s
     LEFT JOIN users l ON s.user_id = l.user_id
         WHERE s.view_relation_id IN (?)
           AND ( s.excluded <> ? OR s.excluded IS NULL )';