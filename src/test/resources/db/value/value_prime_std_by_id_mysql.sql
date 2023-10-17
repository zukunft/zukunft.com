PREPARE value_prime_std_by_id FROM
    'SELECT
            group_id,
            numeric_value,
            source_id,
            last_update,
            excluded,
            protect_id,
            user_id
       FROM values_prime
      WHERE group_id = ?';