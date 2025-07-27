PREPARE value_prime_p3_update_21000_user (numeric, smallint, smallint, smallint, smallint, bigint, bigint) AS
    UPDATE user_values_prime
       SET numeric_value = $1, last_update = Now()
     WHERE phrase_id_1 = $2
       AND phrase_id_2 = $3
       AND phrase_id_3 = $4
       AND phrase_id_4 = $5
       AND user_id = $6
       AND source_id = $7;