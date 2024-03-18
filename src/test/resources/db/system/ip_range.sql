PREPARE ip_range_by_range (text, text) AS
    SELECT ip_range_id,
           ip_from,
           ip_to,
           reason,
           is_active
    FROM ip_ranges
    WHERE ip_from = $1
      and ip_to = $2;
