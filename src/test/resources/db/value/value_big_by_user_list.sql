PREPARE value_big_by_user_list (text, bigint) AS
    SELECT     s.group_id,
               l.user_id,
               l.user_name,
               l.code_id,
               l.ip_address,
               l.email,
               l.first_name,
               l.last_name,
               l.term_id,
               l.view_id,
               l.source_id,
               l.user_profile_id
          FROM user_values_big s
     LEFT JOIN users l ON s.user_id = l.user_id
         WHERE s.group_id = $1 AND ( s.excluded <> $2 OR s.excluded IS NULL );