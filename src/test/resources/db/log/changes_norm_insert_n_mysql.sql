PREPARE changes_norm_insert_n FROM
    'INSERT INTO changes_norm
                 (user_id,change_action_id,change_field_id,new_value,row_id)
          VALUES (?,?,?,?,?)';