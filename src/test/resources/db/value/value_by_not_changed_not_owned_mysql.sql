PREPARE value_by_not_changed_not_owned FROM
    'SELECT user_id
       FROM user_values_prime
      WHERE phrase_id_1 = ?
        AND (excluded <> 1 OR excluded is NULL)
        AND user_id <> ?';
