PREPARE change_delete_o FROM
    'INSERT INTO changes
                 (user_id,change_action_id,change_field_id,old_value,row_id)
          VALUES (?,?,?,?,?)';