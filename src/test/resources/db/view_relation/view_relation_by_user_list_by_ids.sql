PREPARE view_relation_by_user_list_by_ids (bigint[], bigint) AS
    SELECT     s.view_relation_id,
               l.user_id,
               l.user_name
          FROM user_view_relations s
     LEFT JOIN users l ON s.user_id = l.user_id
         WHERE s.view_relation_id = ANY ($1)
           AND ( s.excluded <> $2 OR s.excluded IS NULL );