PREPARE value_prime_p3_by_median_user_ex_owner_user (bigint, bigint, bigint, bigint) AS
    SELECT phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, user_id
      FROM user_values_prime
     WHERE phrase_id_1 = $1
       AND phrase_id_2 = $2
       AND phrase_id_3 = $3
       AND phrase_id_4 = $4;