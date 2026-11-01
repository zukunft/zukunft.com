PREPARE user_log_insert_01111111 FROM
    'INSERT INTO user_log (user_id, session_start, session_end, requests, updated, request_time, update_time)
          VALUES          (?, ?, ?, ?, ?, ?, ?)';
