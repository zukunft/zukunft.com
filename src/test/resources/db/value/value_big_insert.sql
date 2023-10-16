PREPARE value_big_insert (text, bigint, numeric, timestamp) AS
    INSERT INTO values_big
                (group_id, user_id, numeric_value, last_update)
         VALUES ($1, $2, $3, $4);