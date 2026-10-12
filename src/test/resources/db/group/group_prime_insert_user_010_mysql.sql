PREPARE group_prime_insert_user_010 FROM
    'INSERT INTO user_groups_prime (group_id, user_id)
          VALUES                   (?, ?)';