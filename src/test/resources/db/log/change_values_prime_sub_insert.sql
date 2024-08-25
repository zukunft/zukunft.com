INSERT INTO change_values_prime
    (user_id,change_action_id,change_field_id,new_value,group_id)
VALUES
    ($1,$2,$3,$4,$5)
RETURNING
    change_id;