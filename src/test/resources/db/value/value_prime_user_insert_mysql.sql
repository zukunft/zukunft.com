PREPARE value_prime_user_insert FROM
    'INSERT INTO user_values_prime
                 (group_id, user_id)
          VALUES (?, ?)';