PREPARE system_time_slow_list_by_period (bigint, timestamp, bigint, bigint, bigint) AS
    SELECT     system_time_id,
               start_time,
               end_time,
               system_time_type_id,
               url,
               milliseconds
          FROM system_times
         WHERE system_time_type_id = $1
           AND start_time >= $2
           AND milliseconds >= $3
      ORDER BY milliseconds DESC
         LIMIT $4
        OFFSET $5;