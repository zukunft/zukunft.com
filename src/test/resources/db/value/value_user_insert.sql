PREPARE value_user_insert (text, bigint) AS
    INSERT INTO user_values
                (group_id, user_id)
         VALUES ($1, $2)
    RETURNING group_id;