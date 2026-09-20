PREPARE group_prime_insert_010 FROM
    'INSERT INTO groups_prime (group_id, user_id)
          VALUES              (?, ?)';