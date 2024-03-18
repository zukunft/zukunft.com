PREPARE ip_range_list_by_active FROM
   'SELECT ip_range_id,
           ip_from,
           ip_to,
           reason,
           is_active
      FROM ip_ranges
     WHERE is_active = ?';