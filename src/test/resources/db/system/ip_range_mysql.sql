SELECT user_blocked_id,
       ip_from,
       ip_to,
       reason,
       is_active
FROM user_blocked_ips
WHERE ip_from = '66.249.64.95'
  and ip_to = '66.249.64.95';