PREPARE ip_range_list_by_active (bigint) AS
    SELECT user_blocked_id,
           ip_from,
           ip_to,
           reason,
           is_active
      FROM user_blocked_ips
     WHERE is_active = $1;