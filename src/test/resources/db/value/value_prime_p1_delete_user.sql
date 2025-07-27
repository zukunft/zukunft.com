PREPARE value_prime_p1_delete_user (smallint, smallint, smallint, smallint, bigint, bigint) AS
    DELETE FROM user_values_prime
     WHERE phrase_id_1 = $1
       AND phrase_id_2 = $2
       AND phrase_id_3 = $3
       AND phrase_id_4 = $4
       AND user_id = $5
       AND source_id = $6;