PREPARE job_list_by_user (bigint, bigint, bigint) AS
    SELECT job_id,
           user_id,
           job_type_id,
           job_status_id,
           request_time,
           start_time,
           end_time,
           parameter,
           change_field_id,
           row_id,
           source_id,
           ref_id,
           priority
      FROM jobs
     WHERE user_id = $1
     LIMIT $2
    OFFSET $3;
