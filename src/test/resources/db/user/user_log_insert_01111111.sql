PREPARE user_log_insert_01111111 (bigint, timestamp, timestamp, bigint, bigint, bigint, bigint) AS
    INSERT INTO user_log (user_id, session_start, session_end, requests, updated, request_time, update_time)
         VALUES          ($1,$2,$3,$4,$5,$6,$7)
      RETURNING user_log_id;
