PREPARE sys_log_function_id (bigint) AS
    SELECT
           sys_log_function_id,
           sys_log_function_name,
           description,
           code_id
      FROM sys_log_functions
     WHERE sys_log_function_id = $1
  ORDER BY sys_log_function_id;
