PREPARE group_prime_update_100 FROM
    'UPDATE groups_prime
        SET group_name = ?
      WHERE group_id = ?';
