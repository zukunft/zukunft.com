PREPARE group_prime_by_usr_cfg FROM
    'SELECT group_id,
            group_name,
            description
       FROM user_groups_prime
      WHERE group_id = ?
        AND user_id = ?';