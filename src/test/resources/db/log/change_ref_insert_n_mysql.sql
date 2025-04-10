PREPARE change_ref_insert_n FROM
    'INSERT INTO changes
                 (user_id,change_action_id,change_field_id,new_value,new_id,row_id)
          VALUES (?,?,?,?,?,?)';