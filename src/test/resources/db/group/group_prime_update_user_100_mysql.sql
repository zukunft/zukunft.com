PREPARE group_prime_update_user_100 FROM
    'UPDATE user_groups_prime
        SET group_name = ?
      WHERE group_id = ?';
