PREPARE ip_range_by_id (bigint) AS
    SELECT ip_range_id,
           ip_from,
           ip_to,
           reason,
           is_active
    FROM ip_ranges
    WHERE ip_range_id = $1;
