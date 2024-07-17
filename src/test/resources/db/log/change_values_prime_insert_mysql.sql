PREPARE change_values_prime_insert FROM
    'INSERT INTO change_values_prime
        (user_id,change_action_id,change_field_id,old_value,new_value,group_id)
     VALUES
        (?,?,?,?,?,?)';