PREPARE user_log_update_10011111 (bigint, timestamp, bigint, bigint, bigint, bigint, bigint) AS
    UPDATE user_log
       SET user_log_id  = $1,
           session_end  = $2,
           requests     = requests + $3,
           updated      = updated + $4,
           request_time = request_time + $5,
           update_time  = update_time + $6
     WHERE user_log_id = $7;
