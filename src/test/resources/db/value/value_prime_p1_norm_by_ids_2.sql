PREPARE value_prime_p1_norm_by_ids_2 (bigint, bigint, bigint, bigint, bigint, bigint, bigint, bigint) AS
    SELECT     phrase_id_1,
               phrase_id_2,
               phrase_id_3,
               phrase_id_4,
               numeric_value,
               source_id,
               last_update,
               excluded,
               protect_id,
               user_id
          FROM values_prime
         WHERE phrase_id_1 = $1
           AND phrase_id_2 = $2
           AND phrase_id_3 = $3
           AND phrase_id_4 = $4

  UNION SELECT phrase_id_1,
               phrase_id_2,
               phrase_id_3,
               phrase_id_4,
               numeric_value,
               source_id,
               last_update,
               excluded,
               protect_id,
               user_id
          FROM values_prime
         WHERE phrase_id_1 = $5
           AND phrase_id_2 = $6
           AND phrase_id_3 = $7
           AND phrase_id_4 = $8;