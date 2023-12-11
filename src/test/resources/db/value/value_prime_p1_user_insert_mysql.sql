PREPARE value_prime_p1_user_insert FROM
    'INSERT INTO user_values_prime
                 (phrase_id_1, user_id)
          VALUES (?, ?)';