PREPARE value_prime_by_changer_ex_owner (bigint, bigint, bigint, bigint, bigint) AS
    SELECT phrase_id_1,
           phrase_id_2,
           phrase_id_3,
           phrase_id_4,
           user_id
      FROM user_values_prime
     WHERE phrase_id_1 = $1
       AND phrase_id_2 = $2
       AND phrase_id_3 = $3
       AND phrase_id_4 = $4
       AND (excluded <> $5 OR excluded IS NULL);