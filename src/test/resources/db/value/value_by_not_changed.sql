PREPARE value_by_not_changed (bigint) AS
     SELECT user_id
       FROM user_values_prime
      WHERE phrase_id_1 = $1
        AND (excluded <> 1 OR excluded is NULL);