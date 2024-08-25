PREPARE value_big_insert_110000_user (text, bigint, numeric) AS
    INSERT INTO user_values_big (group_id, user_id, numeric_value, last_update)
         VALUES ($1, $2, $3, Now())
      RETURNING group_id;