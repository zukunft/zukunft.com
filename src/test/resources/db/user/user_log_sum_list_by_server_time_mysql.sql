PREPARE user_log_sum_list_by_server_time FROM
   'SELECT     s.user_id,
               s.user_name,
               l.requests,
               l.updated,
               l.request_time,
               l.update_time,
               l.server_time
          FROM users s
     LEFT JOIN (SELECT user_id, SUM (requests) AS requests, SUM (updated) AS updated, SUM (request_time) AS request_time, SUM (update_time) AS update_time, SUM (request_time + update_time) AS server_time FROM user_log WHERE session_end >= ? GROUP BY user_id) l
            ON s.user_id = l.user_id
         WHERE l.server_time IS NOT NULL
      ORDER BY l.server_time DESC
         LIMIT ?
        OFFSET ?';