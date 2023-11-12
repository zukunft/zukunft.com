PREPARE value_prime_user_update_val_upd (numeric, bigint) AS
    UPDATE user_values_prime
       SET numeric_value = $1, last_update = Now()
     WHERE group_id = $2;