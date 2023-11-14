PREPARE group_prime_upd_group_name FROM
    'UPDATE groups_prime
        SET group_name = ?
      WHERE group_id = ?';
