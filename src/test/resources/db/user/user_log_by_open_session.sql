PREPARE user_log_by_open_session (bigint, timestamp, bigint, bigint) AS
    SELECT     user_log_id,
               user_id,
               session_start,
               session_end,
               requests,
               updated,
               request_time,
               update_time
          FROM user_log
         WHERE user_id = $1
           AND session_end >= $2
      ORDER BY session_end DESC
         LIMIT $3
        OFFSET $4;
