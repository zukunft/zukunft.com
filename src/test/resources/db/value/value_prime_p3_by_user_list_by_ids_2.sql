PREPARE value_prime_p3_by_user_list_by_ids_2 (bigint, bigint, bigint, bigint, bigint, bigint, bigint, bigint) AS
    SELECT     s.phrase_id_1,
               s.phrase_id_2,
               s.phrase_id_3,
               s.phrase_id_4,
               s.excluded,
               l.user_id,
               l.user_name
          FROM user_values_prime s
     LEFT JOIN users l ON s.user_id = l.user_id
         WHERE s.phrase_id_1 = $1
           AND s.phrase_id_2 = $2
           AND s.phrase_id_3 = $3
           AND s.phrase_id_4 = $4

  UNION SELECT s.phrase_id_1,
               s.phrase_id_2,
               s.phrase_id_3,
               s.phrase_id_4,
               s.excluded,
               l.user_id,
               l.user_name
          FROM user_values_prime s
     LEFT JOIN users l ON s.user_id = l.user_id
         WHERE s.phrase_id_1 = $5
           AND s.phrase_id_2 = $6
           AND s.phrase_id_3 = $7
           AND s.phrase_id_4 = $8;