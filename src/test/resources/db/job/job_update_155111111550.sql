PREPARE job_update_155111111550
        (bigint, smallint, smallint, timestamp, timestamp, timestamp, bigint, bigint, bigint, bigint, bigint, bigint) AS
    UPDATE jobs
       SET job_id = $1,
           job_type_id = $2,
           job_status_id = $3,
           request_time = $4,
           start_time = $5,
           end_time = $6,
           parameter = $7,
           change_field_id = $8,
           row_id = $9,
           source_id = $10,
           ref_id = $11
     WHERE job_id = $12;