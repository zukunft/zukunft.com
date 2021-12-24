PREPARE value_time_series_by_id (int, int) AS
    SELECT s.value_time_series_id,
           u.value_time_series_id                                                AS user_value_time_series_id,
           s.user_id,
           s.phrase_group_id,
           CASE WHEN (u.source_id IS NULL) THEN s.source_id ELSE u.source_id END AS source_id,
           CASE WHEN (u.excluded IS NULL) THEN s.excluded ELSE u.excluded END    AS excluded,
           CASE
               WHEN (u.protection_type_id IS NULL) THEN s.protection_type_id
               ELSE u.protection_type_id END                                     AS protection_type_id,
           u.share_type_id
    FROM value_time_series s
             LEFT JOIN user_value_time_series u ON s.value_time_series_id = u.value_time_series_id AND u.user_id = $2
    WHERE s.value_time_series_id = $1;