PREPARE job_list_by_job_type FROM
    'SELECT job_id,
            request_time,
            start_time,
            end_time,
            job_type_id,
            row_id,
            change_field_id
       FROM jobs
      WHERE job_type_id = ?
      LIMIT ?
     OFFSET ?';