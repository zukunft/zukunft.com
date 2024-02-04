PREPARE value_prime_p1_std_by_id FROM
    'SELECT phrase_id_1,
            phrase_id_2,
            phrase_id_3,
            phrase_id_4,
            numeric_value,
            source_id,
            last_update,
            excluded,
            protect_id,
            user_id
       FROM values_prime
      WHERE phrase_id_1 = ?';