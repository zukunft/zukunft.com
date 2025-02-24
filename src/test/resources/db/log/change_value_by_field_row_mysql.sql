PREPARE change_value_by_field_row FROM
   'SELECT s.change_id,
           s.user_id,
           s.change_time,
           s.change_action_id,
           s.change_field_id,
           s.group_id,
           s.old_value,
           s.new_value,
           l.user_name,
           l2.table_id
      FROM change_values_prime s
 LEFT JOIN users l ON s.user_id = l.user_id
 LEFT JOIN change_fields l2 ON s.change_field_id = l2.change_field_id
     WHERE s.change_field_id = ?
       AND s.group_id = ?
  ORDER BY s.change_time DESC
     LIMIT ?
    OFFSET ?';
