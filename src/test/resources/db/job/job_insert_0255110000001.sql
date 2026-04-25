PREPARE job_insert_0255110000001 (bigint,smallint,smallint,timestamp,timestamp,smallint) AS
    INSERT INTO jobs (user_id,job_type_id,job_status_id,request_time,start_time,priority)
         VALUES      ($1,$2,$3,$4,$5,$6)
      RETURNING job_id;