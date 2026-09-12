PREPARE value_big_by_user_list_by_ids_2 (text[]) AS
    SELECT     s.group_id,
               s.excluded,
               l.user_id,
               l.user_name
          FROM user_values_big s
     LEFT JOIN users l ON s.user_id = l.user_id
         WHERE s.group_id = ANY ($1);