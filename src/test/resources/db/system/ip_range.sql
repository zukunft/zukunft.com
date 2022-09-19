PREPARE ip_range_by_range (text, text) AS
    SELECT user_blocked_id,
           ip_from,
           ip_to,
           reason,
           is_active
    FROM user_blocked_ips
    WHERE ip_from = $1
      and ip_to = $2;
