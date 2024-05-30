PREPARE group_prime_update_user_group_name FROM
    'UPDATE user_groups_prime
        SET group_name = ?
      WHERE group_id = ?';
