PREPARE sys_log_status_all (bigint, bigint) AS
    SELECT
           sys_log_status_id,
           status_name,
           description,
           code_id
      FROM sys_log_statuus
  ORDER BY sys_log_status_id
     LIMIT $1
    OFFSET $2;
