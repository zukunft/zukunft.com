PREPARE value_by_not_changed_not_owned (bigint, bigint) AS
    SELECT user_id
      FROM user_values_prime
     WHERE phrase_id_1 = $1
       AND (excluded <> 1 OR excluded is NULL)
       AND user_id <> $2;