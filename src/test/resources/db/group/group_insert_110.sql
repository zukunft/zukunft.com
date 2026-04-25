PREPARE group_insert_110 (text, text, bigint) AS
    INSERT INTO groups
                (group_id, group_name, user_id)
         VALUES ($1, $2, $3)
    RETURNING group_id;