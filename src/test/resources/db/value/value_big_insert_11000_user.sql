PREPARE value_big_insert_11000_user (text, bigint, bigint, numeric) AS
    INSERT INTO user_values_big (group_id, user_id, source_id, numeric_value, last_update)
         VALUES ($1, $2, $3, $4, Now())
      RETURNING group_id;