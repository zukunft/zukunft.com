PREPARE sys_log_status_all FROM
    'SELECT
            sys_log_status_id,
            type_name,
            description,
            code_id
       FROM sys_log_status
   ORDER BY sys_log_status_id
      LIMIT ?
     OFFSET ?';