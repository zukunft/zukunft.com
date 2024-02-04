PREPARE value_prime_p4_user_insert FROM
    'INSERT INTO user_values_prime
                 (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, user_id)
          VALUES (?, ?, ?, ?, ?)';