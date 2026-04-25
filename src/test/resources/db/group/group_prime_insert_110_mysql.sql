PREPARE group_prime_insert_110 FROM
    'INSERT INTO groups_prime
                 (group_id, group_name, user_id)
          VALUES (?, ?, ?)';