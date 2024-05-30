PREPARE value_prime_changer_ex_owner_user FROM
    'SELECT phrase_id_1,
            phrase_id_2,
            phrase_id_3,
            phrase_id_4,
            user_id
       FROM user_values_prime
      WHERE phrase_id_1 = ?
        AND phrase_id_2 = ?
        AND phrase_id_3 = ?
        AND phrase_id_4 = ?
        AND (excluded <> ? OR excluded IS NULL)';