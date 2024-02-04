PREPARE group_prime_user_insert FROM
    'INSERT INTO user_groups_prime
                 (group_id, user_id, group_name, description)
          VALUES (?, ?, ?, ?)';