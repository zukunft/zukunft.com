PREPARE job_insert_055110000000 FROM
    'INSERT INTO jobs (job_type_id,job_status_id,request_time,start_time)
          VALUES (?,?,?,?)';