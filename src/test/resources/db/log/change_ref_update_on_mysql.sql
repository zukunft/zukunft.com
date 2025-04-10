PREPARE change_ref_update_on FROM
    'INSERT INTO changes
                 (user_id, change_action_id, change_field_id, old_value, new_value, old_id, new_id, row_id)
          VALUES (?,?,?,?,?,?,?,?)';