PREPARE change_value_by_numeric_value_of_val_big
    (bigint,text,bigint,bigint) AS
        SELECT
                s.change_id,
                s.user_id,
                s.change_time,
                s.change_action_id,
                s.change_field_id,
                s.group_id,
                s.old_value,
                s.new_value,
                l.user_name,
                l2.table_id
           FROM change_values_big s
      LEFT JOIN users l          ON s.user_id = l.user_id
      LEFT JOIN change_fields l2 ON s.change_field_id = l2.change_field_id
          WHERE s.change_field_id = $1
            AND s.group_id = $2
       ORDER BY s.change_time
     DESC LIMIT $3
         OFFSET $4;