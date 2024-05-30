PREPARE value_insert_user (text, bigint) AS
    INSERT INTO user_values
                (group_id, user_id)
         VALUES ($1, $2)
    RETURNING group_id;