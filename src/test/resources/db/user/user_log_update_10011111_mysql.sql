PREPARE user_log_update_10011111 FROM
    'UPDATE user_log
        SET user_log_id  = ?,
            session_end  = ?,
            requests     = requests + ?,
            updated      = updated + ?,
            request_time = request_time + ?,
            update_time  = update_time + ?
      WHERE user_log_id = ?';
