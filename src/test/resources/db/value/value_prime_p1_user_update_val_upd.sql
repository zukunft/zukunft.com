PREPARE value_prime_p1_user_update_val_upd (numeric, bigint) AS
    UPDATE user_values_prime
       SET numeric_value = $1, last_update = Now()
     WHERE phrase_id_1 = $2;