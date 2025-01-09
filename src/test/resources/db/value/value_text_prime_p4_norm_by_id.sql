PREPARE value_text_prime_p4_norm_by_id (bigint, bigint, bigint, bigint) AS
    SELECT phrase_id_1,
           phrase_id_2,
           phrase_id_3,
           phrase_id_4,
           text_value,
           source_id,
           last_update,
           excluded,
           protect_id,
           user_id
    FROM values_text_prime
    WHERE phrase_id_1 = $1
      AND phrase_id_2 = $2
      AND phrase_id_3 = $3
      AND phrase_id_4 = $4;