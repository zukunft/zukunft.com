PREPARE job_update_155111111550 FROM
    'UPDATE jobs
        SET job_id = ?,
            job_type_id = ?,
            job_status_id = ?,
            request_time = ?,
            start_time = ?,
            end_time = ?,
            parameter = ?,
            change_field_id = ?,
            row_id = ?,
            source_id = ?,
            ref_id = ?
      WHERE job_id = ?';