PREPARE value_time_series_prime_p1_by_grp FROM
   'SELECT     s.phrase_id_1,
               s.phrase_id_2,
               s.phrase_id_3,
               s.phrase_id_4,
               s.user_id,
               s.value_time_series_id,
               IF(u.source_id  IS NULL, s.source_id,  u.source_id)  AS source_id,
               IF(u.excluded   IS NULL, s.excluded,   u.excluded)   AS excluded,
               IF(u.protect_id IS NULL, s.protect_id, u.protect_id) AS protect_id
          FROM values_time_series_prime s
     LEFT JOIN user_values_time_series_prime u ON s.phrase_id_1 = u.phrase_id_1
                                              AND s.phrase_id_2 = u.phrase_id_2
                                              AND s.phrase_id_3 = u.phrase_id_3
                                              AND s.phrase_id_4 = u.phrase_id_4
                                              AND u.user_id = ?
         WHERE s.phrase_id_1 = ?
           AND s.phrase_id_2 = ?
           AND s.phrase_id_3 = ?
           AND s.phrase_id_4 = ?';