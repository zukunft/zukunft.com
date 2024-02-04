PREPARE value_by_not_changed FROM
    'SELECT user_id
       FROM user_values_prime
      WHERE phrase_id_1 = ?
        AND phrase_id_2 = ?
        AND phrase_id_3 = ?
        AND phrase_id_4 = ?
        AND (excluded <> 1 OR excluded is NULL)';