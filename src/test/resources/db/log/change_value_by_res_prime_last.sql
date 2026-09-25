PREPARE change_value_by_res_prime_last (bigint, bigint, bigint) AS
    SELECT     s.change_id,
               s.user_id,
               s.change_time,
               s.change_action_id,
               s.change_field_id,
               s.group_id,
               s.old_value,
               s.old_id,
               s.new_value,
               s.new_id,
               l.user_name,
               l2.table_id
          FROM change_values_prime s
     LEFT JOIN users l          ON s.user_id         = l.user_id
     LEFT JOIN change_fields l2 ON s.change_field_id = l2.change_field_id
         WHERE s.group_id = $1
      ORDER BY s.change_time DESC,
               s.change_id   DESC
         LIMIT $2
        OFFSET $3;