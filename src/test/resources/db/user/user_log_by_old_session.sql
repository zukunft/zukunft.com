PREPARE user_log_by_old_session (timestamp, bigint, bigint) AS
    SELECT     user_log_id,
               user_id,
               session_start,
               session_end,
               requests,
               updated,
               request_time,
               update_time
          FROM user_log
         WHERE session_end < $1
      ORDER BY session_end, user_log_id
         LIMIT $2
        OFFSET $3;
