PREPARE value_insert_110000_user (text, bigint, numeric) AS
    INSERT INTO user_values
                (group_id, user_id, numeric_value, last_update)
         VALUES ($1, $2, $3, Now())
    RETURNING group_id;