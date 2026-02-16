PREPARE sys_log_insert_01151501005 FROM
    'INSERT INTO sys_log (sys_log_time, user_id, sys_log_function_id, sys_log_trace, sys_log_level_id, sys_log_text, sys_log_status_id)
          VALUES (?,?,?,?,?,?,?)';