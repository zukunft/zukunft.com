PREPARE ip_range_list_by_active (bigint) AS
    SELECT ip_range_id,
           ip_from,
           ip_to,
           reason,
           is_active
      FROM ip_ranges
     WHERE is_active = $1;