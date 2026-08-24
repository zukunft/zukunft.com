PREPARE component_link_by_user_list_by_ids FROM
   'SELECT     s.component_link_id,
               l.user_id,
               l.user_name
          FROM user_component_links s
     LEFT JOIN users l ON s.user_id = l.user_id
         WHERE s.component_link_id IN (?)
           AND ( s.excluded <> ? OR s.excluded IS NULL )';