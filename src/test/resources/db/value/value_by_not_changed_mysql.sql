PREPARE value_by_not_changed FROM
    'SELECT user_id
       FROM user_values_prime
      WHERE phrase_id_1 = ?
        AND (excluded <> 1 OR excluded is NULL)';