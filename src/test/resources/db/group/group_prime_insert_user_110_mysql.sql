PREPARE group_prime_insert_user_110 FROM
    'INSERT INTO user_groups_prime
                 (group_id, group_name, user_id)
          VALUES (?, ?, ?)';