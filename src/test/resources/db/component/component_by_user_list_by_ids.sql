PREPARE component_by_user_list_by_ids (bigint[], bigint) AS
    SELECT     s.component_id,
               s.component_name,
               l.user_id,
               l.user_name
          FROM user_components s
     LEFT JOIN users l ON s.user_id = l.user_id
         WHERE s.component_id = ANY ($1) AND ( s.excluded <> $2 OR s.excluded IS NULL );