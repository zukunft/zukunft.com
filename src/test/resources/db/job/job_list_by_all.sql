PREPARE job_list_by_all (bigint, bigint) AS
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
     LIMIT $1
    OFFSET $2;
