PREPARE ip_range_by_range FROM
   'SELECT ip_range_id,
           ip_from,
           ip_to,
           reason,
           is_active
    FROM ip_ranges
    WHERE ip_from = ?
      and ip_to = ?';