PREPARE system_time_insert_011111 FROM
    'INSERT INTO system_times (start_time, end_time, system_time_type_id, `url`, milliseconds)
          VALUES              (?, ?, ?, ?, ?)';