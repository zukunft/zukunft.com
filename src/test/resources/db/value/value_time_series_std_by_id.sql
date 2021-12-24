PREPARE value_time_series_std_by_id (int) AS
    SELECT value_time_series_id,
           phrase_group_id,
           source_id,
           excluded,
           protection_type_id,
           user_id
      FROM value_time_series
     WHERE value_time_series_id = $1;