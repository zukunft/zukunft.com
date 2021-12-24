PREPARE value_time_series_by_id FROM
    'SELECT
            s.value_time_series_id,
            u.value_time_series_id AS user_value_time_series_id,
            s.user_id, s.phrase_group_id,
            IF(u.source_id          IS NULL, s.source_id,          u.source_id)          AS source_id,
            IF(u.excluded           IS NULL, s.excluded,           u.excluded)           AS excluded,
            IF(u.protection_type_id IS NULL, s.protection_type_id, u.protection_type_id) AS protection_type_id,
            u.share_type_id
       FROM value_time_series s
  LEFT JOIN user_value_time_series u ON s.value_time_series_id = u.value_time_series_id
                                    AND u.user_id = ?
      WHERE s.value_time_series_id = ?';