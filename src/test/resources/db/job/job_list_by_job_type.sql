PREPARE job_list_by_job_type (bigint, bigint, bigint) AS
    SELECT job_id,
           request_time,
           start_time,
           end_time,
           job_type_id,
           row_id,
           change_field_id
      FROM jobs
     WHERE job_type_id = $1
     LIMIT $2
    OFFSET $3;