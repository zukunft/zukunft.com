PREPARE group_prime_insert FROM
    'INSERT INTO groups_prime
                 (group_id, user_id, group_name, description)
          VALUES (?, ?, ?, ?)';