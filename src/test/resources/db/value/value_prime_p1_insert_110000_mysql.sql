PREPARE value_prime_p1_insert_110000 FROM
    'INSERT INTO values_prime
                 (phrase_id_1, user_id, numeric_value, last_update)
          VALUES (?, ?, ?, Now())';