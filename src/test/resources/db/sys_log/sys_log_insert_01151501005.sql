PREPARE sys_log_insert_01151501005 (timestamp, bigint, smallint, text, smallint, text, smallint) AS
    INSERT INTO sys_log (sys_log_time, user_id, sys_log_function_id, sys_log_trace, sys_log_level_id, sys_log_text, sys_log_status_id)
         VALUES         ($1,$2,$3,$4,$5,$6,$7)
      RETURNING sys_log_id;