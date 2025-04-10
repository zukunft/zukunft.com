PREPARE change_values_prime_insert_n FROM
    'INSERT INTO change_values_prime
        (user_id,change_action_id,change_field_id,new_value,group_id)
     VALUES
        (?,?,?,?,?)';