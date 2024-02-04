PREPARE value_time_series_by_id (bigint, text) AS
    SELECT s.value_time_series_id,
           u.value_time_series_id AS user_value_time_series_id,
           s.user_id,
           s.group_id,
           CASE WHEN (u.source_id  IS NULL) THEN s.source_id  ELSE u.source_id  END AS source_id,
           CASE WHEN (u.excluded   IS NULL) THEN s.excluded   ELSE u.excluded   END AS excluded,
           CASE WHEN (u.protect_id IS NULL) THEN s.protect_id ELSE u.protect_id END AS protect_id
      FROM value_time_series s
 LEFT JOIN user_value_time_series u ON s.value_time_series_id = u.value_time_series_id
                                   AND u.user_id = $1
     WHERE s.group_id = $2;