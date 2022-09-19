PREPARE ip_range_by_id (int) AS
    SELECT user_blocked_id,
           ip_from,
           ip_to,
           reason,
           is_active
    FROM user_blocked_ips
    WHERE user_blocked_id = $1;
