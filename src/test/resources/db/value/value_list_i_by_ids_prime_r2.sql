PREPARE value_list_i_by_ids_prime_r2 (bigint,bigint,bigint,bigint,bigint,bigint,bigint,bigint,bigint) AS
    SELECT s.phrase_id_1,
           s.phrase_id_2,
           s.phrase_id_3,
           s.phrase_id_4,
           s.user_id,
           CASE WHEN (u.time_value  IS NULL) THEN s.time_value  ELSE u.time_value  END AS time_value,
           CASE WHEN (u.source_id   IS NULL) THEN s.source_id   ELSE u.source_id   END AS source_id,
           CASE WHEN (u.last_update IS NULL) THEN s.last_update ELSE u.last_update END AS last_update,
           CASE WHEN (u.excluded    IS NULL) THEN s.excluded    ELSE u.excluded    END AS excluded,
           CASE WHEN (u.protect_id  IS NULL) THEN s.protect_id  ELSE u.protect_id  END AS protect_id,
           u.user_id AS change_user_id,
           u.share_type_id
      FROM values_time_prime s
 LEFT JOIN user_values_time_prime u
        ON s.phrase_id_1 = u.phrase_id_1
       AND s.phrase_id_2 = u.phrase_id_2
       AND s.phrase_id_3 = u.phrase_id_3
       AND s.phrase_id_4 = u.phrase_id_4
       AND u.user_id = $1
     WHERE s.phrase_id_1 = $2 AND s.phrase_id_2 = $3 AND s.phrase_id_3 = $4 AND s.phrase_id_4 = $5
UNION
    SELECT s.phrase_id_1,
           s.phrase_id_2,
           s.phrase_id_3,
           s.phrase_id_4,
           s.user_id,
           CASE WHEN (u.time_value  IS NULL) THEN s.time_value  ELSE u.time_value  END AS time_value,
           CASE WHEN (u.source_id   IS NULL) THEN s.source_id   ELSE u.source_id   END AS source_id,
           CASE WHEN (u.last_update IS NULL) THEN s.last_update ELSE u.last_update END AS last_update,
           CASE WHEN (u.excluded    IS NULL) THEN s.excluded    ELSE u.excluded    END AS excluded,
           CASE WHEN (u.protect_id  IS NULL) THEN s.protect_id  ELSE u.protect_id  END AS protect_id,
           u.user_id AS change_user_id,
           u.share_type_id
      FROM values_time_prime s
 LEFT JOIN user_values_time_prime u
        ON s.phrase_id_1 = u.phrase_id_1
       AND s.phrase_id_2 = u.phrase_id_2
       AND s.phrase_id_3 = u.phrase_id_3
       AND s.phrase_id_4 = u.phrase_id_4
       AND u.user_id = $1
    WHERE s.phrase_id_1 = $6 AND s.phrase_id_2 = $7 AND s.phrase_id_3 = $8 AND s.phrase_id_4 = $9;
