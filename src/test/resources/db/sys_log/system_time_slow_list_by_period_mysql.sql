PREPARE system_time_slow_list_by_period FROM
   'SELECT     system_time_id,
               start_time,
               end_time,
               system_time_type_id,
               `url`,
               milliseconds
          FROM system_times
         WHERE system_time_type_id = ?
           AND start_time >= ?
           AND milliseconds >= ?
      ORDER BY milliseconds DESC
         LIMIT ?
        OFFSET ?';