PREPARE value_prime_p1_update_0110000 (numeric, smallint, smallint, smallint, smallint) AS
    UPDATE values_prime
       SET numeric_value = $1,
           last_update = Now()
     WHERE phrase_id_1 = $2
       AND phrase_id_2 = $3
       AND phrase_id_3 = $4
       AND phrase_id_4 = $5;