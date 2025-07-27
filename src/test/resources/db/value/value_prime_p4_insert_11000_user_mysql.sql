PREPARE value_prime_p4_insert_11000_user FROM
    'INSERT INTO user_values_prime
                 (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, user_id, source_id, numeric_value, last_update)
          VALUES (?, ?, ?, ?, ?, ?, ?, Now())';