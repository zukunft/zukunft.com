PREPARE value_by_not_changed_not_owned (smallint, smallint, smallint, smallint, bigint) AS
    SELECT user_id
      FROM user_values_prime
     WHERE phrase_id_1 = $1
       AND phrase_id_2 = $2
       AND phrase_id_3 = $3
       AND phrase_id_4 = $4
       AND (excluded <> 1 OR excluded is NULL)
       AND user_id <> $5;