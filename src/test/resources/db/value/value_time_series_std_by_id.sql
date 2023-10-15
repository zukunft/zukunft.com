PREPARE value_time_series_std_by_id (bigint) AS
    SELECT value_time_series_id,
           user_id,
           group_id,
           source_id,
           excluded,
           protect_id
      FROM value_time_series
     WHERE value_time_series_id = $1;