PREPARE value_prime_p1_update_val_upd_user (numeric, bigint, bigint, bigint, bigint, bigint) AS
    UPDATE user_values_prime
       SET numeric_value = $1, last_update = Now()
     WHERE phrase_id_1 = $2
       AND phrase_id_2 = $3
       AND phrase_id_3 = $4
       AND phrase_id_4 = $5
       AND user_id = $6;