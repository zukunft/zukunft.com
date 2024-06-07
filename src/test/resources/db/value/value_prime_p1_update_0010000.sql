PREPARE value_prime_p1_update_0010000 (smallint, smallint, smallint, smallint) AS
    UPDATE values_prime
       SET last_update = Now()
     WHERE phrase_id_1 = $1
       AND phrase_id_2 = $2
       AND phrase_id_3 = $3
       AND phrase_id_4 = $4;