PREPARE value_prime_delete (bigint, bigint, bigint, bigint) AS
    DELETE FROM values_prime
     WHERE phrase_id_1 = $1
       AND phrase_id_2 = $2
       AND phrase_id_3 = $3
       AND phrase_id_4 = $4;