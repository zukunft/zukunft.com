PREPARE value_by_not_changed (smallint, smallint, smallint, smallint) AS
     SELECT user_id
       FROM user_values_prime
      WHERE phrase_id_1 = $1
        AND phrase_id_2 = $2
        AND phrase_id_3 = $3
        AND phrase_id_4 = $4
        AND (excluded <> 1 OR excluded is NULL);