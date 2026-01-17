PREPARE job_insert_055110000000 (smallint,smallint,timestamp,timestamp) AS
    INSERT INTO jobs (job_type_id,job_status_id,request_time,start_time)
         VALUES      ($1,$2,$3,$4)
      RETURNING job_id;