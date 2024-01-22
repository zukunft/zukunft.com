PREPARE value_prime_p1_by_grp (bigint, bigint) AS
    SELECT s.phrase_id_1,
           s.phrase_id_2,
           s.phrase_id_3,
           s.phrase_id_4,
           s.user_id,
           CASE WHEN (u.numeric_value      IS NULL) THEN s.numeric_value      ELSE u.numeric_value      END  AS numeric_value,
           CASE WHEN (u.source_id          IS NULL) THEN s.source_id          ELSE u.source_id          END  AS source_id,
           CASE WHEN (u.last_update        IS NULL) THEN s.last_update        ELSE u.last_update        END  AS last_update,
           CASE WHEN (u.excluded           IS NULL) THEN s.excluded           ELSE u.excluded           END  AS excluded,
           CASE WHEN (u.protect_id         IS NULL) THEN s.protect_id         ELSE u.protect_id         END  AS protect_id,
           u.user_id AS change_user_id,
           u.share_type_id
      FROM values_prime s
 LEFT JOIN user_values_prime u
        ON s.phrase_id_1 = u.phrase_id_1
       AND s.phrase_id_2 = u.phrase_id_2
       AND s.phrase_id_3 = u.phrase_id_3
       AND s.phrase_id_4 = u.phrase_id_4
       AND u.user_id = $1
     WHERE s.phrase_id_1 = $2;
