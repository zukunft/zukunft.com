PREPARE user_log_by_open_session FROM
   'SELECT     user_log_id,
               user_id,
               session_start,
               session_end,
               requests,
               updated,
               request_time,
               update_time
          FROM user_log
         WHERE user_id = ?
           AND session_end >= ?
      ORDER BY session_end DESC
         LIMIT ?
        OFFSET ?';
