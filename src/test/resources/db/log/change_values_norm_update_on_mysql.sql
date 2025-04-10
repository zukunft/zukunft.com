PREPARE change_values_norm_update_on FROM
    'INSERT INTO change_values_norm
        (user_id,change_action_id,change_field_id,old_value,new_value,group_id)
     VALUES
        (?,?,?,?,?,?)';