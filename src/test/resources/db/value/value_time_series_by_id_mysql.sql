PREPARE value_time_series_by_id FROM
   'SELECT     s.group_id,
               u.group_id AS user_group_id,
               s.user_id,
               s.value_time_series_id,
               IF(u.source_id  IS NULL, s.source_id,  u.source_id)  AS source_id,
               IF(u.excluded   IS NULL, s.excluded,   u.excluded)   AS excluded,
               IF(u.protect_id IS NULL, s.protect_id, u.protect_id) AS protect_id
          FROM values_time_series s
     LEFT JOIN user_values_time_series u ON s.group_id = u.group_id
                                        AND u.user_id = ?
         WHERE s.group_id = ?';