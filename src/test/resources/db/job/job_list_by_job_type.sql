PREPARE job_list_by_job_type (bigint, bigint, bigint) AS
    SELECT calc_and_cleanup_task_id,
           request_time,
           start_time,
           end_time,
           calc_and_cleanup_task_type_id,
           row_id,
           change_field_id
      FROM calc_and_cleanup_tasks
     WHERE calc_and_cleanup_task_type_id = $1
     LIMIT $2
    OFFSET $3;