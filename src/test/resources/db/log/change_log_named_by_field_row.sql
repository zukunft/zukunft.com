PREPARE change_log_named_by_field_row (bigint, bigint, bigint, bigint) AS
    SELECT s.change_id,
           s.user_id,
           s.change_time,
           s.change_action_id,
           s.change_field_id,
           s.row_id,
           s.old_value,
           s.old_id,
           s.new_value,
           s.new_id,
           l.user_name,
           l2.table_id
      FROM changes s
 LEFT JOIN users l ON s.user_id = l.user_id
 LEFT JOIN change_fields l2 ON s.change_field_id = l2.change_field_id
     WHERE s.change_field_id = $1
       AND s.row_id = $2
  ORDER BY s.change_time DESC
     LIMIT $3
    OFFSET $4;
