PREPARE group_prime_user_upd_group_name FROM
    'UPDATE user_groups_prime
        SET group_name = ?
      WHERE group_id = ?';
