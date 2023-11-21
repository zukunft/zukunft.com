PREPARE value_p1_insert FROM
    'INSERT INTO values_prime
                 (phrase_id_1, user_id, numeric_value, last_update)
          VALUES (?, ?, ?, Now())';