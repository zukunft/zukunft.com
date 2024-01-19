PREPARE value_prime_user_delete_excluded (bigint, bigint, bigint, bigint) AS
    DELETE FROM user_values_prime
     WHERE phrase_id_1 = $1
       AND phrase_id_2 = $2
       AND phrase_id_3 = $3
       AND phrase_id_4 = $4
       AND excluded = 1;