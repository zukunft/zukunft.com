PREPARE ip_range_by_id FROM
   'SELECT user_blocked_id,
           ip_from,
           ip_to,
           reason,
           is_active
    FROM user_blocked_ips
    WHERE user_blocked_id = ?';