PREPARE value_prime_update FROM
    'UPDATE values_prime
        SET numeric_value = ?,last_update = ?
      WHERE group_id = ?';
