PREPARE source_by_user_list_by_ids (bigint[], bigint) AS
    SELECT     s.source_id,
               s.source_name,
               l.user_id,
               l.user_name
          FROM user_sources s
     LEFT JOIN users l ON s.user_id = l.user_id
         WHERE s.source_id = ANY ($1)
           AND ( s.excluded <> $2 OR s.excluded IS NULL );