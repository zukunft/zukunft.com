PREPARE user_log_by_field_row (int,int) AS
    SELECT s.change_id,
           s.user_id,
           s.change_field_id,
           s.row_id,
           s.change_time,
           s.old_value,
           s.old_id,
           s.new_value,
           s.new_id,
           l.user_name
      FROM changes s
 LEFT JOIN users l ON s.user_id = l.user_id
     WHERE s.change_field_id = $1
       AND row_id = $2
  ORDER BY s.change_id DESC;
