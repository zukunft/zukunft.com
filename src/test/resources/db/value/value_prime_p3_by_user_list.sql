PREPARE value_prime_p3_by_user_list (bigint, bigint, bigint, bigint, bigint) AS
    SELECT     s.phrase_id_1,
               s.phrase_id_2,
               s.phrase_id_3,
               s.phrase_id_4,
               l.user_id,
               l.user_name,
               l.code_id,
               l.ip_address,
               l.email,
               l.first_name,
               l.last_name,
               l.term_id,
               l.view_id,
               l.source_id,
               l.user_profile_id
          FROM user_values_prime s
     LEFT JOIN users l ON s.user_id = l.user_id
         WHERE s.phrase_id_1 = $1
           AND s.phrase_id_2 = $2
           AND s.phrase_id_3 = $3
           AND s.phrase_id_4 = $4
           AND ( s.excluded <> $5 OR s.excluded IS NULL );