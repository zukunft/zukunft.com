PREPARE term_view_by_user_list_by_ids (bigint[], bigint) AS
    SELECT     s.term_view_id,
               l.user_id,
               l.user_name
          FROM user_term_views s
     LEFT JOIN users l ON s.user_id = l.user_id
         WHERE s.term_view_id = ANY ($1) AND ( s.excluded <> $2 OR s.excluded IS NULL );