PREPARE job_insert_0255110000001 FROM
    'INSERT INTO jobs (user_id,job_type_id,job_status_id,request_time,start_time,priority)
          VALUES (?,?,?,?,?,?)';