PREPARE ip_range_by_ip_addresses (text, text) AS
    SELECT ip_range_id,
           ip_range_key,
           ip_from,
           ip_to,
           reason,
           is_active
    FROM ip_ranges
    WHERE ip_from = $1
      AND ip_to = $2;
