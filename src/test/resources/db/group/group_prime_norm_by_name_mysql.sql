PREPARE group_prime_norm_by_name FROM
    'SELECT group_id,
            group_name,
            description,
            user_id
       FROM groups_prime
      WHERE group_name = ?';
