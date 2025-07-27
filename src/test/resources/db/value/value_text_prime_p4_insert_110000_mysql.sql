PREPARE value_text_prime_p4_insert_110000 FROM
    'INSERT INTO values_text_prime
                 (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, user_id, text_value, last_update)
          VALUES (?, ?, ?, ?, ?, ?, Now())';