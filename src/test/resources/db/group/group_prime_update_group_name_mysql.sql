PREPARE group_prime_update_group_name FROM
    'UPDATE groups_prime
        SET group_name = ?
      WHERE group_id = ?';
