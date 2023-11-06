PREPARE value_prime_insert FROM
    'INSERT INTO values_prime
                 (group_id, user_id, numeric_value, last_update)
          VALUES (?, ?, ?, Now())';