PREPARE value_insert_110000 (text, bigint, numeric) AS
    INSERT INTO values
                (group_id, user_id, numeric_value, last_update)
         VALUES ($1, $2, $3, Now())
    RETURNING group_id;