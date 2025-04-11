PREPARE changes_big_insert FROM
    'INSERT INTO changes_big
                 (user_id,change_action_id,change_field_id,new_value,row_id)
          VALUES (?,?,?,?,?)';