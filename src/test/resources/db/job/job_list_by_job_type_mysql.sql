PREPARE job_list_by_job_type FROM
    'SELECT job_id,
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
      WHERE job_type_id = ?
      LIMIT ?
     OFFSET ?';