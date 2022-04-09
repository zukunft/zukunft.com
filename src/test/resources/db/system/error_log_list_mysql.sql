PREPARE system_error_log_list_by_all FROM
   'SELECT s.sys_log_id,
           s.user_id,
           s.solver_id,
           s.sys_log_time,
           s.sys_log_type_id,
           s.sys_log_function_id,
           s.sys_log_text,
           s.sys_log_trace,
           s.sys_log_status_id,
           l.sys_log_function_name,
           l2.type_name,
           l3.user_name,
           l4.user_name AS solver_name
    FROM sys_log s
             LEFT JOIN sys_log_functions l ON s.sys_log_function_id = l.sys_log_function_id
             LEFT JOIN sys_log_status l2 ON s.sys_log_status_id = l2.sys_log_status_id
             LEFT JOIN users l3 ON s.user_id = l3.user_id
             LEFT JOIN users l4 ON s.solver_id = l4.user_id
    WHERE (s.sys_log_status_id <> 3 OR s.sys_log_status_id IS NULL)
    ORDER BY s.sys_log_time DESC
    LIMIT ?';