PREPARE value_prime_p1_insert_11000_user FROM
    'INSERT INTO user_values_prime
                 (phrase_id_1, user_id, source_id, numeric_value, last_update)
          VALUES (?, ?, ?, ?, Now())';