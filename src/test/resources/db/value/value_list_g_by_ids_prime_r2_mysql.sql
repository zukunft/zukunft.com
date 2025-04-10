PREPARE value_list_g_by_ids_prime_r2 FROM
   'SELECT s.phrase_id_1,
           s.phrase_id_2,
           s.phrase_id_3,
           s.phrase_id_4,
           s.user_id,
           IF(u.source_id   IS NULL, s.source_id,   u.source_id)   AS source_id,
           IF(u.last_update IS NULL, s.last_update, u.last_update) AS last_update,
           IF(u.excluded    IS NULL, s.excluded,    u.excluded)    AS excluded,
           IF(u.protect_id  IS NULL, s.protect_id,  u.protect_id)  AS protect_id,
           IF(u.geo_value   IS NULL, s.geo_value,   u.geo_value)   AS geo_value,
           u.user_id AS change_user_id,
           u.share_type_id
      FROM values_geo_prime s
 LEFT JOIN user_values_geo_prime u ON s.phrase_id_1 = u.phrase_id_1 AND s.phrase_id_2 = u.phrase_id_2 AND s.phrase_id_3 = u.phrase_id_3 AND s.phrase_id_4 = u.phrase_id_4 AND u.user_id = ?
     WHERE s.phrase_id_1 = ? AND s.phrase_id_2 = ? AND s.phrase_id_3 = ? AND s.phrase_id_4 = ?
  UNION ALL
    SELECT s.phrase_id_1,
           s.phrase_id_2,
           s.phrase_id_3,
           s.phrase_id_4,
           s.user_id,
           IF(u.source_id   IS NULL, s.source_id,   u.source_id)   AS source_id,
           IF(u.last_update IS NULL, s.last_update, u.last_update) AS last_update,
           IF(u.excluded    IS NULL, s.excluded,    u.excluded)    AS excluded,
           IF(u.protect_id  IS NULL, s.protect_id,  u.protect_id)  AS protect_id,
           IF(u.geo_value   IS NULL, s.geo_value,   u.geo_value)   AS geo_value,
           u.user_id AS change_user_id,
           u.share_type_id
      FROM values_geo_prime s
 LEFT JOIN user_values_geo_prime u ON s.phrase_id_1 = u.phrase_id_1 AND s.phrase_id_2 = u.phrase_id_2 AND s.phrase_id_3 = u.phrase_id_3 AND s.phrase_id_4 = u.phrase_id_4 AND u.user_id = ?
     WHERE s.phrase_id_1 = ? AND s.phrase_id_2 = ? AND s.phrase_id_3 = ? AND s.phrase_id_4 = ?';