PREPARE change_insert FROM
    'INSERT INTO changes
                 (user_id,change_action_id,change_field_id,new_value,row_id)
          VALUES (?,?,?,?,?)';