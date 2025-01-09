PREPARE value_text_prime_p4_update_0210000 FROM
    'UPDATE values_text_prime
        SET text_value = ?,
            last_update = Now()
      WHERE phrase_id_1 = ?
        AND phrase_id_2 = ?
        AND phrase_id_3 = ?
        AND phrase_id_4 = ?';
